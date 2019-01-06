<style>
.wm1 {
    width: 150px;
}
.wm2 {
    width: 600px;
    padding: 10px;
}
.go {
    border: 1px solid #415680;
    padding: 5px 25px;
    margin: 0px 10px;
}
.inv {
    visibility: hidden;
}
</style>
<br />
<table width="750">
    <tr class="{TraderMsg_Hide}">
        <th class="pad5 {TraderMsg_Color}" colspan="2">{TraderMsg_Text}&nbsp;</th>
    </tr>
    <tr class="inv"><td style="font-size: 5px;">&nbsp;</td></tr>
    <tr>
        <td class="c center" colspan="2">{Trader_Title}<td>
    </tr>
    <tr>
        <th colspan="2" class="pad5" style="text-align: left;"><span class="fl">{Trader_UsesLeft}: <b class="{Insert_TraderUsesColor}">{Insert_TraderUses}</b></span><span class="fr">{Insert_TraderRight}</span></th>
    </tr>
    <tr>
        <th class="pad5 wm1">{Trader_DoSell}</th>
        <th class="pad10 wm2">
            <a href="?step=2&amp;mode=1&amp;res=1" class="go {Insert_AddRed}">{Metal}</a>
            <a href="?step=2&amp;mode=1&amp;res=2" class="go {Insert_AddRed}">{Crystal}</a>
            <a href="?step=2&amp;mode=1&amp;res=3" class="go {Insert_AddRed}">{Deuterium}</a>
        </th>
        </tr>
    <tr>
        <th class="pad5 wm1">{Trader_DoBuy}</th>
        <th class="pad10 wm2">
            <a href="?step=2&amp;mode=2&amp;res=1" class="go {Insert_AddRed}">{Metal}</a>
            <a href="?step=2&amp;mode=2&amp;res=2" class="go {Insert_AddRed}">{Crystal}</a>
            <a href="?step=2&amp;mode=2&amp;res=3" class="go {Insert_AddRed}">{Deuterium}</a>
        </th>
    </tr>
</table>
