<script>
(() => {
    const elementSelector = "#bxx{Type}{Ref}";
    const endTimestamp = {endTimestamp};
    const isReverse = {isReverse};
    const reverseEndTimestamp = {reverseEndTimestamp};
    const endCallback = {onEndCallback} || function () { };

    $(document).ready(function () {
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
