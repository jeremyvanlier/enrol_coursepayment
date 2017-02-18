Enrol CoursePayment
====================
![MoodleFreak.com](http://moodlefreak.com/logo_small.png)

* Author: Luuk Verhoeven, [MoodleFreak.com](http://moodlefreak.com)
* Requires at least: Moodle 2.6+
* License: [Avetica](http://avetica.nl)

With this plugin you can sell courses to your moodle users.

Description
====================
This plugin allows you to sell courses with multi pull gateways.

Has support for:
* Mollie gateway
* IPN/Callbacks
* Query openen transactions every hour with cron
* Easy to build own extend with other gateway
* Instance based settings like currency, enrol period
* global notification settings
* global sandbox and debug switches
* after successfull transaction user will be enrolled

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

2017021701 - Added reseller support and direct account create function

2017021000 - Added latest https://github.com/mollie/mollie-api-php

2016111200 - Support for availability_coursepayment, purchase a single activity

2015061202 - Intergration of customable vat percentage per instance and global

2015061201 - We added invoice mail support