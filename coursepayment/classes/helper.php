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
     * @param array $data
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
}