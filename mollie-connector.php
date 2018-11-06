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
 * Allow Mollie master account claim.
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package   enrol_coursepayment
 * @copyright 2015 MFreak.nl
 * @author    Luuk Verhoeven
 **/

if (!defined('AJAX_SCRIPT')) {
    define('AJAX_SCRIPT', true);
}
define('NO_DEBUG_DISPLAY', true);

require('../../config.php');
defined('MOODLE_INTERNAL') || die();

$PAGE->set_url('/enrol/coursepayment/mollie-connector.php');

// Get params.
$action = required_param('action', PARAM_ALPHA);
$data = required_param('data', PARAM_RAW);

// Get plugin config.
$config = get_config('enrol_coursepayment');

// Check if this feature is turned on.
if (empty($config->gateway_mollie_external_connector)) {
    throw new Exception('No external API calls allowed!');
}

$mollie = new enrol_coursepayment_mollie();

$return = ['success' => false];

// Get the action we need to process.
switch ($action) {
    case 'newaccount':
        $return = $mollie->add_new_account($data);
        break;
    case 'claim':
        $return = $mollie->claim_new_account($data);
        break;

}
echo json_encode($return);