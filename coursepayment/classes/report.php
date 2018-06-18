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
 * @copyright 2018 MoodleFreak.com
 * @author    Luuk Verhoeven
 **/

namespace enrol_coursepayment;

use enrol_coursepayment\table\report_courses;

defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir . '/tablelib.php');

class report {

    /**
     * table_overview_courses
     *
     * @param int  $courseid
     *
     * @param      $datafilter
     * @param bool $returntable
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function table_overview_courses($courseid = 1, $datafilter, $returntable = false) {
        global $PAGE;
        $columns = [
            'firstname',
            'lastname',
            'course',
            'email',
            'phone1',
            'status',
            'addedon',
        ];

        $table = new report_courses(__FUNCTION__);
        $table->define_baseurl($PAGE->url);
        //  $table->is_downloadable(true);

        $table->set_attribute('cellspacing', '0');
        $table->set_attribute('class', 'admintable generaltable mftable');
        $table->initialbars(true); // Always initial bars.
        $table->define_columns($columns);

        $table->define_headers(
            array_map(function ($val) {
                return get_string('heading:table_' . $val, 'enrol_coursepayment');
            }, $columns)
        );

        $table->sortable(true, 'addedon', SORT_DESC);
        $table->collapsible(false);

        // Prepare.
        $table->setup();

        // Add the rows and sort if needed.
        $results = self::get_all_courses_data();

        // Navigation.
        $table->pagesize(100, count($results));

        $table->data_sort_and_search($results, $datafilter);

        $table->finish_html();

    }

    /**
     * get_all_courses_data
     *
     * @return array
     * @throws \dml_exception
     */
    private static function get_all_courses_data() {
        global $DB;

        $sql = 'SELECT cp.* , u.firstname , u.lastname , u.phone1 , u.email, c.fullname as course 
                FROM {enrol_coursepayment} cp 
                JOIN {course} c ON (c.id = cp.courseid)
                LEFT JOIN {user} u ON (u.id = cp.userid)
                ';

        $results = $DB->get_records_sql($sql);

        // Build user_id set.
        $userIds = [];
        foreach($results as $result){
            $userIds[$result->userid] = $result->userid;
        }

        // @TODO Add all users not in $userIds and not deleted or suspended.

        return $results;
    }

}