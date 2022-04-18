<script>
(() => {
    const elementSelector = "#bxx{Type}{Ref}";
    const endTimestamp = {endTimestamp};
    const isReverse = {isReverse};
    const reverseEndTimestamp = {reverseEndTimestamp};
    const endCallback = {onEndCallback} || function () { };

    $(document).ready(function () {
        if (!document.querySelector(elementSelector)) {
            console.error("element count not be found", elementSelector);

            return;
        }

        countdownHandlerInstance.registerCountdown({
            $element: document.querySelector(elementSelector),
            endTimestamp,
            isReverse,
            reverseEndTimestamp
        }).then(function () {
            endCallback();
        });
    });
})();
</script>
