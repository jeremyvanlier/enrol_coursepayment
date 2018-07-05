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
            bodyContent: '<div class="message"><i class="icon-warn"></i><b>' +
            M.util.get_string('js:claim_title', 'enrol_coursepayment') +
            '</b><br><br>' + M.util.get_string('js:claim_desc', 'enrol_coursepayment') + '<br><br>' +
            '<label for="usernameM">' + M.util.get_string('js:username', 'enrol_coursepayment') + '</label><br>' +
            '<input type="text" autocomplete="off" class="input" name="usernameM" id="usernameM" value="" required><br>' +
            '<label for="passwordM">' + M.util.get_string('js:password', 'enrol_coursepayment') + '</label><br>' +
            '<input type="password" autocomplete="off" class="input" name="passwordM" id="passwordM" value=""  required> ' +
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
                        label : M.util.get_string('js:delay', 'enrol_coursepayment') ,
                        action: 'onCancel'
                    },
                    {
                        name  : 'proceed',
                        label : M.util.get_string('js:connect', 'enrol_coursepayment') ,
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
            var date = new Date(date);
            date.setDate(date.getDate() + 1);
            Y.Cookie.set("dialog_accountclaim", true, {expires: date , 'path' : '/'});
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
        // Hide the popup if there is a cookie set.
        var show = Y.Cookie.get("dialog_accountclaim") === null ? true : false;
        if(show){
            dialog.show();
        }
    });

});