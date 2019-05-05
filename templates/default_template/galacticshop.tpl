<style>
.darkEnergy {
    background-image: url('{skinpath}images/darkenergy.gif');
    background-repeat: no-repeat;
    background-position: 10px center;
    text-align: left;
    padding: 6px;
    padding-left: 60px !important;
}
</style>
<script>
if('{SetActiveFormTab}' != '')
{
    var OverrideFormTab = '{SetActiveFormTab}';
}
else
{
    var OverrideFormTab = '01';
}

if('{SetActiveMarker}' != '')
{
    var OverrideMarker = '{SetActiveMarker}';
}
else
{
    var OverrideMarker = '01';
}
</script>
<script src="dist/js/galacticshop.cachebuster-1546739003831.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="dist/css/galacticshop.cachebuster-1546565145290.min.css" />
<br />
<table width="650">
    <tr>
        <th colspan="6" class="darkEnergy">{DarkEnergyStatusIs}: <b class="{DarkEnergy_Color}">{DarkEnergy_Counter}</b></th>
    </tr>
    <tr>
        <td class="bl"></td>
    </tr>
    <tbody class="JSWarning">
        <tr>
            <th colspan="6" style="border: solid 2px red; background: #E60000; color: white;">{NoJavaScript_Warning}</th>
        </tr>
        <tr>
            <td class="bl"></td>
        </tr>
    </tbody>
    <tr>
        <th class="pad mark" id="Mark01_SMSShop" colspan="2">{BuyDarkEnergy}</th>
        <th class="pad mark" id="Mark02_ItemShop" colspan="2">{BuyItems}</th>
        <th class="pad mark" id="Mark03_ItemFree" colspan="2">{FreePremiumItems}</th>
    </tr>
    <tr>
        <td class="bl"></td>
    </tr>
    {showError}{showMsg}
    <tbody class="Cont01">
        <tr>
            <th class="pad" colspan="6">
                LEFT FOR INDIVIDUAL IMPLEMENTATION OF THE SERVER OWNERS<br/>
                (Implementation notes in galacticshop.php file)
            </th>
        </tr>
    </tbody>
    <tbody class="Cont02">
        <tr>
            <td class="c" colspan="1" style="width: 20%;">{ArticleName}</td>
            <td class="c" colspan="4">{ArticleDesc}</td>
            <td class="c" colspan="1" style="width: 20%;">{ArticleButtom}</td>
        </tr>
        <form action="galacticshop.php" method="post">
            <input type="hidden" name="mode" value="buyitem"/>
            {Articles}
        </form>
    </tbody>
    <tbody class="Cont03">
        <tr>
            <th colspan="6" class="pad">{FreeText}<br/></th>
        </tr>
        <tr>
            <th class="c" style="visibility: hidden;"></th>
        </tr>
        <tr>
            <td class="c" colspan="2">{FreeItemName}</td>
            <td class="c" colspan="2">{FreeItemGivenBy}</td>
            <td class="c" colspan="2">&nbsp;</td>
        </tr>
        {FreeItemsList}
    </tbody>
</table>
