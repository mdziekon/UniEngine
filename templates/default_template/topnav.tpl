<script>
$(document).ready(function()
{
    var ResToolTip = '<div class="center"><b>_ResName_</b></div><div class="center"><b>_ResIncome_</b></div><div><div class="ResL">{When_full_store}</div><div class="ResR">_ResFullTime_</div></div><div><div class="ResL">{Store_Status}</div><div class="ResR">_ResStoreStatus_</div></div>';
    MToolTip = ArrayReplace(ResToolTip, ReplaceArr, ['{Metal}', '{TipIncome_Metal}', '{Metal_full_time}', '{Metal_store_status}']);
    CToolTip = ArrayReplace(ResToolTip, ReplaceArr, ['{Crystal}', '{TipIncome_Crystal}', '{Crystal_full_time}', '{Crystal_store_status}']);
    DToolTip = ArrayReplace(ResToolTip, ReplaceArr, ['{Deuterium}', '{TipIncome_Deuterium}', '{Deuterium_full_time}', '{Deuterium_store_status}']);
    EToolTip = '<div class="center"><b>{Energy}</b></div><div class="center"><b>{Energy_free}</b></div><div class="center">({Energy_used}/{Energy_total})</div>';
    setInterval("Update({JSPerHour_Metal}, {JSPerHour_Crystal}, {JSPerHour_Deuterium}, {JSStore_Metal}, {JSStore_Crystal}, {JSStore_Deuterium}, {JSStoreOverflow_Metal}, {JSStoreOverflow_Crystal}, {JSStoreOverflow_Deuterium}, {JSCount_Metal}, {JSCount_Crystal}, {JSCount_Deuterium})", 1000)
});
</script>
<script src="dist/js/resourceUpdate.cachebuster-1546213884373.min.js"></script>
<link rel="stylesheet" type="text/css" href="css/topNav-1.2.4.1.min.css"/>
<table>
    <tr>
        <td>
            <table>
                <tr>
                    <td><img src="{skinpath}planeten/small/s_{image}.jpg" id="plImg"/></td>
                    <td valign="middle" align="center">
                        <select id="planet" class="plSel plList">{planetlist}</select><br/>
                        <input id="prevPl" class="plSel plBut" type="button" value="<<" title="{PrevPlanet}"/>
                        <input id="plType" class="plSel {Insert_TypeChange_Hide}" type="button" data-id="{Insert_TypeChange_ID}" value="{Insert_TypeChange_Sign}" title="{Insert_TypeChange_Title}"/>
                        <input id="nextPl" class="plSel plBut" type="button" value=">>" title="{NextPlanet}"/>
                    </td>
                </tr>
            </table>
        </td>
        <td>
            <table id="resTopNav" border="0" cellpadding="0" cellspacing="0">
                <tr class="tdct">
                    <td class="w145 resMet" id="resMet"><a href="resources.php"><img src="{skinpath}images/metall.gif"/></a></td>
                    <td class="w145 resCry" id="resCry"><a href="resources.php"><img src="{skinpath}images/kristall.gif"/></a></td>
                    <td class="w145 resDeu" id="resDeu"><a href="resources.php"><img src="{skinpath}images/deuterium.gif"/></a></td>
                    <td class="w145 resEnr" id="resEng"><a href="resources.php"><img src="{skinpath}images/energie.gif"/></a></td>
                    <td class="w50"></td>
                    <td class="w145"><a href="messages.php"><img src="{skinpath}images/message.gif" class="resImg"/></a></td>
                    <td class="w220"><img src="{skinpath}images/darkenergy.gif" class="resImg"/></td>
                </tr>
                <tr class="tdct">
                    <td class="w145 resTD resMet">{Metal}</td>
                    <td class="w145 resTD resCry">{Crystal}</td>
                    <td class="w145 resTD resDeu">{Deuterium}</td>
                    <td class="w145 resTD resEnr">{Energy}</td>
                    <td class="w50"></td>
                    <td class="w145"><a href="messages.php"><u><i><b>{Message}</b></i></u></a></td>
                    <td class="w220"><u><i><b>{DarkEnergy}</b></i></u></td>
                </tr>
                <tr class="tdct">
                    <td class="w145 resMet"><div id="metal">{ShowCount_Metal}</div></td>
                    <td class="w145 resCry"><div id="crystal">{ShowCount_Crystal}</div></td>
                    <td class="w145 resDeu"><div id="deut">{ShowCount_Deuterium}</div></td>
                    <td class="w145 resEnr">{Energy_free}</td>
                    <td class="w50"></td>
                    <td class="w145">{ShowCount_Messages}</td>
                    <td class="w220">{ShowCount_DarkEnergy}</td>
                </tr>
                <tr class="tdct">
                    <td class="w145 resMet"><div id="metalmax">{ShowStore_Metal}</div></td>
                    <td class="w145 resCry"><div id="crystalmax">{ShowStore_Crystal}</div></td>
                    <td class="w145 resDeu"><div id="deuteriummax">{ShowStore_Deuterium}</div></td>
                    <td class="w145 resEnr" id="showET"></td>
                    <td class="w50"></td>
                    <td class="w145"></td>
                    <td class="w220"><a href="galacticshop.php"><b class="red">&#187; {GoToShop} &#171;</b></a></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
