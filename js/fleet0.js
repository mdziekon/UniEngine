/* globals libCommon, JSLang, ShipsData, TotalPlanetResources, ACSUsersMax */

$(document).ready(function () {
    libCommon.init.setupJQuery();

    // Elements Cache
    const $transportTotalStorage = $("#calcStorage");
    const $unionInvitedUsersList = $("#ACSUser_Invited");
    const $unionInvitableUsersList = $("#ACSUser_2Invite");
    const $unionUsersChangedInput = $("[name=\"acsuserschanged\"]");

    const getCurrentTransportTotalStorage = () => {
        const formattedValue = $transportTotalStorage.html();

        return parseInt(libCommon.normalize.removeNonDigit(formattedValue), 10);
    };
    /**
     * @param {number} newValue
     */
    const updateTransportTotalStorage = (newValue) => {
        const isEnoughStorageForPlanetResources = newValue >= TotalPlanetResources;
        const formattedNewValue = libCommon.format.addDots(newValue);

        $transportTotalStorage
            .toggleClass("orange", !isEnoughStorageForPlanetResources)
            .toggleClass("lime", isEnoughStorageForPlanetResources);

        $transportTotalStorage.html(formattedNewValue);
    };

    /**
     * @param {jQueryElement} $input
     */
    const handleShipInputUpdate = ($input) => {
        const newValueRaw = parseInt(libCommon.normalize.removeNonDigit($input.val()), 10);
        const oldValueRaw = $input.data("oldCount");

        const newValue = (
            !Number.isNaN(newValueRaw) ?
                newValueRaw :
                0
        );
        const oldValue = (
            !(oldValueRaw === undefined || isNaN(oldValueRaw)) ?
                oldValueRaw :
                0
        );

        const valueDiff = newValue - oldValue;

        if (valueDiff != 0) {
            const thisShipId = libCommon.normalize.removeNonDigit($input.attr("name"));
            const transportTotalStorage = getCurrentTransportTotalStorage();
            const storageChange = valueDiff * ShipsData[thisShipId].storage;

            updateTransportTotalStorage(transportTotalStorage + storageChange);

            const isUsingMoreThanAvailableShips = newValue > ShipsData[thisShipId].count;

            $input.data("oldCount", newValue);
            $input.toggleClass("red", isUsingMoreThanAvailableShips);
        }

        $input.prettyInputBox();
    };

    $("[name^=\"ship\"]")
        .on("keydown", function (event) {
            if (!(event.which == 38 || event.which == 40)) {
                return;
            }

            const currentCountRaw = parseFloat(libCommon.normalize.removeNonDigit($(this).val()));
            const currentCount = (
                Number.isNaN(currentCountRaw) ?
                    0 :
                    currentCountRaw
            );
            const incrementBy = (
                (event.which == 38) ?
                    1 :
                    -1
            );
            const nextCount = currentCount + incrementBy;
            const nextCountNormalized = (nextCount >= 0 ? nextCount : 0);

            $(this).val(nextCountNormalized);

            handleShipInputUpdate($(this));
        })
        .on("keyup", function () {
            handleShipInputUpdate($(this));
        })
        .on("change", function () {
            handleShipInputUpdate($(this));
        })
        .on("focus", function () {
            if ($(this).val() == "0") {
                $(this).val("");
            }
        })
        .on("blur", function () {
            if ($(this).val() == "") {
                $(this).val("0");
            }
        });

    $(".maxShip").on("click", function () {
        const $shipRow = $(this).closest("[data-shipid]");
        const shipId = $shipRow.data("shipid");
        const $inputElement = $shipRow.find("#ship" + shipId);

        $inputElement.val(ShipsData[shipId].count);

        handleShipInputUpdate($inputElement);
    });
    $(".noShip").on("click", function () {
        const $shipRow = $(this).closest("[data-shipid]");
        const shipId = $shipRow.data("shipid");
        const $inputElement = $shipRow.find("#ship" + shipId);

        $inputElement.val(0);

        handleShipInputUpdate($inputElement);
    });

    $(".maxShipAll").on("click", function () {
        $(".maxShip").click();
    });
    $(".noShipAll").on("click", function () {
        $(".noShip").click();
    });

    $("#ACSUserAdd").on("click", function () {
        const $invitedUsersListElements = $unionInvitedUsersList.find("option");
        const $selectedListOption = $unionInvitableUsersList.children("option:selected");

        if (
            $invitedUsersListElements.length >= (ACSUsersMax + 1) ||
            !$selectedListOption.length
        ) {
            return;
        }

        $selectedListOption.appendTo($unionInvitedUsersList);
        $selectedListOption.prop("selected", false);

        $unionUsersChangedInput.val("1");
    });
    $("#ACSUserRmv").on("click", function () {
        const $selectedListOption = $unionInvitedUsersList.children("option:selected");

        if (
            !$selectedListOption.length ||
            $selectedListOption.is(":disabled")
        ) {
            return;
        }

        $selectedListOption.appendTo($unionInvitableUsersList);
        $selectedListOption.prop("selected", false);

        $unionUsersChangedInput.val("1");
    });

    $("#ACSForm").on("submit", function () {
        const userIds = $unionInvitedUsersList.children("option")
            .map(function () {
                return $(this).val();
            })
            .toArray();

        const userIdsString = userIds.join(",");

        $("[name=\"acs_users\"]").val(userIdsString);
    });

    $(".setACS_ID").on("click", function () {
        $("[name=getacsdata]").val($(this).val());
    });

    // Trigger ship inputs formatting & internal data setting
    $("[name^=\"ship\"]").each(function () {
        handleShipInputUpdate($(this));
    });

    // Dynamically apply styling
    $(".addPad2")
        .children(":not(.pad5)")
        .addClass("pad2");

    $(".FBeh").tipTip({
        maxWidth: "250px",
        attribute: "title",
        delay: 0,
        edgeOffset: 8,
    });
    $(".Speed").tipTip({
        maxWidth: "250px",
        attribute: "title",
        delay: 0,
        edgeOffset: 8,
    });
    $(".fInfo").tipTip({
        maxWidth: "300px",
        minWidth: "200px",
        attribute: "title",
        delay: 0,
        edgeOffset: 8,
    });
    $(".planet").tipTip({
        content: JSLang["fl_coordplanet"],
        delay: 0,
        edgeOffset: 8,
    });
    $(".moon").tipTip({
        content: JSLang["fl_coordmoon"],
        delay: 0,
        edgeOffset: 8,
    });
    $(".debris").tipTip({
        content: JSLang["fl_coorddebris"],
        delay: 0,
        edgeOffset: 8,
    });
});
