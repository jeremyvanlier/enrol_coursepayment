<?php
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
 * This file contains the customcert element date's core interaction API.
 *
 * @package    coursepaymentelement_date
 * @copyright  2013 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_coursepayment\invoice\element\date;

defined('MOODLE_INTERNAL') || die();

/**
 * Date - Course grade date
 */
define('COURSEPAYMENT_DATE_COURSE_GRADE', '0');

/**
 * Date - Issue
 */
define('COURSEPAYMENT_DATE_ISSUE', '-1');

/**
 * Date - Completion
 */
define('COURSEPAYMENT_DATE_COMPLETION', '-2');

/**
 * Date - Course start
 */
define('COURSEPAYMENT_DATE_COURSE_START', '-3');

/**
 * Date - Course end
 */
define('COURSEPAYMENT_DATE_COURSE_END', '-4');

require_once($CFG->dirroot . '/lib/grade/constants.php');

/**
 * The customcert element date's core interaction API.
 *
 * @package    coursepaymentelement_date
 * @copyright  2013 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class element extends \enrol_coursepayment\invoice\element {

    /**
     * This function renders the form elements when adding a customcert element.
     *
     * @param \enrol_coursepayment\invoice\edit_element_form $mform the edit_form instance
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function render_form_elements($mform) {
        global $COURSE;

        // Get the possible date options.
        $dateoptions = array();
        $dateoptions[COURSEPAYMENT_DATE_ISSUE] = get_string('issueddate', 'coursepaymentelement_date');
        $dateoptions[COURSEPAYMENT_DATE_COMPLETION] = get_string('completiondate', 'coursepaymentelement_date');
        $dateoptions[COURSEPAYMENT_DATE_COURSE_START] = get_string('coursestartdate', 'coursepaymentelement_date');
        $dateoptions[COURSEPAYMENT_DATE_COURSE_END] = get_string('courseenddate', 'coursepaymentelement_date');
        $dateoptions[COURSEPAYMENT_DATE_COURSE_GRADE] = get_string('coursegradedate', 'coursepaymentelement_date');
        $dateoptions = $dateoptions + \enrol_coursepayment\invoice\element_helper::get_grade_items($COURSE);

        $mform->addElement('select', 'dateitem', get_string('dateitem', 'coursepaymentelement_date'), $dateoptions);
        $mform->addHelpButton('dateitem', 'dateitem', 'coursepaymentelement_date');

        $mform->addElement('select', 'dateformat', get_string('dateformat', 'coursepaymentelement_date'), self::get_date_formats());
        $mform->addHelpButton('dateformat', 'dateformat', 'coursepaymentelement_date');

        parent::render_form_elements($mform);
    }

    /**
     * This will handle how form data will be saved into the data column in the
     * coursepayment_elements table.
     *
     * @param \stdClass $data the form data
     * @return string the json encoded array
     */
    public function save_unique_data($data) {
        // Array of data we will be storing in the database.
        $arrtostore = array(
            'dateitem' => $data->dateitem,
            'dateformat' => $data->dateformat
        );

        // Encode these variables before saving into the DB.
        return json_encode($arrtostore);
    }

    /**
     * Handles rendering the element on the pdf.
     *
     * @param \pdf      $pdf     the pdf object
     * @param bool      $preview true if it is a preview, false otherwise
     * @param \stdClass $user    the user we are rendering this for
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function render($pdf, $preview, $user) {
        global $DB;

        // If there is no element data, we have nothing to display.
        if (empty($this->get_data())) {
            return;
        }

        $courseid = \enrol_coursepayment\invoice\element_helper::get_courseid($this->id);

        // Decode the information stored in the database.
        $dateinfo = json_decode($this->get_data());
        $dateitem = $dateinfo->dateitem;
        $dateformat = $dateinfo->dateformat;

        // If we are previewing this certificate then just show a demonstration date.
        if ($preview) {
            $date = time();
        } else {
            // Get the page.
            $page = $DB->get_record('coursepayment_pages', array('id' => $this->get_pageid()), '*', MUST_EXIST);
            // Get the customcert this page belongs to.
            $customcert = $DB->get_record('enrol_coursepayment', array('templateid' => $page->templateid), '*', MUST_EXIST);
            // Now we can get the issue for this user.
            $issue = $DB->get_record('coursepayment_issues', array('userid' => $user->id, 'customcertid' => $customcert->id),
                '*', MUST_EXIST);

            if ($dateitem == COURSEPAYMENT_DATE_ISSUE) {
                $date = $issue->timecreated;
            } else if ($dateitem == COURSEPAYMENT_DATE_COMPLETION) {
                // Get the last completion date.
                $sql = "SELECT MAX(c.timecompleted) as timecompleted
                          FROM {course_completions} c
                         WHERE c.userid = :userid
                           AND c.course = :courseid";
                if ($timecompleted = $DB->get_record_sql($sql, array('userid' => $issue->userid, 'courseid' => $courseid))) {
                    if (!empty($timecompleted->timecompleted)) {
                        $date = $timecompleted->timecompleted;
                    }
                }
            } else if ($dateitem == COURSEPAYMENT_DATE_COURSE_START) {
                $date = $DB->get_field('course', 'startdate', array('id' => $courseid));
            } else if ($dateitem == COURSEPAYMENT_DATE_COURSE_END) {
                $date = $DB->get_field('course', 'enddate', array('id' => $courseid));
            } else {
                if ($dateitem == COURSEPAYMENT_DATE_COURSE_GRADE) {
                    $grade = \enrol_coursepayment\invoice\element_helper::get_course_grade_info(
                        $courseid,
                        GRADE_DISPLAY_TYPE_DEFAULT,
                        $user->id
                    );
                } else if (strpos($dateitem, 'gradeitem:') === 0) {
                    $gradeitemid = substr($dateitem, 10);
                    $grade = \enrol_coursepayment\invoice\element_helper::get_grade_item_info(
                        $gradeitemid,
                        $dateitem,
                        $user->id
                    );
                } else {
                    $grade = \enrol_coursepayment\invoice\element_helper::get_mod_grade_info(
                        $dateitem,
                        GRADE_DISPLAY_TYPE_DEFAULT,
                        $user->id
                    );
                }

                if ($grade && !empty($grade->get_dategraded())) {
                    $date = $grade->get_dategraded();
                }
            }
        }

        // Ensure that a date has been set.
        if (!empty($date)) {
            \enrol_coursepayment\invoice\element_helper::render_content($pdf, $this, $this->get_date_format_string($date, $dateformat));
        }
    }

    /**
     * Render the element in html.
     *
     * This function is used to render the element when we are using the
     * drag and drop interface to position it.
     *
     * @return string the html
     * @throws \coding_exception
     */
    public function render_html() {
        // If there is no element data, we have nothing to display.
        if (empty($this->get_data())) {
            return;
        }

        // Decode the information stored in the database.
        $dateinfo = json_decode($this->get_data());
        $dateformat = $dateinfo->dateformat;

        return \enrol_coursepayment\invoice\element_helper::render_html_content($this, $this->get_date_format_string(time(), $dateformat));
    }

    /**
     * Sets the data on the form when editing an element.
     *
     * @param \enrol_coursepayment\invoice\edit_element_form $mform the edit_form instance
     */
    public function definition_after_data($mform) {
        // Set the item and format for this element.
        if (!empty($this->get_data())) {
            $dateinfo = json_decode($this->get_data());

            $element = $mform->getElement('dateitem');
            $element->setValue($dateinfo->dateitem);

            $element = $mform->getElement('dateformat');
            $element->setValue($dateinfo->dateformat);
        }

        parent::definition_after_data($mform);
    }

    /**
     * This function is responsible for handling the restoration process of the element.
     *
     * We will want to update the course module the date element is pointing to as it will
     * have changed in the course restore.
     *
     * @param \restore_coursepayment_activity_task $restore
     * @throws \dml_exception
     */
    public function after_restore($restore) {
        global $DB;

        $dateinfo = json_decode($this->get_data());
        if ($newitem = \restore_dbops::get_backup_ids_record($restore->get_restoreid(), 'course_module', $dateinfo->dateitem)) {
            $dateinfo->dateitem = $newitem->newitemid;
            $DB->set_field('coursepayment_elements', 'data', $this->save_unique_data($dateinfo), array('id' => $this->get_id()));
        }
    }

    /**
     * Helper function to return all the date formats.
     *
     * @return array the list of date formats
     * @throws \coding_exception
     */
    public static function get_date_formats() {
        // Hard-code date so users can see the difference between short dates with and without the leading zero.
        // Eg. 06/07/18 vs 6/07/18.
        $date = 1530849658;

        $suffix = self::get_ordinal_number_suffix(userdate($date, '%d'));

        $dateformats = [
            1 => userdate($date, '%B %d, %Y'),
            2 => userdate($date, '%B %d' . $suffix . ', %Y')
        ];

        $strdateformats = [
            'strftimedate',
            'strftimedatefullshort',
            'strftimedatefullshortwleadingzero',
            'strftimedateshort',
            'strftimedatetime',
            'strftimedatetimeshort',
            'strftimedatetimeshortwleadingzero',
            'strftimedaydate',
            'strftimedaydatetime',
            'strftimedayshort',
            'strftimedaytime',
            'strftimemonthyear',
            'strftimerecent',
            'strftimerecentfull',
            'strftimetime'
        ];

        foreach ($strdateformats as $strdateformat) {
            if ($strdateformat == 'strftimedatefullshortwleadingzero') {
                $dateformats[$strdateformat] = userdate($date, get_string('strftimedatefullshort', 'langconfig'), 99, false);
            } else if ($strdateformat == 'strftimedatetimeshortwleadingzero') {
                $dateformats[$strdateformat] = userdate($date, get_string('strftimedatetimeshort', 'langconfig'), 99, false);
            } else {
                $dateformats[$strdateformat] = userdate($date, get_string($strdateformat, 'langconfig'));
            }
        }

        return $dateformats;
    }

    /**
     * Returns the date in a readable format.
     *
     * @param int    $date
     * @param string $dateformat
     * @return string
     * @throws \coding_exception
     */
    protected function get_date_format_string($date, $dateformat) {
        // Keeping for backwards compatibility.
        if (is_number($dateformat)) {
            switch ($dateformat) {
                case 1:
                    $certificatedate = userdate($date, '%B %d, %Y');
                    break;
                case 2:
                    $suffix = self::get_ordinal_number_suffix(userdate($date, '%d'));
                    $certificatedate = userdate($date, '%B %d' . $suffix . ', %Y');
                    break;
                case 3:
                    $certificatedate = userdate($date, '%d %B %Y');
                    break;
                case 4:
                    $certificatedate = userdate($date, '%B %Y');
                    break;
                default:
                    $certificatedate = userdate($date, get_string('strftimedate', 'langconfig'));
            }
        }

        // Ok, so we must have been passed the actual format in the lang file.
        if (!isset($certificatedate)) {
            if ($dateformat == 'strftimedatefullshortwleadingzero') {
                $certificatedate = userdate($date, get_string('strftimedatefullshort', 'langconfig'), 99, false);
            } else if ($dateformat == 'strftimedatetimeshortwleadingzero') {
                $certificatedate = userdate($date, get_string('strftimedatetimeshort', 'langconfig'), 99, false);
            } else {
                $certificatedate = userdate($date, get_string($dateformat, 'langconfig'));
            }
        }

        return $certificatedate;
    }

    /**
     * Helper function to return the suffix of the day of
     * the month, eg 'st' if it is the 1st of the month.
     *
     * @param int $day the day of the month
     * @return string the suffix.
     * @throws \coding_exception
     */
    protected static function get_ordinal_number_suffix($day) {
        if (!in_array(($day % 100), array(11, 12, 13))) {
            switch ($day % 10) {
                // Handle 1st, 2nd, 3rd.
                case 1:
                    return get_string('numbersuffix_st_as_in_first', 'coursepaymentelement_date');
                case 2:
                    return get_string('numbersuffix_nd_as_in_second', 'coursepaymentelement_date');
                case 3:
                    return get_string('numbersuffix_rd_as_in_third', 'coursepaymentelement_date');
            }
        }
        return 'th';
    }
}