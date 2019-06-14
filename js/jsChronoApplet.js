/* globals Promise, Set */
/* exported CountdownHandler */

class CountdownHandler {
    constructor (config) {
        this.registeredCountdowns = new Set();
        this.registeredIntervalID = undefined;
        this.config = {
            serverTimestampOffset: config.serverTimestampOffset,
            LANG: config.LANG
        };
    }

    //  Arguments
    //      - countdownCtorArgs (Object)
    //          - $element (DOMElement)
    //              A DOM element to be used as a placeholder for the countdown.
    //          - endTimestamp (Number)
    //              Countdown's unix timestamp (in seconds):
    //              - in normal mode: countdown's end
    //              - in reverse mode: countdown's start
    //          - isReverse (Boolean)
    //              Is counting up instead of down?
    //          - reverseEndTimestamp (Number)
    //              Unix timestamp (in seconds) of when the reverse countdown
    //              should stop counting up.
    //          - serverTimestampOffset (Number)
    //              The difference (in milliseconds) between the
    //              server's timestamp and client's timestamp.
    //              Please note that it does not take into account any
    //              delays caused by the network lag or page processing time.
    //
    registerCountdown (countdownCtorArgs) {
        const endPromise = new Promise((resolve) => {
            this.registeredCountdowns.add({
                $element: countdownCtorArgs.$element,
                endTimestamp: countdownCtorArgs.endTimestamp * 1000,
                isReverse: countdownCtorArgs.isReverse,
                reverseEndTimestamp: countdownCtorArgs.reverseEndTimestamp * 1000,
                serverTimestampOffset: this.config.serverTimestampOffset,
                LANG: this.config.LANG,

                promiseResolver: resolve
            });
        });

        this._startEventLoop();

        return endPromise;
    }

    unregisterCountdown (countdown) {
        this.registeredCountdowns.delete(countdown);
    }

    _startEventLoop () {
        if (this.registeredIntervalID) {
            return;
        }

        this.registeredIntervalID = setInterval(() => this._processCountdowns(), 500);
    }

    _stopEventLoop () {
        clearInterval(this.registeredIntervalID);

        this.registeredIntervalID = undefined;
    }

    _processCountdowns () {
        [ ...this.registeredCountdowns ].forEach((countdown) => {
            const result = this._onTimeCountdownTick({
                $element: countdown.$element,
                endTimestamp: countdown.endTimestamp,
                isReverse: countdown.isReverse,
                reverseEndTimestamp: countdown.reverseEndTimestamp,
                serverTimestampOffset: countdown.serverTimestampOffset,
                LANG: countdown.LANG
            });

            if (!result.hasFinished) {
                return;
            }

            this.unregisterCountdown(countdown);

            countdown.promiseResolver();
        });

        if (this.registeredCountdowns.size) {
            return;
        }

        this._stopEventLoop();
    }

    _onTimeCountdownTick ({ $element, endTimestamp, isReverse, reverseEndTimestamp, serverTimestampOffset, LANG }) {
        const clientTimestamp = (new Date()).getTime();
        const currentTimestamp = clientTimestamp + serverTimestampOffset;

        let timestampDiff = (endTimestamp - currentTimestamp);

        if (isReverse) {
            timestampDiff *= -1;
        }

        let countdownDisplayValue;

        const hasFinished = (
            (
                isReverse &&
                currentTimestamp >= (reverseEndTimestamp + serverTimestampOffset)
            ) ||
            !(timestampDiff > 0)
        );

        if (!hasFinished) {
            countdownDisplayValue = this._createTimeCountdownDisplayValue({
                seconds: (timestampDiff / 1000),
                LANG
            });
        } else {
            countdownDisplayValue = "-";
        }

        $element.innerHTML = countdownDisplayValue;

        return {
            hasFinished
        };
    }

    _createTimeCountdownDisplayValue ({ seconds, LANG }) {
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
            timePieces.push(LANG["Chrono_PrettyTime"]["chronoFormat"]["daysFullJSFunction"](days));
        }

        timePieces.push(`${hoursString}:${minutesString}:${secondsString}`);

        return timePieces.join(" ");
    }
}

function strPadLeft (value, desiredLenght, padString) {
    const lengthLeft = desiredLenght - (value.length);

    if (lengthLeft <= 0) {
        return value;
    }

    const padding = (new Array(lengthLeft)).fill(padString, 0, lengthLeft).join("");

    return `${padding}${value}`;
}
