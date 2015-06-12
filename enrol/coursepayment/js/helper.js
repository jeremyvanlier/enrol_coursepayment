/**
 * Helper
 *
 * @package   enrol_coursepayment
 * @copyright 2015 MoodleFreak.com
 * @author    Luuk Verhoeven
 */

// prevent instant delete show a confirm message first
Y.all('.delete').on('click' , function(e){
    var status = confirm('Are you sure?');
    if(status){
        return;
    }

    e.preventDefault();
});