<style>
    .m10 {
        margin-left: 10px;
    }
    .moon {
        background: url('images/moon.png') no-repeat 0pt 0pt;
    }
    .planet {
        background: url('images/planet.png') no-repeat 0pt 0pt;
    }
    .moon, .planet {
        padding-left: 15px;
        margin-left: 5px;
        cursor: help;
    }
    .hide {
        display: none;
    }
    .lalign {
        text-align: left;
        padding: 5px;
        padding-left: 10px;
    }
    .fsmall {
        font-size: 1px;
    }
    .yellow {
        color: yellow !important;
    }
    .darkorange {
        color: #FF8B3D !important;
    }
    .maroon {
        color: #D70000 !important;
    }
</style>
<script>
$(document).ready(function()
{
    $('.planet').tipTip({delay: 0, edgeOffset: 8, content: '{Bash_CordPlanet}'});
    $('.moon').tipTip({delay: 0, edgeOffset: 8, content: '{Bash_CordMoon}'});
});
</script>
<br />
<table width="650">
    <tr>
        <th colspan="3" class="pad5">{Bash_Desc}</th>
    </tr>
    <tr>
        <td style="visilibity: hidden;"></td>
    </tr>
    <tr>
        <td class="c" colspan="3">{Bash_Header}</td>
    </tr>
    <tr {PHP_HideNoAttacks}>
        <th class="orange" colspan="3">{Bash_NoAttacksToday}</th>
    </tr>
    <tr {PHP_HideRecords}>
        <th style="width: 275px">{Bash_Username}</th>
        <th style="width: 100px">{Bash_BashCount}</th>
        <th style="width: 275px">{Bash_Status}</th>
    </tr>
    {Rows}
</table>
