<style>
.expand {
    background: url('images/expand.png') no-repeat 0pt 0pt;
}
.collapse {
    background: url('images/collapse.png') no-repeat 0pt 0pt;
}
.msg {
    background: url('images/msg.png') no-repeat 0pt 0pt;
}
.user {
    background: url('images/users.png') no-repeat 0pt 0pt;
}
.admin {
    background: url('images/edit.png') no-repeat 0pt 0pt;
}
.reqlist {
    background: url('images/request.png') no-repeat 0pt 0pt;
}
.shield {
    background: url('images/shield.png') no-repeat 0pt 0pt;
}
.invite {
    background: url('images/newmail.png') no-repeat 0pt 0pt;
}
.allychat {
    background: url('images/chat.png') no-repeat 2pt 2px;
}
.inviteuser {
    background: url('images/chglog_add.png') no-repeat 0pt 0pt;
}
.expand, .collapse {
    padding-left: 18px;
    cursor: pointer;
}
.msg, .user, .admin, .reqlist, .shield, .invite, .inviteuser, .allychat {
    padding-left: 20px;
    cursor: pointer;
}
.hide {
    display: none;
}
</style>
<script>
$(document).ready(function()
{
    $('.collapsed').hide();
    $('.ceswitch').click(function()
    {
        if($(this).hasClass('collapse')){
            $('.'+$(this).parent().attr('id')+':not(.hide)').hide();
            $(this).removeClass('collapse');
            $(this).addClass('expand');
        } else if($(this).hasClass('expand')){
            $('.'+$(this).parent().attr('id')+':not(.hide)').show().removeClass('collapsed');
            $(this).removeClass('expand');
            $(this).addClass('collapse');
        }
    }).tipTip({maxWidth: 'auto', content: '{AFP_CollExp}', defaultPosition: 'right', delay: 500, edgeOffset: 8});
    $('#lvForm').submit(function()
    {
        return confirm('{AFP_SureWantLeave}');
    });
});
</script>
<br/>
<table width="650">
    <tr>
        <td class="c" colspan="2">{AFP_YouAlly}</td>
    </tr>
    <tr>
        <th width="300px">{AFP_Name}</th>
        <th>{ally_name}</th>
    </tr>
    <tr>
        <th>{AFP_Tag}</th>
        <th>{ally_tag}</th>
    </tr>
    <tr>
        <th>{AFP_Members}</th>
        <th>{ally_members}{members_list}</th>
    </tr>
    <tr>
        <th>{AFP_YourRank}</th>
        <th>{ally_user_rank}{alliance_admin}</th>
    </tr>
    <tr>
        <th>{AFP_WebPage}</th>
        <th>{ally_web}</th>
    </tr>

    <tr {HideFunctions}>
        <td class="c" colspan="2" id="funct_box"><b class="ceswitch collapse">{AFP_MemberOptions}</b></th>
    </tr>
    <tr class="funct_box {HideShowMList}">
        <th colspan="2"><a href="?mode=mlist" class="user">{AFP_MembersList}</a></th>
    </tr>
    <tr class="funct_box {HideShowMList}">
        <th colspan="2"><a href="?mode=invlist" class="invite">{AFP_InvitesList}</a></th>
    </tr>
    <tr class="funct_box {HideInviteNewUser}">
        <th colspan="2"><a href="?mode=invite" class="inviteuser">{AFP_InviteUser}</a></th>
    </tr>
    <tr class="funct_box">
        <th colspan="2"><a href="?mode=pactslist" class="shield">{AFP_PactsList}</a></th>
    </tr>
    <tr class="funct_box {HideSendMail}">
        <th colspan="2"><a href="?mode=sendmsg" class="msg">{AFP_SendMail}</a></th>
    </tr>
    <tr class="funct_box {HideAllyChat}">
        <th colspan="2"><a href="chat.php?rid={Insert_ChatRoomID}" class="allychat">{AFP_AllyChat}</a></th>
    </tr>
    <tr class="funct_box {HideAllyAdmin}">
        <th colspan="2"><a href="?mode=admin" class="admin">{AFP_AllyAdmin}</a></th>
    </tr>
    <tr class="funct_box {HideLookReq}">
        <th colspan="2"><a href="?mode=admin&amp;edit=reqlist&amp;from=front" class="reqlist {RequestColor}">{AFP_ReqList} ({RequestCount})</a></th>
    </tr>

    {requests}

    {send_circular_mail}

    <tr>
        <td class="c" colspan="2" id="inn_box"><b class="ceswitch collapse">{AFP_InnerSec}</b></th>
    </tr>
    <tr class="inn_box">
        <td colspan="2" class="pad5 b center">{ally_text}</td>
    </tr>
    <tr>
        <td class="c" colspan="2" id="ext_box"><b class="ceswitch expand">{AFP_ExternalSec}</b></th>
    </tr>
    <tr class="ext_box collapsed">
        <td colspan="2" class="pad5 b center">{ally_description}</td>
    </tr>

    <tr {HideLeaveAlly}>
        <td class="c" colspan="2" id="leave_box"><b class="ceswitch expand">{AFP_LeaveAlly}</b></th>
    </tr>
    <tr class="leave_box collapsed">
        <th colspan="2">
            <form id="lvForm" action="?mode=exit" method="post" style="margin: 6px;">
                <input type="submit" style="font-weight: bold;" value="{AFP_LeaveButton}"/>
            </form>
        </th>
    </tr>
</table>
