<?php /** @noinspection ALL */
// This file is part of the customcert module for Moodle - http://moodle.org/
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
 * Edit a customcert element.
 *
 * @package    mod_customcert
 * @copyright  2013 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../../config.php');
defined('MOODLE_INTERNAL') || die;

$tid = required_param('tid', PARAM_INT);
$action = required_param('action', PARAM_ALPHA);

$template = $DB->get_record('coursepayment_templates', array('id' => $tid), '*', MUST_EXIST);

// Set the template object.
$template = new \enrol_coursepayment\invoice\template($template);

// Perform checks.
if ($cm = $template->get_cm()) {
    require_login($cm->course, false, $cm);
} else {
    require_login();
}

// Make sure the user has the required capabilities.
$template->require_manage();

$PAGE->navbar->add(get_string('pluginname', 'enrol_coursepayment'),
    new moodle_url('/admin/settings.php', array('section' => 'enrolsettingscoursepayment')));
$PAGE->navbar->add(get_string('enrol_coursepayment_invoice_edit', 'enrol_coursepayment'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('enrol_coursepayment_invoice_edit', 'enrol_coursepayment'));

$PAGE->set_url('/enrol/coursepayment/view/invoice_edit_element.php', array(
    'tid' => $tid,
    'action' => $action,
));

if ($action == 'edit') {
    // The id of the element must be supplied if we are currently editing one.
    $id = required_param('id', PARAM_INT);
    $element = $DB->get_record('customcert_elements', array('id' => $id), '*', MUST_EXIST);
    $pageurl = new moodle_url('/enrol/coursepayment/view/invoice_edit_element.php', array('id' => $id, 'tid' => $tid, 'action' => $action));
} else { // Must be adding an element.
    // We need to supply what element we want added to what page.
    $pageid = required_param('pageid', PARAM_INT);
    $element = new stdClass();
    $element->element = required_param('element', PARAM_ALPHA);
    $pageurl = new moodle_url('/enrol/coursepayment/view/invoice_edit_element.php', array('tid' => $tid, 'element' => $element->element,
        'pageid' => $pageid, 'action' => $action));
}


// Additional page setup.
if ($template->get_context()->contextlevel == CONTEXT_SYSTEM) {
    $PAGE->navbar->add(get_string('managetemplates', 'enrol_coursepayment'),
        new moodle_url('/enrol/coursepayment/manage_templates.php'));
}

$PAGE->navbar->add(get_string('editinvoice', 'enrol_coursepayment'), new moodle_url('/enrol/coursepayment/edit.php',
    array('tid' => $tid)));
$PAGE->navbar->add(get_string('editelement', 'enrol_coursepayment'));

$mform = new \enrol_coursepayment\invoice\edit_element_form($pageurl, array('element' => $element));

// Check if they cancelled.
if ($mform->is_cancelled()) {
    $url = new moodle_url('/enrol/coursepayment/edit.php', array('tid' => $tid));
    redirect($url);
}

if ($data = $mform->get_data()) {
    // Set the id, or page id depending on if we are editing an element, or adding a new one.
    if ($action == 'edit') {
        $data->id = $id;
    } else {
        $data->pageid = $pageid;
    }
    // Set the element variable.
    $data->element = $element->element;
    // Get an instance of the element class.
    if ($e = \enrol_coursepayment\invoice\element_factory::get_element_instance($data)) {
        $e->save_form_elements($data);
    }

    $url = new moodle_url('/enrol/coursepayment/edit.php', array('tid' => $tid));
    redirect($url);
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
