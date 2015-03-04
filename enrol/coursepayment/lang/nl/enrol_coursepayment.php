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
 * language file for coursepayment
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @file: enrol_coursepayment.php
 * @since 2-3-2015
 * @encoding: UTF8
 *
 * @package: enrol_coursepayment
 *
 * @copyright 2015 MoodleFreak.com
 * @author    Luuk Verhoeven
 **/
$string['pluginname'] = 'CoursePayment';
$string['pluginname_desc'] = 'Deze plugin maakt het mogelijk om van cursus te verkopen met een betaal provider';
$string['mailadmins'] = 'E-mail admin';
$string['nocost'] = 'Er zitten geen kosten aan deze cursus';
$string['currency'] = 'Valuta';
$string['cost'] = 'Inschrijf kosten';
$string['assignrole'] = 'Toekennen rol';
$string['mailstudents'] = 'E-mail studenten';
$string['mailteachers'] = 'E-mail leeraren';
$string['expiredaction'] = 'Enrolment verloop actie';
$string['expiredaction_help'] = 'Selecteer actie uit te voeren wanneer de gebruiker de inschrijving verloopt. Houdt u er rekening mee dat sommige gebruikersgegevens en instellingen kunnen worden verwijderd.';
$string['status'] = 'Toestaan CoursePayment inschrijvingen';
$string['status_desc'] = 'Sta gebruikers toe om CoursePayment gebruiken om in te schrijven in een cursus standaard.';
$string['defaultrole'] = 'Standaard roltoewijzing';
$string['defaultrole_desc'] = 'Selecteer rol die de gebruikers moeten worden toegekend tijdens CoursePayment inschrijvingen';
$string['enrolenddate'] = 'Eind datum';
$string['enrolenddate_help'] = 'Indien ingeschakeld, kunnen gebruikers worden ingeschreven tot deze datum.';
$string['enrolenddaterror'] = 'Inschrijving einddatum kan niet eerder dan startdatum';
$string['enrolperiod'] = 'Inschrijving duur';
$string['enrolperiod_desc'] = 'Standaard lengte dat een inschrijving geldig is. Indien ingesteld op nul, zal de inschrijving voor onbeperkte tijd zijn';
$string['enrolstartdate'] = 'Start datum';
$string['enrolstartdate_help'] = 'Indien ingeschakeld, kunnen gebruikers worden ingeschreven vanaf deze datum.';
$string['enrolperiod_help'] = 'Length of time that the enrolment is valid, starting with the moment the user is enrolled. If disabled, the enrolment duration will be unlimited.';
$string['debug'] = 'Debug';
$string['debug_desc'] = 'Mag alleen worden ingesckalled voor ontwikkel doel einde';
$string['sandbox'] = 'Sandbox';
$string['sandbox_desc'] = 'Sandbox mode voor de provider, is niet altijd van kracht soms ligt de instelling bij de provider';
$string['enabled'] = 'Ingeschakkeld';
$string['error:failed_getting_plugin_instance'] = 'Mislukt om instance gegevens op te halen';
$string['crontask'] = 'CoursePayment - order behandeling';
$string['title:returnpage'] = 'Betaal Status';
$string['success_enrolled'] = 'Bedankt voor de aanschaf.<br> We hebben u ingeschreven voor: {$a->fullname}';
$string['error:unknown_order'] = 'Er is geen order bij ons bekend met deze gegevens.';
$string['error:gettingorderdetails'] = 'We waren niet instaat om order gegevens op te halen.';
$string['error:paymentabort'] = 'De betalling is afgebroken.';
$string['gateway_not_exists'] = 'Fout! Gateway is onbekend.';
$string['enabled_desc'] = 'Wanneer ingeschakkeld kan deze enrolment plugin ingesteld worden bij een cursus.';
$string['expiredaction'] = 'Inschrijving verloop actie';
$string['expiredaction_help'] = 'Select action to carry out when user enrolment expires. Please note that some user data and settings are purged from course during course unenrolment.';
$string['expirymessageenrollersubject'] = 'Melding voor het vervallen van de aanmelding';
$string['expirymessageenrollerbody'] = 'De aanmelding in cursus \'{$a->course}\' zal binnen {$a->threshold} vervallen voor volgende gebruikers:

{$a->users}

Ga naar {$a->extendurl} om hun aanmelding te verlengen.';
$string['expirymessageenrolledsubject'] = 'Melding voor het vervallen van de aanmelding';
$string['expirymessageenrolledbody'] = 'Beste {$a->user},

Je aanmelding in cursus \'{$a->course}\' gaat vervallen op {$a->timeend}.

ls je hier een probleem mee hebt, neem dan contact op met {$a->enroller}.';
$string['purchase'] = 'Koop cursus';
$string['provider'] = 'Provider';
$string['name'] = 'Naam';
$string['minimum'] = 'Minimaal';
$string['maximum'] = 'Maximaal';
$string['gateway_mollie_issuers'] = 'Selecteer een bank';
$string['gateway_mollie_select_method'] = 'Klik op een van de betaalmethode hieronder.';
$string['gateway_mollie'] = 'Provider: Mollie';
$string['gateway_mollie_desc'] = 'Offer your customers the payment methods Creditcard, SOFORT Banking, iDEAL, Bancontact/Mister Cash, Bank transfer, Bitcoin, PayPal or paysafecard. Mollie is known for reliability, transparency, nice API’s and ready-to-go modules.';
$string['gateway_mollie_apikey'] = 'API sleutel';
$string['gateway_mollie_link'] = 'Als u nog geen account heeft kunt u via <a href=" https://www.mollie.com/en/signup/1787751">Registeren</a> een account aanmaken';
$string['gateway_mollie_send_button'] = 'Koop met mollie';
