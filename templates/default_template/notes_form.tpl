<script>
var MaxNoteLength = {Input_MaxNoteLength};
</script>
<script src="dist/js/notes_form.cachebuster-1545956361123.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="dist/css/notes_form.cachebuster-1546564327123.min.css" />
<br/>
<form id="thisForm" action="{Input_InsertCMD}" method="post">
    <input type="hidden" name="send" value="1"/>
    <table style="width: 600px;">
        <tr>
            <td class="c center" colspan="2">{Input_InsertTitle}</td>
        </tr>
        <tr>
            <th class="pad2 tRight">{Headers_Title}</th>
            <th class="pad2"><input type="text" name="title" class="pad3 w95p tLeft" maxlength="{Input_MaxTitleLength}" value="{Input_Title}"/></th>
        </tr>
        <tr>
            <th class="pad2 tRight">{Headers_Priority}</th>
            <th class="pad2">
                <select name="priority" class="w95p pad3">
                    <option class="lime" value="1" {Input_PrioritySelect_1}>{Priority_1}</option>
                    <option class="orange" value="2" {Input_PrioritySelect_2}>{Priority_2}</option>
                    <option class="red" value="3" {Input_PrioritySelect_3}>{Priority_3}</option>
                </select>
            </th>
        </tr>
        <tr>
            <th class="pad2 tRight">{Headers_Text}<br/>({Headers_Limit}: <span id="noteLength">0</span> / {Input_MaxNoteLength})</th>
            <th class="pad2"><textarea name="text" class="pad3 w95p high">{Input_Text}</textarea></th>
        </tr>
        <tr>
            <th class="pad5" colspan="2">
                <input type="button" class="button orange" id="goBack" value="{Action_Cancel}"/>
                <input type="submit" class="button lime" value="{Action_Save}"/>
            </th>
        </tr>
        <tbody class="{Input_HideMsgBox}">
            <tr class="inv">
                <th>&nbsp;</th>
            </tr>
            <tr>
                <th class="pad5 {Input_MsgColor}" colspan="2">{Input_MsgText}</th>
            </tr>
        </tbody>
    </table>
</form>
