<style>
.act_accept {
    background: url('./images/tick.green.png') no-repeat 0px -0.5px;
    padding-left: 16px;
}
.act_reject {
    background: url('./images/delete.png') no-repeat 0px 1px;
    padding-left: 12px;
}
.act_accept, .act_reject {
    cursor: pointer;
    margin: 0px 5px;
}
</style>
<script>
var JSLang = {'AcceptInvite': '{AMenu_Inv_Accept}', 'RejectInvite': '{AMenu_Inv_Reject}'};
$(document).ready(function()
{
    $('.act_accept').tipTip({content: JSLang['AcceptInvite'], delay: 250, edgeOffset: 9});
    $('.act_reject').tipTip({content: JSLang['RejectInvite'], delay: 250, edgeOffset: 9});
});
</script>
<br/>
<table width="650">
    <tr>
        <td class="c" colspan="2">{Ally}</td>
    </tr>
    <tr>
        <th class="pad5"><a href="?mode=make">{AMenu_MakeAlly}</a></th>
        <th class="pad5"><a href="?mode=search">{AMenu_LookFor}</a></th>
    </tr>
</table>

<br/>
<table width="650">
    {MsgBox}
    <tr>
        <td colspan="4" class="c">{AMenu_Inv_Head}</td>
    </tr>
    <tr>
        <th style="width: 250px;">{AMenu_Inv_Sender}</th>
        <th style="width: 150px;">{AMenu_Inv_Date}</th>
        <th style="width: 140px;">{AMenu_Inv_State}</th>
        <th style="width: 100px;">{AMenu_Inv_Actions}</th>
    </tr>
    <tbody id="rows">{Insert_InviteRows}</tbody>
</table>
