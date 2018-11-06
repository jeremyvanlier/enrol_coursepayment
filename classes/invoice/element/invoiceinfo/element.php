<?php
// This file is part of the customcert module for Moodle - http://moodle.org/
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
 * This file contains the coursepayment element orderdata's core interaction API.
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

namespace enrol_coursepayment\invoice\element\invoiceinfo;

defined('MOODLE_INTERNAL') || die();

/**
 * The  element text's core interaction API.
 *
 * @package    enrol_coursepayment
 * @copyright  2018 MFreak.nl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class element extends \enrol_coursepayment\invoice\element {

    /**
     * This function renders the form elements when adding a customcert element.
     *
     * @param \enrol_coursepayment\invoice\edit_element_form $mform the edit_form instance
     *
     * @throws \coding_exception
     */
    public function render_form_elements($mform) {
        parent::render_form_elements($mform);
    }

    /**
     * This will handle how form data will be saved into the data column in the
     * coursepayment_elements table.
     *
     * @param \stdClass $data the form data
     *
     * @return string the text
     */
    public function save_unique_data($data) {
        return '';
    }

    /**
     * Handles rendering the element on the pdf.
     *
     * @param \pdf      $pdf     the pdf object
     * @param bool      $preview true if it is a preview, false otherwise
     * @param \stdClass $user    the user we are rendering this for
     *
     * @throws \dml_exception
     * @throws \coding_exception
     */
    public function render($pdf, $preview, $user) {
        global $PAGE;

        $renderer = $PAGE->get_renderer('enrol_coursepayment');
        $text = $renderer->render_template('enrol_coursepayment/element_invoiceinfo' , $this->get_invoiceinfo());

        \enrol_coursepayment\invoice\element_helper::render_content($pdf, $this, $text);
    }

    /**
     * get_invoiceinfo
     *
     * @param bool $includedummydata
     *
     * @return object
     * @throws \dml_exception
     */
    public function get_invoiceinfo($includedummydata = false) {
        $pluginconfig = get_config('enrol_coursepayment');

        $data = (object) [
            'companyname' => $pluginconfig->companyname,
            'address' => $pluginconfig->address,
            'place' => $pluginconfig->place,
            'zipcode' => $pluginconfig->zipcode,
            'kvk' => $pluginconfig->kvk,
            'currency' => $pluginconfig->currency,
            'date' => date('d-m-Y, H:i'),
            'description' => 'TEST'
        ];

        if($includedummydata){
            $data->invoice_number =  'CPAY' . date("Y") . sprintf('%08d', 1);
        }

        return $data;
    }

    /**
     * Render the element in html.
     *
     * This function is used to render the element when we are using the
     * drag and drop interface to position it.
     *
     * @return string the html
     * @throws \dml_exception
     */
    public function render_html() {
        global $PAGE;

        $renderer = $PAGE->get_renderer('enrol_coursepayment');
        return $renderer->render_template('enrol_coursepayment/element_invoiceinfo' , $this->get_invoiceinfo(true));
    }

    /**
     * Sets the data on the form when editing an element.
     *
     * @param \enrol_coursepayment\invoice\edit_element_form $mform the edit_form instance
     */
    public function definition_after_data($mform) {
        parent::definition_after_data($mform);
    }
}
