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
 * Helper
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package   enrol_coursepayment
 * @copyright 2017 MoodleFreak.com
 * @author    Luuk Verhoeven
 */
defined('MOODLE_INTERNAL') || die();

class enrol_coursepayment_helper {

    /**
     * Send a POST request to a remote location.
     *
     * @param string $url
     * @param array  $data
     *
     * @return mixed
     */
    public static function post_request($url = '', $data = []) {
        $fields = '';
        foreach ($data as $key => $value) {
            $fields .= $key . '=' . $value . '&';
        }
        rtrim($fields, '&');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_POST, count($data));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    /**
     * Get all available profile fields
     *
     * @return array
     * @throws dml_exception
     */
    public static function get_profile_fields() {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/user/profile/lib.php');
        require_once($CFG->dirroot . '/user/profile/definelib.php');
        $rs = $DB->get_recordset_sql("SELECT f.* FROM {user_info_field} f ORDER BY name ASC");
        $fields = ['' => ''];
        foreach ($rs as $field) {
            $fields[$field->id] = $field->name;
        }
        $rs->close();
        if (empty($fields)) {
            return [];
        }

        return $fields;
    }

    /**
     * get_profile_field_data
     *
     *
     * @param $fieldid
     * @param $userid
     *
     * @return string
     * @throws dml_exception
     */
    public static function get_profile_field_data($fieldid, $userid) {
        global $DB;
        $field = $DB->get_record('user_info_field', ['id' => $fieldid], '*', MUST_EXIST);

        // Single user mode
        $row = $DB->get_record('user_info_data', ['fieldid' => $field->id, 'userid' => $userid]);
        if (isset($row->data)) {
            return $row->data;
        }

        if (isset($field->defaultdata)) {
            return $field->defaultdata;
        }

        return '';
    }

    /**
     * get_cmid_info
     *
     * @param int $cmid
     * @param int $courseid
     *
     * @return bool|\cm_info
     * @throws moodle_exception
     */
    public static function get_cmid_info($cmid = 0, $courseid = 0) {

        $modinfo = get_fast_modinfo($courseid);
        foreach ($modinfo->sections as $sectionnum => $section) {
            foreach ($section as $coursemoduleid) {
                if ($coursemoduleid == $cmid) {
                    return $modinfo->cms[$coursemoduleid];
                }
            }
        }

        return false;
    }

    /**
     * get_cmid_info
     *
     * @param int $sectionnumber
     * @param int $courseid
     *
     * @return stdClass
     * @throws dml_exception
     */
    public static function get_section_info($sectionnumber = 0, $courseid = 0) {
        global $DB;

        $section = $DB->get_record('course_sections', [
            'course' => $courseid,
            'section' => $sectionnumber,
        ], '*', MUST_EXIST);

        $courseformat = course_get_format($courseid);
        $defaultsectionname = $courseformat->get_default_section_name($section);

        $module = new \stdClass();
        $module->name = $defaultsectionname;

        return $module;
    }

    /**
     *
     * parse_text
     *
     * @param string   $text
     * @param stdClass $obj
     *
     * @return mixed|string
     */
    public static function parse_text($text = '', stdClass $obj) {
        if (preg_match_all('/\{+\w+\}/', $text, $matches)) {
            foreach ($matches[0] as $match) {
                $matchClean = str_replace(['{' , '}'], '', $match);

                if (isset($obj->$matchClean)) {
                    $text = str_replace($match, $obj->$matchClean, $text);
                }
            }
        }

        return $text;
    }

}