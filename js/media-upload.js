/**
 * This script is used to load the WordPress media library
 * @author: CLEANCODED
 */
jQuery(document).ready(function ($) {

    $(document).on('click', '.wpbh-add-media-button', function (event) {
        event.preventDefault();

        var button = $(this);
        var type = $(button).data('type');
        var allow_multiple = false;

        var frame = wp.media({
            multiple: allow_multiple,

            library: {
                type: type
            }
        });

        frame.on('select', function () {

            var attachment = frame.state().get('selection').toJSON();
            var result = attachment.map(function (e) {
                return e.id;
            }).join(', ');

            button.prev().val(result);

        });
        frame.open();
    });

});