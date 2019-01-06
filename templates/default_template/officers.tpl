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
.lime {
    color: lime;
}
.orange {
    color: orange;
}
.red {
    color: red;
}
.center {
    text-align: center;
}
.left {
    text-align: left;
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
<form class="buyForm" action="galacticshop.php" method="post">
    <input type="hidden" name="mode" value="buyitem"/>
    <table width="650">
        <tbody>
            <tr>
                <th colspan="6" class="darkEnergy">{DarkEnergyStatusIs}: <b class="{DarkEnergy_Color}">{DarkEnergy_Counter}</b><b style="float: right; padding-right: 6px;"><a href="galacticshop.php" class="orange">{BuyMoreDE}</a></b></th>
            </tr>
            <tr>
                <td class="bl"></td>
            </tr>
            <tr>
                <th colspan="2" class="c center pad5">{description}</th>
            </tr>
            <tr>
                <td class="bl"></td>
            </tr>
            {ParsedOfficers}
            <tr>
                <th colspan="2">
                    <center style="padding: 10px;">
                    {Buy}<br/>
                    <a href="galacticshop.php?show=shop" style="color: orange;">{BuyTitle}</a>
                    </center>
                </th>
            </tr>
        </tbody>
    </table>
</form>
