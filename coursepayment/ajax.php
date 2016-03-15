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
 * Ajax api calls
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package   enrol_coursepayment
 * @copyright 2015 MoodleFreak.com
 * @author    Luuk Verhoeven
 **/

if (!defined('AJAX_SCRIPT')) {
    define('AJAX_SCRIPT', true);
}
define('NO_DEBUG_DISPLAY', true);

require('../../config.php');
$PAGE->set_url('/enrol/coursepayment/ajax.php');

require_login(get_site(), true, null, true, true);

// Params
$sesskey = required_param('sesskey', PARAM_RAW);
$courseid = required_param('courseid', PARAM_INT);
$action = required_param('action', PARAM_ALPHA);
$data = required_param('data', PARAM_RAW);

// Get the course
$course = $DB->get_record('course' , array('id'=>$courseid) , '*' , MUST_EXIST);

// Default return
$array = array('error' => '', 'status' => false);

if (!confirm_sesskey($sesskey)) {
    $array['error'] = get_string('failed:sesskey', 'block_mambo');
}

if (empty($array['error'])) {

    switch($action){
        case 'discountcode':
            // Validate a discount code
            $discountinstance = new enrol_coursepayment_discountcode($data , $courseid);
            $row = $discountinstance->getDiscountcode();

            if($row){

                $array['amount'] = $row->amount;
                $array['percentage'] = $row->percentage;
                $array['status'] = true;

                unset($array['error']);
            }else{
                $array['error'] = $discountinstance->getLastErrorString();
            }
            break;
    }
}

echo json_encode($array);