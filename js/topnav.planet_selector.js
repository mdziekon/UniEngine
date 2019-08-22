const utils = {
    planetSelector: {
        changeSelection ($selectorEl, selectionIdxModifier) {
            const $options = $selectorEl.find("option");
            const $selectedOption = $options.filter(":selected");

            const currentSelectedIdx = $options.index($selectedOption);

            const $changedSelection = $options.eq(currentSelectedIdx + selectionIdxModifier);

            $selectorEl.val($changedSelection.val());
        },

        switchPlanetType ($selectorEl, relatedPlanetID) {
            const $options = $selectorEl.find("option");

            const $changedSelection = $options.filter(`[data-planet-id="${relatedPlanetID}"]`);

            $selectorEl.val($changedSelection.val());
        },

        navigate ($selectorEl) {
            const currentSelectionURL = $selectorEl.val();

            window.location = currentSelectionURL;
        }
    },

    quickActionBtns: {
        calculateAppropriateSwitcherBtnWidth ({ $planetTypeChangerBtnEl, $planetsSelectorEl }) {
            if (!$planetTypeChangerBtnEl.is(":visible")) {
                return (
                    ($planetsSelectorEl.width() / 2) +
                    1
                );
            }

            return (
                ($planetsSelectorEl.width() / 2) -
                $planetTypeChangerBtnEl.width() +
                2
            );
        }
    }
};

$(document).ready(function () {
    const $parentEl = $("#topnav_resources");
    const $planetsSelectorEl = $parentEl.find("#planet");
    const $planetTypeChangerBtnEl = $parentEl.find("#plType");
    const $quickPlanetSwitcherBtnEls = $parentEl.find(".plBut");

    $quickPlanetSwitcherBtnEls.width(
        utils.quickActionBtns.calculateAppropriateSwitcherBtnWidth({
            $planetTypeChangerBtnEl,
            $planetsSelectorEl
        })
    );

    $("#prevPl").on("click", function () {
        utils.planetSelector.changeSelection($planetsSelectorEl, -1);
        utils.planetSelector.navigate($planetsSelectorEl);
    });
    $("#nextPl").on("click", function () {
        utils.planetSelector.changeSelection($planetsSelectorEl, 1);
        utils.planetSelector.navigate($planetsSelectorEl);
    });
    $planetTypeChangerBtnEl.on("click", function (evt) {
        const $btnEl = $(evt.currentTarget);
        const relatedPlanetID = $btnEl.data("id");

        utils.planetSelector.switchPlanetType($planetsSelectorEl, relatedPlanetID);
        utils.planetSelector.navigate($planetsSelectorEl);
    });
    $planetsSelectorEl.on("change", function () {
        utils.planetSelector.navigate($planetsSelectorEl);
    });
});
