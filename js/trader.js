/* globals libCommon, Mode, NameResM, NameResA, NameResB, Mod_ResA, Mod_ResB, MaxResM, MaxResA, MaxResB */

$(document).ready(function () {
    libCommon.init.setupJQuery();

    function Calculate (UsingMain) {
        if (UsingMain === undefined) {
            UsingMain = false;
        }
        var ResM = libCommon.normalize.removeNonDigit($("[name=\"" + NameResM + "\"]").val());
        var ResA = libCommon.normalize.removeNonDigit($("[name=\"" + NameResA + "\"]").val());
        var ResB = libCommon.normalize.removeNonDigit($("[name=\"" + NameResB + "\"]").val());

        if (UsingMain === false) {
            var Needed;
            if (Mode == 1) {
                Needed = Math.ceil((ResA * Mod_ResA) + (ResB * Mod_ResB));
            } else if (Mode == 2) {
                Needed = Math.floor((ResA * Mod_ResA) + (ResB * Mod_ResB));
            }
            $("#MainRes").val(libCommon.format.addDots(Needed));
        } else {
            if (Mode == 1) {
                ResA = Math.floor((ResM * (parseInt($("[name=\"" + NameResA + "Percent\"]").val()) / 100)) / Mod_ResA);
                ResB = Math.floor((ResM * (parseInt($("[name=\"" + NameResB + "Percent\"]").val()) / 100)) / Mod_ResB);
                $("[name=\"" + NameResA + "\"]").val(ResA).prettyInputBox();
                $("[name=\"" + NameResB + "\"]").val(ResB).prettyInputBox();
            } else if (Mode == 2) {
                ResA = Math.floor((ResM * (parseInt($("[name=\"" + NameResA + "Percent\"]").val()) / 100)) / Mod_ResA);
                ResB = Math.floor((ResM * (parseInt($("[name=\"" + NameResB + "Percent\"]").val()) / 100)) / Mod_ResB);
                $("[name=\"" + NameResA + "\"]").val(ResA).prettyInputBox();
                $("[name=\"" + NameResB + "\"]").val(ResB).prettyInputBox();
            }
        }
        if (Mode == 1) {
            if (ResM > MaxResM) {
                $("[name=\"" + NameResM + "\"]").addClass("resRed");
            } else {
                if ($("[name=\"" + NameResM + "\"]").hasClass("resRed")) {
                    $("[name=\"" + NameResM + "\"]").removeClass("resRed");
                }
            }
        } else {
            if (ResA > MaxResA) {
                $("[name=\"" + NameResA + "\"]").addClass("resRed");
            } else {
                if ($("[name=\"" + NameResA + "\"]").hasClass("resRed")) {
                    $("[name=\"" + NameResA + "\"]").removeClass("resRed");
                }
            }
            if (ResB > MaxResB) {
                $("[name=\"" + NameResB + "\"]").addClass("resRed");
            } else {
                if ($("[name=\"" + NameResB + "\"]").hasClass("resRed")) {
                    $("[name=\"" + NameResB + "\"]").removeClass("resRed");
                }
            }
        }
    }

    $("[name=\"met\"],[name=\"cry\"],[name=\"deu\"]")
        .change(function () {
            $(this).val(libCommon.normalize.removeNonDigit($(this).val()));
            if ($(this).attr("name") != NameResM) {
                Calculate();
            } else {
                Calculate(true);
            }
            $(this).prettyInputBox();
        }).keyup(function () {
            $(this).change();
        }).keydown(function (event) {
            var ThisCount;
            if (event.which == 38) {
                ThisCount = parseFloat(libCommon.normalize.removeNonDigit($(this).val()));
                if (isNaN(ThisCount)) {
                    ThisCount = 0;
                }
                $(this).val(ThisCount + 1).change();
            } else if (event.which == 40) {
                ThisCount = parseFloat(libCommon.normalize.removeNonDigit($(this).val()));
                if (isNaN(ThisCount) || ThisCount <= 0) {
                    return false;
                }
                $(this).val(ThisCount - 1).change();
            }
        }).focus(function () {
            const val = libCommon.normalize.removeNonDigit($(this).val());

            if (!(libCommon.tests.isNonEmptyValue(val, { isZeroAllowed: false }))) {
                $(this).val("");
            }
        }).blur(function () {
            const val = libCommon.normalize.removeNonDigit($(this).val());

            if (!(libCommon.tests.isNonEmptyValue(val, { isZeroAllowed: true }))) {
                $(this).val("0");
            }
        });

    $("#max" + NameResM).click(function () {
        if (Mode == 1) {
            $("[name=\"" + NameResM + "\"]").val(Math.floor(MaxResM)).change();
        } else if (Mode == 2) {
            $("[name=\"" + NameResA + "\"]").val(Math.floor(MaxResA)).change();
            $("[name=\"" + NameResB + "\"]").val(Math.floor(MaxResB)).change();
        }
        return false;
    });
    $("#max" + NameResA).click(function () {
        if (Mode == 1) {
            $("[name=\"" + NameResA + "\"]").val(Math.floor(MaxResM / Mod_ResA)).change();
            $("#zero" + NameResB).click();
            $("[name=\"" + NameResA + "Percent\"]").val(100).change();
        } else if (Mode == 2) {
            $("[name=\"" + NameResA + "\"]").val(Math.floor(MaxResA)).change();
        }
        return false;
    });
    $("#max" + NameResB).click(function () {
        if (Mode == 1) {
            $("[name=\"" + NameResB + "\"]").val(Math.floor(MaxResM / Mod_ResB)).change();
            $("#zero" + NameResA).click();
            $("[name=\"" + NameResB + "Percent\"]").val(100).change();
        } else if (Mode == 2) {
            $("[name=\"" + NameResB + "\"]").val(Math.floor(MaxResB)).change();
        }
        return false;
    });
    $("#zero" + NameResM).click(function () {
        $("[name=\"" + NameResM + "\"]").val("0").change();
        return false;
    });
    $("#zero" + NameResA).click(function () {
        $("[name=\"" + NameResA + "\"]").val("0").change();
        return false;
    });
    $("#zero" + NameResB).click(function () {
        $("[name=\"" + NameResB + "\"]").val("0").change();
        return false;
    });

    $(".modif").click(function () {
        var ThisElement = $("[name=\"" + $(this).parent().children("input.percent").attr("name") + "\"]");
        var AddValue;
        if ($(this).val() == "+") {
            AddValue = 1;
        } else {
            AddValue = -1;
        }
        ThisElement.val(parseInt(ThisElement.val()) + AddValue);
        ThisElement.change();
    });


    $("input.percent").change(function () {
        var OpositeSelector;
        if ($(this).attr("name") == NameResA + "Percent") {
            OpositeSelector = $("[name=\"" + NameResB + "Percent\"]");
        } else {
            OpositeSelector = $("[name=\"" + NameResA + "Percent\"]");
        }

        if ($.isNumeric($(this).val())) {
            var ThisVal = parseInt($(this).val());
            if (ThisVal > 100) {
                ThisVal = 100;
            } else if (ThisVal < 0) {
                ThisVal = 0;
            }
            $(this).val(ThisVal);
            OpositeSelector.val(100 - ThisVal);
        } else {
            $(this).val(0);
            OpositeSelector.val(100);
        }
        Calculate(true);
    }).keyup(function () {
        $(this).change();
        return false;
    }).keypress(function (event) {
        if (event.which == 13) {
            return false;
        }
    }).keydown(function (event) {
        var ThisCount;
        if (event.which == 38) {
            ThisCount = parseFloat(libCommon.normalize.removeNonDigit($(this).val()));
            if (isNaN(ThisCount)) {
                ThisCount = 0;
            } else {
                if (ThisCount >= 100) {
                    return false;
                }
            }
            $(this).val(ThisCount + 1).change();
        } else if (event.which == 40) {
            ThisCount = parseFloat(libCommon.normalize.removeNonDigit($(this).val()));
            if (isNaN(ThisCount) || ThisCount <= 0) {
                return false;
            }
            $(this).val(ThisCount - 1).change();
        }
    });

    $("th").css("padding", "3px");
});
