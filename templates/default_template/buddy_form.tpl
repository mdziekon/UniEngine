<script>
var MaxLength = {Insert_MaxLength};
</script>
<script src="dist/js/buddy_form.cachebuster-1545956361123.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="dist/css/buddy_list.cachebuster-1546565145290.min.css" />
<br/>
<form action="" method="post">
    <input type="hidden" name="send" value="1"/>
    <table style="width: 600px;">
        <tr>
            <td class="c center" colspan="2">{Headers_BuddyForm}
        </tr>
        <tr>
            <th class="pad5 tRight">{Headers_Nick}</th>
            <th class="pad5">{Insert_Username}</th>
        </tr>
        <tr>
            <th class="pad2 tRight">{Headers_Text}<br/>({Headers_Limit}: <span id="Length">0</span> / {Insert_MaxLength})</th>
            <th class="pad2"><textarea name="text" class="text pad2">{Insert_Text}</textarea></th>
        </tr>
        <tr>
            <th class="pad5" colspan="2">
                <input type="button" class="button orange" id="goBack" value="{Headers_Cancel}"/>
                <input type="submit" class="button lime" value="{Headers_Send}"/>
            </th>
        </tr>
        <tbody class="{Insert_MsgBoxHide}">
            <tr class="inv">
                <th>&nbsp;</th>
            </tr>
            <tr>
                <th class="pad5 {Insert_MsgBoxColor}" colspan="2">{Insert_MsgBoxText}</th>
            </tr>
        </tbody>
    </table>
</form>
