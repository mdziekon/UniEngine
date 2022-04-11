<script>
var AllowPrettyInputBox = {P_AllowPrettyInputBox};
var Maxmet = {InsertMaxMetal};
var Maxcry = {InsertMaxCrystal};
var Maxdeu = {InsertMaxDeuterium};
var Mode = {InsertTraderMode};

var NameResM = '{Insert_ResM}';
var NameResA = '{Insert_ResA}';
var NameResB = '{Insert_ResB}';
var Mod_ResA = '{mod_ma_res_a}';
var Mod_ResB = '{mod_ma_res_b}';
var MaxResM = Max{Insert_ResM};
var MaxResA = Max{Insert_ResA};
var MaxResB = Max{Insert_ResB};
</script>
<script src="dist/js/_libCommon.cachebuster-1649555016585.min.js"></script>
<script src="dist/js/trader.cachebuster-1649641504903.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="dist/css/trader.cachebuster-1546564327123.min.css" />
<br/>
<form action="merchant.php?step=2" method="post">
    <input type="hidden" name="exchange" value="yes"/>
    <input type="hidden" name="res" value="3"/>
    <input type="hidden" name="mode" value="{InsertTraderMode}"/>
    <table style="width: 750px;">
        <tr class="{TraderMsg_Hide}">
            <th class="pad5 {TraderMsg_Color}" colspan="5">{TraderMsg_Text}&nbsp;</th>
        </tr>
        <tr class="inv"><td style="font-size: 5px;">&nbsp;</td></tr>
        <tr>
            <td class="c" colspan="5"><b class="white">{Trader_Title}</b> [{Trader_ModeNRes}]<b class="fr" style="padding-right: 5px;"><a href="merchant.php">&#171; {Trader_GoBack}</a></b></td>
        </tr>
        <tr>
            <th colspan="5" class="pad5" style="text-align: left;"><span class="fl">{Trader_UsesLeft}: <b class="{Insert_TraderUsesColor}">{Insert_TraderUses}</b></span><span class="fr">{Insert_TraderRight}</span></th>
        </tr>
        <tr>
            <th>&nbsp;</th>
            <th>{Trader_PercentMode}</th>
            <th colspan="2">&nbsp;</th>
            <th class="w80x">{mod_ma_cours}</th>
        </tr>
        <tr>
            <th colspan="5" class="brLine">&nbsp;</th>
        </tr>
        <tr>
            <th colspan="5">{InsertMainResource}:</th>
        </tr>
        <tr>
            <th><img src="{SkinPath}images/deuterium.gif"/><br />{Deuterium}</th>
            <th class="w120x">&nbsp;</th>
            <th><input name="deu" id="MainRes" type="text" value="0" tabindex="1" class="resInput" autocomplete="off"/></th>
            <th><a href="#" id="maxdeu">{Trader_Max}</a> / <a href="#" id="zerodeu">{Trader_Zero}</a></th>
            <th>1</th>
        </tr>
        <tr>
            <th colspan="5">{InsertOtherResources}:</th>
        </tr>
        <tr>
            <th><img src="{SkinPath}images/metall.gif"/><br />{Metal}</th>
            <th class="w120x">
                <div class="posRel">
                    <input type="button" class="modif" value="-"/>
                    <input class="percent" type="text" name="metPercent" maxlength="3" value="50" autocomplete="off"/>
                    <input type="button" class="modif" value="+"/>
                    <span class="percent">%</span>
                </div>
            </th>
            <th><input name="met" type="text" value="0" tabindex="2" class="resInput" autocomplete="off"/></th>
            <th><a href="#" id="maxmet">{Trader_Max}</a> / <a href="#" id="zeromet">{Trader_Zero}</a></th>
            <th>{mod_ma_res_a}</th>
        </tr>
        <tr>
            <th><img src="{SkinPath}images/kristall.gif"/><br />{Crystal}</th>
            <th class="w120x">
                <div class="posRel">
                    <input type="button" class="modif" value="-"/>
                    <input class="percent" type="text" name="cryPercent" maxlength="3" value="50" autocomplete="off"/>
                    <input type="button" class="modif" value="+"/>
                    <span class="percent">%</span>
                </div>
            </th>
            <th><input name="cry" type="text" value="0" tabindex="3" class="resInput" autocomplete="off"/></th>
            <th><a href="#" id="maxcry">{Trader_Max}</a> / <a href="#" id="zerocry">{Trader_Zero}</a></th>
            <th>{mod_ma_res_b}</th>
        </tr>
        <tr>
            <th colspan="5" class="brLine">&nbsp;</th>
        </tr>
        <tr>
            <th colspan="5"><input type="submit" value="{mod_ma_excha}" class="pad5 lime" style="font-weight: bold;" /></th>
        </tr>
    </table>
</form>
