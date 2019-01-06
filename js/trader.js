/* globals AllowPrettyInputBox, Mode, NameResM, NameResA, NameResB, Mod_ResA, Mod_ResB, MaxResM, MaxResA, MaxResB */

$(document).ready(function () {
    // Internal Functions
    function addDots (Value) {
        Value += "";
        var rgx = /(\d+)(\d\d\d)/;
        while (rgx.test(Value)) {
            Value = Value.replace(rgx, "$1" + "." + "$2");
        }
        return Value;
    }

    function removeNonDigit (Value) {
        Value += "";
        Value = Value.replace(/[^0-9]/g, "");
        return Value;
    }

    $.fn.notEmptyVal = function (canBeZero, doRemoveNonDigit) {
        var ThisVal = $(this).val();
        if (doRemoveNonDigit) {
            ThisVal = removeNonDigit(ThisVal);
        }
        if (canBeZero) {
            if (ThisVal != "" && ThisVal >= 0) {
                return true;
            } else {
                return false;
            }
        } else {
            if (ThisVal != "" && ThisVal > 0) {
                return true;
            } else {
                return false;
            }
        }
    };

    $.fn.prettyInputBox = function (IgnoreUserSetting) {
        return this.each(function () {
            if ((AllowPrettyInputBox !== undefined && AllowPrettyInputBox === true) || (IgnoreUserSetting !== undefined && IgnoreUserSetting === true)) {
                var Value = $(this).val();
                if (Value.indexOf(".") !== -1) {
                    Value = Value.replace(/\./g, "");
                }
                Value = addDots(Value);
                $(this).val(Value);
            }
        });
    };

    function Calculate (UsingMain) {
        if (UsingMain === undefined) {
            UsingMain = false;
        }
        var ResM = removeNonDigit($("[name=\"" + NameResM + "\"]").val());
        var ResA = removeNonDigit($("[name=\"" + NameResA + "\"]").val());
        var ResB = removeNonDigit($("[name=\"" + NameResB + "\"]").val());

        if (UsingMain === false) {
            var Needed;
            if (Mode == 1) {
                Needed = Math.ceil((ResA * Mod_ResA) + (ResB * Mod_ResB));
            } else if (Mode == 2) {
                Needed = Math.floor((ResA * Mod_ResA) + (ResB * Mod_ResB));
            }
            $("#MainRes").val(addDots(Needed));
        } else {
            if (Mode == 1) {
                ResA = Math.floor((ResM * (parseInt($("[name=\"" + NameResA + "Percent\"]").val()) / 100)) / Mod_ResA);
                ResB = Math.floor((ResM * (parseInt($("[name=\"" + NameResB + "Percent\"]").val()) / 100)) / Mod_ResB);
                $("[name=\"" + NameResA + "\"]").val(ResA).prettyInputBox(true);
                $("[name=\"" + NameResB + "\"]").val(ResB).prettyInputBox(true);
            } else if (Mode == 2) {
                ResA = Math.floor((ResM * (parseInt($("[name=\"" + NameResA + "Percent\"]").val()) / 100)) / Mod_ResA);
                ResB = Math.floor((ResM * (parseInt($("[name=\"" + NameResB + "Percent\"]").val()) / 100)) / Mod_ResB);
                $("[name=\"" + NameResA + "\"]").val(ResA).prettyInputBox(true);
                $("[name=\"" + NameResB + "\"]").val(ResB).prettyInputBox(true);
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
            $(this).val(removeNonDigit($(this).val()));
            if ($(this).attr("name") != NameResM) {
                Calculate();
            } else {
                Calculate(true);
            }
            $(this).prettyInputBox(true);
        }).keyup(function () {
            $(this).change();
        }).keydown(function (event) {
            var ThisCount;
            if (event.which == 38) {
                ThisCount = parseFloat(removeNonDigit($(this).val()));
                if (isNaN(ThisCount)) {
                    ThisCount = 0;
                }
                $(this).val(ThisCount + 1).change();
            } else if (event.which == 40) {
                ThisCount = parseFloat(removeNonDigit($(this).val()));
                if (isNaN(ThisCount) || ThisCount <= 0) {
                    return false;
                }
                $(this).val(ThisCount - 1).change();
            }
        }).focus(function () {
            if (!$(this).notEmptyVal(false, true)) {
                $(this).val("");
            }
        }).blur(function () {
            if (!$(this).notEmptyVal(true, true)) {
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
            ThisCount = parseFloat(removeNonDigit($(this).val()));
            if (isNaN(ThisCount)) {
                ThisCount = 0;
            } else {
                if (ThisCount >= 100) {
                    return false;
                }
            }
            $(this).val(ThisCount + 1).change();
        } else if (event.which == 40) {
            ThisCount = parseFloat(removeNonDigit($(this).val()));
            if (isNaN(ThisCount) || ThisCount <= 0) {
                return false;
            }
            $(this).val(ThisCount - 1).change();
        }
    });

    $("th").css("padding", "3px");
});
