/* globals AllowPrettyInputBox, JSLang, ShipPrices */

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

    $.fn.prettyInputBox = function () {
        return this.each(function () {
            if (AllowPrettyInputBox !== undefined && AllowPrettyInputBox === true) {
                var Value = removeNonDigit($(this).val());
                Value = addDots(Value);
                $(this).val(Value);
            }
        });
    };

    var TotalPrice = new Object();
    TotalPrice["metal"] = 0;
    TotalPrice["crystal"] = 0;
    TotalPrice["deuterium"] = 0;
    var ClickFromMax = false;
    var ClickFromSelectAll = false;
    var TotalCount = 0;

    $(".ssDiv").tipTip({delay: 100});
    $(".infoRes_metal").tipTip({content: JSLang["Metal"], defaultPosition: "top", delay: 50});
    $(".infoRes_crystal").tipTip({content: JSLang["Crystal"], defaultPosition: "top", delay: 50});
    $(".infoRes_deuterium").tipTip({content: JSLang["Deuterium"], defaultPosition: "top", delay: 50});

    $(".ssDiv").hover(function () {
        $(this).find(".ssImg, .ssBg, .ssLvl").addClass("ssHover");
    }, function () {
        $(this).find(".ssImg, .ssBg, .ssLvl").removeClass("ssHover");
    });

    $("[id^=\"ssEl_\"]")
        .click(function () {
            $(this).children("input").focus();
        });

    $("a.buildDo_Gray, a.destroyDo_Gray").click(function () {
        return false;
    });

    $("#buttonC").click(function () {
        if (!$(this).hasClass("construct_Gray")) {
            $("#disassemblerForm").submit();
        }
        return false;
    });

    $(".maxDo").click(function () {
        var ThisParent = $(this).parent();
        if (ThisParent.children(".ssDis").length > 0) {
            return false;
        }
        ClickFromMax = true;
        ThisParent.children(".ssInput").val($(".count", ThisParent).html()).keyup();
    });

    // Cache for ssInput
    var ButtonElement = $("#buttonC");

    $(".ssInput")
        .click(function () {
            if ($(this).is(":focus")) {
                return false;
            }
        })
        .keypress(function (e) {
            var keyCode = (e.keyCode ? e.keyCode : e.which);
            if (keyCode == 13) {
                ButtonElement.click();
            }
        })
        .keyup(function () {
            var ThisCount = parseFloat(removeNonDigit($(this).val()));
            if (ClickFromSelectAll === false) {
            // Skip this if using SelectAll
                var ThisMax = parseFloat(removeNonDigit($(".count", $(this).parent()).html()));
                if (ThisCount > ThisMax) {
                    ThisCount = ThisMax;
                    $(this).val(ThisMax);
                    $(this).prettyInputBox();
                }
            }
            var OldCount = $(this).data("oldCount");
            if (OldCount === undefined || isNaN(OldCount)) {
                OldCount = 0;
            }
            if (isNaN(ThisCount)) {
                ThisCount = 0;
            }
            var Difference = ThisCount - OldCount;
            if (Difference != 0) {
                $(this).data("oldCount", ThisCount);
                var ThisElementID = $(this).attr("name").replace("elem[", "").replace("]", "");
                for (var Key in ShipPrices[ThisElementID]) {
                    TotalPrice[Key] += ShipPrices[ThisElementID][Key] * Difference;
                }
                TotalCount += Difference;
                if (ClickFromSelectAll === false) {
                // Skip this if using SelectAll
                    for (var PriceKey in TotalPrice) {
                        var ThisSelector = $("#resC_" + PriceKey);
                        ThisSelector.html(addDots(TotalPrice[PriceKey]));
                    }

                    if (TotalCount > 0) {
                        if (ButtonElement.hasClass("construct_Gray")) {
                            ButtonElement.removeClass("construct_Gray").addClass("construct_Green");
                        }
                    } else {
                        if (ButtonElement.hasClass("construct_Green")) {
                            ButtonElement.removeClass("construct_Green").addClass("construct_Gray");
                        }
                    }
                }
                $(this).prettyInputBox();
            }
        })
        .change(function () {
            $(this).keyup();
        })
        .focus(function () {
            if (ClickFromMax === false && $(this).val() != "") {
                $(this).val("").keyup();
            }
            ClickFromMax = false;
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
        });

    $("#cancelReq").click(function () {
        $(".ssInput").each(function () {
            $(this).val("").keyup();
        });
    });
    $("#selAll").click(function () {
        ClickFromSelectAll = true;
        $(".maxDo:not(.inv)").each(function () {
            $(this).click();
        });

        for (var Key in TotalPrice) {
            var ThisSelector = $("#resC_" + Key);
            ThisSelector.html(addDots(TotalPrice[Key]));
        }

        if (TotalCount > 0) {
            if (ButtonElement.hasClass("construct_Gray")) {
                ButtonElement.removeClass("construct_Gray").addClass("construct_Green");
            }
        } else {
            if (ButtonElement.hasClass("construct_Green")) {
                ButtonElement.removeClass("construct_Green").addClass("construct_Gray");
            }
        }

        ClickFromSelectAll = false;
    });

    $(".tabSwitch")
        .hover(function () {
            $(this).addClass("tabHover");
        }, function () {
            $(this).removeClass("tabHover");
        })
        .click(function () {
            if (!$(this).hasClass("tabSelect")) {
                $(".tabSwitch").removeClass("tabSelect");
                $(this).addClass("tabSelect");
                $(".cx").hide();
                $("#cx_" + $(this).attr("id").replace("tab_", "")).show();
            }
        });

    $("#tab_ships").click();
});
