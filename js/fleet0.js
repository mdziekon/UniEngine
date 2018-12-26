/* globals AllowPrettyInputBox, JSLang, ShipsData, TotalPlanetResources, JSShipSet, ACSUsersMax */

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

    // Rest of scripts...

    $(".setACS_ID").click(function () {
        $("[name=getacsdata]").val($(this).val());
    });

    $(".FBeh").tipTip({maxWidth: "250px", delay: 0, edgeOffset: 8, attribute: "title"});
    $(".Speed").tipTip({maxWidth: "250px", delay: 0, edgeOffset: 8, attribute: "title"});
    $(".fInfo").tipTip({maxWidth: "300px", minWidth: "200px", delay: 0, edgeOffset: 8, attribute: "title"});
    $(".planet").tipTip({delay: 0, edgeOffset: 8, content: JSLang["fl_coordplanet"]});
    $(".moon").tipTip({delay: 0, edgeOffset: 8, content: JSLang["fl_coordmoon"]});
    $(".debris").tipTip({delay: 0, edgeOffset: 8, content: JSLang["fl_coorddebris"]});

    // Elements Cache
    var CalcStorage = $("#calcStorage");

    $("[name^=\"ship\"]")
        .keydown(function (event) {
            var ThisCount;
            if (event.which == 38) {
                ThisCount = parseFloat(removeNonDigit($(this).val()));
                if (isNaN(ThisCount)) {
                    ThisCount = 0;
                }
                $(this).val(ThisCount + 1).keyup();
            } else if (event.which == 40) {
                ThisCount = parseFloat(removeNonDigit($(this).val()));
                if (isNaN(ThisCount) || ThisCount <= 0) {
                    return false;
                }
                $(this).val(ThisCount - 1).keyup();
            }
        })
        .keyup(function () {
            var ThisCount = parseInt(removeNonDigit($(this).val()), 10);
            var OldCount = $(this).data("oldCount");
            if (OldCount === undefined || isNaN(OldCount)) {
                OldCount = 0;
            }
            if (isNaN(ThisCount)) {
                ThisCount = 0;
            }
            var Difference = ThisCount - OldCount;
            if (Difference != 0) {
                var ThisShipID = removeNonDigit($(this).attr("name"));
                var StorageCalced = parseInt(removeNonDigit(CalcStorage.html()), 10);
                StorageCalced += Difference * ShipsData["storage"][ThisShipID];
                if (StorageCalced >= TotalPlanetResources) {
                    CalcStorage.removeClass("orange").addClass("lime");
                } else {
                    CalcStorage.removeClass("lime").addClass("orange");
                }
                CalcStorage.html(addDots(StorageCalced));
                $(this).data("oldCount", ThisCount);

                if (ThisCount > ShipsData["count"][ThisShipID]) {
                    $(this).addClass("red");
                } else {
                    $(this).removeClass("red");
                }
            }

            $(this).prettyInputBox();
        })
        .change(function () {
            $(this).keyup();
        })
        .focus(function () {
            if ($(this).val() == "0") {
                $(this).val("");
            }
        })
        .blur(function () {
            if ($(this).val() == "") {
                $(this).val("0");
            }
        });

    $(".maxShip").click(function () {
        var GetClass = $(this).parent().attr("class");
        var GetID = GetClass.split(" ")[0].substr(2);
        $("#ship" + GetID).val(ShipsData["count"][GetID]).keyup();
    });
    $(".noShip").click(function () {
        var GetClass = $(this).parent().attr("class");
        var GetID = GetClass.split(" ")[0].substr(2);
        $("#ship" + GetID).val(0).keyup();
    });

    $(".maxShipAll").click(function () {
        $(".maxShip").click();
    });
    $(".noShipAll").click(function () {
        $(".noShip").click();
    });

    $(".addPad2").children(":not(.pad5)").addClass("pad2");

    if (JSShipSet !== false) {
        for (var ShipID in JSShipSet) {
            $("#ship" + ShipID).val(JSShipSet[ShipID]).keyup();
        }
    }

    var ACSUsers_Invited = $("#ACSUser_Invited");
    var ACSUsers_2Invite = $("#ACSUser_2Invite");
    var ACSUsers_Changed = $("[name=\"acsuserschanged\"]");

    $("#ACSUserAdd").click(function () {
        if ($("option", ACSUsers_Invited).length < (ACSUsersMax + 1)) {
            var ThisSelected = ACSUsers_2Invite.children("option:selected");
            if (ThisSelected.length > 0) {
                ACSUsers_Invited.append($("<option></option>").attr("value", ThisSelected.val()).text(ThisSelected.text()));
                ThisSelected.remove();
                ACSUsers_Changed.val("1");
            }
        }
    });
    $("#ACSUserRmv").click(function () {
        var ThisSelected = ACSUsers_Invited.children("option:selected");
        if (ThisSelected.length > 0) {
            if (!ThisSelected.is(":disabled")) {
                ACSUsers_2Invite.append($("<option></option>").attr("value", ThisSelected.val()).text(ThisSelected.text()));
                ThisSelected.remove();
                ACSUsers_Changed.val("1");
            }
        }
    });

    $("#ACSForm").submit(function () {
        var UsersString = "";
        ACSUsers_Invited.children("option").each(function () {
            UsersString += $(this).val() + ",";
        });
        $("[name=\"acs_users\"]").val(UsersString);
    });

    $("[name^=\"ship\"]").change();
});
