/* globals AllowPrettyInputBox, ShowElementOnStartup, JSLang, ShipPrices, ShipTimes, RunQueueHandler, Resources, QueueArray */
/* exported createDestructionTooltipContentHTML */

function createDestructionTooltipContentHTML (props) {
    const LANG = props.LANG;
    const resources = props.resources;
    const destructionTime = props.destructionTime;

    const resourcesListHTML = resources
        .map(function (resourceDetails) {
            const resourceName = resourceDetails.name;
            const resourceColor = resourceDetails.color;
            const resourceValue = resourceDetails.value;

            const resourceHTML = `
                <span class="destLab">
                    ${resourceName}:
                </span>
                <span class="destVal ${resourceColor}">
                    ${resourceValue}
                </span>
                <br/>
            `;

            return resourceHTML.trim();
        })
        .join("");

    const contentHTML = `
        <b class="destCost">
            ${LANG["InfoBox_DestroyCost"]}:
        </b>
        <br />
        ${resourcesListHTML}
        <b class="destTime">
            ${LANG["InfoBox_DestroyTime"]}:
        </b>
        <br/>
        <span class="destTimeVal">
            ${destructionTime}
        </span>
    `;

    return contentHTML.trim();
}

$(document).ready(function () {
    var local_ShowElementOnStartup = ShowElementOnStartup;

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

    // PHP2JS Port
    function prettyTime (Seconds) {
        var $Seconds    = parseFloat(Seconds);
        var $Days       = Math.floor($Seconds / 86400);
        $Seconds   -= $Days * 86400;
        var $Hours      = Math.floor($Seconds / 3600);
        $Seconds   -= $Hours * 3600;
        var $Minutes    = Math.floor($Seconds / 60);
        $Seconds   -= $Minutes * 60;

        if ($Hours < 10) {
            $Hours = "0" + $Hours + "";
        }
        if ($Minutes < 10) {
            $Minutes = "0" + $Minutes + "";
        }
        if ($Seconds < 10) {
            $Seconds = "0" + $Seconds + "";
        }
        var $Time = "";
        if ($Days > 0) {
            $Time = $Days + "d ";
        }
        $Time += $Hours + "g " + $Minutes + "m " + $Seconds + "s";

        return $Time;
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

    var nfoMainElement = $("#nfoElements");
    var nfoElement0 = $("#nfoEl_0", nfoMainElement);
    var nfoLastVisible = nfoElement0;
    var nfoSelected = null;
    var TotalPrice = new Object();
    TotalPrice["metal"] = 0;
    TotalPrice["crystal"] = 0;
    TotalPrice["deuterium"] = 0;
    var TotalTime = 0;
    var TotalCount = 0;
    var ClickFromMax = false;
    var QueueHandlerInterval = false;
    var CurrentQueueID = 1;

    if (local_ShowElementOnStartup === "") {
        if (window.location.href.indexOf("#b_id_") !== -1) {
            local_ShowElementOnStartup = window.location.href.match(/.*?#b_id_([0-9]{1,})/);
            local_ShowElementOnStartup = local_ShowElementOnStartup[1];
        }
    }

    if ($("#ssEl_" + local_ShowElementOnStartup).length) {
        nfoSelected = $("#nfoEl_" + local_ShowElementOnStartup, nfoMainElement);
        nfoElement0.toggle();
        nfoLastVisible = nfoSelected.toggle().removeClass("hide");
        $("#ssEl_" + local_ShowElementOnStartup).find(".ssImg, .ssBg, .ssLvl").addClass("ssSelect");
    }

    $(".ssDiv").tipTip({delay: 100});
    $(".tReqDiv").tipTip({delay: 50});
    $(".endDate").tipTip({delay: 100});
    $("b.infoResReq").tipTip({content: JSLang["InfoBox_ShowResReq"], edgeOffset: 10, defaultPosition: "top", delay: 50});
    $("b.infoTechReq").tipTip({content: JSLang["InfoBox_ShowTechReq"], edgeOffset: 10, defaultPosition: "top", delay: 50});
    $(".infoRes_metal").tipTip({content: JSLang["Metal"], defaultPosition: "top", delay: 50});
    $(".infoRes_crystal").tipTip({content: JSLang["Crystal"], defaultPosition: "top", delay: 50});
    $(".infoRes_deuterium").tipTip({content: JSLang["Deuterium"], defaultPosition: "top", delay: 50});
    $(".infoRes_energy_max").tipTip({content: JSLang["Energy"], defaultPosition: "top", delay: 50});
    $(".infoRes_darkEnergy").tipTip({content: JSLang["DarkEnergy"], defaultPosition: "top", delay: 50});

    $(".ssDiv").hover(function () {
        $(this).find(".ssImg, .ssBg, .ssLvl").addClass("ssHover");
    }, function () {
        $(this).find(".ssImg, .ssBg, .ssLvl").removeClass("ssHover");
    });

    $(".cancelQueue").click(function () {
        if (!$(this).hasClass("cancelQueue")) {
            return true;
        }

        if ($(this).hasClass("premblock")) {
            alert(JSLang["Queue_CantCancel_Premium"]);
            return false;
        } else {
            return confirm(JSLang["Queue_ConfirmCancel"]);
        }
    });
    $("[id^=\"ssEl_\"]")
        .hover(function () {
            if (nfoSelected === null) {
                var ElementID = $(this).attr("id").replace("ssEl_", "");
                nfoElement0.toggle();
                nfoLastVisible = $("#nfoEl_" + ElementID, nfoMainElement).removeClass("hide");
                if (!nfoLastVisible.is(":visible")) {
                    nfoLastVisible.toggle();
                }
            }
        }, function () {
            if (nfoSelected === null) {
                nfoLastVisible.toggle();
                nfoLastVisible = nfoElement0;
                nfoElement0.toggle();
            }
        })
        .click(function () {
            var ElementID;
            if (nfoSelected === null) {
                var CheckIfVisibleID = "nfoEl_" + $(this).attr("id").replace("ssEl_", "");
                if (CheckIfVisibleID != nfoLastVisible.attr("id")) {
                    ElementID = $(this).attr("id").replace("ssEl_", "");
                    nfoElement0.toggle();
                    nfoLastVisible = $("#nfoEl_" + ElementID, nfoMainElement).toggle().removeClass("hide");
                }
                ElementID = $(this).attr("id").replace("ssEl_", "");
                nfoSelected = $("#nfoEl_" + ElementID, nfoMainElement);
                $(this).find(".ssImg, .ssBg, .ssLvl").addClass("ssSelect").end().children("input").focus();
            } else {
                ElementID = $(this).attr("id").replace("ssEl_", "");
                if (nfoSelected.attr("id") != "nfoEl_" + ElementID) {
                    nfoLastVisible.toggle();
                    nfoLastVisible = $("#nfoEl_" + ElementID, nfoMainElement).toggle().removeClass("hide");
                    nfoSelected = $("#nfoEl_" + ElementID, nfoMainElement);
                    $(".ssSelect").removeClass("ssSelect");
                    $(this).find(".ssImg, .ssBg, .ssLvl").addClass("ssSelect").end().children("input").focus();
                } else {
                    nfoSelected = null;
                    $(this).find(".ssImg, .ssBg, .ssLvl").removeClass("ssSelect").end().children("input").blur();
                }
            }
        });

    $(".reqSelector")
        .click(function () {
            if (!$(this).hasClass("reqSelected")) {
                var thisParent = $(this).parent();
                thisParent.children(".reqSelected").removeClass("reqSelected");
                $(this).addClass("reqSelected");
                if ($(this).hasClass("infoResReq")) {
                    $("div.infoResReq", thisParent).show(0).removeClass("hide");
                    $("div.infoTechReq", thisParent).hide(0);
                } else {
                    $("div.infoTechReq", thisParent).show(0).removeClass("hide");
                    $("div.infoResReq", thisParent).hide(0);
                }
            }
        });

    $("a.buildDo_Gray, a.destroyDo_Gray").click(function () {
        return false;
    });

    $("#buttonC").click(function () {
        if (!$(this).hasClass("construct_Gray")) {
            $("#shipyardForm").submit();
        }
        return false;
    });

    $(".maxDo").click(function () {
        var ThisParent = $(this).parent();
        if (ThisParent.children(".ssDis").length > 0) {
            return false;
        }
        var isSelected = ThisParent.children(".ssBg").hasClass("ssSelect");
        ClickFromMax = true;
        $(".ssInput").val("").keyup();
        ThisParent.children(".ssInput").val($("#maxConst_" + ThisParent.attr("id").replace("ssEl_", "")).html()).keyup();
        if (isSelected) {
            return false;
        }
    });

    // Cache for ssInput
    var TimerElement = $("#timeC");
    var ButtonElement = $("#buttonC");

    $(".ssInput").click(function () {
        if ($(this).parent().children(".ssBg").hasClass("ssSelect")) {
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
                TotalTime += ShipTimes[ThisElementID] * Difference;
                TotalCount += Difference;
                for (var PriceKey in TotalPrice) {
                    var ThisSelector = $("#resC_" + PriceKey);
                    ThisSelector.html(addDots(TotalPrice[PriceKey]));
                    if (TotalPrice[PriceKey] > Resources[PriceKey]) {
                        if (!ThisSelector.hasClass("red")) {
                            ThisSelector.addClass("red");
                        }
                    } else {
                        if (ThisSelector.hasClass("red")) {
                            ThisSelector.removeClass("red");
                        }
                    }
                }
                TimerElement.html(prettyTime(TotalTime));
                if (TotalCount > 0) {
                    if (ButtonElement.hasClass("construct_Gray")) {
                        ButtonElement.removeClass("construct_Gray").addClass("construct_Green");
                    }
                } else {
                    if (ButtonElement.hasClass("construct_Green")) {
                        ButtonElement.removeClass("construct_Green").addClass("construct_Gray");
                    }
                }

                if (ThisCount > removeNonDigit($("#maxConst_" + ThisElementID).html())) {
                    $(this).addClass("red");
                } else {
                    $(this).removeClass("red");
                }

                $(this).prettyInputBox();
            }
        })
        .change(function () {
            $(this).keyup();
        })
        .focus(function (event) {
            if (ClickFromMax === false && $(this).val() != "") {
                $(this).val("").keyup();
            }
            ClickFromMax = false;
            var GetThisParent = $(this).parent();
            if (!GetThisParent.children("img").hasClass("ssSelect")) {
                GetThisParent.click();
                event.preventDefault();
            }
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

    if (RunQueueHandler === "true") {
        var QueueSelectorsCache = new Object();
        QueueSelectorsCache["tr"] = new Object();
        QueueSelectorsCache["count"] = new Object();
        QueueHandlerInterval = setInterval(function () {
            if (QueueSelectorsCache["tr"][CurrentQueueID] === undefined) {
                QueueSelectorsCache["tr"][CurrentQueueID] = $("#queueID_" + CurrentQueueID);
                if (QueueSelectorsCache["tr"][CurrentQueueID].length > 0) {
                    QueueSelectorsCache["count"][CurrentQueueID] = QueueSelectorsCache["tr"][CurrentQueueID].find(".count");
                } else {
                    clearInterval(QueueHandlerInterval);
                    return false;
                }
            }

            QueueArray[CurrentQueueID]["Count"] -= QueueArray[CurrentQueueID]["Remove"];
            if (QueueArray[CurrentQueueID]["Count"] > 0) {
                QueueSelectorsCache["count"][CurrentQueueID].html(addDots(Math.ceil(QueueArray[CurrentQueueID]["Count"])));
            } else {
                QueueSelectorsCache["tr"][CurrentQueueID].remove();
                CurrentQueueID += 1;
            }
        }, 100);
    }
});
