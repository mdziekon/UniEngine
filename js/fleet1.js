/* globals libCommon, AllowCreateTimeCounters, maxIs, JSLang, shipsDetails */

$(document).ready(function () {
    libCommon.init.setupJQuery();

    var FlightDuration;

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
        const flightDistance = distance();
        const flightDuration = duration();
        const uniSpeedFactor = speedFactor();

        const totalConsumption = Object
            .entries(shipsDetails)
            .reduce(
                (accumulator, [ _shipId, shipDetails ]) => {
                    const allShipsBaseConsumption = parseInt(shipDetails.totalConsumptionOfShipType, 10);
                    const shipSpeed = parseInt(shipDetails.speed, 10);

                    const finalSpeed = 35000 / (flightDuration * uniSpeedFactor - 10) * Math.sqrt(flightDistance * 10 / shipSpeed);
                    const allShipsConsumption = allShipsBaseConsumption * flightDistance / 35000 * ((finalSpeed / 10) + 1) * ((finalSpeed / 10) + 1);

                    return (accumulator + allShipsConsumption);
                },
                0
            );

        return Math.round(totalConsumption) + 1;
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
        $("#distance").html(libCommon.format.addDots(distance()));
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
                setHTMLCons = "<b class=\"lime\">" + libCommon.format.addDots(cons) + "</b>";
            } else {
                setHTMLCons = "<b class=\"red\">" + libCommon.format.addDots(cons) + "</b>";
            }
            setHTMLStor = "<b class=\"lime\">" + libCommon.format.addDots(stor) + "</b>";
        } else {
            if (deuterium >= cons) {
                setHTMLCons = "<b class=\"orange\">" + libCommon.format.addDots(cons) + "</b>";
            } else {
                setHTMLCons = "<b class=\"red\">" + libCommon.format.addDots(cons) + "</b>";
            }
            setHTMLStor = "<b class=\"red\">" + libCommon.format.addDots(stor) + "</b>";
        }
        $("#consumption").html(setHTMLCons);
        $("#storageShow").html(setHTMLStor);
    }

    function createTimeCounters () {
        const currentTimeFormatted = libCommon.format.formatDateToFlightEvent(0);
        const reachTimeFormatted = libCommon.format.formatDateToFlightEvent((FlightDuration * 1000));
        const backTimeFormatted = libCommon.format.formatDateToFlightEvent((FlightDuration * 2 * 1000));

        $("#curr_time").html(currentTimeFormatted);
        $("#reach_time").html(reachTimeFormatted);
        $("#comeback_time").html(backTimeFormatted);
    }

    setInterval(createTimeCounters, 250);

    // Rest of Scripts
    $("th:not(.FBlock, .inv), .updateInfo").addClass("pad2");

    $(".updateInfo:not(.fastLink, select, .setSpeed)")
        .keyup(function () {
            if ($(this).isNonEmptyValue({ isZeroAllowed: true })) {
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
            if ($(this).isNonEmptyValue()) {
                $(this).data("last_val", $(this).val());
                $(this).val("");
            }
        })
        .blur(function () {
            if (!$(this).isNonEmptyValue()) {
                if ($(this).isNonEmptyDataSlot("last_val")) {
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
        if (!$("#galaxy_selector").isNonEmptyValue()) {
            if ($("#galaxy_selector").isNonEmptyDataSlot("last_val")) {
                $("#galaxy_selector").val($("#galaxy_selector").data("last_val"));
                $("#galaxy_selector").data("last_val", "");
            }
        }
        if (!$("#system_selector").isNonEmptyValue()) {
            if ($("#system_selector").isNonEmptyDataSlot("last_val")) {
                $("#system_selector").val($("#system_selector").data("last_val"));
                $("#system_selector").data("last_val", "");
            }
        }
        if (!$("#select_planet").isNonEmptyValue()) {
            if ($("#select_planet").isNonEmptyDataSlot("last_val")) {
                $("#select_planet").val($("#select_planet").data("last_val"));
                $("#select_planet").data("last_val", "");
            }
        }
    });

    shortInfo();
});
