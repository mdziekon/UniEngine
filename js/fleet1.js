/* globals AllowCreateTimeCounters, ServerClientDifference, maxIs, JSLang */

$(document).ready(function () {
    var FlightDuration;

    // Internal Functions
    $.fn.notEmptyVal = function (canBeZero) {
        if (canBeZero) {
            if ($(this).val() != "" && $(this).val() >= 0) {
                return true;
            } else {
                return false;
            }
        } else {
            if ($(this).val() != "" && $(this).val() > 0) {
                return true;
            } else {
                return false;
            }
        }
    };
    $.fn.notEmptyData = function (key) {
        if ($(this).data(key) != "" && $(this).data(key) > 0) {
            return true;
        } else {
            return false;
        }
    };
    function addDots (Value) {
        Value += "";
        var rgx = /(\d+)(\d\d\d)/;
        while (rgx.test(Value)) {
            Value = Value.replace(rgx, "$1" + "." + "$2");
        }
        return Value;
    }

    // Fleet Functions
    function setTarget (galaxy, system, planet, type) {
        $("#galaxy_selector").val(galaxy);
        $("#system_selector").val(system);
        $("#select_planet").val(planet);
        $("#type_selector").val(type);
    }

    function distance () {
        var thisGalaxy = parseInt($("#ThisGalaxy").val(), 10);
        var thisSystem = parseInt($("#ThisSystem").val(), 10);
        var thisPlanet = parseInt($("#ThisPlanet").val(), 10);
        var targetGalaxy = parseInt($("#galaxy_selector").val(), 10);
        var targetSystem = parseInt($("#system_selector").val(), 10);
        var targetPlanet = parseInt($("#select_planet").val(), 10);
        var dist = 0;

        if ((targetGalaxy - thisGalaxy) !== 0) {
            dist = Math.abs(targetGalaxy - thisGalaxy) * 20000;
        } else if ((targetSystem - thisSystem) !== 0) {
            dist = Math.abs(targetSystem - thisSystem) * 95 + 2700;
        } else if ((targetPlanet - thisPlanet) !== 0) {
            dist = Math.abs(targetPlanet - thisPlanet) * 5 + 1000;
        } else {
            dist = 5;
        }
        return dist;
    }

    function duration () {
        var ret = Math.round((35000 / speed() * Math.sqrt(distance() * 10 / maxspeed()) + 10) / speedFactor());
        FlightDuration = ret;
        if (AllowCreateTimeCounters === true) {
            createTimeCounters();
        }
        return ret;
    }

    function maxspeed () {
        return parseInt($("#MaxSpeed").val(), 10);
    }

    function speed () {
        return parseFloat($(".setSpeed_Current").attr("data-speed"));
    }

    function speedFactor () {
        return parseInt($("#SpeedFactor").val(), 10);
    }

    function consumption () {
        var calc_consumption = 0;
        var basicConsumption = 0;
        var i;
        // var sp = speed();
        var dist = distance();
        var dur = duration();
        var speedfactor = speedFactor();
        for (i = 200; i < 300; i += 1) {
            var ThisConsumption = $("#consumption" + i).val();
            if (ThisConsumption !== undefined && ThisConsumption !== "") {
                var spd = 35000 / (dur * speedfactor - 10) * Math.sqrt(dist * 10 / parseInt($("#speed" + i).val(), 10));
                basicConsumption = parseInt(ThisConsumption, 10);
                calc_consumption += basicConsumption * dist / 35000 * ((spd / 10) + 1) * ((spd / 10) + 1);
            }
        }
        calc_consumption = Math.round(calc_consumption) + 1;
        return calc_consumption;
    }

    function storage () {
        return (parseInt($("#Storage").val(), 10) - consumption());
    }

    function GetFuelStorage () {
        return parseInt($("#FuelStorage").val(), 10);
    }

    function planetDeuterium () {
        return parseInt($("#PlanetDeuterium").val(), 10);
    }

    function shortInfo () {
        $("#distance").html(addDots(distance()));
        var seconds = duration();
        var hours = Math.floor(seconds / 3600);
        seconds -= hours * 3600;
        var minutes = Math.floor(seconds / 60);
        seconds -= minutes * 60;
        if (minutes < 10) {
            minutes = "0" + minutes;
        }
        if (seconds < 10) {
            seconds = "0" + seconds;
        }
        if (hours < 10) {
            hours = "0" + hours;
        }
        $("#duration").html(hours + ":" + minutes + ":" + seconds + " h");
        var stor = storage();
        var cons = consumption();
        var FuelStorage = GetFuelStorage();
        if (FuelStorage >= cons) {
            stor += cons;
        } else {
            stor += FuelStorage;
        }
        var deuterium = planetDeuterium();
        var setHTMLCons = "";
        var setHTMLStor = "";
        if (stor >= 0) {
            if (deuterium >= cons) {
                setHTMLCons = "<b class=\"lime\">" + addDots(cons) + "</b>";
            } else {
                setHTMLCons = "<b class=\"red\">" + addDots(cons) + "</b>";
            }
            setHTMLStor = "<b class=\"lime\">" + addDots(stor) + "</b>";
        } else {
            if (deuterium >= cons) {
                setHTMLCons = "<b class=\"orange\">" + addDots(cons) + "</b>";
            } else {
                setHTMLCons = "<b class=\"red\">" + addDots(cons) + "</b>";
            }
            setHTMLStor = "<b class=\"red\">" + addDots(stor) + "</b>";
        }
        $("#consumption").html(setHTMLCons);
        $("#storageShow").html(setHTMLStor);
    }

    function createTimeCounters () {
        var CurrentTime = new Date().getTime() + ServerClientDifference;
        var DateObj = new Date(CurrentTime);
        var CTimeCounter = new Date(DateObj.getTime());
        var TimeCounter = new Date((DateObj.getTime() + (FlightDuration * 1000)));
        var TimeCounter2 = new Date((DateObj.getTime() + ((FlightDuration * 2) * 1000)));
        var CYears = ((CTimeCounter.getFullYear()).toString()).substr(2, 2);
        var CMonths = CTimeCounter.getMonth() + 1;
        var CDays = CTimeCounter.getDate();
        var CHours = CTimeCounter.getHours();
        var CMins = CTimeCounter.getMinutes();
        var CSecs = CTimeCounter.getSeconds();
        if (CYears < 10) {
            if (CYears === 0) {
                CYears = "00";
            } else {
                CYears = "0" + CYears;
            }
        }
        if (CMonths < 10) {
            CMonths = "0" + CMonths;
        }
        if (CDays < 10) {
            CDays = "0" + CDays;
        }
        if (CHours < 10) {
            if (CHours === 0) {
                CHours = "00";
            } else {
                CHours = "0" + CHours;
            }
        }
        if (CMins < 10) {
            if (CMins === 0) {
                CMins = "00";
            } else {
                CMins = "0" + CMins;
            }
        }
        if (CSecs < 10) {
            if (CSecs === 0) {
                CSecs = "00";
            } else {
                CSecs = "0" + CSecs;
            }
        }
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
        $("#curr_time").html(CHours + ":" + CMins + ":" + CSecs + " - " + CDays + "." + CMonths + "." + CYears);
        $("#reach_time").html(Hours + ":" + Mins + ":" + Secs + " - " + Days + "." + Months + "." + Years);
        $("#comeback_time").html(Hours2 + ":" + Mins2 + ":" + Secs2 + " - " + Days2 + "." + Months2 + "." + Years2);
    }

    setInterval(createTimeCounters, 250);

    // Rest of Scripts
    $("th:not(.FBlock, .inv), .updateInfo").addClass("pad2");

    $(".updateInfo:not(.fastLink, select, .setSpeed)")
        .keyup(function () {
            if ($(this).notEmptyVal(true)) {
                var TName = $(this).attr("name");
                if (TName == "galaxy" || TName == "system" || TName == "planet") {
                    if ($(this).val() < 1) {
                        $(this).val(1);
                    } else if ($(this).val() > maxIs[TName]) {
                        $(this).val(maxIs[TName]);
                    }
                }
                shortInfo();
            }
        })
        .change(function () {
            $(this).keyup();
        })
        .focus(function () {
            if ($(this).notEmptyVal()) {
                $(this).data("last_val", $(this).val());
                $(this).val("");
            }
        })
        .blur(function () {
            if (!$(this).notEmptyVal()) {
                if ($(this).notEmptyData("last_val")) {
                    $(this).val($(this).data("last_val"));
                    $(this).data("last_val", "");
                }
            }
        });
    $("select.updateInfo:not(.fastLink)")
        .keyup(function () {
            shortInfo();
        })
        .change(function () {
            $(this).keyup();
        });

    $(".setSpeed")
        .click(function () {
            $("input[name=\"speed\"]").val($(this).attr("data-speed"));
            $(".setSpeed_Selected").removeClass("setSpeed_Selected");
            $(this).addClass("setSpeed_Selected");
            shortInfo();
            return false;
        })
        .hover(
            function () {
                if ($("input[name=\"galaxy\"]").val() == "" || $("input[name=\"system\"]").val() == "" || $("input[name=\"planet\"]").val() == "") {
                    return;
                }
                $(".setSpeed_Current").removeClass("setSpeed_Current");
                $(this).addClass("setSpeed_Current");
                shortInfo();
            },
            function () {
                if ($("input[name=\"galaxy\"]").val() == "" || $("input[name=\"system\"]").val() == "" || $("input[name=\"planet\"]").val() == "") {
                    return;
                }
                $(this).removeClass("setSpeed_Current");
                $(".setSpeed_Selected").addClass("setSpeed_Current");
                shortInfo();
            });

    $(".fastLink")
        .keyup(function () {
            if ($(this).val() !== "-") {
                var coordinates = $(this).val().split(",");
                setTarget(coordinates[0], coordinates[1], coordinates[2], coordinates[3]);
                shortInfo();
            }
            var GetIDNum = $(this).attr("id").substr(6);
            if (GetIDNum == "1") {
                GetIDNum = 2;
            } else {
                GetIDNum = 1;
            }
            $("#fl_sel" + GetIDNum).val("-").attr("selected", true);
        })
        .change(function () {
            $(this).keyup();
        });

    $("[name=galaxy]").tipTip({delay: 0, edgeOffset: 8, content: JSLang["fl1_targetGalaxy"]});
    $("[name=system]").tipTip({delay: 0, edgeOffset: 8, content: JSLang["fl1_targetSystem"]});
    $("[name=planet]").tipTip({delay: 0, edgeOffset: 8, content: JSLang["fl1_targetPlanet"]});

    $("#goBack").click(function () {
        $("#thisForm").attr("action", "fleet.php").prepend("<input type=\"hidden\" name=\"gobackUsed\" value=\"1\"/>").submit();
    });

    $("#thisForm").submit(function () {
        if (!$("#galaxy_selector").notEmptyVal()) {
            if ($("#galaxy_selector").notEmptyData("last_val")) {
                $("#galaxy_selector").val($("#galaxy_selector").data("last_val"));
                $("#galaxy_selector").data("last_val", "");
            }
        }
        if (!$("#system_selector").notEmptyVal()) {
            if ($("#system_selector").notEmptyData("last_val")) {
                $("#system_selector").val($("#system_selector").data("last_val"));
                $("#system_selector").data("last_val", "");
            }
        }
        if (!$("#select_planet").notEmptyVal()) {
            if ($("#select_planet").notEmptyData("last_val")) {
                $("#select_planet").val($("#select_planet").data("last_val"));
                $("#select_planet").data("last_val", "");
            }
        }
    });

    shortInfo();
});
