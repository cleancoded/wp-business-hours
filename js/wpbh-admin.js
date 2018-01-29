jQuery(document).ready(function ($) {
    $(document).on('click', '.add-row', function () {

        var row = $(this).closest('tr').clone(true);
        row.find('input').each(function () {
            $(this).val("");
        });
        $(this).parents('tr').after(row);

        return false;
    });

    $(document).on('click', '.remove-row', function () {
        if ($(this).parents('tbody').find('tr').length > 1) {
            $(this).parents('tr').remove();
        }
        return false;
    });
});