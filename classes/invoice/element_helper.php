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
 * Template helper class
 *
 * This parts is copied from "mod_customcert" - Mark Nelson <markn@moodle.com>
 * Thanks for allowing us to use it.
 *
 * This file is modified not compatible with the original.
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package   enrol_coursepayment
 * @copyright 2018 MFreak.nl
 * @author    Luuk Verhoeven
 */

namespace enrol_coursepayment\invoice;

use enrol_coursepayment\invoice\helper;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/grade/constants.php');
require_once($CFG->dirroot . '/grade/lib.php');
require_once($CFG->dirroot . '/grade/querylib.php');

/**
 * Class helper.
 */
class element_helper {

    /**
     * @var int the top-left of element
     */
    const COURSEPAYMENT_REF_POINT_TOPLEFT = 0;

    /**
     * @var int the top-center of element
     */
    const COURSEPAYMENT_REF_POINT_TOPCENTER = 1;

    /**
     * @var int the top-left of element
     */
    const COURSEPAYMENT_REF_POINT_TOPRIGHT = 2;

    /**
     * Common behaviour for rendering specified content on the pdf.
     *
     * @param \pdf $pdf the pdf object
     * @param \enrol_coursepayment\invoice\element $element the coursepayment invoice element
     * @param string $content the content to render
     */
    public static function render_content($pdf, $element, $content) {
        list($font, $attr) = self::get_font($element);
        $pdf->setFont($font, $attr, $element->get_fontsize());
        $fontcolour = \TCPDF_COLORS::convertHTMLColorToDec($element->get_colour(), $fontcolour);
        $pdf->SetTextColor($fontcolour['R'], $fontcolour['G'], $fontcolour['B']);

        $x = $element->get_posx();
        $y = $element->get_posy();
        $w = $element->get_width();
        $refpoint = $element->get_refpoint();
        $actualwidth = $pdf->GetStringWidth($content);

        if ($w and $w < $actualwidth) {
            $actualwidth = $w;
        }

        switch ($refpoint) {
            case self::COURSEPAYMENT_REF_POINT_TOPRIGHT:
                $x = $element->get_posx() - $actualwidth;
                if ($x < 0) {
                    $x = 0;
                    $w = $element->get_posx();
                } else {
                    $w = $actualwidth;
                }
                break;
            case self::COURSEPAYMENT_REF_POINT_TOPCENTER:
                $x = $element->get_posx() - $actualwidth / 2;
                if ($x < 0) {
                    $x = 0;
                    $w = $element->get_posx() * 2;
                } else {
                    $w = $actualwidth;
                }
                break;
        }

        if ($w) {
            $w += 0.0001;
        }
        $pdf->setCellPaddings(0, 0, 0, 0);
        $pdf->writeHTMLCell($w, 0, $x, $y, $content, 0, 0, false, true);
    }

    /**
     * Common behaviour for rendering specified content on the drag and drop page.
     *
     * @param \enrol_coursepayment\invoice\element $element the customcert element
     * @param string $content the content to render
     * @return string the html
     */
    public static function render_html_content($element, $content) {
        list($font, $attr) = self::get_font($element);
        $fontstyle = 'font-family: ' . $font;
        if (strpos($attr, 'B') !== false) {
            $fontstyle .= '; font-weight: bold';
        }
        if (strpos($attr, 'I') !== false) {
            $fontstyle .= '; font-style: italic';
        }

        $style = $fontstyle . '; color: ' . $element->get_colour() . '; font-size: ' . $element->get_fontsize() . 'pt;';
        if ($element->get_width()) {
            $style .= ' width: ' . $element->get_width() . 'mm';
        }
        return \html_writer::div($content, '', array('style' => $style));
    }

    /**
     * Helper function to render the font elements.
     *
     * @param \enrol_coursepayment\invoice\edit_element_form $mform the edit_form instance.
     *
     * @throws \coding_exception
     */
    public static function render_form_element_font($mform) {
        $mform->addElement('select', 'font', get_string('font', 'enrol_coursepayment'), helper::get_fonts());
        $mform->setType('font', PARAM_TEXT);
        $mform->setDefault('font', 'times');
        $mform->addHelpButton('font', 'font', 'enrol_coursepayment');
        $mform->addElement('select', 'fontsize', get_string('fontsize', 'enrol_coursepayment'),
            helper::get_font_sizes());
        $mform->setType('fontsize', PARAM_INT);
        $mform->setDefault('fontsize', 12);
        $mform->addHelpButton('fontsize', 'fontsize', 'enrol_coursepayment');
    }

    /**
     * Helper function to render the colour elements.
     *
     * @param \enrol_coursepayment\invoice\edit_element_form $mform the edit_form instance.
     *
     * @throws \coding_exception
     */
    public static function render_form_element_colour($mform) {
        $mform->addElement('coursepayment_colourpicker', 'colour', get_string('fontcolour', 'enrol_coursepayment'));
        $mform->setType('colour', PARAM_RAW); // Need to validate that this is a valid colour.
        $mform->setDefault('colour', '#000000');
        $mform->addHelpButton('colour', 'fontcolour', 'enrol_coursepayment');
    }

    /**
     * Helper function to render the position elements.
     *
     * @param \enrol_coursepayment\invoice\edit_element_form $mform the edit_form instance.
     *
     * @throws \coding_exception
     */
    public static function render_form_element_position($mform) {
        $mform->addElement('text', 'posx', get_string('posx', 'enrol_coursepayment'), array('size' => 10));
        $mform->setType('posx', PARAM_INT);
        $mform->setDefault('posx', 0);
        $mform->addHelpButton('posx', 'posx', 'enrol_coursepayment');
        $mform->addElement('text', 'posy', get_string('posy', 'enrol_coursepayment'), array('size' => 10));
        $mform->setType('posy', PARAM_INT);
        $mform->setDefault('posy', 0);
        $mform->addHelpButton('posy', 'posy', 'enrol_coursepayment');
    }

    /**
     * Helper function to render the width element.
     *
     * @param \enrol_coursepayment\invoice\edit_element_form $mform the edit_form instance.
     *
     * @throws \coding_exception
     */
    public static function render_form_element_width($mform) {
        $mform->addElement('text', 'width', get_string('elementwidth', 'enrol_coursepayment'), array('size' => 10));
        $mform->setType('width', PARAM_INT);
        $mform->setDefault('width', 0);
        $mform->addHelpButton('width', 'elementwidth', 'enrol_coursepayment');
        $refpointoptions = array();
        $refpointoptions[self::COURSEPAYMENT_REF_POINT_TOPLEFT] = get_string('topleft', 'enrol_coursepayment');
        $refpointoptions[self::COURSEPAYMENT_REF_POINT_TOPCENTER] = get_string('topcenter', 'enrol_coursepayment');
        $refpointoptions[self::COURSEPAYMENT_REF_POINT_TOPRIGHT] = get_string('topright', 'enrol_coursepayment');
        $mform->addElement('select', 'refpoint', get_string('refpoint', 'enrol_coursepayment'), $refpointoptions);
        $mform->setType('refpoint', PARAM_INT);
        $mform->setDefault('refpoint', self::COURSEPAYMENT_REF_POINT_TOPCENTER);
        $mform->addHelpButton('refpoint', 'refpoint', 'enrol_coursepayment');
    }

    /**
     * Helper function to performs validation on the colour element.
     *
     * @param array $data the submitted data
     *
     * @return array the validation errors
     * @throws \coding_exception
     */
    public static function validate_form_element_colour($data) {
        $errors = array();
        // Validate the colour.
        if (!self::validate_colour($data['colour'])) {
            $errors['colour'] = get_string('invalidcolour', 'enrol_coursepayment');
        }
        return $errors;
    }

    /**
     * Helper function to performs validation on the position elements.
     *
     * @param array $data the submitted data
     *
     * @return array the validation errors
     * @throws \coding_exception
     */
    public static function validate_form_element_position($data) {
        $errors = array();

        // Check if posx is not set, or not numeric or less than 0.
        if ((!isset($data['posx'])) || (!is_numeric($data['posx'])) || ($data['posx'] < 0)) {
            $errors['posx'] = get_string('invalidposition', 'enrol_coursepayment', 'X');
        }
        // Check if posy is not set, or not numeric or less than 0.
        if ((!isset($data['posy'])) || (!is_numeric($data['posy'])) || ($data['posy'] < 0)) {
            $errors['posy'] = get_string('invalidposition', 'enrol_coursepayment', 'Y');
        }

        return $errors;
    }

    /**
     * Helper function to perform validation on the width element.
     *
     * @param array $data the submitted data
     *
     * @return array the validation errors
     * @throws \coding_exception
     */
    public static function validate_form_element_width($data) {
        $errors = array();

        // Check if width is less than 0.
        if (isset($data['width']) && $data['width'] < 0) {
            $errors['width'] = get_string('invalidelementwidth', 'enrol_coursepayment');
        }

        return $errors;
    }

    /**
     * Returns the font used for this element.
     *
     * @param \enrol_coursepayment\invoice\element $element the customcert element
     * @return array the font and font attributes
     */
    public static function get_font($element) {
        // Variable for the font.
        $font = $element->get_font();
        // Get the last two characters of the font name.
        $fontlength = strlen($font);
        $lastchar = $font[$fontlength - 1];
        $secondlastchar = $font[$fontlength - 2];
        // The attributes of the font.
        $attr = '';
        // Check if the last character is 'i'.
        if ($lastchar == 'i') {
            // Remove the 'i' from the font name.
            $font = substr($font, 0, -1);
            // Check if the second last char is b.
            if ($secondlastchar == 'b') {
                // Remove the 'b' from the font name.
                $font = substr($font, 0, -1);
                $attr .= 'B';
            }
            $attr .= 'I';
        } else if ($lastchar == 'b') {
            // Remove the 'b' from the font name.
            $font = substr($font, 0, -1);
            $attr .= 'B';
        }
        return array($font, $attr);
    }

    /**
     * Validates the colour selected.
     *
     * @param string $colour
     * @return bool returns true if the colour is valid, false otherwise
     */
    public static function validate_colour($colour) {
        // List of valid HTML colour names.
        $colournames = array(
            'aliceblue', 'antiquewhite', 'aqua', 'aquamarine', 'azure',
            'beige', 'bisque', 'black', 'blanchedalmond', 'blue',
            'blueviolet', 'brown', 'burlywood', 'cadetblue', 'chartreuse',
            'chocolate', 'coral', 'cornflowerblue', 'cornsilk', 'crimson',
            'cyan', 'darkblue', 'darkcyan', 'darkgoldenrod', 'darkgray',
            'darkgrey', 'darkgreen', 'darkkhaki', 'darkmagenta',
            'darkolivegreen', 'darkorange', 'darkorchid', 'darkred',
            'darksalmon', 'darkseagreen', 'darkslateblue', 'darkslategray',
            'darkslategrey', 'darkturquoise', 'darkviolet', 'deeppink',
            'deepskyblue', 'dimgray', 'dimgrey', 'dodgerblue', 'firebrick',
            'floralwhite', 'forestgreen', 'fuchsia', 'gainsboro',
            'ghostwhite', 'gold', 'goldenrod', 'gray', 'grey', 'green',
            'greenyellow', 'honeydew', 'hotpink', 'indianred', 'indigo',
            'ivory', 'khaki', 'lavender', 'lavenderblush', 'lawngreen',
            'lemonchiffon', 'lightblue', 'lightcoral', 'lightcyan',
            'lightgoldenrodyellow', 'lightgray', 'lightgrey', 'lightgreen',
            'lightpink', 'lightsalmon', 'lightseagreen', 'lightskyblue',
            'lightslategray', 'lightslategrey', 'lightsteelblue', 'lightyellow',
            'lime', 'limegreen', 'linen', 'magenta', 'maroon',
            'mediumaquamarine', 'mediumblue', 'mediumorchid', 'mediumpurple',
            'mediumseagreen', 'mediumslateblue', 'mediumspringgreen',
            'mediumturquoise', 'mediumvioletred', 'midnightblue', 'mintcream',
            'mistyrose', 'moccasin', 'navajowhite', 'navy', 'oldlace', 'olive',
            'olivedrab', 'orange', 'orangered', 'orchid', 'palegoldenrod',
            'palegreen', 'paleturquoise', 'palevioletred', 'papayawhip',
            'peachpuff', 'peru', 'pink', 'plum', 'powderblue', 'purple', 'red',
            'rosybrown', 'royalblue', 'saddlebrown', 'salmon', 'sandybrown',
            'seagreen', 'seashell', 'sienna', 'silver', 'skyblue', 'slateblue',
            'slategray', 'slategrey', 'snow', 'springgreen', 'steelblue', 'tan',
            'teal', 'thistle', 'tomato', 'turquoise', 'violet', 'wheat', 'white',
            'whitesmoke', 'yellow', 'yellowgreen'
        );

        if (preg_match('/^#?([[:xdigit:]]{3}){1,2}$/', $colour)) {
            return true;
        } else if (in_array(strtolower($colour), $colournames)) {
            return true;
        }

        return false;
    }

    /**
     * Helper function that returns the sequence on a specified customcert page for a
     * newly created element.
     *
     * @param int $pageid the id of the page we are adding this element to
     *
     * @return int the element number
     * @throws \dml_exception
     */
    public static function get_element_sequence($pageid) {
        global $DB;

        // Set the sequence of the element we are creating.
        $sequence = 1;
        // Check if there already elements that exist, if so, overwrite value.
        $sql = "SELECT MAX(sequence) as maxsequence
                  FROM {coursepayment_elements}
                 WHERE pageid = :id";
        // Get the current max sequence on this page and add 1 to get the new sequence.
        if ($maxseq = $DB->get_record_sql($sql, array('id' => $pageid))) {
            $sequence = $maxseq->maxsequence + 1;
        }

        return $sequence;
    }

    /**
     * Helper function that returns the course id for this element.
     *
     * @param int $elementid The element id
     *
     * @return int The course id
     * @throws \dml_exception
     */
    public static function get_courseid($elementid) {
        global $DB, $SITE;

        $sql = "SELECT course
                  FROM {customcert} c
            INNER JOIN {coursepayment_pages} cp
                    ON c.templateid = cp.templateid
            INNER JOIN {coursepayment_elements} ce
                    ON cp.id = ce.pageid
                 WHERE ce.id = :elementid";

        // Check if there is a course associated with this element.
        if ($course = $DB->get_record_sql($sql, array('elementid' => $elementid))) {
            return $course->course;
        } else { // Must be in a site template.
            return $SITE->id;
        }
    }

    /**
     * Return the list of possible elements to add.
     *
     * @return array the list of element types that can be used.
     * @throws \coding_exception
     */
    public static function get_available_element_types() {
        global $CFG;

        // Array to store the element types.
        $options = array();

        // Check that the directory exists.
        $elementdir = "$CFG->dirroot/enrol/coursepayment/classes/invoice/element";

        if (file_exists($elementdir)) {
            // Get directory contents.
            $elementfolders = new \DirectoryIterator($elementdir);
            // Loop through the elements folder.
            foreach ($elementfolders as $elementfolder) {
                // If it is not a directory or it is '.' or '..', skip it.
                if (!$elementfolder->isDir() || $elementfolder->isDot()) {
                    continue;
                }
                // Check that the standard class exists, if not we do
                // not want to display it as an option as it will not work.
                $foldername = $elementfolder->getFilename();

                // Get the class name.
                $classname = '\\enrol_coursepayment\\invoice\\element\\' . $foldername . '\\element';

                // Ensure the necessary class exists.
                if (class_exists($classname)) {
                    $options[$foldername] = get_string('invoice_element_' . $foldername, 'enrol_coursepayment');
                }
            }
        }

        \core_collator::asort($options);
        return $options;
    }

    /**
     * Helper function to return all the grades items for a given course.
     *
     * @param \stdClass $course The course we want to return the grade items for
     *
     * @return array the array of gradeable items in the course
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function get_grade_items($course) {
        global $DB;

        // Array to store the grade items.
        $modules = array();

        // Collect course modules data.
        $modinfo = get_fast_modinfo($course);
        $mods = $modinfo->get_cms();
        $sections = $modinfo->get_section_info_all();

        // Create the section label depending on course format.
        $sectionlabel = get_string('section');
        if ($course->format == 'topics') {
            $sectionlabel = get_string('topic');
        } else if ($course->format == 'weeks') {
            $sectionlabel = get_string('week');
        }

        // Loop through each course section.
        for ($i = 0; $i <= count($sections) - 1; $i++) {
            // Confirm the index exists, should always be true.
            if (isset($sections[$i])) {
                // Get the individual section.
                $section = $sections[$i];
                // Get the mods for this section.
                $sectionmods = explode(",", $section->sequence);
                // Loop through the section mods.
                foreach ($sectionmods as $sectionmod) {
                    // Should never happen unless DB is borked.
                    if (empty($mods[$sectionmod])) {
                        continue;
                    }
                    $mod = $mods[$sectionmod];
                    $instance = $DB->get_record($mod->modname, array('id' => $mod->instance));
                    // Get the grade items for this activity.
                    if ($gradeitems = grade_get_grade_items_for_activity($mod)) {
                        $moditem = grade_get_grades($course->id, 'mod', $mod->modname, $mod->instance);
                        $gradeitem = reset($moditem->items);
                        if (isset($gradeitem->grademax)) {
                            $modules[$mod->id] = $sectionlabel . ' ' . $section->section . ' : ' . $instance->name;
                        }
                    }
                }
            }
        }

        if ($gradeitems = \grade_item::fetch_all(['courseid' => $course->id])) {
            $arrgradeitems = [];
            foreach ($gradeitems as $gi) {
                // Skip the course and mod items since we already have them.
                if ($gi->itemtype == 'mod' || $gi->itemtype == 'course') {
                    continue;
                }
                $arrgradeitems['gradeitem:' . $gi->id] = get_string('gradeitem', 'grades') . ' : ' . $gi->get_name(true);
            }

            // Alphabetise this.
            asort($arrgradeitems);

            // Merge results.
            $modules = $modules + $arrgradeitems;
        }

        return $modules;
    }

    /**
     * Helper function to return the grade information for a course for a specified user.
     *
     * @param int $courseid
     * @param int $gradeformat
     * @param int $userid
     * @return grade_information|bool the grade information, or false if there is none.
     */
    public static function get_course_grade_info($courseid, $gradeformat, $userid) {
        $courseitem = \grade_item::fetch_course_item($courseid);

        if (!$courseitem) {
            return false;
        }

        // Define how many decimals to display.
        $decimals = 2;
        if ($gradeformat == GRADE_DISPLAY_TYPE_PERCENTAGE) {
            $decimals = 0;
        }

        $grade = new \grade_grade(array('itemid' => $courseitem->id, 'userid' => $userid));

        return new grade_information(
            $courseitem->get_name(),
            $grade->finalgrade,
            grade_format_gradevalue($grade->finalgrade, $courseitem, true, $gradeformat, $decimals),
            $grade->get_dategraded()
        );
    }

    /**
     * Helper function to return the grade information for a module for a specified user.
     *
     * @param int $cmid
     * @param int $gradeformat
     * @param int $userid
     *
     * @return grade_information|bool the grade information, or false if there is none.
     * @throws \dml_exception
     */
    public static function get_mod_grade_info($cmid, $gradeformat, $userid) {
        global $DB;

        if (!$cm = $DB->get_record('course_modules', array('id' => $cmid))) {
            return false;
        }

        if (!$module = $DB->get_record('modules', array('id' => $cm->module))) {
            return false;
        }

        $gradeitem = grade_get_grades($cm->course, 'mod', $module->name, $cm->instance, $userid);

        if (empty($gradeitem)) {
            return false;
        }

        // Define how many decimals to display.
        $decimals = 2;
        if ($gradeformat == GRADE_DISPLAY_TYPE_PERCENTAGE) {
            $decimals = 0;
        }

        $item = new \grade_item();
        $item->gradetype = GRADE_TYPE_VALUE;
        $item->courseid = $cm->course;
        $itemproperties = reset($gradeitem->items);
        foreach ($itemproperties as $key => $value) {
            $item->$key = $value;
        }

        $objgrade = $item->grades[$userid];

        $dategraded = null;
        if (!empty($objgrade->dategraded)) {
            $dategraded = $objgrade->dategraded;
        }

        return new grade_information(
            $item->name,
            $objgrade->grade,
            grade_format_gradevalue($objgrade->grade, $item, true, $gradeformat, $decimals),
            $dategraded
        );
    }

    /**
     * Helper function to return the grade information for a grade item for a specified user.
     *
     * @param int $gradeitemid
     * @param int $gradeformat
     * @param int $userid
     * @return grade_information|bool the grade information, or false if there is none.
     */
    public static function get_grade_item_info($gradeitemid, $gradeformat, $userid) {
        if (!$gradeitem = \grade_item::fetch(['id' => $gradeitemid])) {
            return false;
        }

        // Define how many decimals to display.
        $decimals = 2;
        if ($gradeformat == GRADE_DISPLAY_TYPE_PERCENTAGE) {
            $decimals = 0;
        }

        $grade = new \grade_grade(array('itemid' => $gradeitem->id, 'userid' => $userid));

        return new grade_information(
            $gradeitem->get_name(),
            $grade->finalgrade,
            grade_format_gradevalue($grade->finalgrade, $gradeitem, true, $gradeformat, $decimals),
            $grade->get_dategraded()
        );
    }
}
