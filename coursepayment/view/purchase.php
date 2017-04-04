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
 * Standalone payment page.
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package   coursepayment
 * @copyright 2017 MoodleFreak.com
 * @author    Luuk Verhoeven
 **/
require_once(dirname(__FILE__) . '/../../../config.php');
require_login();

$courseid = required_param('id', PARAM_INT);
$instanceid = required_param('instanceid', PARAM_INT);
$gateway = required_param('gateway', PARAM_ALPHA);

$PAGE->set_pagelayout('popup');
$PAGE->set_url('/enrol/coursepayment/view/purchase.php', [
    'courseid' => $courseid,
    'instanceid' => $instanceid,
    'gateway' => $gateway,
]);

$context = context_system::instance();
$PAGE->set_context($context);

// Course.
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$PAGE->set_title($course->fullname);

$PAGE->requires->css('/enrol/coursepayment/gateway_' . $gateway . '.css');

// Enrol instance.
$instance = $DB->get_record('enrol', array('id' => $instanceid, 'courseid' => $course->id), '*', MUST_EXIST);

// Protection.
if ($DB->record_exists('user_enrolments', array('userid' => $USER->id, 'enrolid' => $instance->id))) {
    redirect(new moodle_url('/'));
}

if ($instance->enrolstartdate != 0 && $instance->enrolstartdate > time()) {
    redirect(new moodle_url('/'));
}

if ($instance->enrolenddate != 0 && $instance->enrolenddate < time()) {
    redirect(new moodle_url('/'));
}

$cost = (float)($instance->cost <= 0) ? $this->get_config('cost') : $instance->cost;

if (abs($cost) < 0.01 || isguestuser()) { // no cost, other enrolment methods (instances) should be used
    redirect(new moodle_url('/'));
}

$PAGE->requires->js_init_call('M.enrol_coursepayment_mollie_standalone.init', array(
    $CFG->wwwroot . '/enrol/coursepayment/ajax.php',
    sesskey(),
    $course->id
), false, array(
    'name' => 'enrol_coursepayment_mollie_standalone',
    'fullpath' => '/enrol/coursepayment/js/mollie_standalone.js',
    'requires' => array('node', 'io')
));

// Config to send to the gateways
$config = new stdClass();
$config->instanceid = $instance->id;
$config->courseid = $instance->courseid;
$config->userid = $USER->id;
$config->userfullname = fullname($USER);
$config->currency = $instance->currency;
$config->cost = $cost;
$config->instancename = empty($instance->name) ? $course->fullname : $instance->name;
$config->localisedcost = format_float($cost, 2, true);
$config->coursename = $course->fullname;
$config->locale = $USER->lang;
$config->customint1 = $instance->customint1;


echo $OUTPUT->header();

$gateway = 'enrol_coursepayment_' . $gateway;
if (!class_exists($gateway)) {
    throw new Exception('Gateway not exists');
}
/* @var enrol_coursepayment_gateway $gateway */
$gateway = new $gateway();
$gateway->set_instanceconfig($config);
echo $gateway->order_form(true);

echo $OUTPUT->footer();

