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
 * coursepayment enrolments plugin settings and presets.
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package   enrol_coursepayment
 * @copyright 2015 MoodleFreak.com
 * @author    Luuk Verhoeven
 **/


defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $message = optional_param('message', false, PARAM_TEXT);
    $config = get_config('enrol_coursepayment');

    // Check if we have connect mollie account to avetica.
    if (empty($config->gateway_mollie_account_claim)) {
        // We should show a login box.
        $PAGE->requires->js('/enrol/coursepayment/js/accountclaim.js');
    }

    // Check if we have a parent for the mollie connector (allow to create accounts)
    if (empty($config->gateway_mollie_parent_api)) {
        set_config('gateway_mollie_parent_api', 'http://moodle300.moodlefreak.com/enrol/coursepayment/mollie-connector.php', 'enrol_coursepayment');
    }

    // Check if there is a message.
    if (!empty($message)) {
        $settings->add(new admin_setting_heading('enrol_coursepayment_message', '', html_writer::div(get_string('message:' . $message, 'enrol_coursepayment'), 'alert alert-success')));
    }

    //--- settings ------------------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('enrol_coursepayment_settings', '', get_string('pluginname_desc', 'enrol_coursepayment')));

    $options = array(
        ENROL_EXT_REMOVED_KEEP => get_string('extremovedkeep', 'enrol'),
        ENROL_EXT_REMOVED_SUSPENDNOROLES => get_string('extremovedsuspendnoroles', 'enrol'),
        ENROL_EXT_REMOVED_UNENROL => get_string('extremovedunenrol', 'enrol'),
    );
    $settings->add(new admin_setting_configselect('enrol_coursepayment/expiredaction', get_string('expiredaction', 'enrol_coursepayment'), get_string('expiredaction_help', 'enrol_coursepayment'), ENROL_EXT_REMOVED_SUSPENDNOROLES, $options));

    $options = array();
    for ($i = 0; $i < 24; $i++) {
        $options[$i] = $i;
    }
    $settings->add(new admin_setting_configselect('enrol_coursepayment/expirynotifyhour', get_string('expirynotifyhour', 'core_enrol'), '', 6, $options));

    $settings->add(new admin_setting_heading('enrol_coursepayment_invoicedetails', get_string('invoicedetails', 'enrol_coursepayment'), get_string('invoicedetails_desc', 'enrol_coursepayment')));
    $settings->add(new admin_setting_configtext('enrol_coursepayment/companyname', get_string('companyname', 'enrol_coursepayment'), '', '', PARAM_TEXT));
    $settings->add(new admin_setting_configtext('enrol_coursepayment/address', get_string('address', 'enrol_coursepayment'), '', '', PARAM_TEXT));
    $settings->add(new admin_setting_configtext('enrol_coursepayment/zipcode', get_string('zipcode', 'enrol_coursepayment'), '', '', PARAM_TEXT));
    $settings->add(new admin_setting_configtext('enrol_coursepayment/place', get_string('place', 'enrol_coursepayment'), '', '', PARAM_TEXT));
    $settings->add(new admin_setting_configtext('enrol_coursepayment/kvk', get_string('kvk', 'enrol_coursepayment'), '', '', PARAM_TEXT));
    $settings->add(new admin_setting_configtext('enrol_coursepayment/btw', get_string('btw', 'enrol_coursepayment'), '', '', PARAM_TEXT));

    $settings->add(new admin_setting_heading('enrol_coursepayment_welcomemail', get_string('welcomemail', 'enrol_coursepayment'), ''));
    $settings->add(new admin_setting_configcheckbox('enrol_coursepayment/mailstudents', get_string('mailstudents', 'enrol_coursepayment'), get_string('welcometocoursetext', ''), 0));
    $settings->add(new admin_setting_configcheckbox('enrol_coursepayment/mailteachers', get_string('mailteachers', 'enrol_coursepayment'), get_string('enrolmentnewuser', 'enrol'), 0));
    $settings->add(new admin_setting_configcheckbox('enrol_coursepayment/mailadmins', get_string('mailadmins', 'enrol_coursepayment'), get_string('enrolmentnewuser', 'enrol'), 0));

    $settings->add(new admin_setting_heading('enrol_coursepayment_invoicemail', get_string('invoicemail', 'enrol_coursepayment'), ''));
    $settings->add(new admin_setting_configcheckbox('enrol_coursepayment/mailstudents_invoice', get_string('mailstudents', 'enrol_coursepayment'), '', 1));
    $settings->add(new admin_setting_configcheckbox('enrol_coursepayment/mailteachers_invoice', get_string('mailteachers', 'enrol_coursepayment'), '', 0));
    $settings->add(new admin_setting_configcheckbox('enrol_coursepayment/mailadmins_invoice', get_string('mailadmins', 'enrol_coursepayment'), '', 1));

    // enrol instance defaults
    $settings->add(new admin_setting_heading('enrol_coursepayment_defaults', get_string('enrolinstancedefaults', 'admin'), get_string('enrolinstancedefaults_desc', 'admin')));

    $optionsyesno = array(
        ENROL_INSTANCE_ENABLED => get_string('yes'),
        ENROL_INSTANCE_DISABLED => get_string('no')
    );
    $settings->add(new admin_setting_configselect('enrol_coursepayment/status', get_string('status', 'enrol_coursepayment'), get_string('status_desc', 'enrol_coursepayment'), ENROL_INSTANCE_DISABLED, $optionsyesno));
    $settings->add(new admin_setting_configtext('enrol_coursepayment/cost', get_string('cost', 'enrol_coursepayment'), '', 10.00, PARAM_FLOAT, 4));

    $coursepaymentcurrencies = enrol_get_plugin('coursepayment')->get_currencies();
    $settings->add(new admin_setting_configselect('enrol_coursepayment/currency', get_string('currency', 'enrol_coursepayment'), '', 'EUR', $coursepaymentcurrencies));

    $vatpercentages = enrol_get_plugin('coursepayment')->get_vat_percentages();
    $settings->add(new admin_setting_configselect('enrol_coursepayment/vatpercentage', get_string('vatpercentages', 'enrol_coursepayment'), '', 21, $vatpercentages));

    if (!during_initial_install()) {
        $options = get_default_enrol_roles(context_system::instance());
        $student = get_archetype_roles('student');
        $student = reset($student);
        $settings->add(new admin_setting_configselect('enrol_coursepayment/roleid', get_string('defaultrole', 'enrol_coursepayment'), get_string('defaultrole_desc', 'enrol_coursepayment'), $student->id, $options));
    }
    $settings->add(new admin_setting_configduration('enrol_coursepayment/enrolperiod', get_string('enrolperiod', 'enrol_coursepayment'), get_string('enrolperiod_desc', 'enrol_coursepayment'), 0));

    $options = array(
        0 => get_string('no'),
        1 => get_string('expirynotifyenroller', 'core_enrol'),
        2 => get_string('expirynotifyall', 'core_enrol')
    );
    $settings->add(new admin_setting_configselect('enrol_manual/expirynotify', get_string('expirynotify', 'core_enrol'), get_string('expirynotify_help', 'core_enrol'), 0, $options));

    $settings->add(new admin_setting_configduration('enrol_manual/expirythreshold', get_string('expirythreshold', 'core_enrol'), get_string('expirythreshold_help', 'core_enrol'), 86400, 86400));

    // add mollie settings to the plugin https://www.mollie.com
    $yesno = array(0 => get_string('no'), 1 => get_string('yes'));

    $obj = new stdClass();
    $obj->link = $CFG->wwwroot . '/enrol/coursepayment/view/discountcode.php';
    $settings->add(new admin_setting_heading('enrol_coursepayment_discount', get_string('enrol_coursepayment_discount', 'enrol_coursepayment'), get_string('enrol_coursepayment_discount_desc', 'enrol_coursepayment', $obj)));

    $settings->add(new admin_setting_heading('enrol_coursepayment_gateway_mollie', get_string('gateway_mollie', 'enrol_coursepayment'), get_string('gateway_mollie_desc', 'enrol_coursepayment')));
    $settings->add(new admin_setting_heading('enrol_coursepayment_register', '', '<aside style="border: 1px solid red;padding: 3px">' . get_string('gateway_mollie_link', 'enrol_coursepayment', (object)[
            'link' => $CFG->wwwroot . '/enrol/coursepayment/view/newaccount.php'
        ]) . '</aside><hr/>'));
    $settings->add(new admin_setting_configselect('enrol_coursepayment/gateway_mollie_enabled', get_string('enabled', 'enrol_coursepayment'), get_string('enabled_desc', 'enrol_coursepayment'), 1, $yesno));
    $settings->add(new admin_setting_configtext('enrol_coursepayment/gateway_mollie_apikey', get_string('gateway_mollie_apikey', 'enrol_coursepayment'), '', '', PARAM_ALPHANUMEXT));
    $settings->add(new admin_setting_configselect('enrol_coursepayment/gateway_mollie_debug', get_string('debug', 'enrol_coursepayment'), get_string('debug_desc', 'enrol_coursepayment'), 0, $yesno));
    $settings->add(new admin_setting_configselect('enrol_coursepayment/gateway_mollie_sandbox', get_string('sandbox', 'enrol_coursepayment'), get_string('sandbox_desc', 'enrol_coursepayment'), 0, $yesno));
    $settings->add(new admin_setting_configselect('enrol_coursepayment/gateway_mollie_external_connector', get_string('gateway_mollie_external_connector', 'enrol_coursepayment'), get_string('gateway_mollie_external_connector_desc', 'enrol_coursepayment'), 0, $yesno));

    // Check if gateway_mollie_external_connector is enabled.
    if ($config->gateway_mollie_external_connector) {
        $settings->add(new admin_setting_configtext('enrol_coursepayment/gateway_mollie_partner_id', get_string('gateway_mollie_partner_id', 'enrol_coursepayment'), '', '', PARAM_INT));
        $settings->add(new admin_setting_configtext('enrol_coursepayment/gateway_mollie_profile_key', get_string('gateway_mollie_profile_key', 'enrol_coursepayment'), '', '', PARAM_TEXT));
        $settings->add(new admin_setting_configtext('enrol_coursepayment/gateway_mollie_app_secret', get_string('gateway_mollie_app_secret', 'enrol_coursepayment'), '', '', PARAM_TEXT));
    }

    if (!empty($config->gateway_mollie_apikey)) {
        try {
            $gateway = new enrol_coursepayment_mollie();
            $methods = $gateway->get_enabled_modes();
            $settings->add(new admin_setting_heading('enrol_coursepayment_methods', '', $methods));
        } catch (Exception $exc) {
            $settings->add(new admin_setting_heading('enrol_coursepayment_warning', '', '<div style="color:red">' . $exc->getMessage() . '</div>'));
        }
    }
}