<script>
var AllowPrettyInputBox  = {P_AllowPrettyInputBox};
var JSLang = {'Metal': '{Metal}', 'Crystal': '{Crystal}', 'Deuterium': '{Deuterium}'};
var ShipPrices = {Create_InsertPrices};
</script>
<script src="dist/js/_libCommon.cachebuster-1649555016585.min.js"></script>
<script src="dist/js/disassembler.cachebuster-1649640165056.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="dist/css/structures.cachebuster-1546565145290.min.css" />
<div style="height: 6px;"></div>
<div class="w870x">
    <div style="float: left; width: 600px;">
        <table class="w100p">
            <tr>
                <th class="pad5 w50p tabSwitch" id="tab_ships">{Disassembler_Tab_Ships}</th>
                <th class="pad5 w50p tabSwitch" id="tab_defs">{Disassembler_Tab_Defense}</th>
            </tr>
        </table>
        <form action="" method="post" style="margin: 0px;" id="disassemblerForm">
        <input type="hidden" name="cmd" value="exec"/>
        <table class="w100p cx" id="cx_ships">
            <tr>
                <td class="c center">{ListBox_ShipsList}</td>
            </tr>
            <tr>
                <th class="pad5">{Create_StructuresList_Ships}</th>
            </tr>
        </table>
        <table class="w100p cx" id="cx_defs">
            <tr>
                <td class="c center">{ListBox_DefensesList}</td>
            </tr>
            <tr>
                <th class="pad5">{Create_StructuresList_Defense}</th>
            </tr>
        </table>
        </form>
    </div>
    <div class="fr">
        <table class="w260x mb10">
            <tr>
                <td class="c center" colspan="2">{Disassembler_Header}</td>
            </tr>
            <tr>
                <th class="pad2 w50x" rowspan="3">{Disassembler_Gain}</th>
                <th class="pad2">
                    <img src="{SkinPath}images/metall.gif" class="infoRes_metal fl"/>
                    <div class="fr resCounter" id="resC_metal">0</div>
                </th>
            </tr>
            <tr>
                <th class="pad2">
                    <img src="{SkinPath}images/kristall.gif" class="infoRes_crystal fl"/>
                    <div class="fr resCounter" id="resC_crystal">0</div>
                </th>
            </tr>
            <tr>
                <th class="pad2">
                    <img src="{SkinPath}images/deuterium.gif" class="infoRes_deuterium fl"/>
                    <div class="fr resCounter" id="resC_deuterium">0</div>
                </th>
            </tr>
            <tr>
                <th class="pad5" colspan="2">{Create_DisassemblerPercent}</th>
            </tr>
            <tr>
                <th class="pad2 center" colspan="2">
                    <a href="#" id="buttonC" class="infoButton construct_Gray">
                        <span class="infoButtonText">{Disassembler_Proceed}<br />{Cart_Ships}</span>
                    </a>
                </th>
            </tr>
            <tr>
                <th class="pad2 center" colspan="2"><a class="reqSelector red" id="cancelReq"><img src="images/delete.png" class="linkImg"/>{Disassembler_CancelRequest}</a></th>
            </tr>
            <tr>
                <th class="pad2 center" colspan="2"><a class="reqSelector lime" id="selAll"><img src="images/bin.png" class="linkImg"/>{Disassembler_SelectAll}</a></th>
            </tr>
        </table>
        <table class="w260x mb10 {HideDisassembleResult}">
            <td class="c center" colspan="2">{Disassembler_Result}</td>
            <tr>
                <th class="pad5 infoDes {Create_DisassembleResult_Color}" colspan="2">{Create_DisassembleResult}</th>
            </tr>
        </table>
    </div>
</div>
