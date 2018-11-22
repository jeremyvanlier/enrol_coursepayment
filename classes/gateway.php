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
 * @package   enrol_coursepayment
 * @copyright 2015 MFreak.nl
 * @author    Luuk Verhoeven
 */

use enrol_coursepayment\invoice\template;

defined('MOODLE_INTERNAL') || die();

abstract class enrol_coursepayment_gateway {

    /**
     * Payment is aborted
     *
     * @const PAYMENT_STATUS_ABORT
     */
    const PAYMENT_STATUS_ABORT = 0;

    /**
     * Payment is done successfully
     *
     * @const PAYMENT_STATUS_SUCCESS
     */
    const PAYMENT_STATUS_SUCCESS = 1;

    /**
     * Payment was cancelled
     *
     * @const PAYMENT_STATUS_CANCEL
     */
    const PAYMENT_STATUS_CANCEL = 2;

    /**
     * Payment not finished because of a error/exception
     *
     * @const PAYMENT_STATUS_ERROR
     */
    const PAYMENT_STATUS_ERROR = 3;

    /**
     * Payment is waiting
     *
     * @const PAYMENT_STATUS_WAITING
     */
    const PAYMENT_STATUS_WAITING = 4;

    /**
     * The prefix that would be prepended to invoice number
     *
     * @const INVOICE_PREFIX
     */
    const INVOICE_PREFIX = 'CPAY';

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
     * Cache the config of the plugin complete
     *
     * @var stdClass|bool
     */
    protected $pluginconfig = false;

    /**
     * show more debug messages to the user inline only for testing purposes
     *
     * @var bool
     */
    protected $showdebug = false;

    /**
     * set the gateway on sandbox mode this will be handy for testing purposes !important fake transactions will be
     * enrolled in a course
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
     * Multi-account data
     *
     * @var null
     */
    protected $multiaccount = null;

    /**
     * this will contain all values about the course, instance, price
     *
     * @var object
     */
    protected $instanceconfig;

    public function __construct() {
        // Load the config always when class is called we will need the settings/credentials.
        $this->get_config();
    }

    /**
     * validate if a payment provider has a valid ip address
     *
     * @return boolean
     */
    abstract public function ip_validation();

    /**
     * add new course order from a user
     *
     * @return boolean
     */
    abstract public function new_order_course();

    /**
     * add new activity order from a user
     *
     * @return boolean
     */
    abstract public function new_order_activity();

    /**
     * handle the return of payment provider
     *
     * @return boolean
     */
    abstract public function callback();

    /**
     * render the order_form of the gateway to allow order
     *
     * @param bool $standalone
     *
     * @return string
     */
    abstract public function order_form($standalone = false);

    /**
     * check if a order is valid
     *
     * @param string           $orderid
     *
     * @return bool
     * @throws coding_exception
     * @throws dml_exception
     * @global moodle_database $DB
     */
    public function validate_order($orderid = '') {
        global $DB;
        $row = $DB->get_record('enrol_coursepayment', [
            'orderid' => $orderid,
            'gateway' => $this->name,
        ]);

        if ($row) {
            if ($row->cost == 0) {

                $obj = new stdClass();
                $obj->id = $row->id;
                $obj->timeupdated = time();
                $obj->status = self::PAYMENT_STATUS_SUCCESS;
                $DB->update_record('enrol_coursepayment', $obj);

                // This is 0 cost order.
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
     * @throws coding_exception
     */
    public function show_payment_button() {

        if ($this->config->enabled == 0) {
            return '';
        }

        return '<div align="center"><form action="" method="post"><input type="hidden" name="gateway" value="' . $this->name . '"/>
                            <input type="submit" class="form-submit btn btn-primary coursepayment-btn"  value="' .
            get_string('gateway_' . $this->name . '_send_button', "enrol_coursepayment") . '" />
                        </form></div><hr/>';
    }

    /**
     * load payment provider settings
     */
    protected function get_config() {

        $this->pluginconfig = get_config("enrol_coursepayment");

        // Used for removing gateway prefix in the plugin.
        $stripcount = strlen('gateway_' . $this->name . '_');
        $this->config = new stdClass();

        foreach ($this->pluginconfig as $key => $value) {

            // Adding the correct settings to the gateway.
            if (stristr($key, 'gateway_' . $this->name . '_')) {
                $k = substr($key, $stripcount);
                $this->config->{$k} = $value;
            }
        }

        // Check if we need to override with multi-account data.
        $this->load_multi_account_config();
    }

    /**
     * add message to the log
     *
     * @param $var
     */
    protected function log($var) {

        $this->log .= date('d-m-Y H:i:s') . ' | Gateway:' . $this->name . ' = ' .
            (is_string($var) ? $var : '(object)') . PHP_EOL;
    }

    /**
     * render log if is enabled in the plugin settings
     */
    function __destruct() {
        if (!empty($this->pluginconfig->debug) && !empty($this->log)) {
            echo '<pre>';
            echo($this->log);
            echo '</pre>';
        }
    }

    /**
     * create a new order for a user
     *
     * @param array $data
     *
     * @return array
     * @throws dml_exception
     */
    protected function create_new_course_order_record($data = []) {
        global $DB;

        $cost = $this->instanceconfig->cost;

        $orderidentifier = uniqid(time());

        $obj = new stdClass();

        if (!empty($data['discount'])) {
            $discount = $data['discount'];
            $obj->discountdata = serialize($discount);

            // We have discount data.
            if ($discount->percentage > 0) {
                $cost = round($cost / 100 * (100 - $discount->percentage), 2);
            } else {
                $cost = round($cost - $discount->amount);
            }

            // Make sure not below 0.
            if ($cost <= 0) {
                $cost = 0;
            }
        }

        $obj->orderid = $orderidentifier;
        $obj->gateway_transaction_id = '';
        $obj->invoice_number = 0;
        $obj->gateway = $this->name;
        $obj->addedon = time();
        $obj->timeupdated = 0;
        $obj->userid = $this->instanceconfig->userid;

        if (!empty($this->pluginconfig->multi_account)) {
            $obj->profile_data = enrol_coursepayment_helper::get_profile_field_data($this->pluginconfig->multi_account_fieldid,
                $this->instanceconfig->userid);
        }

        $obj->courseid = $this->instanceconfig->courseid;;
        $obj->instanceid = $this->instanceconfig->instanceid;
        $obj->cost = $cost;
        $obj->vatpercentage = is_numeric($this->instanceconfig->customint1) ? $this->instanceconfig->customint1 :
            $this->pluginconfig->vatpercentage;
        $obj->status = self::PAYMENT_STATUS_WAITING;
        $id = $DB->insert_record('enrol_coursepayment', $obj);

        return [
            'orderid' => $orderidentifier,
            'id' => $id,
            'cost' => $cost,
        ];
    }

    /**
     * Create a new order for a user
     *
     * @param array $data
     *
     * @return array
     * @throws dml_exception
     */
    protected function create_new_activity_order_record($data = []) {
        global $DB;

        $cost = $this->instanceconfig->cost;
        $obj = new stdClass();

        if (!empty($data['discount'])) {
            $discount = $data['discount'];
            $obj->discountdata = serialize($discount);
            // We have discount data.
            if ($discount->percentage > 0) {
                $cost = round($cost / 100 * (100 - $discount->percentage), 2);
            } else {
                $cost = round($cost - $discount->amount);
            }
            // Make sure not below 0.
            if ($cost <= 0) {
                $cost = 0;
            }
        }

        $orderidentifier = uniqid(time());
        $obj->orderid = $orderidentifier;
        $obj->gateway_transaction_id = '';
        $obj->invoice_number = 0;
        $obj->gateway = $this->name;
        $obj->addedon = time();
        $obj->timeupdated = 0;
        $obj->userid = $this->instanceconfig->userid;
        $obj->courseid = $this->instanceconfig->courseid;
        $obj->cmid = $this->instanceconfig->cmid;;
        $obj->instanceid = 0;
        $obj->is_activity = 1;
        $obj->cost = $cost;

        if (!empty($this->pluginconfig->multi_account)) {
            $obj->profile_data = enrol_coursepayment_helper::get_profile_field_data($this->pluginconfig->multi_account_fieldid,
                $this->instanceconfig->userid);
        }

        $obj->vatpercentage = is_numeric($this->instanceconfig->customint1) ? $this->instanceconfig->customint1 :
            $this->pluginconfig->vatpercentage;

        $obj->status = self::PAYMENT_STATUS_WAITING;
        $obj->section = isset($this->instanceconfig->section) ? $this->instanceconfig->section : -10;
        $id = $DB->insert_record('enrol_coursepayment', $obj);

        return [
            'orderid' => $orderidentifier,
            'id' => $id,
            'cost' => $cost,
        ];
    }

    /**
     * Set instance config
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
     * @throws dml_exception
     */
    protected function enrol($record = null) {
        global $DB, $CFG;

        if (empty($record)) {
            return false;
        }

        // Doesn't need a enrolment.
        if ($record->is_activity == 1) {
            return true;
        }

        require_once($CFG->libdir . '/eventslib.php');
        require_once($CFG->libdir . '/enrollib.php');
        require_once($CFG->libdir . '/filelib.php');

        $plugin = enrol_get_plugin('coursepayment');

        // First we need all the data to enrol.
        $plugininstance = $DB->get_record("enrol", ["id" => $record->instanceid, "status" => 0]);
        $user = $DB->get_record("user", ['id' => $record->userid]);
        $course = $DB->get_record('course', ['id' => $record->courseid]);
        $context = context_course::instance($course->id, IGNORE_MISSING);

        if ($plugininstance->enrolperiod) {
            $timestart = time();
            $timeend = $timestart + $plugininstance->enrolperiod;
        } else {
            $timestart = 0;
            $timeend = 0;
        }

        // Enrol user.
        $plugin->enrol_user($plugininstance, $user->id, $plugininstance->roleid, $timestart, $timeend);

        // Send messages about the enrolment.
        $this->enrol_mail($plugin, $course, $context, $user);

        return true;
    }

    /**
     * @param $plugin
     * @param $course
     * @param $context
     *
     * @param $user
     *
     * @throws coding_exception
     */
    protected function enrol_mail($plugin, $course, $context, $user) {
        global $CFG;
        $teacher = false;

        // Pass $view=true to filter hidden caps if the user cannot see them.
        if ($users = get_users_by_capability($context, 'moodle/course:update',
            'u.*', 'u.id ASC', '', '', '', '', false, true)) {
            $users = sort_by_roleassignment_authority($users, $context);
            $teacher = array_shift($users);
        }

        $mailstudents = $plugin->get_config('mailstudents');
        $mailteachers = $plugin->get_config('mailteachers');
        $mailadmins = $plugin->get_config('mailadmins');

        $shortname = format_string($course->shortname, true, ['context' => $context]);

        if (!empty($mailstudents)) {
            $a = new stdClass();
            $a->coursename = format_string($course->fullname, true, ['context' => $context]);
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
            $a->course = format_string($course->fullname, true, ['context' => $context]);
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
            $a->course = format_string($course->fullname, true, ['context' => $context]);
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
    }

    /**
     * Send invoice to the customer, teacher and extra mail-accounts
     *
     * @param \stdClass $coursepayment
     * @param string    $method
     *
     * @return bool
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    protected function send_invoice(\stdClass $coursepayment, $method = '') {
        global $DB, $CFG;

        if (empty($coursepayment)) {
            return false;
        }

        require_once($CFG->libdir . '/eventslib.php');
        require_once($CFG->libdir . '/enrollib.php');
        require_once($CFG->libdir . '/filelib.php');

        $user = $DB->get_record("user", ['id' => $coursepayment->userid]);
        $course = $DB->get_record('course', ['id' => $coursepayment->courseid]);
        $context = context_course::instance($course->id, IGNORE_MISSING);

        $a = $this->get_invoice_strings($user, $course, $coursepayment, $method);

        // Generate PDF invoice.
        $file = template::render($coursepayment, $user, $this->pluginconfig, $a);

        if (!empty($this->pluginconfig->mailstudents_invoice)) {

            $eventdata = new stdClass();
            $eventdata->modulename = 'moodle';
            $eventdata->component = 'enrol_coursepayment';
            $eventdata->name = 'coursepayment_invoice';
            $eventdata->userfrom = core_user::get_support_user();
            $eventdata->userto = $user;
            $eventdata->subject = get_string("mail:invoice_subject", 'enrol_coursepayment', $a);
            $eventdata->fullmessage = html_to_text(get_string('mail:invoice_message', 'enrol_coursepayment', $a));
            $eventdata->fullmessageformat = FORMAT_HTML;
            $eventdata->fullmessagehtml = get_string('mail:invoice_message', 'enrol_coursepayment', $a);
            $eventdata->smallmessage = '';
            $eventdata->attachment = $file;
            $eventdata->attachname = $a->invoice_number . '.pdf';

            message_send($eventdata);
        }

        if (!empty($this->pluginconfig->mailteachers_invoice)) {

            // Getting the teachers
            if ($users = get_users_by_capability($context, 'moodle/course:update', 'u.*', 'u.id ASC',
                '', '', '', '', false, true)) {
                $users = sort_by_roleassignment_authority($users, $context);
                $teacher = array_shift($users);
            } else {
                $teacher = false;
            }

            if (!empty($teacher)) {

                $eventdata = new stdClass();
                $eventdata->modulename = 'moodle';
                $eventdata->component = 'enrol_coursepayment';
                $eventdata->name = 'coursepayment_invoice';
                $eventdata->userfrom = core_user::get_support_user();
                $eventdata->userto = $teacher;
                $eventdata->subject = get_string("mail:invoice_subject", 'enrol_coursepayment', $a);
                $eventdata->fullmessage = html_to_text(get_string('mail:invoice_message', 'enrol_coursepayment', $a));
                $eventdata->fullmessageformat = FORMAT_HTML;
                $eventdata->fullmessagehtml = get_string('mail:invoice_message', 'enrol_coursepayment', $a);
                $eventdata->smallmessage = '';
                $eventdata->attachment = $file;
                $eventdata->attachname = $a->invoice_number . '.pdf';
                message_send($eventdata);
            }
        }

        if (!empty($this->pluginconfig->mailadmins_invoice)) {

            $admins = get_admins();
            foreach ($admins as $admin) {
                $eventdata = new stdClass();
                $eventdata->modulename = 'moodle';
                $eventdata->component = 'enrol_coursepayment';
                $eventdata->name = 'coursepayment_invoice';
                $eventdata->userfrom = core_user::get_support_user();
                $eventdata->userto = $admin;
                $eventdata->subject = get_string("mail:invoice_subject", 'enrol_coursepayment', $a);
                $eventdata->fullmessage = html_to_text(get_string('mail:invoice_message', 'enrol_coursepayment', $a));
                $eventdata->fullmessageformat = FORMAT_HTML;
                $eventdata->fullmessagehtml = get_string('mail:invoice_message', 'enrol_coursepayment', $a);
                $eventdata->smallmessage = '';
                $eventdata->attachment = $file;
                $eventdata->attachname = $a->invoice_number . '.pdf';
                message_send($eventdata);
            }
        }

        if (!empty($this->pluginconfig->custom_mails_invoice)) {
            $parts = explode(',', $this->pluginconfig->custom_mails_invoice);
            foreach ($parts as $part) {
                $part = trim($part);
                if (filter_var($part, FILTER_VALIDATE_EMAIL)) {
                    // Get temp user object.
                    $dummyuser = new stdClass();
                    $dummyuser->id = 1;
                    $dummyuser->email = $part;
                    $dummyuser->firstname = ' ';
                    $dummyuser->username = ' ';
                    $dummyuser->lastname = '';
                    $dummyuser->confirmed = 1;
                    $dummyuser->suspended = 0;
                    $dummyuser->deleted = 0;
                    $dummyuser->picture = 0;
                    $dummyuser->auth = 'manual';
                    $dummyuser->firstnamephonetic = '';
                    $dummyuser->lastnamephonetic = '';
                    $dummyuser->middlename = '';
                    $dummyuser->alternatename = '';
                    $dummyuser->imagealt = '';
                    $dummyuser->emailstop = 0;

                    $eventdata = new stdClass();
                    $eventdata->modulename = 'moodle';
                    $eventdata->component = 'enrol_coursepayment';
                    $eventdata->name = 'coursepayment_invoice';
                    $eventdata->userfrom = core_user::get_support_user();
                    $eventdata->userto = $dummyuser;
                    $eventdata->subject = get_string("mail:invoice_subject", 'enrol_coursepayment', $a);
                    $eventdata->fullmessage = html_to_text(get_string('mail:invoice_message',
                        'enrol_coursepayment', $a));
                    $eventdata->fullmessageformat = FORMAT_HTML;
                    $eventdata->fullmessagehtml = get_string('mail:invoice_message', 'enrol_coursepayment', $a);
                    $eventdata->smallmessage = '';
                    $eventdata->attachment = $file;
                    $eventdata->attachname = $a->invoice_number . '.pdf';
                    message_send($eventdata);
                }
            }
        }

        return true;
    }

    /**
     * add form for when discount code are created
     *
     * @param string $discountcode
     * @param array  $status
     *
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    protected function form_discount_code($discountcode = '', $status = []) {
        global $DB;
        $string = '';

        // Check if there is a discount code.
        $row = $DB->get_record('enrol_coursepayment_discount', [], 'id', IGNORE_MULTIPLE);
        if ($row) {
            $string .= '<hr/>';
            $string .= '<div align="center"><p>' . get_string('discount_code_desc', 'enrol_coursepayment') . '<br/>
                            ' . ((!empty($status['error_discount']) ?
                    '<b style="color:red"  id="error_coursepayment">' . $status['message'] . '</b>' :
                    '<b style="color:red" id="error_coursepayment"></b>')) . '<br/></p>
                            <input type="text" autocomplete="off" name="discountcode" id="discountcode"  
                                value="' . $discountcode . '" /><div id="price_holder"></div>
                        </div>';
        }

        return $string;
    }

    /**
     * Get a new invoice_number
     *
     * @return int
     * @throws dml_exception
     */
    protected function get_new_invoice_number() {
        global $DB;
        $rows = $DB->get_records('enrol_coursepayment', [], 'invoice_number desc', 'invoice_number', 0, 1);
        if ($rows) {
            $row = reset($rows);

            return $row->invoice_number + 1;
        }

        return 1;
    }

    /**
     * Get a nice format invoice number
     *
     * @param object $record
     *
     * @return string
     */
    protected function get_invoice_number_format($record = null) {

        if (!empty($record->invoice_number) && !empty($record->addedon)) {
            return self::INVOICE_PREFIX . date("Y", $record->addedon) . sprintf('%08d',
                    $record->invoice_number);
        }

        return 'TEST';
    }

    /**
     * get_payment_description
     *
     * @param $record
     *
     * @return mixed
     * @throws dml_exception
     */
    protected function get_payment_description($record) {
        global $DB, $SITE;

        $obj = new stdClass();
        $obj->invoice_number = $this->get_invoice_number_format($record);

        // Course.
        $obj->course = $DB->get_field('course', 'fullname', ['id' => $record->courseid]);
        $obj->course_shortname = $DB->get_field('course', 'shortname', ['id' => $record->courseid]);

        // Site.
        $obj->site = $SITE->fullname;
        $obj->site_shortname = $SITE->shortname;

        // Add enrolment instance.
        $enrol = $DB->get_record('enrol', ['id' => $record->instanceid], '*');
        if ($enrol) {
            $obj->customtext1 = $enrol->customtext1;
            $obj->customtext2 = $enrol->customtext2;
        } else {
            $obj->customtext1 = '';
            $obj->customtext2 = '';
        }

        // Fallback prevent Mollie issue.
        if (empty($this->pluginconfig->transaction_name)) {
            $this->pluginconfig->transaction_name = '{invoice_number}';
        }

        return enrol_coursepayment_helper::parse_text($this->pluginconfig->transaction_name, $obj);
    }

    /**
     * get correct number format used for pricing
     *
     * @param float|int $number
     *
     * @return string
     */
    public function price($number = 0.00) {
        return number_format(round($number, 2), 2, ',', ' ');
    }

    /**
     * Add agreement check if needed
     *
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    protected function add_agreement_checkbox() {
        $string = '';

        $agreement = get_config('enrol_coursepayment', 'link_agreement');
        if (!empty($agreement)) {
            $obj = new stdClass();
            $obj->link = $agreement;
            $string .= '<hr/>  <div id="coursepayment_agreement_checkbox">
                <input type="checkbox" name="agreement" id="coursepayment_agreement" required>
                <label for="coursepayment_agreement">' .
                get_string('agreement_label', 'enrol_coursepayment', $obj) .
                '</label></div>';
        }

        return $string;
    }

    /**
     * Load multi-account config if needed
     *
     * @param int    $userid       only needed when running from cron
     * @param string $profilevalue only needed when running from cron
     *
     * @throws dml_exception
     */
    protected function load_multi_account_config($userid = 0, $profilevalue = '') {
        global $USER, $DB;

        // Normally we can $USER only in cron we need to fix this.
        if ($userid == 0) {
            $userid = $USER->id;
        }

        if (!empty($this->pluginconfig->multi_account)) {
            // Check if we match profile value of any of the multi-accounts.
            if (empty($profilevalue)) {
                $profilevalue = enrol_coursepayment_helper::get_profile_field_data($this->pluginconfig->multi_account_fieldid,
                    $userid);
            }

            // Load default multi-account.
            $this->multiaccount = $DB->get_record('coursepayment_multiaccount', ['is_default' => 1],
                '*', MUST_EXIST);

            if (!empty($profilevalue)) {
                // Check if we have a multi-account matching your value.
                $mutiaccount = $DB->get_record('coursepayment_multiaccount', [
                    'profile_value' => $profilevalue,
                ], '*');

                // Found we should use this.
                if (!empty($mutiaccount)) {
                    $this->multiaccount = $mutiaccount;
                }
            }

            // Reset some values that can't be used by multi-account.
            $this->config->enabled = true;
            $this->config->external_connector = 0;

            // Update invoice details.
            $this->pluginconfig->companyname = $this->multiaccount->company_name;
            $this->pluginconfig->address = $this->multiaccount->address;
            $this->pluginconfig->place = $this->multiaccount->place;
            $this->pluginconfig->zipcode = $this->multiaccount->zipcode;
            $this->pluginconfig->kvk = $this->multiaccount->kvk;
            $this->pluginconfig->btw = $this->multiaccount->btw;

            $stripcount = strlen('gateway_' . $this->name . '_');

            // Override the normal settings.
            foreach ($this->multiaccount as $key => $value) {

                // adding the correct settings to the gateway
                if (stristr($key, 'gateway_' . $this->name . '_')) {
                    $k = substr($key, $stripcount);
                    $this->config->{$k} = $value;
                }
            }
        }
    }

    /**
     * Make strings for invoice messages and invoice.
     *
     * @param $user
     * @param $course
     * @param $coursepayment
     *
     * @param $method
     *
     * @return stdClass
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    private function get_invoice_strings($user, $course, $coursepayment, $method) {

        $context = context_course::instance($course->id, IGNORE_MISSING);
        $invoicenumber = $coursepayment->invoice_number;

        // Mail object.
        $a = new stdClass();
        $a->course = format_string($course->fullname, true, ['context' => $context]);
        $a->fullname = fullname($user);
        $a->email = $user->email;
        $a->date = date('d-m-Y, H:i', $coursepayment->addedon);

        // Fix this could also be a activity or section.
        if ($coursepayment->cmid > 0 && $coursepayment->is_activity == 1) {
            $module = enrol_coursepayment_helper::get_cmid_info($coursepayment->cmid, $course->id);
            $a->fullcourse = $module->name;
            $a->content_type = get_string('activity');
        } else if ($coursepayment->section > 0) {
            $module = enrol_coursepayment_helper::get_section_info($coursepayment->section, $course->id);
            $a->fullcourse = $module->name;
            $a->content_type = get_string('section');
        } else {
            $a->fullcourse = $course->fullname;
            $a->content_type = get_string('course');
        }

        // Set record invoice number this is not done.
        if ($coursepayment->invoice_number == 0) {
            $coursepayment->invoice_number = $invoicenumber;
        }

        $a->invoice_number = $this->get_invoice_number_format($coursepayment);

        // Company data.
        $a->companyname = $this->pluginconfig->companyname;
        $a->address = $this->pluginconfig->address;
        $a->address = $this->pluginconfig->address;
        $a->place = $this->pluginconfig->place;
        $a->zipcode = $this->pluginconfig->zipcode;
        $a->kvk = $this->pluginconfig->kvk;
        $a->btw = $this->pluginconfig->btw;
        $a->currency = $this->pluginconfig->currency;
        $a->method = $method;
        $a->description = $this->get_payment_description($coursepayment);

        // Calculate cost.
        $a->vatpercentage = $coursepayment->vatpercentage;

        $vatprice = ($coursepayment->cost / (100 + $a->vatpercentage)) * $a->vatpercentage;

        $a->costvat = $this->price($vatprice);
        $a->cost = $this->price($coursepayment->cost);
        $a->costsub = $this->price($coursepayment->cost - $vatprice);

        return $a;
    }
}