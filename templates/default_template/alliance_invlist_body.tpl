<style>
.act_del {
    background: url('./images/delete.png') no-repeat 0pt 1pt;
    padding-left: 12px;
    cursor: pointer;
}
</style>
<script>
var JSLang = {'DeleteInvite': '{Ally_INVList_ActionDel}'};
$(document).ready(function()
{
    $('.act_del').tipTip({content: JSLang['DeleteInvite'], delay: 250, edgeOffset: 9, defaultPosition: 'right'});
});
</script>
<br/>
<table width="700">
    {MsgBox}
    <tr>
        <td colspan="5" class="c"><b class="fl">{Ally_INVList_Head}</b><b class="fr">(<a href="alliance.php">&#171; {GoBack}</a>)</b></td>
    </tr>
    <tr>
        <th style="width: 150px;">{Ally_INVList_UserOwner}</th>
        <th style="width: 150px;">{Ally_INVList_UserSender}</th>
        <th style="width: 150px;">{Ally_INVList_Date}</th>
        <th style="width: 140px;">{Ally_INVList_State}</th>
        <th style="width: 50px;">{Ally_INVList_Actions}</th>
    </tr>
    <tbody id="rows">{Insert_Rows}</tbody>
</table>
