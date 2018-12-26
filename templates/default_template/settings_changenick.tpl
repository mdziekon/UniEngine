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
.pad3 {
    padding: 3px;
}
.w90p {
    width: 90%;
}
.bold {
    font-weight: bold;
}
.infoBox {
    padding: 8px 16px;
    text-align: left;
}
</style>
<script>
$(document).ready(function()
{
    $('#buyForm').submit(function()
    {
        return confirm('{AreYouSure}');
    });
    $('#GoBack').click(function()
    {
        window.location = 'settings.php';
    });
});
</script>
<br />
<form action="?mode=nickchange" method="post" id="buyForm">
    <table width="750">
        <tr>
            <th colspan="6" class="darkEnergy">{DarkEnergyStatusIs}: <b class="{DarkEnergy_Color}">{DarkEnergy_Counter}</b><b style="float: right; padding-right: 6px;"><a href="galacticshop.php" class="orange">{BuyMoreDE}</a></b></th>
        </tr>
        <tr>
            <td class="bl"></td>
        </tr>
        <tr>
            <td colspan="2" class="c">{ChangeYourNick}</td>
        </tr>
        <tr>
            <th colspan="2" class="infoBox">{NickChange_Info}</th>
        </tr>
        <tr>
            <th class="pad5">{NewNick}</th>
            <th class="pad5"><input type="text" class="pad3 w90p" name="newnick"/></th>
        </tr>
        <tr>
            <th colspan="2" class="pad5"><input type="submit" class="bold pad3 w90p lime" value="{ChangeIt}"/></th>
        </tr>
        <tr>
            <th colspan="2" class="pad5"><input type="button" class="bold pad3 w90p orange" value="{GoBack}" id="GoBack"/></th>
        </tr>
    </table>
</form>
