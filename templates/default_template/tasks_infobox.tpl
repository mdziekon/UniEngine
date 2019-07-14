<style>
#infoBar {
    width: 100%;
    position: fixed;
    top: -40px;
    left: 0px;
    border: 0px;
    height: 32px;
    z-index: 1000;
    font-weight: bold;
    padding-top: 5px;
    padding-bottom: 5px;
    /* Computed styles based on current theme */
    /* background-color: infobackground; */
    /* border-bottom: 2px ridge threedlightshadow; */
    /* color: infotext; */
}
</style>
<script>
function probeCSS() {
    // FIXME: this is a workaround to capture appropriate theme colors,
    // provide a better solution in the future
    const $probeEl = $("<th>", { style: "display: none" });

    $("body").append($probeEl);

    const themeStyles = {
        color: $probeEl.css("color"),
        backgroundColor: $probeEl.css("background-color"),
        borderBottomColor: $probeEl.css("border-bottom-color"),
        borderBottomStyle: $probeEl.css("border-bottom-style"),
        borderBottomWidth: $probeEl.css("border-bottom-width"),
    };

    $probeEl.remove();

    return themeStyles;
}

$(document).ready(function(){
    const styles = probeCSS();

    const $infoBar = $('#infoBar');

    $infoBar.css(styles);

    $infoBar.animate({top: '0px'}, 1000);

    setTimeout(
        function() {
            $("#doHide").click();
        },
        10000
    );

    $('#doHide').click(function() {
        $infoBar.animate({top: '-50px'}, 1000);

        return false;
    });
});
</script>
<div id="infoBar">
    <div style="margin: 0 auto; width: 500px;">
        <div style="width: 40px; float: left;"><img src="images/done.png"/></div>
        <div style="width: 460px; float: left; text-align: center;">
            <p style="margin: 0; line-height: 32px;">
                {TextFirst} {Task} {TextSecond} {CatLinks}!
            </p>
        </div>
    </div>
    <div style="float: right; width: 32px;">
        <a id="doHide" title="{Hide}" style="cursor: pointer;"><img src="images/hide_big.png"/></a>
    </div>
</div>
