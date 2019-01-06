<style>
.img {
    vertical-align: text-top;
}
</style>
<script>
$(document).ready(function()
{
    $('[src*="chglog_add"]').tipTip({content: '<b>{TipTip_Add}</b>', delay: 0}).css('cursor', 'pointer');
    $('[src*="chglog_fix"]').tipTip({content: '<b>{TipTip_Fix}</b>', delay: 0}).css('cursor', 'pointer');
    $('[src*="chglog_upd"]').tipTip({content: '<b>{TipTip_Upd}</b>', delay: 0}).css('cursor', 'pointer');
    $('[src*="chglog_opt"]').tipTip({content: '<b>{TipTip_Opt}</b>', delay: 0}).css('cursor', 'pointer');
    $('[src*="chglog_chg"]').tipTip({content: '<b>{TipTip_Chg}</b>', delay: 0}).css('cursor', 'pointer');
});
</script>
<br />
<table width="800">
    <tbody>
        <tr>
            <td class="c" colspan="7">{ServerInfo}</td>
        </tr>
        {InfoTable}
    </tbody>
</table>
<br/>
<table width="800">
    <tbody>
        <tr>
            <td class="c" colspan="2">{Changelog}</td>
        </tr>
        <tr>
            <td class="c">{Version}</td>
            <td class="c">{Description}</td>
        </tr>
        {ChangesList}
    </tbody>
</table>
