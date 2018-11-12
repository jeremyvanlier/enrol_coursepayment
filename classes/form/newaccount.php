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
 * Create a new account Form
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package   enrol_coursepayment
 * @copyright 2017 MFreak.nl
 * @author    Luuk Verhoeven
 */

namespace enrol_coursepayment\form;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

global $CFG;
require_once($CFG->libdir . '/formslib.php');

class newaccount extends \moodleform {

    protected function definition() {

        $mform = &$this->_form;

        $mform->addElement('header', 'header1', get_string('form:newaccount', 'enrol_coursepayment'));

        // Fullname customer.
        $mform->addElement('text', 'name', get_string('form:name', 'enrol_coursepayment'),
            ['size' => '48',]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        // Company customer.
        $mform->addElement('text', 'company_name', get_string('form:company_name', 'enrol_coursepayment'),
            ['size' => '48',]);
        $mform->setType('company_name', PARAM_TEXT);
        $mform->addRule('company_name', null, 'required', null, 'client');

        // Email customer.
        $mform->addElement('text', 'email', get_string('form:email', 'enrol_coursepayment'),
            ['size' => '48',]);
        $mform->setType('email', PARAM_EMAIL);
        $mform->addRule('email', null, 'required', null, 'client');

        // Address (street and number) customer.
        $mform->addElement('text', 'address', get_string('form:address', 'enrol_coursepayment'),
            ['size' => '48',]);
        $mform->setType('address', PARAM_TEXT);
        $mform->addRule('address', null, 'required', null, 'client');

        // Zipcode customer.
        $mform->addElement('text', 'zipcode', get_string('form:zipcode', 'enrol_coursepayment'),
            ['size' => '48',]);
        $mform->setType('zipcode', PARAM_TEXT);
        $mform->addRule('zipcode', null, 'required', null, 'client');

        // City customer.
        $mform->addElement('text', 'city', get_string('form:city', 'enrol_coursepayment'),
            ['size' => '48',]);
        $mform->setType('city', PARAM_TEXT);
        $mform->addRule('city', null, 'required', null, 'client');

        $this->add_action_buttons(true, get_string('form:register', 'enrol_coursepayment'));
    }
}