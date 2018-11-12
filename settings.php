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
 * Coursepayment enrolments plugin settings and presets.
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package   enrol_coursepayment
 * @copyright 2015 MFreak.nl
 * @author    Luuk Verhoeven
 **/

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $install = $PAGE->url->get_path() === '/admin/upgradesettings.php' ? true : false;
    $message = optional_param('message', false, PARAM_TEXT);
    $config = get_config('enrol_coursepayment');

    // Check if we have connect mollie account to avetica.
    if (empty($config->gateway_mollie_account_claim) &&
        // Check if there is already a API key.
        !empty($config->gateway_mollie_apikey) &&
        // Make sure its not a reseller.
        empty($config->gateway_mollie_external_connector) &&
        // Not showing when using multi-account.
        empty($config->multi_account)
    ) {

        // We should show a login box.
        $PAGE->requires->strings_for_js([
            'js:claim_title',
            'js:claim_desc',
            'js:username',
            'js:password',
            'js:connect',
            'js:delay',
        ], 'enrol_coursepayment');

        $PAGE->requires->js('/enrol/coursepayment/js/accountclaim.js');
    }

    // Add mollie settings to the plugin https://www.mollie.com
    $yesno = [
        0 => get_string('no'),
        1 => get_string('yes'),
    ];

    // Check if we have a parent for the mollie connector (allow to create accounts)
    if (empty($config->gateway_mollie_parent_api)) {
        set_config('gateway_mollie_parent_api',
            'https://moodle.avetica.nl/enrol/coursepayment/mollie-connector.php',
            'enrol_coursepayment');
    }

    // Add some more logic.
    $tabs = new \enrol_coursepayment\adminsetting\tabs('enrol_coursepayment/tabs', $settings->name, 'invoicedetails');
    $tabs->addtab('invoicedetails', get_string('settings:tab_invoicedetails', 'enrol_coursepayment'));
    $tabs->addtab('mail', get_string('settings:tab_mail', 'enrol_coursepayment'));
    $tabs->addtab('gateway', get_string('settings:tab_gateway', 'enrol_coursepayment'));
    $tabs->addtab('enrolment', get_string('settings:tab_enrolment', 'enrol_coursepayment'));
    if (!empty($config->multi_account)) {
        $tabs->addtab('multiaccount', get_string('settings:tab_multiaccount', 'enrol_coursepayment'));
    }
    $tabs->addtab('advanced', get_string('settings:tab_advanced', 'enrol_coursepayment'));
    $settings->add($tabs);

    $tab = $tabs->get_setting();

    // Check if there is a message.
    if (!empty($message)) {
        $settings->add(new admin_setting_heading('enrol_coursepayment_message', '',
            html_writer::div(get_string('message:' . $message, 'enrol_coursepayment'), 'alert alert-success')));
    }

    // Display the correct tab.
    if (empty($tab) || $tab === 'invoicedetails' || !empty($install)) {

        // Invoice settings.
        include('settings/invoicedetails.php');

    } else if ($tab === 'enrolment' || !empty($install)) {

        // Enrolment settings.
        include('settings/enrolment.php');

    } else if ($tab === 'mail' || !empty($install)) {

        // E-mail settings.
        include('settings/mail.php');

    } else if ($tab === 'multiaccount' || !empty($install)) {

        // E-mail settings.
        include('settings/multiaccount.php');

    } else if ($tab === 'gateway' || !empty($install)) {

        // Gateway settings.
        include('settings/gateway.php');

    } else if ($tab === 'advanced' || !empty($install)) {

        // Advanced settings.
        include('settings/advanced.php');
    }
}