jQuery(function ($) {
    count = 0;
    all_texts = new Array();
    $('.wpbh_ajax_widgets').each(function () {
        $widget = $(this);
        $widget.attr('id', 'wpbh_widget' + count);
        var text = $widget.attr('data-text');

        if (text !== null && typeof text !== 'undefined')
            all_texts[count] = text;

        if ($('.wpbh_ajax_widgets').length == count + 1) {
            $.post(wpbh_ajax.ajax_url, {
                action: 'wpbh_ajax_widget',
                data: all_texts,
                ajax_nonce : wpbh_ajax.ajax_nonce
            }, function (response) {
                response = $.parseJSON(response);
                for (i = 0; i <= response.length; i++) {
                    $('#wpbh_widget' + i).html(response[i]);
                }
            });
        }
        count++;
    });
});