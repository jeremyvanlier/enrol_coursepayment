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
 *
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package   coursepayment
 * @copyright 2017 MoodleFreak.com
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
$PAGE->navbar->add(get_string('enrol_coursepayment_multi_account', 'enrol_coursepayment'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('enrol_coursepayment_multi_account', 'enrol_coursepayment'));

$action = optional_param('action', false, PARAM_ALPHA);
$id = optional_param('id', false, PARAM_INT);
$PAGE->set_url('/enrol/coursepayment/view/multi_account.php', array(
    'action' => $action,
    'id' => $id,
));

switch ($action) {
    case 'delete':
        $DB->delete_records('coursepayment_multiaccount', ['id' => $id]);

        redirect(new moodle_url('/admin/settings.php', [
            'section' => 'enrolsettingscoursepayment',
            's_enrol_coursepayment_tabs' => 'multiaccount',
        ]));
        break;
    case 'new':
    case 'edit':

        $form = new \enrol_coursepayment\form\multiaccount($PAGE->url);

        if ($form->is_cancelled()) {
            redirect(new moodle_url('/admin/settings.php', [
                'section' => 'enrolsettingscoursepayment',
                's_enrol_coursepayment_tabs' => 'multiaccount',
            ]));
        }

        if ($action == 'edit') {
            // Get multi-account.
            $multiaccount = $DB->get_record('coursepayment_multiaccount', ['id' => $id], '*', MUST_EXIST);
            $form->set_data($multiaccount);
        }

        if (($data = $form->get_data()) != false) {

            if ($id > 0) {
                $data->id = $multiaccount->id;
                $DB->update_record('coursepayment_multiaccount', $data);

            } else {
                $total = $DB->count_records('coursepayment_multiaccount');

                $data->is_default = empty($total) ? 1 : 0;
                $data->added_on = time();
                $DB->insert_record('coursepayment_multiaccount', $data);
            }

            redirect(new moodle_url('/admin/settings.php', [
                'section' => 'enrolsettingscoursepayment',
                's_enrol_coursepayment_tabs' => 'multiaccount',
            ]));
        }

        echo $OUTPUT->header();
        echo $form->render();
        echo $OUTPUT->footer();
        break;
}