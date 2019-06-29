<script src="{FilePath}/dist/js/jsChronoApplet.cachebuster-1560520835921.min.js"></script>
<script>
const serverTimestampOffset = ({ServerTimestamp} * 1000) - new Date().getTime();
const LANG = {
    Chrono_PrettyTime: {
        chronoFormat: {
            daysFullJSFunction: {LANG_daysFullJSFunction}
        }
    }
};

const countdownHandlerInstance = new CountdownHandler({
    serverTimestampOffset,
    LANG
});
</script>
