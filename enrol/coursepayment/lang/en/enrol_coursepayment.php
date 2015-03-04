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
 * language file for coursepayment
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @file: enrol_coursepayment.php
 * @since 2-3-2015
 * @encoding: UTF8
 *
 * @package: enrol_coursepayment
 *
 * @copyright 2015 MoodleFreak.com
 * @author    Luuk Verhoeven
 **/
$string['pluginname'] = 'CoursePayment';
$string['pluginname_desc'] = 'This plugin allows you to purchase course with a payment gateway';
$string['mailadmins'] = 'Notify admin';
$string['nocost'] = 'There is no cost associated with enrolling in this course!';
$string['currency'] = 'Currency';
$string['cost'] = 'Enrol cost';
$string['assignrole'] = 'Assign role';
$string['mailstudents'] = 'Notify students';
$string['mailteachers'] = 'Notify teachers';
$string['expiredaction'] = 'Enrolment expiration action';
$string['expiredaction_help'] = 'Select action to carry out when user enrolment expires. Please note that some user data and settings are purged from course during course unenrolment.';
$string['status'] = 'Allow CoursePayment enrolments';
$string['status_desc'] = 'Allow users to use CoursePayment to enrol into a course by default.';
$string['defaultrole'] = 'Default role assignment';
$string['defaultrole_desc'] = 'Select role which should be assigned to users during CoursePayment enrolments';
$string['enrolenddate'] = 'End date';
$string['enrolenddate_help'] = 'If enabled, users can be enrolled until this date only.';
$string['enrolenddaterror'] = 'Enrolment end date cannot be earlier than start date';
$string['enrolperiod'] = 'Enrolment duration';
$string['enrolperiod_desc'] = 'Default length of time that the enrolment is valid. If set to zero, the enrolment duration will be unlimited by default.';
$string['enrolstartdate'] = 'Start date';
$string['enrolstartdate_help'] = 'If enabled, users can be enrolled from this date onward only.';
$string['enrolperiod_help'] = 'Length of time that the enrolment is valid, starting with the moment the user is enrolled. If disabled, the enrolment duration will be unlimited.';
$string['debug'] = 'Debug';
$string['debug_desc'] = 'Should only be enabled by developers this outputs debug messages';
$string['sandbox'] = 'Sandbox';
$string['sandbox_desc'] = 'Only for testing purposes should not be enabeld. User can enrol in course without paying. For mollie this has no effect';
$string['enabled'] = 'Enabled';
$string['error:failed_getting_plugin_instance'] = 'Failed getting instance details!';
$string['crontask'] = 'CoursePayment - process orders';
$string['title:returnpage'] = 'Payment Status';
$string['success_enrolled'] = 'Thanks for your purchase.<br> We have enrolled you for: {$a->fullname}';
$string['error:unknown_order'] = 'Unknown order we don\'t have a record of it!';
$string['error:gettingorderdetails'] = 'We where unable to query the gateway for order details. We will retry later.';
$string['error:paymentabort'] = 'The payment is aborted!';
$string['gateway_not_exists'] = 'Error! Gateway not exists';
$string['enabled_desc'] = 'Status of the gateway if this can be used to create a transaction';
$string['purchase'] = 'Purchase course';
$string['provider'] = 'Provider';
$string['name'] = 'Name';
$string['minimum'] = 'Minimum';
$string['maximum'] = 'Maximum';
$string['gateway_mollie_issuers'] = 'Select a bank';
$string['gateway_mollie_select_method'] = 'Please click the method you like to use.';
$string['gateway_mollie'] = 'Gateway: Mollie';
$string['gateway_mollie_desc'] = 'Offer your customers the payment methods Creditcard, SOFORT Banking, iDEAL, Bancontact/Mister Cash, Bank transfer, Bitcoin, PayPal or paysafecard. Mollie is known for reliability, transparency, nice API’s and ready-to-go modules.';
$string['gateway_mollie_apikey'] = 'API key';
$string['gateway_mollie_link'] = 'If you don\'t have a account please <a href="https://www.mollie.com/en/">register</a>';
$string['gateway_mollie_send_button'] = 'Purchase with mollie';
