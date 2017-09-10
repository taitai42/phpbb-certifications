/**
 * Created by yannis on 9/6/17.
 */

$(function () {
    $('#crenaux .time').timepicker({
        'showDuration': true,
        'timeFormat': 'H:i'
    });

    $('#crenaux .date').datepicker({
        beforeShow: function (input, inst) {
            inst.dpDiv.css({marginTop: -input.offsetHeight + 'px', marginLeft: input.offsetWidth + 'px'});
        },
        'format': 'dd/mm/yyyy',
        'autoclose': true
    });


    $('.added .time').timepicker({
        'showDuration': true,
        'timeFormat': 'H:i'
    });


    $('.added .date').datepicker({
        beforeShow: function (input, inst) {
            inst.dpDiv.css({marginTop: -input.offsetHeight + 'px', marginLeft: input.offsetWidth + 'px'});
        },
        'format': 'dd/mm/yyyy',
        'autoclose': true
    });

    // initialize datepair
    $('.added').datepair();

    // initialize datepair
    $('#crenaux').datepair();



    var tocopy = $(".tocopy").html();
    $(".delcreneau").on('click', function (e) {
        e.preventDefault();
        $(this).closest('div').remove();
    });

    if (typeof added == 'undefined') {
        var added = 0;
    }

    $("#addcreneau").on('click', function (e) {
        e.preventDefault();

        added++;

        $(".creneaux").append('<div id="added' + added + '">' + tocopy + '</div> <br>');
        $('#added' + added + ' .time').timepicker({
            'showDuration': true,
            'timeFormat': 'H:i'
        });

        $('#added' + added + ' [type="text"]').each(function (i, el) {
            $(el).attr('name', $(el).attr('name').replace(/^slot\[(\d+)\]/, 'slot[' + added + ']'));
        });

        $('#added' + added + ' .date').datepicker({
            beforeShow: function (input, inst) {
                inst.dpDiv.css({marginTop: -input.offsetHeight + 'px', marginLeft: input.offsetWidth + 'px'});
            },
            'format': 'dd/mm/yyyy',
            'autoclose': true
        });

        // initialize datepair
        $('#added' + added).datepair();
        $(".delcreneau").each(function (i, el) {
            $(el).on('click', function (e) {
                e.preventDefault();
                $(this).closest('br').remove();
                $(this).closest('div').remove();
                added--;
            });
        });
    });
});