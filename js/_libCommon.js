/* globals AllowPrettyInputBox, ServerClientDifference */
/* exported libCommon */

const addDots = (value) => {
    value += "";
    var rgx = /(\d+)(\d\d\d)/;
    while (rgx.test(value)) {
        value = value.replace(rgx, "$1" + "." + "$2");
    }
    return value;
};
// TODO: Also defined in common.js, deduplicate
const padStringLeft = (value, desiredLenght, padString) => {
    const strValue = String(value);
    const lengthLeft = desiredLenght - (strValue.length);

    if (lengthLeft <= 0) {
        return strValue;
    }

    const padding = (new Array(lengthLeft)).fill(padString, 0, lengthLeft).join("");

    return `${padding}${strValue}`;
};
const formatDateToFlightEvent = (eventOffset) => {
    const currentServerTimestamp = (new Date()).getTime() + ServerClientDifference;
    const currentDate = new Date(currentServerTimestamp + eventOffset);
    const currentDateParts = {
        years: ((currentDate.getFullYear()).toString()).substr(2, 2),
        months: currentDate.getMonth() + 1,
        days: currentDate.getDate(),
        hours: currentDate.getHours(),
        mins: currentDate.getMinutes(),
        secs: currentDate.getSeconds(),
    };

    Object.keys(currentDateParts).forEach((partKey) => {
        currentDateParts[partKey] = padStringLeft(currentDateParts[partKey], 2, "0");
    });

    const formattedTime = `${currentDateParts.hours}:${currentDateParts.mins}:${currentDateParts.secs}`;
    const formattedDate = `${currentDateParts.days}.${currentDateParts.months}.${currentDateParts.years}`;

    return `${formattedTime} - ${formattedDate}`;
};

const removeNonDigit = (value) => {
    value += "";
    value = value.replace(/[^0-9]/g, "");
    return value;
};

const prettyInputBox = (jqThis) => {
    if (!AllowPrettyInputBox) {
        return jqThis;
    }

    return jqThis.each(function () {
        const normalizedValue = removeNonDigit($(this).val());
        const formattedValue = addDots(normalizedValue);
        $(this).val(formattedValue);
    });
};

const isNonEmptyValue = (currentValue, params) => {
    const isZeroAllowed = (params || {}).isZeroAllowed || false;
    const valueComparator = (
        isZeroAllowed ?
            (value) => value >= 0 :
            (value) => value > 0
    );

    return (
        currentValue != "" &&
        valueComparator(currentValue)
    );
};
const isNonEmptyDataSlot = (jqThis, dataKey) => {
    const currentValue = $(jqThis).data(dataKey);

    return (
        currentValue != "" &&
        currentValue > 0
    );
};

const setupJQuery = () => {
    $.fn.prettyInputBox = function () {
        return prettyInputBox(this);
    };
    $.fn.isNonEmptyValue = function (params) {
        return isNonEmptyValue($(this).val(), params);
    };
    $.fn.isNonEmptyDataSlot = function (dataKey) {
        return isNonEmptyDataSlot(this, dataKey);
    };
};

const libCommon = {
    init: {
        setupJQuery,
    },
    tests: {
        isNonEmptyValue
    },
    normalize: {
        removeNonDigit
    },
    format: {
        addDots,
        padStringLeft,
        formatDateToFlightEvent,
    }
};
