/* globals MyTechs, MyFleets, AllowPrettyInputBox, PlanetOwnerTxt, CurrentSlot */
/* exported f */

var local_CurrentSlot;

function f (target_url, win_name) {
    var new_win = window.open(target_url,win_name,"resizable=yes,scrollbars=yes,menubar=no,toolbar=no,width=550,height=280,top=0,left=0");
    new_win.focus();
}

function fillMyTechs (type) {
    for (var TechID in MyTechs) {
        $("[name=\"" + type + "_techs[" + local_CurrentSlot + "][" + TechID + "]\"]").val(MyTechs[TechID]);
    }
}
function fillMyFleets (type) {
    for (var ShipID in MyFleets) {
        $("[name=\"" + type + "_ships[" + local_CurrentSlot + "][" + ShipID + "]\"]").val(MyFleets[ShipID]);
    }
}
function cleanTechs (type) {
    $("[name^=\"" + type + "_techs[\"]").each(function () {
        $(this).val("");
    });
}
function cleanFleets (type) {
    $("[name^=\"" + type + "_ships[\"]").each(function () {
        $(this).val("");
    });
}

$(document).ready(function () {
    local_CurrentSlot = CurrentSlot;

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

    $("#toggleView_Result").click(function () {
        $("#result").toggle(0);
        return false;
    });

    $("#simForm").submit(function () {
        $("[name^=\"atk_techs\"],[name^=\"def_techs\"],[name^=\"atk_ships\"],[name^=\"def_ships\"]").each(function () {
            if ($(this).val() == "" || $(this).val() == "0") {
                $(this).attr("name", "");
            }
        });
    });

    $("[name^=\"atk_ships\"],[name^=\"def_ships\"]").keyup(function () {
        $(this).prettyInputBox();
    });

    $(".fillTech_atk").click(function () {
        fillMyTechs("atk");
        return false;
    });
    $(".fillTech_def").click(function () {
        fillMyTechs("def");
        return false;
    });
    $(".clnTech_atk").click(function () {
        cleanTechs("atk");
        return false;
    });
    $(".clnTech_def").click(function () {
        cleanTechs("def");
        return false;
    });

    $(".fillShip_atk").click(function () {
        fillMyFleets("atk");
        return false;
    });
    $(".fillShip_def").click(function () {
        fillMyFleets("def");
        return false;
    });
    $(".clnShip_atk").click(function () {
        cleanFleets("atk");
        return false;
    });
    $(".clnShip_def").click(function () {
        cleanFleets("def");
        return false;
    });

    $(".maxOne").click(function () {
        var $ThisName = $(this).parent().parent().children("input[type=\"text\"]").attr("name");
        var $ThisMatch = $ThisName.match(/.*?\[[0-9]{1,}\]\[([0-9]{1,})\]/);
        $("[name=\"" + $ThisName + "\"]").val(MyFleets[$ThisMatch[1]]);
    });
    $(".clnOne").click(function () {
        var $ThisName = $(this).parent().parent().children("input[type=\"text\"]").attr("name");
        $("[name=\"" + $ThisName + "\"]").val("");
    });

    $(".chgSlot").click(function () {
        var $ThisSlot = $(this).val().replace("#", "");

        $("tbody#slot_" + local_CurrentSlot).addClass("hide");
        $("tbody#slot_" + $ThisSlot).removeClass("hide");

        local_CurrentSlot = $ThisSlot;
        $("#attacker_slot").html($ThisSlot);
        $("#defender_slot").html($ThisSlot);
        if ($ThisSlot == 1) {
            $("#is_planet_owner").html(PlanetOwnerTxt);
        } else {
            $("#is_planet_owner").html("");
        }

        $("[value^=\"#\"]").removeClass("bold");
        $("[value=\"#" + $ThisSlot + "\"]").addClass("bold");

        return false;
    });
});
