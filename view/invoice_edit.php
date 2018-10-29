<?php /** @noinspection ALL */
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
 * Edit invoice details.
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package   enrol_coursepayment
 * @copyright 26-10-2018 MoodleFreak.com
 * @author    Luuk Verhoeven
 **/
require_once(dirname(__FILE__) . '/../../../config.php');
require_login();

$context = context_system::instance();

if (!has_capability('enrol/coursepayment:config', $context)) {
    print_error("error:capability_config", 'enrol_coursepayment');
}
$PAGE->navbar->add(get_string('pluginname', 'enrol_coursepayment'),
    new moodle_url('/admin/settings.php', array('section' => 'enrolsettingscoursepayment')));
$PAGE->navbar->add(get_string('enrol_coursepayment_invoice_edit', 'enrol_coursepayment'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('enrol_coursepayment_invoice_edit', 'enrol_coursepayment'));

$invoicetype = optional_param('invoicetype', 'default', PARAM_ALPHA);
$id = optional_param('id', false, PARAM_INT);
$action = optional_param('action', false, PARAM_ALPHA);

$PAGE->set_url('/enrol/coursepayment/view/invoice_edit.php', array(
    'action' => $invoicetype,
    'id' => $id,
    'action' => $action,
));

switch($action){


    default:
        echo $OUTPUT->header();
        echo $OUTPUT->footer();
        break;
}
