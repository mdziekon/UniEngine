{Insert_ChronoApplets}
<script>
var JSLang = {'SFB_Help_PostEndTime': '{SFB_Help_PostEndTime}', 'SFB_Help_EndTime': '{SFB_Help_EndTime}', 'SFB_Confirm_Cancel': '{SFB_Confirm_Cancel}'};
</script>
<script src="../dist/js/admin/SFBSettings_body_overview.cachebuster-1545956361123.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="../dist/css/admin/SFBSettings_body_overview.cachebuster-1546567692327.min.css" />
<br/>
<table style="width: 1050px;">
    <tbody class="{Insert_HideMsgBox}">
        <tr>
            <th class="pad5 {Insert_MsgBoxColor}" colspan="9">{Insert_MsgBoxText}</th>
        </tr>
        <tr>
            <th class="inv" style="font-size: 2px;">&nbsp;</th>
        </tr>
    </tbody>
    <tr>
        <td class="c pad5" colspan="9">
            <span class="fl">{SFB_Header_Active}</span>
            <span class="fr"><a href="?cmd=add" style="background: url('../images/chglog_add.png') no-repeat 0px 0px; padding-left: 18px;">{SFB_Action_AddBlockade}</a></span>
        </td>
    </tr>
    {Insert_ActiveList}
    <tr>
        <td class="c pad5" colspan="9">{SFB_Header_Log}</td>
    </tr>
    {Insert_InActiveList}
</table>
