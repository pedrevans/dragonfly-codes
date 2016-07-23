/**
 * Created by pete on 22/07/2016.
 */

jQuery(document).ready(function ($) {
    /*
     * When the submit button is pressed,
     */

    function dragonfly_codes_post(data, callback) {
        console.log('dragonfly_codes_post ' + JSON.stringify(data));
        $.ajax({
            type: 'post',
            dataType: 'json',
            url: dragonfly_ajax_object.ajax_url,
            data: data,
            success: function (response) {
                console.log('dragonfly_codes_post callback got response = ' + response);
                if (callback) {
                    callback(response);
                }
            }
        });
    }
    $('#dragonfly-evaluate-button').on('click', function (e) {
        console.log("Caught click on #dragonfly-evaluate-button. Nonce = "+$(this).data('nonce'));
        var data = {
            action : 'dragonfly_evaluate',
            nonce : $(this).data('nonce'),
            postid : $(this).data('postid'),
            participant_email: $('#dragonfly-input-participant-email').val()
        };
        var values = [];
        var count = 0;
        $('.dragonfly-input-text').each(function(i, inp){
            var value = $(this).val();
            if (value != undefined && value != '') {
                values.push(value);
                count++;
            }
        } );
        data.codes = values;
        console.log("#dragonfly-evaluate-button.on POST = "+JSON.stringify(data));
        if (data.participant_email == undefined || data.participant_email == '')
            return;
        if (count == 0)
            return;
        dragonfly_codes_post(data, function (response) {
            console.log('dragonfly_codes_post callback response: '+JSON.stringify(response));
            var output = '';
            if (!response.success) {
                output = 'FAILED</br>';
            } else {
                $('#dragonfly-wp-admin-page-link').show();
            }
            output += '<strong>' + response.message + '</strong><br/>';
            $('#dragonfly-evaluate-result').html(output);
        });
    });

});
