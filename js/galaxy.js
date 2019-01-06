/* globals AllowPrettyInputBox, UseAjax, GalaxySettings */
/* globals nd */
/* exported galaxy_submit, Phalanx */

var BlockFunction = true;
var HideTimeout = false;
var RespCodes = new Array();
var Lang = new Array();
var LockGalaxyForm = false;
var AjaxBox = false;
var isReady = false;
var icoTipObj = {
    style: {
        classes: "tiptip_content"
    },
    show: {
        delay: 500
    },
    position: {
        my: "top center",
        at: "bottom center",
        adjust: {y: 10}
    }
};

var El_FlyingFleets = false;

function galaxy_submit (value) {
    if (!isReady) {
        return false;
    }
    $("#auto").attr("name", value);
    $("#galaxy_form").submit();
}

function Phalanx (Galaxy, System, Planet, Type) {
    var NewWindow = window.open("phalanx.php?galaxy=" + Galaxy + "&system=" + System + "&planet=" + Planet + "&planettype=" + Type, "", "resizable=yes,scrollbars=yes,menubar=no,toolbar=no,width=640,height=480,top=0,left=0");
    NewWindow.focus();
    return false;
}

function sendShips (missionType, Galaxy, System, Planet, PlanetType, Options) {
    if (!isReady) {
        return false;
    }
    if (BlockFunction !== true) {
        BlockFunction = true;

        var requestPage;
        var requestOpt;
        var Mission;

        switch (missionType) {
        case 1: //Spy
            Mission = 6;
            requestPage = "ajax/galaxyfleet.php";
            requestOpt = {"galaxy": Galaxy, "system": System, "planet": Planet, "type": PlanetType, "mission": Mission};
            break;
        case 2: //Recycling
            Mission = 8;
            requestPage = "ajax/galaxyfleet.php";
            requestOpt = {"galaxy": Galaxy, "system": System, "planet": Planet, "type": PlanetType, "mission": Mission};
            break;
        case 3: //Colonization
            Mission = 7;
            requestPage = "ajax/galaxyfleet.php";
            requestOpt = {"galaxy": Galaxy, "system": System, "planet": Planet, "type": PlanetType, "mission": Mission};
            break;
        case 4: //Missile Attack
            Mission = 10;
            requestPage = "ajax/sendmissiles.php";
            requestOpt = {"galaxy": Galaxy, "system": System, "planet": Planet, "count": Options["count"], "target": Options["target"]};
            break;
        default://Set to Spy
            Mission = 6;
            requestPage = "ajax/galaxyfleet.php";
            requestOpt = {"galaxy": Galaxy, "system": System, "planet": Planet, "type": PlanetType, "mission": Mission};
            break;
        }
        $.post(requestPage, requestOpt)
            .complete(function (response, requestStatus) {
                if (requestStatus == "success") {
                    var data = response["responseText"];

                    BlockFunction   = false;
                    if (HideTimeout !== false) {
                        clearTimeout(HideTimeout);
                    }

                    var retVals;
                    var Code;
                    var Update;
                    var Missiles;
                    var UsedSlots;
                    var Message;
                    var Infos;
                    var SpyProbes;
                    var Recyclers;
                    var Colonizers;
                    var ShipCount;

                    if (Mission == 10) {
                        retVals         = data.split("|");
                        Code			= retVals[0];
                        Update			= retVals[1];
                        Missiles		= retVals[2];
                        UsedSlots		= retVals[3];
                    } else {
                        retVals         = data.split("|");

                        Message         = retVals[0];
                        Infos           = retVals[1];

                        retVals         = Infos.split(",");
                        UsedSlots       = retVals[0];
                        SpyProbes       = retVals[1];
                        Recyclers       = retVals[2];
                        Colonizers      = retVals[3];

                        retVals         = Message.split(";");
                        Code            = retVals[0];
                        Update          = retVals[1];
                        ShipCount       = retVals[2];
                        Galaxy          = retVals[3];
                        System          = retVals[4];
                        Planet          = retVals[5];
                        PlanetType      = retVals[6];
                    }

                    var CodeText = RespCodes[Code];
                    if ((CodeText.indexOf("xyz")) >= 0) {
                        CodeText = CodeText.replace("xyz", ShipCount);
                    }

                    if (CodeText.indexOf("replace_target") >= 0) {
                        var AddReplace;
                        if (PlanetType == 3) {
                            AddReplace = " (" + Lang["ajax_moon_sign"] + ")";
                        } else {
                            AddReplace = "";
                        }
                        var ReplaceTextNow = CodeText.replace("replace_target", "[" + Galaxy + ":" + System + ":" + Planet + "]" + AddReplace);
                        CodeText = ReplaceTextNow;
                    }

                    var textClass;
                    if (Code.indexOf("600") >= 0) {
                        textClass = "lime";
                    } else {
                        textClass = "red";
                    }

                    ShowAjaxInfo(CodeText, textClass);
                    if (Update == 1) {
                        if (Mission == 10) {
                            $("#missiles, #missiles2").html(Missiles);
                            El_FlyingFleets.html(UsedSlots);
                        } else {
                            El_FlyingFleets.html(UsedSlots);
                            $("#probes").html(SpyProbes);
                            $("#recyclers").html(Recyclers);
                            $("#colonizers").html(Colonizers);
                        }
                    }
                } else {
                    // On Error
                    var ThisRespCode = 0;
                    if (requestStatus == "timeout") {
                        ThisRespCode = "696";
                    } else {
                        ThisRespCode = "698";
                    }
                    ShowAjaxInfo(RespCodes[ThisRespCode], "red", 1500);
                }
            });
    }
    return false;
}

function ShowAjaxInfo (Text, Color, HideTime) {
    if (HideTimeout !== false) {
        clearTimeout(HideTimeout);
    }
    AjaxBox["th"].show(0);
    AjaxBox["info"].html(Text).css("color", Color);
    if (typeof HideTime == "undefined") {
        HideTime = -1;
    }
    if (HideTime >= 0) {
        HideTimeout = setTimeout(function () {
            AjaxBox["th"].hide(400);
            HideTimeout = false;
        }, HideTime);
    }
}

$(document).ready(function () {
    isReady = true;
    var ThisGalaxy = $("[name=\"galaxy\"]");
    var ThisSystem = $("[name=\"system\"]");
    var GalRows = $("#galRows");
    var AutoInput = $("#auto");
    var colonizedCount = $("#colonizedCount");
    var LeftMenu_Messages = $("#lm_msg");
    var MissilesForm = $("#MissileForm");
    var MissilesFields = {"galaxy": $("[name=\"m_galaxy\"]", MissilesForm), "system": $("[name=\"m_system\"]", MissilesForm), "planet": $("[name=\"m_planet\"]", MissilesForm)};
    var CurrentMissilesPos = "0:0:0";
    var MissilesPos = $("#MissilePos");
    AjaxBox = {"th": $("#fleetstatusrow"), "info": $("#ajaxInfoBox")};
    El_FlyingFleets = $("#slots");

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

    BlockFunction = false;

    $("#closeMF").click(function () {
        if (UseAjax === true) {
            CurrentMissilesPos = "0:0:0";
            MissilesForm.hide("fast");
        } else {
            document.location.href = document.location.href.replace("mode=2", "mode=3");
        }
    });

    $(document)
        .on("click", ".icoMissile", function () {
            nd(); //Close ToolTipMenu
            if (UseAjax === true) {
                var RegExp = /galaxy=([0-9]{1,3}).*?system=([0-9]{1,3}).*?planet=([0-9]{1,3})/gi;
                var GetPos = RegExp.exec($(this).attr("href"));

                var ThisPos = GetPos[1] + ":" + GetPos[2] + ":" + GetPos[3];

                if (ThisPos != CurrentMissilesPos) {
                    MissilesFields["galaxy"].val(GetPos[1]);
                    MissilesFields["system"].val(GetPos[2]);
                    MissilesFields["planet"].val(GetPos[3]);
                    CurrentMissilesPos = GetPos[1] + ":" + GetPos[2] + ":" + GetPos[3];
                    $("body").animate({scrollTop:0}, "fast");
                    if (MissilesForm.is(":visible")) {
                        MissilesPos.fadeOut("fast", function () {
                            MissilesPos.html(CurrentMissilesPos); $(this).fadeIn("fast");
                        });
                    } else {
                        MissilesPos.html(CurrentMissilesPos);
                        MissilesForm.show("fast");
                    }
                } else {
                    CurrentMissilesPos = "0:0:0";
                    MissilesForm.hide("fast");
                }

                return false;
            }
        })
        .on("mouseover", ".icoTip", function () {
            if ($(this).data("hasTip") !== true) {
                if ($(this).hasClass("icoMissile")) {
                    $(this).qtip($.extend(icoTipObj, {content: Lang["icoTip_missile"]}));
                } else if ($(this).hasClass("icoSpy")) {
                    $(this).qtip($.extend(icoTipObj, {content: Lang["icoTip_spy"]}));
                } else if ($(this).hasClass("icoMsg")) {
                    $(this).qtip($.extend(icoTipObj, {content: Lang["icoTip_msg"]}));
                } else if ($(this).hasClass("icoBuddy")) {
                    $(this).qtip($.extend(icoTipObj, {content: Lang["icoTip_buddy"]}));
                }
                $(this).data("hasTip", true).trigger("mouseover");
            }
        });
    $("#MissileForm").submit(function () {
        sendShips(4, MissilesFields["galaxy"].val(), MissilesFields["system"].val(), MissilesFields["planet"].val(), 1, {"count": $("[name=\"m_count\"]").val(), "target": $("[name=\"m_target\"]").val()});
        return false;
    });
    $("[name=\"m_count\"]").change(function () {
        $(this).prettyInputBox();
    }).keyup(function () {
        $(this).change();
    }).keydown(function () {
        $(this).change();
    });

    $("#galaxy_form").submit(function () {
        if (UseAjax === true) {
            if (LockGalaxyForm === false) {
                LockGalaxyForm = true;

                if (ThisGalaxy.val() == "" || isNaN(ThisGalaxy.val())) {
                    ThisGalaxy.val(1);
                }
                if (ThisSystem.val() == "" || isNaN(ThisSystem.val())) {
                    ThisSystem.val(1);
                }

                var OldGalaxy = parseInt(ThisGalaxy.val(), 10);
                var OldSystem = parseInt(ThisSystem.val(), 10);

                if (typeof AutoInput.attr("name") != "undefined" && AutoInput.attr("name") != "") {
                    var ThisName = AutoInput.attr("name");
                    switch (ThisName) {
                    case "galaxyLeft":
                        if (ThisGalaxy.val() == 1) {
                            ThisGalaxy.val(GalaxySettings["maxGal"]);
                        } else {
                            ThisGalaxy.val(parseInt(ThisGalaxy.val(), 10) - 1);
                        }
                        break;
                    case "galaxyRight":
                        if (ThisGalaxy.val() == GalaxySettings["maxGal"]) {
                            ThisGalaxy.val(1);
                        } else {
                            ThisGalaxy.val(parseInt(ThisGalaxy.val(), 10) + 1);
                        }
                        break;
                    case "systemLeft":
                        if (ThisSystem.val() == 1) {
                            ThisSystem.val(GalaxySettings["maxSys"]);
                            if (ThisGalaxy.val() == 1) {
                                ThisGalaxy.val(GalaxySettings["maxGal"]);
                            } else {
                                ThisGalaxy.val(parseInt(ThisGalaxy.val(), 10) - 1);
                            }
                        } else {
                            ThisSystem.val(parseInt(ThisSystem.val(), 10) - 1);
                        }
                        break;
                    case "systemRight":
                        if (ThisSystem.val() == GalaxySettings["maxSys"]) {
                            ThisSystem.val(1);
                            if (ThisGalaxy.val() == GalaxySettings["maxGal"]) {
                                ThisGalaxy.val(1);
                            } else {
                                ThisGalaxy.val(parseInt(ThisGalaxy.val(), 10) + 1);
                            }
                        } else {
                            ThisSystem.val(parseInt(ThisSystem.val(), 10) + 1);
                        }
                        break;
                    }
                }

                $(document.body).append("<div id=\"cover\" style=\"position: absolute; background: url(./images/ajax-loader-big.gif) no-repeat center center;\"><div style=\"width: 100%; height: 100%; background: black; opacity: 0.3;\"></div></div>");
                $("#cover").offset(GalRows.offset()).width(GalRows.outerWidth()).height(GalRows.outerHeight());

                $.get("ajax/galaxy.php", {"galaxy": ThisGalaxy.val(), "system": ThisSystem.val()})
                    .complete(function (Response, RequestStatus) {
                        var ErrorOccured = false;
                        ThisGalaxy.val(OldGalaxy);
                        ThisSystem.val(OldSystem);
                        if (RequestStatus == "success") {
                            Response = $.parseJSON(Response["responseText"]);
                            if (typeof Response["Err"] === "undefined") {
                                if (Response["msg"] > 0) {
                                    var LeftMenu_MessagesCount = LeftMenu_Messages.children("#lm_msgc");
                                    if (LeftMenu_MessagesCount.length > 0) {
                                        LeftMenu_MessagesCount.html("(" + Response["msg"] + ")");
                                    } else {
                                        LeftMenu_Messages.addClass("orange").append("<b id=\"lm_msgc\">(" + Response["msg"] + ")</b>");
                                    }
                                } else {
                                    LeftMenu_Messages.children("#lm_msgc").remove().end().removeClass("orange");
                                }
                                El_FlyingFleets.html(Response["fly"]);
                                colonizedCount.html(Response["PC"]);
                                GalRows.html(Response["Data"]);
                                ThisGalaxy.val(Response["G"]);
                                ThisSystem.val(Response["S"]);
                                document.location.hash = ThisGalaxy.val() + ":" + ThisSystem.val();

                                CurrentMissilesPos = "0:0:0";
                                MissilesForm.hide("fast");
                            } else {
                                ErrorOccured = true;
                            }
                        }
                        if (RequestStatus != "success" || ErrorOccured === true) {
                            var ThisRespCode = 0;
                            if (RequestStatus == "success") {
                                ThisRespCode = Response["Err"];
                            } else {
                                if (RequestStatus == "timeout") {
                                    ThisRespCode = "696";
                                } else {
                                    ThisRespCode = "695";
                                }
                            }
                            ShowAjaxInfo(RespCodes[ThisRespCode], "red", 1500);
                        }
                        $("#cover").remove();

                        LockGalaxyForm = false;
                    });
            }

            AutoInput.attr("name", "");
            return false;
        }
    });

    $(window).resize(function () {
        if ($("#cover").is(":visible")) {
            $("#cover").offset(GalRows.offset()).width(GalRows.outerWidth()).height(GalRows.outerHeight());
        }
    });

    if (UseAjax === true && typeof document.location.hash != "undefined" && document.location.hash != "") {
        var Position = document.location.hash.replace("#", "").split(":");
        if (ThisGalaxy.val() != Position[0] || ThisSystem.val() != Position[1]) {
            ThisGalaxy.val(Position[0]);
            ThisSystem.val(Position[1]);
            $("#galaxy_form").submit();
        }
    }
});
