window.uniengine = window.uniengine || {};
window.uniengine.phpInjectionData = window.uniengine.phpInjectionData || {};
window.uniengine.phpInjectionData.lang = window.uniengine.phpInjectionData.lang || {};

function getLangStorage () {
    return window.uniengine.phpInjectionData.lang;
}

function strPadLeft (value, desiredLenght, padString) {
    const lengthLeft = desiredLenght - (value.length);

    if (lengthLeft <= 0) {
        return value;
    }

    const padding = (new Array(lengthLeft)).fill(padString, 0, lengthLeft).join("");

    return `${padding}${value}`;
}

function prettyTime ({ seconds }) {
    const lang = getLangStorage().common;

    seconds = Math.floor(seconds);

    const SECONDS_IN_MINUTE = 60;
    const SECONDS_IN_HOUR = 60 * SECONDS_IN_MINUTE;
    const SECONDS_IN_DAY = 24 * SECONDS_IN_HOUR;

    const days = Math.floor(seconds / SECONDS_IN_DAY);

    seconds -= (days * SECONDS_IN_DAY);

    const hours = Math.floor(seconds / SECONDS_IN_HOUR);

    seconds -= (hours * SECONDS_IN_HOUR);

    const minutes = Math.floor(seconds / SECONDS_IN_MINUTE);

    seconds -= (minutes * SECONDS_IN_MINUTE);

    const hoursString = strPadLeft(String(hours), 2, "0");
    const minutesString = strPadLeft(String(minutes), 2, "0");
    const secondsString = strPadLeft(String(seconds), 2, "0");

    const timePieces = [];

    if (days > 0) {
        timePieces.push(lang.prettyTime.formatters.daysFullJSFunction(days));
    }

    timePieces.push(`${hoursString}:${minutesString}:${secondsString}`);

    return timePieces.join(" ");
}

window.uniengine.common = {
    getLangStorage,
    strPadLeft,
    prettyTime
};
