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
 * Helper functions
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package   enrol_coursepayment
 * @copyright 2018 MFreak.nl
 * @author    Luuk Verhoeven
 */
namespace enrol_coursepayment\invoice;
defined('MOODLE_INTERNAL') || die;

final class helper {

    /**
     * Handles uploading an image for the customcert module.
     *
     * @param int $draftitemid the draft area containing the files
     * @param int $contextid the context we are storing this image in
     * @param string $filearea indentifies the file area.
     */
    public static function upload_files($draftitemid, $contextid, $filearea = 'image') {
        global $CFG;

        // Save the file if it exists that is currently in the draft area.
        require_once($CFG->dirroot . '/lib/filelib.php');
        file_save_draft_area_files($draftitemid, $contextid, 'enrol_coursepayment', $filearea, 0);
    }
}