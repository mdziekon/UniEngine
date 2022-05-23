/* globals libCommon, JSLang, AllyPact_AttackWarn, SetResources, SelectQuantumGate, NeedQuantumGate, ResSortArrayAll, QuantumGateDeuteriumUse, ResSortArrayNoDeu, FlightDuration, uniengine */

var SetMaxNow = false;
var LastStorageLowerTh0 = false;
var QuantumGateOptionModif = false;

$(document).ready(function () {
    libCommon.init.setupJQuery();

    var FlightDurationTarget = FlightDuration;
    var FlightDurationGoback = FlightDuration;

    $.fn.setStorageShow = function (setVar) {
        var AddColor = "orange";
        var RemColor = "lime";
        var RemColor2 = "red";
        if (setVar > 0) {
            AddColor = "lime";
            RemColor = "orange";
        } else if (setVar < 0) {
            AddColor = "red";
            RemColor2 = "orange";
        }
        var El = $("#FreeStorageShow");
        $("#FreeStorageShow").val(setVar).prettyInputBox().html($("#FreeStorageShow").val());
        if (El.hasClass(RemColor)) {
            $("#FreeStorageShow").removeClass(RemColor);
        } else if (El.hasClass(RemColor2)) {
            $("#FreeStorageShow").removeClass(RemColor2);
        }
        if (!El.hasClass(AddColor)) {
            $("#FreeStorageShow").addClass(AddColor);
        }
    };

    function createTimeCounters () {
        const reachTimeFormatted = libCommon.format.formatDateToFlightEvent((FlightDurationTarget * 1000));
        const backTimeFormatted = libCommon.format.formatDateToFlightEvent(((FlightDurationTarget + FlightDurationGoback) * 1000));

        $("#ReachTime").html(reachTimeFormatted);
        $("#BackTime").html(backTimeFormatted);
    }

    setInterval(createTimeCounters, 250);

    $(".setMaxResource").click(function () {
        SetMaxNow = true;

        const resourceKey = $(this).data("resourceKey");
        const elSelector = `[name="resource${resourceKey}"]`;

        $(elSelector).val($(elSelector).val() + 1).change();

        SetMaxNow = false;

        return false;
    });
    $(".setZeroResource").click(function () {
        const resourceKey = $(this).data("resourceKey");
        const elSelector = `[name="resource${resourceKey}"]`;

        $(elSelector).val("0").change();

        return false;
    });

    $("#setMaxAll").click(function () {
        ResSortArrayAll.forEach((resourceKey) => {
            $(`.setMaxResource[data-resource-key='${resourceKey}']`).click();
        });
    });
    $("#setZeroAll").click(function () {
        ResSortArrayAll.forEach((resourceKey) => {
            $(`.setZeroResource[data-resource-key='${resourceKey}']`).click();
        });
    });

    $("[name^=\"resource\"]").change(function () {
        var ThisID          = $(this).attr("name").substr(8);

        var LastValue       = $(this).data("lastVal");
        var CurrentValue    = parseInt(libCommon.normalize.removeNonDigit($(this).val()), 10);
        if (LastValue === undefined || isNaN(LastValue) || LastValue < 0) {
            LastValue    = 0;
        }
        if (isNaN(CurrentValue) || CurrentValue < 0) {
            CurrentValue    = 0;
        }
        if (CurrentValue != LastValue || QuantumGateOptionModif) {
            var MaxValue        = parseInt($("#PlanetResource" + ThisID).val(), 10);
            if (ThisID == 3) {
                var DeleteFromVal = parseInt($("#Consumption").val(), 10);
                if ($("#usequantumgate").is(":checked")) {
                    var SelectedMission = $("[name=\"mission\"]:checked").val();
                    if (SelectedMission !== undefined) {
                        if (QuantumGateDeuteriumUse[SelectedMission] == "1") {
                            DeleteFromVal /= 2;
                            DeleteFromVal = Math.ceil(DeleteFromVal);
                        } else if (QuantumGateDeuteriumUse[SelectedMission] == "2") {
                            DeleteFromVal = 0;
                        }
                    }
                }
                MaxValue       -= DeleteFromVal;
                if (MaxValue < 0) {
                    MaxValue = 0;
                }
            }
            if (CurrentValue > MaxValue || SetMaxNow) {
                CurrentValue = MaxValue;
            }
            var FreeStorage     = parseInt($("#FreeStorage").val(), 10);
            var LastValDiff     = CurrentValue - LastValue;
            if (LastValDiff < 0) {
                FreeStorage    -= LastValDiff;
            } else {
                if (LastValDiff > FreeStorage) {
                    CurrentValue -= (LastValDiff - FreeStorage);
                    FreeStorage = 0;
                } else if (LastValDiff == FreeStorage) {
                    FreeStorage = 0;
                } else {
                    FreeStorage -= LastValDiff;
                }
                if (CurrentValue < 0) {
                    FreeStorage += CurrentValue;
                    CurrentValue = 0;
                    LastStorageLowerTh0 = true;
                }
            }

            $(this).val(CurrentValue).prettyInputBox().data("lastVal", CurrentValue);
            $("#FreeStorage").val(FreeStorage).setStorageShow(FreeStorage);
        } else {
            if ($(this).val() != libCommon.normalize.removeNonDigit($(this).val())) {
                $(this).val(CurrentValue).prettyInputBox();
            }
        }
    })
        .keyup(function () {
            $(this).change();
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

    const getQuantumGateModifiers = (modifierType) => {
        if (modifierType == 1) {
            const flightConsumption = parseInt($("#Consumption").val(), 10);
            const fuelStorageReduceHalf = parseInt($("#FuelStorageReduceH").val(), 10);

            const consumptionModifier = (
                Number.isNaN(flightConsumption) ?
                    0 :
                    Math.ceil(flightConsumption / 2)
            );

            return {
                storageModifier: (consumptionModifier - fuelStorageReduceHalf),
                consumptionModifier: consumptionModifier,
                flightTimeToTarget: 1,
                flightTimeBackToOrigin: 0,
            };
        }

        if (modifierType == 2) {
            const flightConsumption = parseInt($("#Consumption").val(), 10);
            const fuelStorageReduce = parseInt($("#FuelStorageReduce").val(), 10);

            const consumptionModifier = (
                Number.isNaN(flightConsumption) ?
                    0 :
                    flightConsumption
            );

            return {
                storageModifier: (consumptionModifier - fuelStorageReduce),
                consumptionModifier: consumptionModifier,
                flightTimeToTarget: 1,
                flightTimeBackToOrigin: 1,
            };
        }

        return {
            storageModifier: 0,
            consumptionModifier: 0,
            flightTimeToTarget: 0,
            flightTimeBackToOrigin: 0,
        };
    };

    $(".mSelect").change(function () {
        const quantumGateMissionModifierType  = QuantumGateDeuteriumUse[$(".mSelect:checked").val()];

        if (quantumGateMissionModifierType === undefined) {
            return;
        }

        const quantumGateModifiers = getQuantumGateModifiers(quantumGateMissionModifierType);

        let StorageModif_New = quantumGateModifiers.storageModifier;
        let ConsuptionModif_New = quantumGateModifiers.consumptionModifier;
        let FlyTimeTargetModif = quantumGateModifiers.flightTimeToTarget;
        let FlyTimeBackModif = quantumGateModifiers.flightTimeBackToOrigin;

        // Change ConsumptionVar
        var ConsuptionModif_Old = $("#FuelUse").data("ConsuptionModif_Old");
        var ConsuptionVar_Now   = $("#FuelUse").data("ConsuptionVar_Now");
        if (ConsuptionModif_Old === undefined) {
            ConsuptionModif_Old = 0;
        }
        if (ConsuptionVar_Now === undefined) {
            ConsuptionVar_Now = parseInt($("#Consumption").val(), 10);
        }
        if (!$("#usequantumgate").is(":checked")) {
            ConsuptionModif_New = 0;
        }
        var ConsuptionModif_Dif = ConsuptionModif_New - ConsuptionModif_Old;
        if (ConsuptionModif_Dif !== 0) {
            ConsuptionVar_Now -= ConsuptionModif_Dif;
            $("#FuelUse").html(libCommon.format.addDots(ConsuptionVar_Now));
            $("#FuelUse").data("ConsuptionModif_Old", ConsuptionModif_New);
            $("#FuelUse").data("ConsuptionVar_Now", ConsuptionVar_Now);
        }

        // Change StorageVar
        var Changed = false;
        var StorageModif_Old = $("#FreeStorage").data("StorageModif_Old");
        if (StorageModif_Old === undefined) {
            StorageModif_Old = 0;
        }
        if (!$("#usequantumgate").is(":checked")) {
            StorageModif_New = 0;
        }
        var StorageModif_Dif = StorageModif_New - StorageModif_Old;
        if (StorageModif_Dif !== 0) {
            var FreeStorage = parseInt($("#FreeStorage").val(), 10) + StorageModif_Dif;
            $("#FreeStorage").data("StorageModif_Old", StorageModif_New);
            Changed = true;
        }

        if (Changed) {
            $("#FreeStorage").val(FreeStorage).setStorageShow(FreeStorage);
            QuantumGateOptionModif = true;
            $("[name=resource3]").change();
            if (LastStorageLowerTh0) {
                LastStorageLowerTh0 = false;
                var ThisResID = "1";
                var NextResID = "2";
                if (ResSortArrayNoDeu[0] == "met") {
                    ThisResID = "2";
                    NextResID = "1";
                }
                $("[name=resource" + ThisResID + "]").change();
                if (LastStorageLowerTh0) {
                    LastStorageLowerTh0 = false;
                    $("[name=resource" + NextResID + "]").change();
                    LastStorageLowerTh0 = false;
                }
            }
            QuantumGateOptionModif = false;
        }

        if (!$("#usequantumgate").is(":checked")) {
            FlyTimeBackModif = 0;
            FlyTimeTargetModif = 0;
        }

        if (FlyTimeTargetModif == 1) {
            FlightDurationTarget = 1;
        } else {
            FlightDurationTarget = FlightDuration;
        }
        if (FlyTimeBackModif == 1) {
            FlightDurationGoback = 1;
        } else {
            FlightDurationGoback = FlightDuration;
        }

        const flightTimes = [];

        const flightToTargetDuration = uniengine.common.prettyTime({
            seconds: FlightDurationTarget,
            isDayConversionDisabled: true,
        });

        flightTimes.push(flightToTargetDuration);

        if (FlyTimeTargetModif == 1 && FlyTimeBackModif == 0) {
            const flightBackToOriginDuration = uniengine.common.prettyTime({
                seconds: FlightDurationGoback,
                isDayConversionDisabled: true,
            });

            flightTimes.push(flightBackToOriginDuration);

            $(".flyTimeInfo").show();
            $(".flyTimeNoInfo").hide();
        } else {
            $(".flyTimeInfo").hide();
            $(".flyTimeNoInfo").show();
        }
        $("#FlightTimeShow").html(flightTimes.join("<br/>"));
    });
    $("#usequantumgate").click(function () {
        if (NeedQuantumGate == "1") {
            if ($(this).is(":checked")) {
                $("#noDeutInfo").hide();
            } else {
                $("#noDeutInfo").show();
            }
        }
        $(".mSelect").change();
    });

    if (NeedQuantumGate != "1" || (NeedQuantumGate == "1" && $("#usequantumgate").is(":checked") == true)) {
        $("#noDeutInfo").hide();
    }

    $(".flyTimeInfo").tipTip({delay: 0, maxWidth: 250, edgeOffset: 8, content: JSLang["fl2_FlyTimeInfo"]});
    $(".planet").tipTip({delay: 0, edgeOffset: 8, content: JSLang["fl_coordplanet"]});
    $(".moon").tipTip({delay: 0, edgeOffset: 8, content: JSLang["fl_coordmoon"]});
    $(".debris").tipTip({delay: 0, edgeOffset: 8, content: JSLang["fl_coorddebris"]});
    $(".flyTimeInfo").hide();
    $("th:not(.FBlock, .inv, .QuantumInfo), #gTb td").addClass("pad2");
    $("#InfoTable").find("th:not(.nfoInv)").addClass("nfoTH");
    $("#InfoTable").find("th.inv").addClass("nfoInv");

    $("#goBack").click(function () {
        $("#thisForm").attr("action", "fleet1.php").prepend("<input type=\"hidden\" name=\"gobackUsed\" value=\"1\"/>").submit();
    });

    $("#thisForm").submit(function () {
        if ($("[name=\"gobackUsed\"]").length <= 0) {
            if (AllyPact_AttackWarn === true) {
                var ThisMission = $("[name=\"mission\"]:checked").val();
                if (ThisMission == 1 || ThisMission == 2 || ThisMission == 9 || ThisMission == 10) {
                    return confirm(JSLang["confirm_allypact_attack"]);
                }
            }
        }
    });

    if (SetResources === false) {
        if ($("#quickres").val() == "1") {
            $("#setMaxAll").click();
        }
    } else {
        $("[name=\"resource1\"]").val(SetResources["resource1"]).change();
        $("[name=\"resource2\"]").val(SetResources["resource2"]).change();
        $("[name=\"resource3\"]").val(SetResources["resource3"]).change();
    }

    if (SelectQuantumGate === true) {
        $("#usequantumgate").click();
        $(".mSelect").change();
    }
});
