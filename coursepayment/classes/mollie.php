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
 * Mollie gateway wrapper convert internal methods to Mollie API
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package   enrol_coursepayment
 * @copyright 2015 MoodleFreak.com
 * @author    Luuk Verhoeven
 */
defined('MOODLE_INTERNAL') || die();

class enrol_coursepayment_mollie extends enrol_coursepayment_gateway {

    protected $name = 'mollie';

    /**
     * class container
     *
     * @var Mollie_API_Client
     */
    protected $client;


    public function __construct() {
        parent::__construct();
        require_once dirname(__FILE__) . "/../libs/Mollie/API/Autoloader.php";

        $this->client = new Mollie_API_Client();
        $this->client->setApiKey($this->config->apikey);
    }


    /**
     * validate if a payment provider has a valid ip address
     *
     * @return boolean
     */
    public function ip_validation() {
        // The rationale people give for requesting and using that IP information is for whitelisting purposes.
        // The thought being that by actively denying any requests from other IPs they hope
        // to secure their website from hackers that might be trying to get a paid order without making an actual payment.
        // However, this IP check is not required since the webhook script will always need to actively fetch the payment from the Mollie API,
        // and check its status that way. If you are whitelisting and Mollie ever changes IPs, you might miss this news and be left with a broken store.
        // Without improved security or any other benefit.
        return true;
    }

    /**
     * add new activity order from a user
     *
     * @param string $method
     * @param string $issuer
     * @param string $discountcode
     *
     * @return array
     * @throws moodle_exception
     * @global moodle_database $DB
     */
    public function new_order_activity($method = '', $issuer = '', $discountcode = '') {

        global $CFG, $DB;

        // extra order data
        $data = array();

        if (!empty($discountcode)) {

            // validate the discountcode we received
            $discountinstance = new enrol_coursepayment_discountcode($discountcode, $this->instanceconfig->courseid);
            $row = $discountinstance->getDiscountcode();

            if ($row) {
                // looks okay we need to save this to the order
                $data['discount'] = $row;
            } else {

                return array(
                    'status' => false,
                    'error_discount' => true,
                    'message' => $discountinstance->getLastErrorString()
                );
            }
        }

        // add new internal order
        $order = $this->create_new_activity_order_record($data);
        try {

            if ($order['cost'] == 0) {
                redirect($CFG->wwwroot . '/enrol/coursepayment/return.php?orderid=' . $order['orderid'] . '&gateway=' . $this->name . '&instanceid=' . $this->instanceconfig->instanceid);

                return;
            }

            // https://www.mollie.com/en/docs/payments
            $payment = $this->client->payments->create(array(
                "amount" => $order['cost'],
                "method" => $method,
                "locale" => (in_array($this->instanceconfig->locale, array(
                    'de',
                    'en',
                    'fr',
                    'es',
                    'nl'
                )) ? $this->instanceconfig->locale : 'en'),
                "description" => $this->instanceconfig->coursename,
                "redirectUrl" => $CFG->wwwroot . '/enrol/coursepayment/return.php?orderid=' . $order['orderid'] . '&gateway=' . $this->name . '&instanceid=' . $this->instanceconfig->instanceid,
                "webhookUrl" => $CFG->wwwroot . '/enrol/coursepayment/ipn/mollie.php?orderid=' . $order['orderid'] . '&gateway=' . $this->name . '&instanceid=' . $this->instanceconfig->instanceid,
                "metadata" => array(
                    "order_id" => $order['orderid'],
                    "id" => $order['id'],
                    "userid" => $this->instanceconfig->userid,
                    "userfullname" => $this->instanceconfig->userfullname,
                ),
                "issuer" => !empty($issuer) ? $issuer : null
            ));

            // update the local order we add the gateway identifier to the order
            $obj = new stdClass();
            $obj->id = $order['id'];
            $obj->gateway_transaction_id = $payment->id;
            $DB->update_record('enrol_coursepayment', $obj);

            // send the user to the gateway payment page
            redirect($payment->getPaymentUrl());

        } catch (Mollie_API_Exception $e) {
            $this->log("API call failed: " . htmlspecialchars($e->getMessage()));
        }

        return array('status' => false);
    }


    /**
     * add new order from a user
     *
     * @param string $method
     * @param string $issuer
     * @param string $discountcode
     *
     * @return array
     * @throws moodle_exception
     * @global moodle_database $DB
     */
    public function new_order_course($method = '', $issuer = '', $discountcode = '') {

        global $CFG, $DB;

        // extra order data
        $data = array();

        if (!empty($discountcode)) {

            // validate the discountcode we received
            $discountinstance = new enrol_coursepayment_discountcode($discountcode, $this->instanceconfig->courseid);
            $row = $discountinstance->getDiscountcode();

            if ($row) {
                // looks okay we need to save this to the order
                $data['discount'] = $row;
            } else {

                return array(
                    'status' => false,
                    'error_discount' => true,
                    'message' => $discountinstance->getLastErrorString()
                );
            }
        }

        // add new internal order
        $order = $this->create_new_course_order_record($data);
        try {

            if ($order['cost'] == 0) {
                redirect($CFG->wwwroot . '/enrol/coursepayment/return.php?orderid=' . $order['orderid'] . '&gateway=' . $this->name . '&instanceid=' . $this->instanceconfig->instanceid);

                return;
            }

            // https://www.mollie.com/en/docs/payments
            $payment = $this->client->payments->create(array(
                "amount" => $order['cost'],
                "method" => $method,
                "locale" => (in_array($this->instanceconfig->locale, array(
                    'de',
                    'en',
                    'fr',
                    'es',
                    'nl'
                )) ? $this->instanceconfig->locale : 'en'),
                "description" => $this->instanceconfig->coursename,
                "redirectUrl" => $CFG->wwwroot . '/enrol/coursepayment/return.php?orderid=' . $order['orderid'] . '&gateway=' . $this->name . '&instanceid=' . $this->instanceconfig->instanceid,
                "webhookUrl" => $CFG->wwwroot . '/enrol/coursepayment/ipn/mollie.php?orderid=' . $order['orderid'] . '&gateway=' . $this->name . '&instanceid=' . $this->instanceconfig->instanceid,
                "metadata" => array(
                    "order_id" => $order['orderid'],
                    "id" => $order['id'],
                    "userid" => $this->instanceconfig->userid,
                    "userfullname" => $this->instanceconfig->userfullname,
                ),
                "issuer" => !empty($issuer) ? $issuer : null
            ));

            // update the local order we add the gateway identifier to the order
            $obj = new stdClass();
            $obj->id = $order['id'];
            $obj->gateway_transaction_id = $payment->id;
            $DB->update_record('enrol_coursepayment', $obj);

            // send the user to the gateway payment page
            redirect($payment->getPaymentUrl());

        } catch (Mollie_API_Exception $e) {
            $this->log("API call failed: " . htmlspecialchars($e->getMessage()));
        }

        return array('status' => false);
    }

    /**
     * handle the return of payment provider
     *
     * @return boolean
     */
    public function callback() {
        return true;// not used for now
    }

    /**
     * render the order_form of the gateway to allow order
     *
     * @return string
     */
    public function order_form() {

        global $PAGE;

        // check if the gateway is enabled
        if ($this->config->enabled == 0) {
            return '';
        }

        $method = optional_param('method', false, PARAM_ALPHA);

        $itemtype = 'course';
        if (!empty($this->instanceconfig->is_activity)) {
            $itemtype = 'activity';
        }

        $issuer = optional_param('issuer', false, PARAM_ALPHANUMEXT);
        $discountcode = optional_param('discountcode', false, PARAM_ALPHANUMEXT);
        $status = array();

        // method is selected by the user
        if (!empty($method)) {

            switch ($itemtype) {
                case 'activity':
                    $status = $this->new_order_activity($method, $issuer, $discountcode);
                    break;

                default:
                    $status = $this->new_order_course($method, $issuer, $discountcode);
            }

            if (isset($status['status']) && $status['status'] == false) {
                // we showing the same form again
            } else {
                return;
            }
        }

        $PAGE->requires->js('/enrol/coursepayment/js/mollie.js');
        $string = '';

        try {
            $string .= '<div align="center">
                            <p>' . get_string('gateway_mollie_select_method', 'enrol_coursepayment') . '</p>
                    <form id="coursepayment_mollie_form" action="" method="post">
                    <table id="coursepayment_mollie_gateways" cellpadding="5">';
            $methods = $this->client->methods->all();
            $i = 0;
            foreach ($methods as $method) {

                $string .= '<tr data-method="' . $method->id . '" class="' . $method->id . (($i == 0) ? ' selected' : '') . '">';
                $string .= '<td><b>' . htmlspecialchars($method->description) . '</b></td>';
                $string .= '<td><img src="' . htmlspecialchars($method->image->normal) . '"></td>';
                $string .= '</tr>';

                if ($method->id == Mollie_API_Object_Method::IDEAL) {

                    $issuers = $this->client->issuers->all();
                    $string .= '<tr id="issuers_ideal" class="skip">
                                    <td>
                                    <select name="issuer">
                                        <option value="">' . get_string('gateway_mollie_issuers', 'enrol_coursepayment') . '</option>';

                    foreach ($issuers as $issuer) {
                        if ($issuer->method == Mollie_API_Object_Method::IDEAL) {
                            $string .= '<option value=' . htmlspecialchars($issuer->id) . '>' . htmlspecialchars($issuer->name) . '</option>';
                        }
                    }
                    $string .= '</select></td><td>&nbsp;</td></tr>';
                }
                $i++;
            }

            $string .= '</table>';
            $string .= $this->form_discount_code($discountcode, $status);
            $string .= '<input type="hidden" name="gateway" value="' . $this->name . '" />
                    <input type="hidden" id="input_method" name="method" value="" />
                    <input type="submit" class="form-submit" value="' . get_string('purchase', "enrol_coursepayment") . '" />
                </form>
            </div>';

        } catch (Mollie_API_Exception $e) {
            $this->log("API call failed: " . htmlspecialchars($e->getMessage()));
        }

        return $string;

    }

    /*
	 * Get the all the activated methods for this API key.
	 */
    public function get_enabled_modes() {

        $string = '';
        try {

            $methods = $this->client->methods->all();

            $string .= '<table class="coursepayment_setting_mollie" cellpadding="5">
                            <tr>
                                <th style="text-align: left">' . get_string('provider', 'enrol_coursepayment') . '</th>
                                <th style="text-align: left">' . get_string('name', 'enrol_coursepayment') . '</th>
                                <th style="text-align: left">' . get_string('minimum', 'enrol_coursepayment') . '</th>
                                <th style="text-align: left">' . get_string('maximum', 'enrol_coursepayment') . '</th>
                            </tr>';

            foreach ($methods as $method) {
                $string .= '<tr>';
                $string .= '<td><img src="' . htmlspecialchars($method->image->normal) . '"> </td>';
                $string .= '<td>' . htmlspecialchars($method->description) . '</td>';
                $string .= '<td>' . $method->amount->minimum . '</td>';
                $string .= '<td>' . $method->amount->maximum . '</td>';
                $string .= '</tr>';
            }
            $string .= '</table>';
        } catch (Mollie_API_Exception $e) {
            $this->log("API call failed: " . htmlspecialchars($e->getMessage()));
        }

        return $string;
    }

    /**
     * check if order is really paid
     *
     * @param string $orderid
     *
     * @return array
     */
    public function validate_order($orderid = '') {
        global $DB;

        if (parent::validate_order($orderid)) {
            // first let it check by main class
            return array('status' => true, 'message' => 'free_payment');
        }

        $return = array('status' => false, 'message' => '');

        // first check if we know of it
        $row = $DB->get_record('enrol_coursepayment', array('orderid' => $orderid, 'gateway' => $this->name));


        if ($row) {

            // missing a transactionid this is not good
            if (empty($row->gateway_transaction_id)) {
                $obj = new stdClass();
                $obj->id = $row->id;
                $obj->timeupdated = time();
                $obj->status = self::PAYMENT_STATUS_ERROR;
                $DB->update_record('enrol_coursepayment', $obj);

                $return['status'] = false;
                $return['message'] = 'empty_transaction_id';

                return $return;
            }

            // payment already marked as paid
            if ($row->status == self::PAYMENT_STATUS_SUCCESS) {
                $return['status'] = true;
                $return['message'] = 'already_marked_as_paid';

                return $return;
            }

            try {

                // get details from gateway
                $payment = $this->client->payments->get($row->gateway_transaction_id);
                $obj = new stdClass();
                $obj->id = $row->id;
                $obj->timeupdated = time();

                if ($payment->isPaid() == true && $row->status != self::PAYMENT_STATUS_SUCCESS) {

                    // Get a new invoice number
                    $obj->invoice_number = ($payment->mode != 'test') ? $this->get_new_invoice_number() : 0;

                    // Sending the invoice to customer
                    // Make sure we save invoice number to prevent incorrect number
                    $this->send_invoice($row, $obj->invoice_number, ucfirst($this->name));
                    $DB->update_record('enrol_coursepayment', $obj);


                    // At this point you'd probably want to start the process of delivering the product to the customer.
                    if ($this->enrol($row)) {
                        $obj->status = self::PAYMENT_STATUS_SUCCESS;
                        $return['status'] = true;
                    }

                } elseif ($payment->isOpen() == false) {

                    // The payment isn't paid and isn't open anymore. We can assume it was aborted.
                    // we can mark this payment as aborted
                    $obj->status = self::PAYMENT_STATUS_ABORT;
                    $return['message'] = get_string('error:paymentabort', 'enrol_coursepayment');
                }

                $DB->update_record('enrol_coursepayment', $obj);

            } catch (Mollie_API_Exception $e) {
                $this->log("API call failed: " . htmlspecialchars($e->getMessage()));
                $return['message'] = get_string('error:gettingorderdetails', 'enrol_coursepayment');
            }

        } else {
            $return['message'] = get_string('error:unknown_order', 'enrol_coursepayment');
        }

        return $return;
    }

    /**
     * This function will update invoice numbers
     * Only needed when upgrading a version lower then 2015061201
     */
    public function upgrade_invoice_numbers() {

        global $DB;

        $results = $DB->get_records('enrol_coursepayment', array('gateway' => $this->name, 'invoice_number' => 0));

        foreach ($results as $result) {

            // Making sure its a real payment, no invoice number will be generated for a test order
            try {
                $item = $this->client->payments->get($result->gateway_transaction_id);
                if (!empty($item)) {
                    if ($item->mode == 'test') {
                        continue;
                    }
                }
            } catch (Exception $exc) {
            }

            echo $result->id . ': add invoice number<br/>';

            $obj = new stdClass();
            $obj->id = $result->id;
            $obj->invoice_number = $this->get_new_invoice_number();
            $DB->update_record('enrol_coursepayment', $obj);
        }
    }

    /**
     * Create a new child account
     * https://www.mollie.com/nl/support/post/documentatie-reseller-api#ref-account-create
     *
     * @param $data
     *
     * @return array
     */
    public function add_new_account($data) {
        $return = [
            'success' => false,
            'error' => ''
        ];

        $data = unserialize($data);

        $fields = [
            'username',
            'name',
            'company_name',
            'email',
            'address',
            'city',
        ];

        // Validate all data exists.
        foreach ($fields as $field) {
            if (!array_key_exists($field, $data)) {
                $return['error'] = 'Missing "' . $field . '" field!';

                return $return;
            }
        }

        // Sending request to Mollie..

        // 1. Register Mollie_Autoloader
        require_once dirname(__FILE__) . "/../libs/Mollie/RESELLER/autoloader.php";
        Mollie_Autoloader::register();

        // 2. Define Mollie config
        $partner_id = 1790631;
        $profile_key = 'F2737D9B';
        $app_secret = '6950FDCAB27914E77CDFFBFCF8B7F121ECDB8CD2';

        // 3. Instantiate class with Mollie config
        $mollie = new Mollie_Reseller($partner_id, $profile_key, $app_secret);

        // 4. Call API accountCreate
        try {
            $data->country = 'NL';
            $simplexml = $mollie->accountCreate($data->username, (array)$data);
        } catch (Mollie_Exception $e) {
            die('An error occurred when creating an account: ' . $e->getMessage());
        }

        var_dump($simplexml);

        return $return;
    }

    /**
     * Claim a new mollie account
     * https://www.mollie.com/nl/support/post/documentatie-reseller-api#ref-account-claim
     *
     * @param $data
     *
     * @return array
     */
    public function claim_new_account($data) {

        $return = [
            'success' => false,
            'error' => ''
        ];

        $data = unserialize($data);

        $fields = [
            'username',
            'password',
        ];

        // Validate all data exists.
        foreach ($fields as $field) {
            if (!array_key_exists($field, $data)) {
                $return['error'] = 'Missing "' . $field . '" field!';

                return $return;
            }
        }


    }

}