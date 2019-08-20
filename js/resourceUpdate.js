/* exported buildResourceUpdaterCache, updateResourceCounters */
/* globals PHPInject_topnav_data, PHPInject_topnav_lang */

var constants = {
    colorsValues: {
        red: "#ff0000",
        green: "#00ff00"
    }
};

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
//          - hasDepletedStorage (boolean)
//          - hasNoProduction (boolean)
//
function buildResourceUpdaterCache (params) {
    const cache = {
        previousElapsedTime: -Infinity,
        resources: {}
    };

    params.resources.forEach((resourceDetails) => {
        cache.resources[resourceDetails.resourceKey] = {
            hasReachedRealMaxCapacity: false,
            hasDepletedStorage: false,
            hasNoProduction: false
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
        // Opt: prevent further calculations if the resource storage has been depleted
        if (cache.resources[resourceKey].hasDepletedStorage) {
            return;
        }
        // Opt: prevent further calculations if there is no resource production
        if (cache.resources[resourceKey].hasNoProduction) {
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
        cache.resources[resourceKey].hasDepletedStorage = resourceState.hasDepletedStorage;
        cache.resources[resourceKey].hasNoProduction = resourceState.hasNoProduction;
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
//      - hasDepletedStorage (boolean)
//      - hasNoProduction (boolean)
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

    const finalResourceAmount = Math.max(
        Math.min(
            Math.max(
                maxPracticalStorage,
                params.resourceDetails.state.initial
            ),
            theoreticalResourceAmount
        ),
        0
    );

    const hasReachedStorageMaxCapacity = (finalResourceAmount >= params.resourceDetails.storage.maxCapacity);
    const hasReachedRealMaxCapacity = (finalResourceAmount >= maxPracticalStorage);
    const hasDepletedStorage = (finalResourceAmount <= 0 && theoreticalIncome < 0);
    const hasNoProduction = (theoreticalIncome == 0);

    return {
        currentResourceAmount: finalResourceAmount,
        hasReachedStorageMaxCapacity,
        hasReachedRealMaxCapacity,
        hasDepletedStorage,
        hasNoProduction
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

function planetSelector_changeSelection ($selectorEl, selectionIdxModifier) {
    const $options = $selectorEl.find("option");
    const $selectedOption = $options.filter(":selected");

    const currentSelectedIdx = $options.index($selectedOption);

    const $changedSelection = $options.eq(currentSelectedIdx + selectionIdxModifier);

    $selectorEl.val($changedSelection.val());
}

function planetSelector_switchPlanetType ($selectorEl, otherPlanetID) {
    const $options = $selectorEl.find("option");

    const $changedSelection = $options.filter(`[data-planet-id="${otherPlanetID}"]`);

    $selectorEl.val($changedSelection.val());
}

function planetSelector_navigate ($selectorEl) {
    const currentSelectionURL = $selectorEl.val();

    window.location = currentSelectionURL;
}

class ResourceTooltip {
    constructor ({ resourceKey, $parentEl, values, bodyCreator }) {
        this.resourceKey = resourceKey;
        this.bodyCreator = bodyCreator;

        this.cache = {
            $parentEl,
            $elements: undefined,
            qTipAPI: undefined
        };

        this._initialise(values);
    }

    _initialise (values) {
        const bodyHTML = this._createTooltipBody(values);

        const $elements = this._getDOMElements();

        const $hookEl = $elements.$hookEl;
        const $targetEl = $elements.$tooltipTargetEl;
        const $triggerEls = $elements.$tooltipTriggerEls;

        const sharedSettings = {
            show: {
                delay: 0,
                effect: false
            },
            hide: {
                delay: 0,
                effect: false
            },
            style: {
                classes: "tiptip_content ui-tooltip-tipsy ui-tooltip-shadow"
            }
        };

        const tooltipOptions = $.extend(
            {},
            {
                content: bodyHTML,
                position: {
                    target: $targetEl,
                    my: "top center",
                    at: "bottom center",
                    adjust: { y: 10 }
                }
            },
            sharedSettings
        );

        $hookEl.qtip(tooltipOptions);

        $triggerEls.not($hookEl)
            .on("mouseenter", function () {
                $hookEl.trigger("mouseenter");
            })
            .on("mouseleave", function () {
                $hookEl.trigger("mouseleave");
            });

        this.cache.qTipAPI = $hookEl.qtip("api");
    }

    _findElements () {
        const resourceKey = this._getResourceKey();
        const $parentEl = this._getParentEl();

        const $resourceEls = $parentEl.find(`[data-resource-key="${resourceKey}"]`);
        const $hookEl = $resourceEls.filter(".tooltip-hook");
        const $tooltipTargetEl = $resourceEls.filter(".tooltip-target");
        const $tooltipTriggerEls = $resourceEls.filter(".tooltip-trigger");

        return {
            $hookEl,
            $tooltipTargetEl,
            $tooltipTriggerEls
        };
    }

    _getResourceKey () {
        return this.resourceKey;
    }

    _getParentEl () {
        return this.cache.$parentEl;
    }

    _getDOMElements () {
        if (!this.cache.$elements) {
            this.cache.$elements = this._findElements();
        }

        return this.cache.$elements;
    }

    _createTooltipBody (values) {
        return this.bodyCreator(values);
    }
}

function createProductionResourceTooltipBody (values) {
    const lang = PHPInject_topnav_lang;

    const bodyHTML = `
        <div class="center">
            <b>${values.resourceName}</b>
        </div>
        <div class="center">
            <b>(${values.incomePerHour} / h)</b>
        </div>
        <div>
            <div class="ResL">
                ${lang.When_full_store}
            </div>
            <div class="ResR">
                ${values.fullStoreInText}
            </div>
        </div>
        <div>
            <div class="ResL">
                ${lang.Store_Status}
            </div>
            <div class="ResR">
                ${values.storeStatusText}
            </div>
        </div>
    `;

    return bodyHTML;
}

function createEnergyResourceTooltipBody (values) {
    const bodyHTML = `
        <div class="center">
            <b>${values.resourceName}</b>
        </div>
        <div class="center">
            <b>${values.unused}</b>
        </div>
        <div class="center">
            (${values.used} / ${values.total})
        </div>
    `;

    return bodyHTML;
}

$(document).ready(function () {
    var PlanetList = $("#planet");

    if ($("#plType").is(":visible")) {
        $(".plBut").width((PlanetList.width() / 2) - $("#plType").width() + 2);
    } else {
        $(".plBut").width((PlanetList.width() / 2) + 1);
    }

    $("#prevPl").on("click", function () {
        planetSelector_changeSelection(PlanetList, -1);
        planetSelector_navigate(PlanetList);
    });
    $("#nextPl").on("click", function () {
        planetSelector_changeSelection(PlanetList, 1);
        planetSelector_navigate(PlanetList);
    });
    $("#plType").on("click", function (evt) {
        const $btnEl = $(evt.currentTarget);
        const otherPlanetID = $btnEl.data("id");

        planetSelector_switchPlanetType(PlanetList, otherPlanetID);
        planetSelector_navigate(PlanetList);
    });
    PlanetList.on("change", function () {
        planetSelector_navigate(PlanetList);
    });

    [ "metal", "crystal", "deuterium" ].forEach((resourceKey) => {
        new ResourceTooltip({
            resourceKey,
            $parentEl: $("#topnav_resources"),
            values: PHPInject_topnav_data.resourcesState[resourceKey],
            bodyCreator: createProductionResourceTooltipBody
        });
    });

    new ResourceTooltip({
        resourceKey: "energy",
        $parentEl: $("#topnav_resources"),
        values: PHPInject_topnav_data.specialResourcesState.energy,
        bodyCreator: createEnergyResourceTooltipBody
    });
});
