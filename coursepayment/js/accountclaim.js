/**
 * Account claim dialog
 *
 * @package   enrol_coursepayment
 * @copyright 2017 MoodleFreak.com
 * @author    Luuk Verhoeven
 */
YUI().use('event-base', 'node', "panel", "cookie", "io", 'anim', function (Y) {

    Y.on('domready', function () {

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

        dialog.onCancel = function (e) {
            e.preventDefault();
            this.hide();

            // hide for a day by default
            if (Y.one('#hide_diagnose').get("checked")) {
                Y.log('Hide Long');
                Y.Cookie.set("dialog_accountclaim", true, {expires: new Date().addExtraDays(356)});
            }
            else {
                Y.log('Hide short');
                Y.Cookie.set("dialog_accountclaim", true, {expires: new Date().addHours(4)});
            }
        };

        dialog.onOK = function (e) {
            e.preventDefault();
            this.hide();

            // code that executes the user confirmed action
            Y.Cookie.set("dialog_accountclaim", true, {expires: new Date().addExtraDays(356)});
        };

        dialog.show();
    });

});