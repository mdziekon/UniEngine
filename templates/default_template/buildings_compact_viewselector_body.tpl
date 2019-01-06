<script>
var View_Mode = '{PHP_ViewMode}';
</script>
<form style="display: none;" action="" method="post"><input type="text" name="chgView" value="1"/><input type="text" id="chgViewIn" name="mode" value=""/></form>
<div id="viewOn" class="pabs vdiv">
    <ul class="vList">
        <li><span class="alpha"><b>{PHP_ViewText}:</b></span></li>
        <li><span class="point modeSelector" id="viewMode_0"><a class="vAlign vEl"></a> <b class="vAlign">{PHP_Mode0}</b></span></li>
        <li><span class="point modeSelector" id="viewMode_1"><a class="vAlign vEl b"></a> <b class="vAlign">{PHP_Mode1}</b></span></li>
        <li><span class="place"></span></li>
        <li><span class="point" id="hideView"><a class="vArr b"></a></span></li>
    </ul>
</div>
<div id="viewOff" class="pabs vdiv">
    <ul class="vList b">
        <li><span class="alpha"><b>{PHP_ChangeView}</b></span></li>
        <li><span class="point" id="showView"><a class="vArr"></a></span></li>
    </ul>
</div>
