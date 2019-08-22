{PHPInject_commonJS_html}
<script src="{FilePath}/dist/js/jsChronoApplet.cachebuster-1566255596620.min.js"></script>
<script>
const serverTimestampOffset = ({ServerTimestamp} * 1000) - new Date().getTime();

const countdownHandlerInstance = new CountdownHandler({
    serverTimestampOffset
});
</script>
