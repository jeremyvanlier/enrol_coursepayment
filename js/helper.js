/**
 * Helper
 *
 * @package   enrol_coursepayment
 * @copyright 2015 MFreak.nl
 * @author    Luuk Verhoeven
 */

// Prevent instant delete show a confirm message first.
Y.all('.delete').on('click', function(e) {
    var status = confirm('Are you sure?');
    if (status) {
        return;
    }

    e.preventDefault();
});