/* globals AllowPrettyInputBox */

$(document).ready(function () {
    // Internal Functions
    function addDots (value) {
        value += "";
        var rgx = /(\d+)(\d\d\d)/;
        while (rgx.test(value)) {
            value = value.replace(rgx, "$1" + "." + "$2");
        }
        return value;
    }

    function removeNonDigit (Value) {
        Value += "";
        Value = Value.replace(/[^0-9]/g, "");
        return Value;
    }

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
            var ThisValue = parseInt(removeNonDigit($(this).val()), 10);
            if (isNaN(ThisValue)) {
                $(this).removeClass("red");
            } else {
                var MaxValue = parseInt($(this).attr("data-maxVal"), 10);
                if (ThisValue > MaxValue) {
                    $(this).addClass("red");
                } else {
                    $(this).removeClass("red");
                }
                if (AllowPrettyInputBox === true) {
                    ThisValue = addDots(ThisValue);
                }
                $(this).val(ThisValue);
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
