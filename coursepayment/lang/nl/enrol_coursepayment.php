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
 * @package   enrol_coursepayment
 * @copyright 2015 MoodleFreak.com
 * @author    Luuk Verhoeven
 */

$string['pluginname'] = 'CoursePayment';
$string['pluginname_desc'] = 'Deze plugin maakt het mogelijk om een cursus te verkopen met een betaal provider';
$string['mailadmins'] = 'E-mail admin';
$string['nocost'] = 'Er zitten geen kosten aan deze cursus';
$string['currency'] = 'Valuta';
$string['cost'] = 'Inschrijfkosten';
$string['assignrole'] = 'Toekennen rol';
$string['welcomemail'] = 'Welkom cursus e-mail';
$string['invoicemail'] = 'Facturatie e-mail';
$string['mailstudents'] = 'E-mail studenten';
$string['mailteachers'] = 'E-mail leraren';
$string['expiredaction'] = 'Enrolment verloop actie';
$string['expiredaction_help'] = 'Selecteer actie die uitvoerd moet wordenwanneer de inschrijving van de gebruiker verloopt. Houdt u er rekening mee dat sommige gebruikersgegevens en instellingen kunnen worden verwijderd.';
$string['status'] = 'sta CoursePayment inschrijvingen toe';
$string['status_desc'] = 'Sta gebruikers toe om CoursePayment gebruiken om in te schrijven in een cursus standaard.';
$string['defaultrole'] = 'Standaard roltoewijzing';
$string['defaultrole_desc'] = 'Selecteer rol die de gebruikers moeten worden toegekend tijdens CoursePayment inschrijvingen';
$string['enrolenddate'] = 'Einddatum';
$string['enrolenddate_help'] = 'Indien ingeschakeld, kunnen gebruikers worden ingeschreven tot deze datum.';
$string['enrolenddaterror'] = 'Inschrijving einddatum kan niet eerder dan startdatum';
$string['enrolperiod'] = 'Inschrijvingsduur';
$string['enrolperiod_desc'] = 'Standaard duur dat een inschrijving geldig is. Indien ingesteld op nul, zal de inschrijving voor onbeperkte tijd zijn';
$string['enrolstartdate'] = 'Startdatum';
$string['enrolstartdate_help'] = 'Indien ingeschakeld, kunnen gebruikers worden ingeschreven vanaf deze datum.';
$string['enrolperiod_help'] = 'duur dat de inschrijving geldig is, gestart vanaf het moment dat de gebruiker is aangemeld in de cursus. Wanneer deze optie is uitgeschakeld, is de duratie oneindig.';
$string['debug'] = 'Debug';
$string['debug_desc'] = 'Mag alleen worden ingeschakeld voor ontwikkel doel einde';
$string['sandbox'] = 'Sandbox';
$string['sandbox_desc'] = 'Sandbox mode voor de provider, is niet altijd van kracht. Soms ligt de instelling bij de provider';
$string['enabled'] = 'Ingeschakeld';
$string['error:failed_getting_plugin_instance'] = 'instance gegevens ophalen mislukt';
$string['crontask'] = 'CoursePayment - order behandeling';
$string['title:returnpage'] = 'Betaal Status';
$string['success_enrolled'] = 'Bedankt voor de aanschaf.<br> We hebben u ingeschreven voor: {$a->fullname}';
$string['error:unknown_order'] = 'Er is geen order bij ons bekend met deze gegevens.';
$string['error:gettingorderdetails'] = 'We waren niet in staat om de order gegevens op te halen.';
$string['error:paymentabort'] = 'De betaling is afgebroken.';
$string['gateway_not_exists'] = 'Fout! Gateway is onbekend.';
$string['enabled_desc'] = 'Wanneer ingeschakeld kan deze enrolment plugin ingesteld worden bij een cursus.';
$string['expiredaction'] = 'Inschrijving verloop actie';
$string['expiredaction_help'] = 'Selecteer actie die uitvoerd moet wordenwanneer de inschrijving van de gebruiker verloopt. Houdt u er rekening mee dat sommige gebruikersgegevens en instellingen kunnen worden verwijderd.';
$string['expirymessageenrollersubject'] = 'Melding voor het vervallen van de aanmelding';
$string['expirymessageenrollerbody'] = 'De aanmelding in cursus \'{$a->course}\' zal binnen {$a->threshold} vervallen voor volgende gebruikers:

{$a->users}

Ga naar {$a->extendurl} om hun aanmelding te verlengen.';
$string['expirymessageenrolledsubject'] = 'Melding voor het vervallen van de aanmelding';
$string['expirymessageenrolledbody'] = 'Beste {$a->user},

Je aanmelding in cursus \'{$a->course}\' gaat vervallen op {$a->timeend}.

ls je hier een probleem mee hebt, neem dan contact op met {$a->enroller}.';
$string['purchase'] = 'Betalen';
$string['provider'] = 'Provider';
$string['name'] = 'Naam';
$string['minimum'] = 'Minimaal';
$string['maximum'] = 'Maximaal';
$string['instancedesc'] = 'Omschrijving';
$string['gateway_mollie_issuers'] = 'Selecteer een bank';
$string['gateway_mollie_select_method'] = 'Klik op een van de betaalmethodes hieronder.';
$string['gateway_mollie'] = 'Provider: Mollie';
$string['gateway_mollie_desc'] = 'Offer your customers the payment methods Creditcard, SOFORT Banking, iDEAL, Bancontact/Mister Cash, Bank transfer, Bitcoin, PayPal or paysafecard. Mollie is known for reliability, transparency, nice API’s and ready-to-go modules.';
$string['gateway_mollie_apikey'] = 'API sleutel';
$string['gateway_mollie_link'] = 'Als u nog geen account heeft kunt u via <a href="{$a->link}">Registeren</a> een account aanmaken';
$string['gateway_mollie_send_button'] = 'Koop deze cursus';
$string['error:capability_config'] = 'Fout: Je heb de volgende toegang nodig, coursepayment/config';
$string['enrol_coursepayment_discount'] = 'Kortingscodebeheer';
$string['enrol_coursepayment_discount_desc'] = 'Korting codes kunnen aangemaakt worden via de onderstaande knop. <br/><br/><a href="{$a->link}" class="btn btn-small btn-primary">Kortingscodebeheer</a>';
$string['new:discountcode'] = 'Nieuwe kortingscode toevoegen';
// TABLE
$string['th:code'] = 'Kortingscode';
$string['th:courseid'] = 'Cursus';
$string['th:start_time'] = 'Geldig vanaf';
$string['th:end_time'] = 'Geldig to';
$string['th:amount'] = 'Waarde';
$string['th:action'] = 'Actie';
// FORM
$string['form:allcourses'] = 'Volledige website';
$string['form:code'] = 'Kortignscode<br> (moet uniek zijn)';
$string['form:discountcode'] = 'Kortingscode';
$string['form:start_time'] = 'Geldig vanaf';
$string['form:end_time'] = 'Geldig to';
$string['form:save'] = 'Bewaar wijzigingen';
$string['form:amount'] = 'Bedrag van kortingscode';
$string['form:percentage'] = 'Percentage van kortingscode';
// ERR
$string['error:number_to_low'] = 'Dit nummer is te laag';
$string['error:price_wrongformat'] = 'Dit is geen nummer';
$string['error:code_not_unique'] = 'Kortingscode moet uniek zijn';
$string['error:no_record'] = 'Fout: Komt niet voor in onze database!';
$string['error:not_within_the_time_period'] = 'Fout: Niet geldig binnen deze tijd periode!';
$string['error:not_for_this_course'] = 'Fout: Deze kortingscode is bedoelt voor een andere cursus!';
// SETTING
$string['discount_code_desc'] = 'Als u een kortingscode heeft kunt u die hieronder invullen';
$string['discountcode_invalid'] = 'Fout: Deze kortingscode is onjuist, niet geldig meer of hoort niet bij deze cursus!';
$string['vatpercentages'] = 'BTW percentage inbegrepen in de kosten';
$string['invoicedetails'] = 'Invoice details';
$string['invoicedetails_desc'] = 'Deze velden zijn verplicht! Wanneer u dit leeg laat zijn uw facturen onjuist.';
$string['btw'] = 'BTW nummer';
$string['kvk'] = 'KvK nummer';
$string['place'] = 'Vestigingplaats';
$string['zipcode'] = 'Postcode';
$string['address'] = 'Adres';
$string['companyname'] = 'Naam organisatie';
// MAIL
$string['mail:invoice_subject'] = 'Bedankt voor het kopen van: {$a->course} / {$a->fullname}';
$string['mail:invoice_message'] = '<h2>Factuur</h2>
<br/>
<b>{$a->companyname}</b><br/>
{$a->address}<br/>
{$a->zipcode} {$a->place}<br/>
<br/>
KvK: {$a->kvk}<br/>
BTW: {$a->btw}<br/>
<br/>
Factuur nummer: {$a->invoice_number}<br/>
Datum: {$a->date}<br/>
<br/>
<br/>
Aan:<br/>
<b>{$a->fullname}</b><br/>
{$a->email}<br/>
<br/>
<table cellpadding="0" cellspacing="0" style="margin:0;padding:0;width: 100%">
    <tr>
        <td colspan="2">Cursusmodule: {$a->fullcourse}</td>
    </tr>
    <tr>
        <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
        <td  style="width: 30%">BTW ({$a->vatpercentage}%)</td>
        <td>{$a->currency} {$a->costvat}</td>
    </tr>
     <tr>
        <td>Totale kosten</td>
        <td>{$a->currency} {$a->cost}</td>
    </tr>
</table><br/><br/>
Gekocht op {$a->date} en betaald via <b>{$a->method}</b>';

$string['coursepayment:config'] = 'Instellen';
$string['coursepayment:manage'] = 'Beheren';
$string['coursepayment:unenrol'] = 'Uitschrijven';
$string['coursepayment:unenrolself'] = 'Zichzelf uitschrijven';
$string['success_enrolled_activity'] = 'Betaling gelukt, u kunt nu de activiteit starten.';
$string['gateway_mollie_external_connector'] = 'External API connector';
$string['gateway_mollie_external_connector_desc'] = 'Deze functie moet worden uitgeschakeld! (Alleen in te schakelen als je weet wat je doet)';