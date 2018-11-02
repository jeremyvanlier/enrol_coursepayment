<?php
/**
 * File: avetica-mollie-connect-account.php
 * Encoding: UTF8
 *
 * @Version: 1.0.0
 * @Since  10-2-2017
 * @Author : MFreak.nl | Ldesign.nl - Luuk Verhoeven
 *
 *  This REST API allows to connect other mollie accounts
 **/

if (!defined('AJAX_SCRIPT')) {
    define('AJAX_SCRIPT', true);
}
define('NO_DEBUG_DISPLAY', true);

require('../../config.php');
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