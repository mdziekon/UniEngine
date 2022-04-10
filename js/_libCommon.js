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

const setupJQuery = () => {
    $.fn.prettyInputBox = function () {
        return prettyInputBox(this);
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
