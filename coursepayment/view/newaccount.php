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
 * Add a new account on mollie
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package   enrol_coursepayment
 * @copyright 2017 MoodleFreak.com
 * @author    Luuk Verhoeven
 */

require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->libdir . '/tablelib.php');
require_login();

$context = context_system::instance();

if (!has_capability('enrol/coursepayment:config', $context)) {
    print_error("error:capability_config", 'enrol_coursepayment');
}
// Get plugin config.
$config = get_config('enrol_coursepayment');

if (empty($config->gateway_mollie_parent_api)) {
    throw new Exception('Error: gateway_mollie_parent_api is missing!');
}

// set navbar
$PAGE->navbar->add(get_string('pluginname', 'enrol_coursepayment'), new moodle_url('/admin/settings.php', array('section' => 'enrolsettingscoursepayment')));
$PAGE->navbar->add(get_string('enrol_coursepayment_newaccount', 'enrol_coursepayment'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('enrol_coursepayment_newaccount', 'enrol_coursepayment'));

$PAGE->requires->js('/enrol/coursepayment/js/helper.js');

$PAGE->set_url('/enrol/coursepayment/view/newaccount.php');

$form = new \enrol_coursepayment\form\newaccount($PAGE->url);

// cancel form
if ($form->is_cancelled()) {
    redirect(new \moodle_url('/admin/settings.php?section=enrolsettingscoursepayment'));
}

if (($data = $form->get_data()) != false) {

    // Send to your parent.
    $response = enrol_coursepayment_helper::post_request($config->gateway_mollie_parent_api, [
        'data' => serialize($data),
        'action' => 'newaccount'
    ]);

    $response = json_decode($response);

    if(!empty($response->error)){
        throw new Exception($response->error);
    }

    // Should be a success.
    set_config('gateway_mollie_account_claim', 1, 'enrol_coursepayment');

    redirect(new \moodle_url('/admin/settings.php?section=enrolsettingscoursepayment&message=added_account'));
}

echo $OUTPUT->header();
echo $form->render();
echo $OUTPUT->footer();
