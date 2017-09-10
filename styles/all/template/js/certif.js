/**
 * Created by yannis on 9/10/17.
 */
$(function () {
    var selected = false;

    $(".label").on('click', function (e, el) {
        selected = $(this).hasClass('label-active');
        $(".label-active").each(function (i, el) {
            $(el).removeClass('label-active');
            $(el).addClass('label-default');
        });

        if (!selected) {
            $(this).removeClass('label-default');
            $(this).addClass('label-active');
        }

        $('[name="slot"]').val(!selected ? $(this).attr('data-id') : 0);
    });
});