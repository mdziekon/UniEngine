/* exported ReplaceArr, ArrayReplace, buildResourceUpdaterCache, updateResourceCounters */

var MToolTip = "";
var CToolTip = "";
var DToolTip = "";
var EToolTip = "";
var constants = {
    colorsValues: {
        red: "#ff0000",
        green: "#00ff00"
    }
};

var ReplaceArr = new Array("_ResName_", "_ResIncome_", "_ResFullTime_", "_ResStoreStatus_");
var ResTipStyle = {classes: "tiptip_content ui-tooltip-tipsy ui-tooltip-shadow"};
var qTipSettings = {show: {delay: 0, effect: false}, hide: {delay: 0, effect: false}, style: ResTipStyle};

function number_format (value) {
    value += "";
    var rgx = /(\d+)(\d\d\d)/;
    while (rgx.test(value)) {
        value = value.replace(rgx, "$1" + "." + "$2");
    }
    return value;
}

//  Arguments:
//      - params (object)
//          - resources (array)
//              - resourceKey (string)
//              - storage (object)
//                  - maxCapacity (number)
//                  - overflowCapacity (number)
//              - state (object)
//                  - initial (number)
//                  - incomePerHour (number)
//
//  Returns: object
//      - previousElapsedTime (number)
//      - resources (object<resourceKey: string, details: object>)
//          - hasReachedRealMaxCapacity (boolean)
//
function buildResourceUpdaterCache (params) {
    const cache = {
        previousElapsedTime: -Infinity,
        resources: {}
    };

    params.resources.forEach((resourceDetails) => {
        cache.resources[resourceDetails.resourceKey] = {
            hasReachedRealMaxCapacity: false
        };
    });

    return cache;
}

//  Arguments:
//      - params (object)
//          - $parentEl (jQuery object)
//          - timestamps (object)
//              Unix timestamps as returned by JS's Date object.
//              - initial (number) [unit: miliseconds]
//              - current (number) [unit: miliseconds]
//          - resources (array)
//              - resourceKey (string)
//              - storage (object)
//                  - maxCapacity (number)
//                  - overflowCapacity (number)
//              - state (object)
//                  - initial (number)
//                  - incomePerHour (number)
//      - cache (object)
//          Same as returned by buildResourceUpdaterCache()
//
function updateResourceCounters (params, cache) {
    const $parentEl = params.$parentEl;

    const elapsedTime = Math.floor((params.timestamps.current - params.timestamps.initial) / 1000);

    // Opt: update only on full tick (each second)
    if (elapsedTime <= cache.previousElapsedTime) {
        return;
    }

    params.resources.forEach((resourceDetails) => {
        const resourceKey = resourceDetails.resourceKey;

        // Opt: prevent further calculations if store is already full
        if (cache.resources[resourceKey].hasReachedRealMaxCapacity) {
            return;
        }

        const $resourceEl = $parentEl.find(`[data-resource-key="${resourceKey}"]`);

        const selectors = {
            $resourceAmount: $resourceEl.find(".amount_display"),
            $resourceStorage: $resourceEl.find(".storage_display")
        };

        const resourceState = _calculateResourceState({
            elapsedTime,
            resourceDetails
        });

        _updateResourceCounterDOM(selectors, resourceState);

        // Update per-resource cache
        cache.resources[resourceKey].hasReachedRealMaxCapacity = resourceState.hasReachedRealMaxCapacity;
    });

    // Update cache
    cache.previousElapsedTime = elapsedTime;
}

//  Arguments:
//      - params (object)
//          - elapsedTime (number) [unit: seconds]
//              Time elapsed since the initial run.
//          - resourceDetails (object)
//              - storage (object)
//                  - maxCapacity (number)
//                  - overflowCapacity (number)
//              - state (object)
//                  - initial (number)
//                  - incomePerHour (number)
//
//  Returns: object
//      - currentResourceAmount (number)
//      - hasReachedStorageMaxCapacity (boolean)
//      - hasReachedRealMaxCapacity (boolean)
//
function _calculateResourceState (params) {
    const maxPracticalStorage = Math.max(
        params.resourceDetails.storage.maxCapacity,
        params.resourceDetails.storage.overflowCapacity
    );

    const theoreticalIncome = (
        (params.resourceDetails.state.incomePerHour / 3600) *
        params.elapsedTime
    );
    const theoreticalResourceAmount = (
        params.resourceDetails.state.initial +
        theoreticalIncome
    );

    const finalResourceAmount = Math.min(
        maxPracticalStorage,
        theoreticalResourceAmount
    );

    const hasReachedStorageMaxCapacity = (finalResourceAmount >= params.resourceDetails.storage.maxCapacity);
    const hasReachedRealMaxCapacity = (finalResourceAmount >= maxPracticalStorage);

    return {
        currentResourceAmount: finalResourceAmount,
        hasReachedStorageMaxCapacity,
        hasReachedRealMaxCapacity
    };
}

//  Arguments:
//      - selectors (object)
//          - $resourceAmount
//          - $resourceStorage
//      - resourceState (object)
//          - currentResourceAmount (number)
//          - hasReachedStorageMaxCapacity (boolean)
//
function _updateResourceCounterDOM (selectors, resourceState) {
    const resourceDisplayValue = number_format(Math.floor(resourceState.currentResourceAmount));
    const resourceDisplayColor = (
        resourceState.hasReachedStorageMaxCapacity ?
            constants.colorsValues.red :
            constants.colorsValues.green
    );

    selectors.$resourceAmount.html(resourceDisplayValue);
    selectors.$resourceAmount.css("color", resourceDisplayColor);
    selectors.$resourceStorage.css("color", resourceDisplayColor);
}

function ArrayReplace (Org, Search, Replace) {
    var SearchLen = Search.length;
    for (var i = 0; i < SearchLen; i += 1) {
        Org = Org.replace(Search[i], Replace[i]);
    }
    return Org;
}

$(document).ready(function () {
    var ResElements = {
        "met": $("#resMet"),
        "cry": $("#resCry"),
        "deu": $("#resDeu"),
        "eng": $("#resEng")
    };
    var PlanetList = $("#planet");

    if ($("#plType").is(":visible")) {
        $(".plBut").width((PlanetList.width() / 2) - $("#plType").width() + 2);
    } else {
        $(".plBut").width((PlanetList.width() / 2) + 1);
    }

    // PlanetList Handler
    $("#prevPl").click(function () {
        if (PlanetList.children().length > 1) {
            if (PlanetList.children(":first-child").is(":selected")) {
                PlanetList.children(":last-child").attr("selected", true);
            } else {
                PlanetList.children(":selected").prev().attr("selected", true);
            }
            PlanetList.change();
        }
    });
    $("#nextPl").click(function () {
        if (PlanetList.children().length > 1) {
            if (PlanetList.children(":last-child").is(":selected")) {
                PlanetList.children(":first-child").attr("selected", true);
            } else {
                PlanetList.children(":selected").next().attr("selected", true);
            }
            PlanetList.change();
        }
    });
    PlanetList.change(function () {
        window.location = String($(this).val());
    });
    $("#plType").click(function () {
        PlanetList.val($("option[value*=\"cp=" + $(this).attr("data-id") + "\"]", PlanetList).val()).change();
    });

    // qTips
    ResElements["met"].qtip(
        $.extend(
            {
                content: MToolTip,
                position: {
                    target: $("#metalmax"),
                    my: "top center",
                    at: "bottom center",
                    adjust: { y: 10 }
                }
            },
            qTipSettings
        )
    );
    $(".resMet:not(#resMet)").hover(function () {
        ResElements["met"].mouseover();
    }, function () {
        ResElements["met"].mouseout();
    });

    ResElements["cry"].qtip(
        $.extend(
            {
                content: CToolTip,
                position: {
                    target: $("#crystalmax"),
                    my: "top center",
                    at: "bottom center",
                    adjust: { y: 10 }
                }
            },
            qTipSettings
        )
    );
    $(".resCry:not(#resCry)").hover(function () {
        ResElements["cry"].mouseover();
    }, function () {
        ResElements["cry"].mouseout();
    });

    ResElements["deu"].qtip(
        $.extend(
            {
                content: DToolTip,
                position: {
                    target: $("#deuteriummax"),
                    my: "top center",
                    at: "bottom center",
                    adjust: { y: 10 }
                }
            },
            qTipSettings
        )
    );
    $(".resDeu:not(#resDeu)").hover(function () {
        ResElements["deu"].mouseover();
    }, function () {
        ResElements["deu"].mouseout();
    });

    ResElements["eng"].qtip(
        $.extend(
            {
                content: EToolTip,
                position: {
                    target: $("#showET"),
                    my: "top center",
                    at: "bottom center",
                    adjust: { y: 10 }
                }
            },
            qTipSettings
        )
    );
    $(".resEnr:not(#resEng)").hover(function () {
        ResElements["eng"].mouseover();
    }, function () {
        ResElements["eng"].mouseout();
    });
});
