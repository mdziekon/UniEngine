<script>
var JS_Lang = new Object;
var SpyExpanded = {SpyExpanded};
JS_Lang['mess_convert_title']       = '{mess_convert_title}';
JS_Lang['mess_report_e_title']      = '{mess_report_e_title}';
JS_Lang['mess_delete_single_title'] = '{mess_delete_single_title}';
JS_Lang['mess_reply_title']         = '{mess_reply_title}';
JS_Lang['mess_replyally_title']     = '{mess_replyally_title}';
JS_Lang['mess_ignore_title']        = '{mess_ignore_title}';
JS_Lang['mess_report_title']        = '{mess_report_title}';
JS_Lang['mess_selectall_title']     = '{mess_selectall_title}';
JS_Lang['Sure_WantDeleteAll']       = '{Sure_WantDeleteAll}';
JS_Lang['Sure_WantDeleteCat']       = '{Sure_WantDeleteCat}';
var DynamicCode = new Object;
DynamicCode['collapse'] = '<span class="cpe">{Action_Collapse}<img src="./images/collapse.png" class="thImg"/></span>';
DynamicCode['loading'] = '<span class="load">{Action_ThreadLoading}<img src="./images/ajax-loader.gif" class="thImg"/></span>';
DynamicCode['error'] = '<span class="therr red"><img src="./images/delete.png" class="thImg"/> {Action_ThreadError}</span>';
DynamicCode['sBR'] = '<tr><th class="sBR" colspan="3">&nbsp;</th></tr>';
</script>
<style>
/* Buttons Declarations */
.sth:not(.nohide) {
    {SpyDisplay}
}
</style>
<script src="dist/js/messages.cachebuster-1545956361123.min.js"></script>
<link rel="stylesheet" type="text/css" href="dist/css/messages.cachebuster-1546565145290.min.css" />
<form id="msg_form" action="messages.php" method="post" style="margin-top: 5px; margin-bottom: 0px;">
    <input name="time" value="{InsertTimestamp}" type="hidden"/>
    <input name="category" value="{MessCategory}" type="hidden"/>
    <input name="page" value="{ThisPage}" type="hidden"/>
    <input name="delid" id="delid" type="hidden" value=""/>
    <table width="750">
        {MsgBox}
        <tr>
            <td class="c" colspan="2">
                <a href="messages.php" title="{GoBackToCatList}">{title}</a> &#187; <a style="color: {MessCategoryColor}" href="messages.php?mode=show&amp;messcat={MessCategory}">{SelectedCat}</a>
            </td>
        </tr>
        <tr{Hide_headers}{Hide_AdminMsg}>
            <th style="padding: 7px; width: 20%;">{mess_actions}</th>
            <th style="width: 78%;">
                <select class="fwb delMsgSel">
                    <option value="deletemarked">{mess_deletemarked}</option>
                    <option value="deleteunmarked">{mess_deleteunmarked}</option>
                    <option value="deleteallcat" {show_delete_all_cat}>{mess_deleteallcat}</option>
                    <option value="deleteall">{mess_deleteall}</option>
                    <option value="setcatread" {show_delete_all_cat}>{mess_setcatasread}</option>
                    <option value="setallread">{mess_setallasread}</option>
                </select>
                <input class="fwb" value="{mess_perform_action}" type="submit"/>
            </th>
        </tr>
        <tr{Hide_NoActions}>
            <th style="padding: 7px;" class="red" colspan="2">{NoActionsHere}</th>
        </tr>
        {Pagination}
    </table>
    <table width="750" id="msgCont">
        <tr{Hide_headers}>
            <td class="c center" style="width: 20px;"><input type="checkbox" class="selectAll"/></td>
            <td class="c center" style="width: 364px;">{mess_from}</td>
            <td class="c center" style="width: 364px;">{mess_subject}</td>
        </tr>
        <tr{Hide_headers}>
            <td class="inv"></td>
        </tr>
        {content}
        <tr{Hide_headers}>
            <td class="inv"></td>
        </tr>
        <tr{Hide_headers}>
            <td class="c center"><input type="checkbox" class="selectAll"/></td>
            <td class="c" colspan="2">&nbsp;</td>
        </tr>
    </table>
    <table width="750">
        {Pagination}
        <tr{Hide_headers}{Hide_AdminMsg}>
            <th style="padding: 7px; width: 20%;">{mess_actions}</th>
            <th style="width: 78%;">
                <select class="fwb delMsgSel" name="deletemessages">
                    <option value="deletemarked">{mess_deletemarked}</option>
                    <option value="deleteunmarked">{mess_deleteunmarked}</option>
                    <option value="deleteallcat" {show_delete_all_cat}>{mess_deleteallcat}</option>
                    <option value="deleteall">{mess_deleteall}</option>
                    <option value="setcatread" {show_delete_all_cat}>{mess_setcatasread}</option>
                    <option value="setallread">{mess_setallasread}</option>
                </select>
                <input class="fwb" value="{mess_perform_action}" type="submit" />
            </th>
        </tr>
    </table>
</form>
