/* globals libCommon */

$(document).ready(function () {
    libCommon.init.setupJQuery();

    $(".countInput")
        .focus(function () {
            if ($(this).val() == "0") {
                $(this).val("");
            }
        })
        .blur(function () {
            if ($(this).val() == "") {
                $(this).val("0");
            }
        })
        .keyup(function () {
            var ThisValue = parseInt(libCommon.normalize.removeNonDigit($(this).val()), 10);
            if (isNaN(ThisValue)) {
                $(this).removeClass("red");
            } else {
                var MaxValue = parseInt($(this).attr("data-maxVal"), 10);
                if (ThisValue > MaxValue) {
                    $(this).addClass("red");
                } else {
                    $(this).removeClass("red");
                }
                $(this).prettyInputBox();
            }
        })
        .keydown(function () {
            $(this).keyup();
        })
        .change(function () {
            $(this).keyup();
        });

    $(".setMax").click(function () {
        var ThisInput = $("input[name=\"ship_" + $(this).attr("data-ID") + "\"]");

        ThisInput.val(ThisInput.attr("data-maxVal"));
        ThisInput.change();
    });
    $(".setMin").click(function () {
        var ThisInput = $("input[name=\"ship_" + $(this).attr("data-ID") + "\"]");

        ThisInput.val(0);
        ThisInput.change();
    });
});
