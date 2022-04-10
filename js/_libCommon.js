/* globals AllowPrettyInputBox */
/* exported libCommon */

const addDots = (value) => {
    value += "";
    var rgx = /(\d+)(\d\d\d)/;
    while (rgx.test(value)) {
        value = value.replace(rgx, "$1" + "." + "$2");
    }
    return value;
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

const isNonEmptyValue = (jqThis, params) => {
    const isZeroAllowed = (params || {}).isZeroAllowed || false;
    const valueComparator = (
        isZeroAllowed ?
            (value) => value >= 0 :
            (value) => value > 0
    );

    const currentValue = $(jqThis).val();

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
        return isNonEmptyValue(this, params);
    };
    $.fn.isNonEmptyDataSlot = function (dataKey) {
        return isNonEmptyDataSlot(this, dataKey);
    };
};

const libCommon = {
    init: {
        setupJQuery,
    },
    normalize: {
        removeNonDigit
    },
    format: {
        addDots,
    }
};
