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
 * Multi-account setting page.
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package coursepayment
 * @copyright 2017 MoodleFreak.com
 * @author    Luuk Verhoeven
 **/
defined('MOODLE_INTERNAL') || die();
($ADMIN->fulltree) || die();

if (!empty($config->multi_account)) {
    // Special settings for multi-account support.

    
    
    // Map profile field.
    $fields = enrol_coursepayment_helper::get_profile_fields();

    if(count($fields) == 1){
        // Show error.
        $settings->add(new admin_setting_heading('enrol_coursepayment_message', '',
            html_writer::div(get_string('message:error_add_profile_field', 'enrol_coursepayment'), 'alert alert-danger')));
    }

    // Add multiple mollie accounts.


    // Add multiple invoice details.


    // Set a default if profile data doesn't match.



}