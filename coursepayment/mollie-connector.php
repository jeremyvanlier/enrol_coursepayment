<?php
/**
 * File: avetica-mollie-connect-account.php
 * Encoding: UTF8
 *
 * @Version: 1.0.0
 * @Since  10-2-2017
 * @Author : MoodleFreak.com | Ldesign.nl - Luuk Verhoeven
 *
 *  This REST API allows to connect other mollie accounts
 **/

if (!defined('AJAX_SCRIPT')) {
    define('AJAX_SCRIPT', true);
}
define('NO_DEBUG_DISPLAY', true);

require('../../config.php');
$PAGE->set_url('/enrol/coursepayment/mollie-connector.php');

require_once dirname(__FILE__) . "/libs/Mollie/API/Autoloader.php";

// Get params.
$action = required_param('action', PARAM_ALPHA);
$data = required_param('data', PARAM_RAW);

// Get plugin config.
$config = get_config('enrol_coursepayment');

// Check if this feature is turned on.
if(empty($config->gateway_mollie_external_connector)){
    throw new Exception('No external API calls allowed!');
}

$this->client = new Mollie_API_Client();
$this->client->setApiKey($config->gateway_mollie_apikey);

// Get the action we need to process.


