/* globals AllowPrettyInputBox, ServerClientDifference, JSLang, AllyPact_AttackWarn, SetResources, SelectQuantumGate, NeedQuantumGate, ResSortArrayAll, QuantumGateDeuteriumUse, ResSortArrayNoDeu, FlightDuration */

var SetMaxNow = false;
var LastStorageLowerTh0 = false;
var QuantumGateOptionModif = false;

$(document).ready(function () {
    var FlightDurationTarget = FlightDuration;
    var FlightDurationGoback = FlightDuration;

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
    $.fn.notEmptyData = function (key, canBeZero, doRemoveNonDigit) {
        var ThisVal = $(this).data(key);
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

    // Rest of scripts...

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
        var CurrentTime = new Date().getTime() + ServerClientDifference;
        var DateObj = new Date(CurrentTime);
        var TimeCounter = new Date((DateObj.getTime() + (FlightDurationTarget * 1000)));
        var TimeCounter2 = new Date((DateObj.getTime() + ((FlightDurationTarget + FlightDurationGoback) * 1000)));
        var Years = ((TimeCounter.getFullYear()).toString()).substr(2, 2);
        var Months = TimeCounter.getMonth() + 1;
        var Days = TimeCounter.getDate();
        var Hours = TimeCounter.getHours();
        var Mins = TimeCounter.getMinutes();
        var Secs = TimeCounter.getSeconds();
        if (Years < 10) {
            if (Years === 0) {
                Years = "00";
            } else {
                Years = "0" + Years;
            }
        }
        if (Months < 10) {
            Months = "0" + Months;
        }
        if (Days < 10) {
            Days = "0" + Days;
        }
        if (Hours < 10) {
            if (Hours === 0) {
                Hours = "00";
            } else {
                Hours = "0" + Hours;
            }
        }
        if (Mins < 10) {
            if (Mins === 0) {
                Mins = "00";
            } else {
                Mins = "0" + Mins;
            }
        }
        if (Secs < 10) {
            if (Secs === 0) {
                Secs = "00";
            } else {
                Secs = "0" + Secs;
            }
        }
        var Years2 = ((TimeCounter2.getFullYear()).toString()).substr(2, 2);
        var Months2 = TimeCounter2.getMonth() + 1;
        var Days2 = TimeCounter2.getDate();
        var Hours2 = TimeCounter2.getHours();
        var Mins2 = TimeCounter2.getMinutes();
        var Secs2 = TimeCounter2.getSeconds();
        if (Years2 < 10) {
            if (Years2 === 0) {
                Years2 = "00";
            } else {
                Years2 = "0" + Years2;
            }
        }
        if (Months2 < 10) {
            Months2 = "0" + Months2;
        }
        if (Days2 < 10) {
            Days2 = "0" + Days2;
        }
        if (Hours2 < 10) {
            if (Hours2 === 0) {
                Hours2 = "00";
            } else {
                Hours2 = "0" + Hours2;
            }
        }
        if (Mins2 < 10) {
            if (Mins2 === 0) {
                Mins2 = "00";
            } else {
                Mins2 = "0" + Mins2;
            }
        }
        if (Secs2 < 10) {
            if (Secs2 === 0) {
                Secs2 = "00";
            } else {
                Secs2 = "0" + Secs2;
            }
        }
        $("#ReachTime").html(Hours + ":" + Mins + ":" + Secs + " - " + Days + "." + Months + "." + Years);
        $("#BackTime").html(Hours2 + ":" + Mins2 + ":" + Secs2 + " - " + Days2 + "." + Months2 + "." + Years2);
    }

    setInterval(createTimeCounters, 250);

    $("[id^=setMax]:not(#setMaxAll)").click(function () {
        SetMaxNow = true;
        var ElSelector = "[name=\"resource" + $(this).attr("id").substr(6) + "\"]";
        $(ElSelector).val($(ElSelector).val() + 1).change();
        SetMaxNow = false;

        return false;
    });
    $("[id^=setZero]:not(#setZeroAll)").click(function () {
        var ElSelector = "[name=\"resource" + $(this).attr("id").substr(7) + "\"]";
        $(ElSelector).val("0").change();

        return false;
    });

    $("#setMaxAll").click(function () {
        for (var Index in ResSortArrayAll) {
            $("#setMax" + ResSortArrayAll[Index]).click();
        }
    });
    $("#setZeroAll").click(function () {
        for (var Index in ResSortArrayAll) {
            $("#setZero" + ResSortArrayAll[Index]).click();
        }
    });

    $("[name^=\"resource\"]").change(function () {
        var ThisID          = $(this).attr("name").substr(8);

        var LastValue       = $(this).data("lastVal");
        var CurrentValue    = parseInt(removeNonDigit($(this).val()), 10);
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
            if ($(this).val() != removeNonDigit($(this).val())) {
                $(this).val(CurrentValue).prettyInputBox();
            }
        }
    })
        .keyup(function () {
            $(this).change();
        }).focus(function () {
            if (!$(this).notEmptyVal(false, true)) {
                $(this).val("");
            }
        }).blur(function () {
            if (!$(this).notEmptyVal(true, true)) {
                $(this).val("0");
            }
        });

    $(".mSelect").change(function () {
        var NewStorageModifier  = QuantumGateDeuteriumUse[$(".mSelect:checked").val()];
        var ConsuptionModif_New = 0;
        var StorageModif_New    = 0;

        var FlyTimeTargetModif;
        var FlyTimeBackModif;

        if (NewStorageModifier !== undefined) {
            switch (NewStorageModifier) {
            case 0:
                StorageModif_New = 0;
                ConsuptionModif_New = 0;
                FlyTimeTargetModif = 0;
                FlyTimeBackModif   = 0;
                break;
            case 1:
                StorageModif_New = parseInt($("#Consumption").val(), 10);
                if (isNaN(StorageModif_New)) {
                    StorageModif_New = 0;
                } else {
                    StorageModif_New /= 2;
                    StorageModif_New = Math.ceil(StorageModif_New);
                }
                ConsuptionModif_New = StorageModif_New;
                StorageModif_New    -= parseInt($("#FuelStorageReduceH").val(), 10);
                FlyTimeTargetModif  = 1;
                FlyTimeBackModif    = 0;
                break;
            case 2:
                StorageModif_New = parseInt($("#Consumption").val(), 10);
                if (isNaN(StorageModif_New)) {
                    StorageModif_New = 0;
                }
                ConsuptionModif_New = StorageModif_New;
                StorageModif_New    -= parseInt($("#FuelStorageReduce").val(), 10);
                FlyTimeTargetModif  = 1;
                FlyTimeBackModif    = 1;
                break;
            default:
                StorageModif_New = 0;
                ConsuptionModif_New = 0;
                FlyTimeTargetModif = 0;
                FlyTimeBackModif   = 0;
                break;
            }

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
                $("#FuelUse").html(addDots(ConsuptionVar_Now));
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

            var FlightTimeSecs = FlightDurationTarget;
            var FlightTimeHour = Math.floor(FlightTimeSecs / 3600);
            FlightTimeSecs -= FlightTimeHour * 3600;
            var FlightTimeMins = Math.floor(FlightTimeSecs / 60);
            FlightTimeSecs -= FlightTimeMins * 60;
            if (FlightTimeMins < 10) {
                FlightTimeMins = "0" + FlightTimeMins;
            }
            if (FlightTimeSecs < 10) {
                FlightTimeSecs = "0" + FlightTimeSecs;
            }
            if (FlightTimeHour < 10) {
                FlightTimeHour = "0" + FlightTimeHour;
            }
            var SetFlightTimeShow = FlightTimeHour + ":" + FlightTimeMins + ":" + FlightTimeSecs;
            if (FlyTimeTargetModif == 1 && FlyTimeBackModif == 0) {
                FlightTimeSecs = FlightDurationGoback;
                FlightTimeHour = Math.floor(FlightTimeSecs / 3600);
                FlightTimeSecs -= FlightTimeHour * 3600;
                FlightTimeMins = Math.floor(FlightTimeSecs / 60);
                FlightTimeSecs -= FlightTimeMins * 60;
                if (FlightTimeMins < 10) {
                    FlightTimeMins = "0" + FlightTimeMins;
                }
                if (FlightTimeSecs < 10) {
                    FlightTimeSecs = "0" + FlightTimeSecs;
                }
                if (FlightTimeHour < 10) {
                    FlightTimeHour = "0" + FlightTimeHour;
                }
                SetFlightTimeShow += " h<br/>" + FlightTimeHour + ":" + FlightTimeMins + ":" + FlightTimeSecs;
                $(".flyTimeInfo").show();
                $(".flyTimeNoInfo").hide();
            } else {
                $(".flyTimeInfo").hide();
                $(".flyTimeNoInfo").show();
            }
            $("#FlightTimeShow").html(SetFlightTimeShow);
        }
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
