Enrol CoursePayment
====================
![MFreak.nl](https://mfreak.nl/logo_small.png)

* Author: Luuk Verhoeven, [MFreak.nl](http:///mfreak.nl)
* Requires at least: Moodle 2.6+
* License: [Avetica](http://avetica.nl)

With this plugin you can sell courses to your moodle users. There is also a [availability plugin](http://git.dev.avetica.net:8080/luuk/availability_coursepayment) available.

Description
====================
This plugin allows you to sell courses with multi pull gateways.

Has support for:
* Mollie gateway.
* IPN/Callbacks.
* Query openen transactions every hour with cron.
* Easy to build own extend with other gateway.
* Instance based settings like currency, enrol period.
* Global notification settings.
* Global sandbox and debug switches.
* After successfull transaction user will be enrolled.
* Multiple Mollie accounts support, account selection based on matching profile field value.

Installation
====================

1. copy this plugin to the `enrol` folder called `coursepayment`
2. login as administrator
3. go to Site Administrator > Notification
4. install the plugin
5. register on the gateway page you interested at
6. add global settings and your gateway settings

Changelog 
====================

See Git for the complete history, major changes will be listed below

2018070500 - Added privacy provider GDPR

2018070500 - Git folder structure changed.

2018010800 - Added support focustom transaction Mollie description 

2017082101 - Added multi account option, to support multiple Mollie accounts. 
   The correct payment account is selected based on profile field

2017021701 - Added reseller support and direct account create function

2017021000 - Added latest https://github.com/mollie/mollie-api-php

2016111200 - Support for availability_coursepayment, purchase a single activity

2015061202 - Intergration of customable vat percentage per instance and global

2015061201 - We added invoice mail support