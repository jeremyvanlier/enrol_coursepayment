<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * this is the abstract class for the gateway
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @file      : gateway.php
 * @since     2-3-2015
 * @encoding  : UTF8
 *
 * @package   : enrol_coursepayment
 *
 * @copyright 2015 MoodleFreak.com
 * @author    Luuk Verhoeven
 **/

defined('MOODLE_INTERNAL') || die();

abstract class enrol_coursepayment_gateway {

    const PAYMENT_STATUS_ABORT = 0;
    const PAYMENT_STATUS_SUCCESS = 1;
    const PAYMENT_STATUS_CANCEL = 2;
    const PAYMENT_STATUS_ERROR = 3;
    const PAYMENT_STATUS_WAITING = 4;

    /**
     * name of the gateway
     *
     * @var string
     */
    protected $name = "";

    /**
     * this will contain the gateway their settings
     *
     * @var null|object
     */
    protected $config = null;

    /**
     * cache the config of the plugin complete
     *
     * @var array
     */
    protected $pluginconfig = array();
    /**
     * show more debug messages to the user inline only for testing purposes
     *
     * @var bool
     */
    protected $showdebug = false;

    /**
     * set the gateway on sandbox mode this will be handy for testing purposes !important fake transactions will be enrolled in a course
     *
     * @var bool
     */
    protected $sandbox = false;

    /**
     * log messages
     *
     * @var string
     */
    protected $log = '';

    /**
     * this will contain all values about the course, instance, price
     *
     * @var object
     */
    protected $instanceconfig;


    public function __construct() {
        //load the config always when class is called we will need the settings/credentials
        $this->get_config();
    }

    /**
     * validate if a payment provider has a valid ip address
     *
     * @return boolean
     */
    abstract public function ip_validation();

    /**
     * add new order of a user
     *
     * @return boolean
     */
    abstract public function new_order();

    /**
     * handle the return of payment provider
     *
     * @return boolean
     */
    abstract public function callback();

    /**
     * render the order_form of the gateway to allow order
     *
     * @return string
     */
    abstract public function order_form();

    /**
     * check if a order is valid
     *
     * @param string $orderid
     * @global moodle_database $DB
     * @return array
     */
    public function validate_order($orderid = ''){
        global $DB;
        $row = $DB->get_record('enrol_coursepayment', array('orderid' => $orderid, 'gateway' => $this->name));
        
        if($row){
            if($row->cost == 0){

                //
                $obj = new stdClass();
                $obj->id = $row->id;
                $obj->timeupdated = time();
                $obj->status = self::PAYMENT_STATUS_SUCCESS;
                $DB->update_record('enrol_coursepayment', $obj);

                // this is 0 cost order
                $this->enrol($row);
                return true;
            }
        }
        return false;
    }

    /**
     * add a payment button for this gateway
     *
     * @return string
     */
    public function show_payment_button() {

        if ($this->config->enabled == 0) {
            return '';
        }

        return '<div align="center">
                        <form action="" method="post">
                            <input type="hidden" name="gateway" value="' . $this->name . '"/>
                            <input type="submit" class="form-submit"  value="' . get_string('gateway_' . $this->name . '_send_button', "enrol_coursepayment") . '" />
                        </form>
                </div><hr/>';
    }


    /**
     * load payment provider settings
     */
    public function get_config() {

        $this->pluginconfig = get_config("enrol_coursepayment");

        // used for removing gateway prefix in the plugin
        $stripcount = strlen('gateway_' . $this->name . '_');
        $this->config = new stdClass();

        foreach ($this->pluginconfig as $key => $value) {

            // adding the correct settings to the gateway
            if (stristr($key, 'gateway_' . $this->name . '_')) {
                $k = substr($key, $stripcount);
                $this->config->{$k} = $value;
            }
        }
    }

    /**
     * show_debug
     *
     * @param bool $boolean
     */
    public function show_debug($boolean = false) {
        $this->showdebug = !empty($boolean) ? true : false;
    }

    /**
     * add message to the log
     *
     * @param $var
     */
    protected function log($var) {
        $this->log .= date('d-m-Y H:i:s') . ' | Gateway:' . $this->name . ' = ' . (is_string($var) ? $var : print_r($var, true)) . PHP_EOL;
    }


    /**
     * render log if is enabled in the plugin settings
     */
    function __destruct() {
        if ($this->config->debug == 1 && !empty($this->log)) {
            echo '<pre>';
            print_r($this->log);
            echo '</pre>';
        }
    }

    /**
     * create a new order for a user
     *
     * @param array $data
     *
     * @return array
     */
    protected function create_new_order_record($data = array()) {
        global $DB;

        $cost = $this->instanceconfig->cost;

        $orderidentifier = uniqid(time());

        $obj = new stdClass();


        if(!empty($data['discount'])){
            $discount = $data['discount'];
            $obj->discountdata = serialize($discount);
            // we have discount data
            if($discount->percentage > 0){
                $cost = round($cost / 100 * (100 - $discount->percentage), 2);
            }else{
                $cost = round($cost - $discount->amount);
            }
            // make sure not below 0
            if($cost <= 0){
                $cost = 0;
            }
        }

        $obj->orderid = $orderidentifier;
        $obj->gateway_transaction_id = '';
        $obj->gateway = $this->name;
        $obj->addedon = time();
        $obj->timeupdated = 0;
        $obj->userid = $this->instanceconfig->userid;
        $obj->courseid = $this->instanceconfig->courseid;;
        $obj->instanceid = $this->instanceconfig->instanceid;
        $obj->cost = $cost;
        $obj->status = self::PAYMENT_STATUS_WAITING;
        $id = $DB->insert_record('enrol_coursepayment', $obj);

        return array(
            'orderid' => $orderidentifier,
            'id' => $id,
            'cost' => $cost
        );
    }

    /**
     * set instance config
     *
     * @param object $config
     */
    public function set_instanceconfig($config) {
        $this->instanceconfig = (object)$config;
    }


    /**
     * Enrol a user to the course use enrol_coursepayment record
     *
     * @param object $record
     *
     * @return bool
     * @throws coding_exception
     */
    public function enrol($record = null) {
        global $DB, $CFG;

        require_once($CFG->libdir . '/eventslib.php');
        require_once($CFG->libdir . '/enrollib.php');
        require_once($CFG->libdir . '/filelib.php');

        if (empty($record)) {
            return false;
        }

        $plugin = enrol_get_plugin('coursepayment');

        // first we need all the data to enrol
        $plugininstance = $DB->get_record("enrol", array("id" => $record->instanceid, "status" => 0));
        $user = $DB->get_record("user", array('id' => $record->userid));
        $course = $DB->get_record('course', array('id' => $record->courseid));
        $context = context_course::instance($course->id, IGNORE_MISSING);

        if ($plugininstance->enrolperiod) {
            $timestart = time();
            $timeend = $timestart + $plugininstance->enrolperiod;
        } else {
            $timestart = 0;
            $timeend = 0;
        }

        // Enrol user
        $plugin->enrol_user($plugininstance, $user->id, $plugininstance->roleid, $timestart, $timeend);

        // Pass $view=true to filter hidden caps if the user cannot see them
        if ($users = get_users_by_capability($context, 'moodle/course:update', 'u.*', 'u.id ASC', '', '', '', '', false, true)) {
            $users = sort_by_roleassignment_authority($users, $context);
            $teacher = array_shift($users);
        } else {
            $teacher = false;
        }

        $mailstudents = $plugin->get_config('mailstudents');
        $mailteachers = $plugin->get_config('mailteachers');
        $mailadmins = $plugin->get_config('mailadmins');
        $shortname = format_string($course->shortname, true, array('context' => $context));

        if (!empty($mailstudents)) {
            $a = new stdClass();
            $a->coursename = format_string($course->fullname, true, array('context' => $context));
            $a->profileurl = "$CFG->wwwroot/user/view.php?id=$user->id";

            $eventdata = new stdClass();
            $eventdata->modulename = 'moodle';
            $eventdata->component = 'enrol_coursepayment';
            $eventdata->name = 'coursepayment_enrolment';
            $eventdata->userfrom = empty($teacher) ? get_admin() : $teacher;
            $eventdata->userto = $user;
            $eventdata->subject = get_string("enrolmentnew", 'enrol', $shortname);
            $eventdata->fullmessage = get_string('welcometocoursetext', '', $a);
            $eventdata->fullmessageformat = FORMAT_PLAIN;
            $eventdata->fullmessagehtml = '';
            $eventdata->smallmessage = '';
            message_send($eventdata);

        }

        if (!empty($mailteachers) && !empty($teacher)) {
            $a->course = format_string($course->fullname, true, array('context' => $context));
            $a->user = fullname($user);

            $eventdata = new stdClass();
            $eventdata->modulename = 'moodle';
            $eventdata->component = 'enrol_coursepayment';
            $eventdata->name = 'coursepayment_enrolment';
            $eventdata->userfrom = $user;
            $eventdata->userto = $teacher;
            $eventdata->subject = get_string("enrolmentnew", 'enrol', $shortname);
            $eventdata->fullmessage = get_string('enrolmentnewuser', 'enrol', $a);
            $eventdata->fullmessageformat = FORMAT_PLAIN;
            $eventdata->fullmessagehtml = '';
            $eventdata->smallmessage = '';
            message_send($eventdata);
        }

        if (!empty($mailadmins)) {
            $a->course = format_string($course->fullname, true, array('context' => $context));
            $a->user = fullname($user);
            $admins = get_admins();
            foreach ($admins as $admin) {
                $eventdata = new stdClass();
                $eventdata->modulename = 'moodle';
                $eventdata->component = 'enrol_coursepayment';
                $eventdata->name = 'coursepayment_enrolment';
                $eventdata->userfrom = $user;
                $eventdata->userto = $admin;
                $eventdata->subject = get_string("enrolmentnew", 'enrol', $shortname);
                $eventdata->fullmessage = get_string('enrolmentnewuser', 'enrol', $a);
                $eventdata->fullmessageformat = FORMAT_PLAIN;
                $eventdata->fullmessagehtml = '';
                $eventdata->smallmessage = '';
                message_send($eventdata);
            }
        }
        return true;
    }

    /**
     * add form for when discount code are created
     *
     * @param string $discountcode
     * @param array $status
     *
     * @return string
     * @throws coding_exception
     */
    protected function form_discount_code($discountcode = '' , $status = array()){
        global $DB;
        $string = '';
        // check if there is a discount code
        $row = $DB->get_record('enrol_coursepayment_discount' , array() , 'id' , IGNORE_MULTIPLE);
        if($row){
            $string .= '<hr/>';
            $string .= '<div align="center">
                            <p>'.get_string('discount_code_desc', 'enrol_coursepayment').'<br/>
                            '.((!empty($status['error_discount'])? '<b style="color:red">'.get_string('discountcode_invalid' , 'enrol_coursepayment').'</b>': '')).'<br/>
                            </p>
                            <input type="text" name="discountcode"  value="'.$discountcode.'" />
                        </div>';
        }
        return $string;
    }

}