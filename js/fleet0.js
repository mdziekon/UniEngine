/* globals libCommon, JSLang, ShipsData, TotalPlanetResources, ACSUsersMax */

$(document).ready(function () {
    libCommon.init.setupJQuery();

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
            if (!(event.which == 38 || event.which == 40)) {
                return;
            }

            const currentCountRaw = parseFloat(libCommon.normalize.removeNonDigit($(this).val()));
            const currentCount = (
                Number.isNaN(currentCountRaw) ?
                    0 :
                    currentCountRaw
            );
            const incrementBy = (
                (event.which == 38) ?
                    1 :
                    -1
            );
            const nextCount = currentCount + incrementBy;
            const nextCountNormalized = (nextCount >= 0 ? nextCount : 0);

            $(this).val(nextCountNormalized).keyup();
        })
        .keyup(function () {
            var ThisCount = parseInt(libCommon.normalize.removeNonDigit($(this).val()), 10);
            var OldCount = $(this).data("oldCount");
            if (OldCount === undefined || isNaN(OldCount)) {
                OldCount = 0;
            }
            if (isNaN(ThisCount)) {
                ThisCount = 0;
            }
            var Difference = ThisCount - OldCount;
            if (Difference != 0) {
                var ThisShipID = libCommon.normalize.removeNonDigit($(this).attr("name"));
                var StorageCalced = parseInt(libCommon.normalize.removeNonDigit(CalcStorage.html()), 10);
                StorageCalced += Difference * ShipsData[ThisShipID]["storage"];
                if (StorageCalced >= TotalPlanetResources) {
                    CalcStorage.removeClass("orange").addClass("lime");
                } else {
                    CalcStorage.removeClass("lime").addClass("orange");
                }
                CalcStorage.html(libCommon.format.addDots(StorageCalced));
                $(this).data("oldCount", ThisCount);

                if (ThisCount > ShipsData[ThisShipID]["count"]) {
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
        $("#ship" + GetID).val(ShipsData[GetID]["count"]).keyup();
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
