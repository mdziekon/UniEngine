/* globals libCommon, AllowCreateTimeCounters, maxIs, JSLang, shipsDetails, uniengine */

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

    function getPlanetDeuterium () {
        return parseInt($("#PlanetDeuterium").val(), 10);
    }

    function updateFlightDetails () {
        const durationPrettyTime = uniengine.common.prettyTime({
            seconds: duration(),
            isDayConversionDisabled: true,
        });

        const fleetResourcesStorage = storage();
        const fuelConsumption = consumption();
        const fleetFuelStorage = GetFuelStorage();

        const fleetTotalStorage = fleetResourcesStorage + (
            (fleetFuelStorage >= fuelConsumption) ?
                fuelConsumption :
                fleetFuelStorage
        );
        const availableFuel = getPlanetDeuterium();

        const storageValueColor = (
            (fleetTotalStorage >= 0) ?
                "lime" :
                "red"
        );
        const consumptionValueColor = (
            (availableFuel < fuelConsumption) ?
                "red" :
                (
                    (fleetTotalStorage >= 0) ?
                        "lime" :
                        "orange"
                )
        );
        const storageHTML = `<b class="${storageValueColor}">${libCommon.format.addDots(fleetTotalStorage)}</b>`;
        const consumptionHTML = `<b class="${consumptionValueColor}">${libCommon.format.addDots(fuelConsumption)}</b>`;

        $("#duration").html(`${durationPrettyTime} h`);
        $("#distance").html(libCommon.format.addDots(distance()));
        $("#storageShow").html(storageHTML);
        $("#consumption").html(consumptionHTML);
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
            const $element = $(this);

            if (!$element.isNonEmptyValue({ isZeroAllowed: true })) {
                return;
            }

            const inputName = $element.attr("name");
            const inputValue = $element.val();

            if (
                inputName == "galaxy" ||
                inputName == "system" ||
                inputName == "planet"
            ) {
                if (inputValue < 1) {
                    $element.val(1);
                }
                if (inputValue > maxIs[inputName]) {
                    $element.val(maxIs[inputName]);
                }
            }
            updateFlightDetails();
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
            updateFlightDetails();
        })
        .change(function () {
            $(this).keyup();
        });

    $(".setSpeed")
        .click(function () {
            $("input[name=\"speed\"]").val($(this).attr("data-speed"));
            $(".setSpeed_Selected").removeClass("setSpeed_Selected");
            $(this).addClass("setSpeed_Selected");
            updateFlightDetails();
            return false;
        })
        .hover(
            function () {
                if ($("input[name=\"galaxy\"]").val() == "" || $("input[name=\"system\"]").val() == "" || $("input[name=\"planet\"]").val() == "") {
                    return;
                }
                $(".setSpeed_Current").removeClass("setSpeed_Current");
                $(this).addClass("setSpeed_Current");
                updateFlightDetails();
            },
            function () {
                if ($("input[name=\"galaxy\"]").val() == "" || $("input[name=\"system\"]").val() == "" || $("input[name=\"planet\"]").val() == "") {
                    return;
                }
                $(this).removeClass("setSpeed_Current");
                $(".setSpeed_Selected").addClass("setSpeed_Current");
                updateFlightDetails();
            });

    $(".fastLink")
        .keyup(function () {
            if ($(this).val() !== "-") {
                var coordinates = $(this).val().split(",");
                setTarget(coordinates[0], coordinates[1], coordinates[2], coordinates[3]);
                updateFlightDetails();
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

    updateFlightDetails();
});
