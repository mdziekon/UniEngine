/* global MaxLength */

$(document).ready(function () {
    var textLength = $("#Length");

    $("#goBack").click(function () {
        document.location.href = "?";
    });

    $("textarea[name=\"text\"]")
        .keyup(function () {
            var Length = $(this).val().length;

            if (Length > MaxLength) {
                $(this).val($(this).val().substr(0, MaxLength));
                Length = MaxLength;
            }

            textLength.html(Length);
        })
        .keydown(function () {
            $(this).keyup();
        })
        .change(function () {
            $(this).keyup();
        });

    $("textarea[name=\"text\"]").keyup();
});
