jQuery(document).ready(function ($) {

    $('#widgets-right .color-picker, .inactive-sidebar .color-picker, .panel-dialog .color-picker').wpColorPicker();

    $(document).ajaxComplete(function () {
        $('#widgets-right .color-picker, .inactive-sidebar .color-picker, .panel-dialog .color-picker').wpColorPicker();
    });
});