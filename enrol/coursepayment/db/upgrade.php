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
 * upgrade older versions to support new features
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @file: messages.php
 * @since 4-3-2015
 * @encoding: UTF8
 *
 * @package: enrol_coursepayment
 *
 * @copyright 2015 MoodleFreak.com
 * @author    Luuk Verhoeven
 **/
function xmldb_enrol_coursepayment_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();


    // add discount code feature
    if ($oldversion < 2015051500) {

        // Define field discountdata to be added to enrol_coursepayment.
        $table = new xmldb_table('enrol_coursepayment');
        $field = new xmldb_field('discountdata', XMLDB_TYPE_TEXT, null, null, null, null, null, 'addedon');

        // Conditionally launch add field discountdata.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('enrol_coursepayment_discount');

        // Adding fields to table enrol_coursepayment_discount.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '9', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('code', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '9', null, XMLDB_NOTNULL, null, null);
        $table->add_field('start_time', XMLDB_TYPE_INTEGER, '9', null, XMLDB_NOTNULL, null, null);
        $table->add_field('end_time', XMLDB_TYPE_INTEGER, '9', null, XMLDB_NOTNULL, null, null);
        $table->add_field('percentage', XMLDB_TYPE_NUMBER, '8, 5', null, XMLDB_NOTNULL, null, '0.00000');
        $table->add_field('amount', XMLDB_TYPE_NUMBER, '10, 2', null, XMLDB_NOTNULL, null, '0.00');
        $table->add_field('created_by', XMLDB_TYPE_INTEGER, '9', null, XMLDB_NOTNULL, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for enrol_coursepayment_discount.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Coursepayment savepoint reached.
        upgrade_plugin_savepoint(true, 2015051500, 'enrol', 'coursepayment');
    }
}
