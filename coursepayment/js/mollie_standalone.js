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
 * helper for selecting a payment option
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package   enrol_coursepayment
 * @copyright 2017 MoodleFreak.com
 * @author    Luuk Verhoeven
 */

/**
 * Gateway mollie standalone page class
 *
 * @package   enrol_coursepayment
 * @copyright 2015 MoodleFreak.com
 * @author    Luuk Verhoeven
 */
M.enrol_coursepayment_mollie_standalone = {
    config:           {
        'courseid': 0,
        'sesskey':  '',
        'ajaxurl':  ''
    },
    log:              function (val) {
        try {
            Y.log(val);

        } catch (e) {
            try {
                console.warn(val);
            } catch (e) {
            }
        }
    },
    init:             function (Y, ajaxurl, sesskey, courseid) {
        M.enrol_coursepayment_mollie_standalone.log('Load: M.enrol_coursepayment_mollie_standalone');

        // Set config
        this.config.courseid = courseid;
        this.config.ajaxurl = ajaxurl;
        this.config.sesskey = sesskey;

        M.enrol_coursepayment_mollie_standalone.log(this.config);
        if (Y.one('#discountcode')) {
            this.validatediscount();
        }
        if(Y.one('#coursepayment_agreement')){
            this.validate_agreement();
        }
    },
    /**
     * Validate agreement checkbox on form submit.
     */
    validate_agreement: function () {
        M.enrol_coursepayment_mollie_standalone.log('validate_agreement()');
        Y.all('.coursepayment_mollie_form').on("submit" , function (e) {
            M.enrol_coursepayment_mollie_standalone.log('submit()');

            M.enrol_coursepayment_mollie_standalone.log(e);

            if(!Y.one('#coursepayment_agreement').get('checked')){
                e.preventDefault();
            }
        });
    },
    /**
     * Validate discount code.
     */
    validatediscount: function () {
        M.enrol_coursepayment_mollie_standalone.log('validatediscount()');

        var costorignal = Y.one('#cost').get("text");
        M.enrol_coursepayment_mollie_standalone.log(costorignal);

        Y.one('#discountcode').on("keyup", function (e) {

            var config = M.enrol_coursepayment_mollie_standalone.config;
            Y.io(config.ajaxurl, {
                method:  'GET',
                data:    {
                    'action':   'discountcode',
                    'courseid': config.courseid,
                    'sesskey':  config.sesskey,
                    'data':     Y.one('#discountcode').get('value')
                },
                on:      {
                    success: function (id, o) {
                        try {
                            var response = Y.JSON.parse(o.responseText);
                            if (response.error) {
                                Y.one('#discountcode').setStyle('border', '1px solid red');
                                Y.one('#error_coursepayment').setHTML(response.error);
                                Y.one('#header-amount span').setHTML(costorignal);
                            }
                            else if (response.status == true) {
                                Y.one('#error_coursepayment').setHTML('');
                                Y.one('#discountcode').setStyle('border', '1px solid green');
                                // Update

                                if (response.amount > 0) {
                                    var newprice = parseFloat(costorignal) - response.amount;
                                    if (newprice < 0) {
                                        newprice = 0;
                                    }

                                    Y.one('#header-amount span').setHTML(newprice.toFixed(2));
                                }
                                else {
                                    var newprice = (parseFloat(costorignal) / 100)  * (100 - response.percentage) ;
                                    if (newprice < 0) {
                                        newprice = 0;
                                    }
                                    Y.one('#header-amount span').setHTML(newprice.toFixed(2));
                                }
                            }
                            M.enrol_coursepayment_mollie_standalone.log(response);
                        } catch (e) {
                            // exception
                            M.enrol_coursepayment_mollie_standalone.log(e);
                        }
                    },
                    failure: function (x, o) {
                        M.enrol_coursepayment_mollie_standalone.log("Async call failed!");

                    }
                },
                headers: {
                    'Content-Type': 'application/json'
                }
            })
        })
    }
}