<style>
.darkEnergy {
    background-image: url('{skinpath}images/darkenergy.gif');
    background-repeat: no-repeat;
    background-position: 10px center;
    text-align: left;
    padding: 6px;
    padding-left: 60px !important;
}
.bl {
    visibility: hidden;
    height: 10px;
}
.lalign {
    text-align: left;
}
.pad {
    padding: 10px;
}
.pad3 {
    padding: 3px;
}
.bold {
    font-weight: bold;
}
</style>
<script>
$(document).ready(function()
{
    $('.buyForm').submit(function()
    {
        return confirm('{AreYouSure}');
    });
});
</script>
<br />
<table width="750">
    <tbody>
        <tr>
            <th colspan="6" class="darkEnergy">
                {DarkEnergyStatusIs}: <b class="{DarkEnergy_Color}">{DarkEnergy_Counter}</b><b style="float: right; padding-right: 6px;"><a href="galacticshop.php" class="orange">{BuyMoreDE}</a></b>
            </th>
        </tr>
        <tr>
            <td class="bl"></td>
        </tr>
        <tr>
            <td class="c" colspan="2">{aboutpro}</td>
        </tr>
        <tr>
            <th class="c" rowspan="3">
                <img src="{skinpath}img/proacc.jpg" align="top" border="0" height="120" width="120"/>
            </th>
            <th class="c lalign pad">{Desc}</th>
        </tr>
        <tr>
            <th class="c lalign pad">
                <b class="orange">{BenefitsTitle}</b><br /><br />
                {ParsedBenefits}
            </th>
        </tr>
        <tr>
            <th class="c pad">
                <u>{ProAccStatus}</u>
                <br /><br />
                <b class="{ProStateColor}">{ProState}</b> <b class="{ProTimeColor}">{ProTime}</b>
                <br /><br />
                <form class="buyForm" action="galacticshop.php" method="post">
                    <input type="hidden" name="mode" value="buyitem"/>
                    <input type="submit" class="pad3 bold confirm" value="{BuyButton1}&#10; ({Cost} {CostVal1} {CostUnits})" name="buyitem_{ShopItemID1}"/>
                    <br /><br />
                    <input type="submit" class="pad3 bold confirm" value="{BuyButton2}&#10; ({Cost} {CostVal2} {CostUnits})" name="buyitem_{ShopItemID2}"/>
                </form>
            </th>
        </tr>
    </tbody>
</table>
