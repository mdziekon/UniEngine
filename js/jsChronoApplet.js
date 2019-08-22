/* globals Promise, Set, uniengine */
/* exported CountdownHandler */

class CountdownHandler {
    constructor (config) {
        this.registeredCountdowns = new Set();
        this.registeredIntervalID = undefined;
        this.config = {
            serverTimestampOffset: config.serverTimestampOffset
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
                serverTimestampOffset: countdown.serverTimestampOffset
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

    _onTimeCountdownTick ({ $element, endTimestamp, isReverse, reverseEndTimestamp, serverTimestampOffset }) {
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
                seconds: (timestampDiff / 1000)
            });
        } else {
            countdownDisplayValue = "-";
        }

        $element.innerHTML = countdownDisplayValue;

        return {
            hasFinished
        };
    }

    _createTimeCountdownDisplayValue ({ seconds }) {
        return uniengine.common.prettyTime({ seconds });
    }
}
