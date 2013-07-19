<style>
#infoBar {
    width: 100%; 
    position: fixed; 
    top: -40px; 
    left: 0px; 
    background-color: infobackground; 
    border: 0px;
    border-bottom: 2px ridge threedlightshadow;
    color: infotext;
    height: 32px; 
    z-index: 1000;
    font-weight: bold;
}
#infoBar:hover {
    background-color: highlight;
    color: highlighttext;
    border-bottom-color: threedshadow;
}
</style>
<script>
$(document).ready(function(){
    $('#infoBar').animate({top: '0px'}, 1000);
    setTimeout(function(){$("#doHide").click();}, 10000);
    $('#doHide').click(function()
    {
        $('#infoBar').animate({top: '-40px'}, 1000);
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