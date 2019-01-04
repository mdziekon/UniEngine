<script>
var JSLang = {'JS_Confirm': '{JS_Confirm}'};
</script>
<script src="dist/js/notes_body.cachebuster-1545956361123.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="dist/css/notes_body.cachebuster-1546564327123.min.css" />
<br/>
<form id="thisForm" action="?cmd=delete" method="post">
    <table style="width: 600px;">
        <tr>
            <td class="c center" colspan="3">{Title}</td>
        </tr>
        <tr>
            <th class="pad5" colspan="3"><a href="?cmd=add" style="background: url('images/chglog_add.png') no-repeat; padding-left: 18px;">{Link_AddNote}</a></th>
        </tr>
        <tr>
            <td class="c pad2 center" style="width: 25px;"><input type="checkbox" id="selAll" title="{Info_SelectAll}"/></td>
            <td class="c pad2" style="width: 400px;">{Headers_Title}</td>
            <td class="c pad2" style="width: 165px;">{Headers_Date}</td>
        </tr>
        <tr class="{Input_HideAtNotes}">
            <th class="pad2 orange" colspan="3">{Info_NoRows}</th>
        </tr>
        {Input_NotesList}
        <tr class="{Input_HideAtNoNotes}{Input_HideAtNoPagination}">
            <th class="pad7" colspan="3">{Input_Pagination}</th>
        </tr>
        <tr class="{Input_HideAtNoNotes}">
            <th class="pad5" colspan="3">
                <span class="fl">
                    <select name="action" class="pad2" style="width: 250px;">
                        <option value="1">{Action_DeleteSelected}</option>
                        <option value="2">{Action_DeleteAll}</option>
                    </select>
                </span>
                <span class="fr">
                    <input type="submit" value="{Action_Submit}" class="pad2" style="width: 100px; font-weight: 700;">
                </span>
            </th>
        </tr>
        <tbody class="{Input_HideMsgBox}">
            <tr class="inv">
                <th>&nbsp;</th>
            </tr>
            <tr>
                <th class="pad5 {Input_MsgColor}" colspan="3">{Input_MsgText}</th>
            </tr>
        </tbody>
    </table>
</form>
