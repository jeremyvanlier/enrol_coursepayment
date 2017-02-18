/**
 * Account claim dialog
 * We use the old Javascript moodle way here because we need to support older versions
 *
 * @package   enrol_coursepayment
 * @copyright 2017 MoodleFreak.com
 * @author    Luuk Verhoeven
 */
YUI().use('event-base', 'json', 'node', "panel", "cookie", "io", 'anim', 'io-base', function (Y) {

    Y.on('domready', function () {

        /**
         * Dialog
         */
        var dialog = new Y.Panel({
            contentBox : Y.Node.create('<div id="dialog" />'),
            bodyContent: '<div class="message"><i class="icon-warn"></i><b>Betaalprovider Mollie koppelen aan Avetica' +
            '</b><br/><br/>Uw account is nog niet aan Avetica gekoppeld u kunt dit oplossen door' +
            ' hieronder uw Mollie gebruikersnaam en wachtwoord in te vullen.<br/><br/>' +
            '<label for="usernameM">Gebruikersnaam</label><input type="text" class="input" name="usernameM"' +
            ' id="usernameM"/> ' +
            '<label for="passwordM">Wachtwoord</label><input type="password" class="input" name="passwordM"' +
            ' id="passwordM"/> ' +
            '</div>',
            width      : 410,
            zIndex     : 9999,
            centered   : true,
            modal      : true, // modal behavior
            render     : '.example',
            visible    : false, // make visible explicitly with .show()
            buttons    : {
                footer: [
                    {
                        name  : 'cancel',
                        label : 'Uitstellen',
                        action: 'onCancel'
                    },
                    {
                        name  : 'proceed',
                        label : 'Koppelen',
                        action: 'onOK'
                    }
                ]
            }
        });

        /**
         * Cancel button is pressed.
         * @param e
         */
        dialog.onCancel = function (e) {
            e.preventDefault();
            this.hide();
            Y.Cookie.set("dialog_accountclaim", true, {expires: new Date().addDays(1)});
        };

        /**
         * Ok button is pressed.
         * @param e
         */
        dialog.onOK = function (e) {
            e.preventDefault();

            // Get form data and send to the reseller to claim this account.
            Y.io("/enrol/coursepayment/ajax.php", {
                method : 'GET',
                data   : {
                    'sesskey' : M.cfg.sesskey,
                    'courseid': 1,
                    'username': Y.one('#usernameM').get('value'),
                    'password': Y.one('#passwordM').get('value'),
                    'action'  : 'accountclaim',
                    'data'    : 'not_used'
                },
                on     : {
                    success: function (id, o) {
                        console.log(o);
                        try {
                            var response = Y.JSON.parse(o.response);
                            if (response.error) {
                                alert(response.error);
                            }
                            if (response.status) {
                                dialog.hide();
                            }
                        } catch (e) {
                            throw e;
                        }
                    },
                    failure: function (x, o) {
                        console.log('failure');
                        console.log(o);
                    }
                },
                headers: {
                    'Content-Type': 'application/json'
                }
            });
        };

        dialog.show();
    });

});