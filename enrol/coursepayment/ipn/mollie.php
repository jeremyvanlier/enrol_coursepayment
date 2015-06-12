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
 * webhook for mollie
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package   enrol_coursepayment
 * @copyright 2015 MoodleFreak.com
 * @author    Luuk Verhoeven
 */

define('NO_DEBUG_DISPLAY', true);

require("../../../config.php");
require_once("../lib.php");

set_exception_handler('enrol_coursepayment_ipn_exception_handler');

$orderid = required_param('orderid', PARAM_ALPHANUMEXT);
$instanceid = required_param('instanceid', PARAM_INT); // if no instanceid is given
if (! $plugininstance = $DB->get_record("enrol", array("id"=>$instanceid, "status"=>0))) {
    throw new Exception(get_string('error:failed_getting_plugin_instance' , 'enrol_coursepayment'));
}
$return = enrol_get_plugin('coursepayment')->order_valid($orderid, 'mollie' , $plugininstance);

if ($return['status'] == true) {

    echo 'success';

} else {

    // send a status message to user
    throw new Exception($return['message']);
}

/**
 *  exception handler.
 *
 * @param Exception $ex
 */
function enrol_coursepayment_ipn_exception_handler($ex)
{
    // header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);

    $info = get_exception_info($ex);

    $logerrmsg = "IPN exception handler: ".$info->message;
    echo $logerrmsg;

    if (debugging('', DEBUG_NORMAL)) {
        $logerrmsg .= ' Debug: '.$info->debuginfo."\n".format_backtrace($info->backtrace, true);
    }
    error_log($logerrmsg);
}