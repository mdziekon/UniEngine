<style>
.button {
    width: 150px;
    font-weight: 700;
    padding: 3px;
}
</style>
<script>
var $_MaxLength = {Insert_MaxLength};
$(document).ready(function()
{
    $('#text').keydown(function()
    {
        var TextLength = $(this).val().length;
        if(TextLength > $_MaxLength)
        {
            $(this).val($(this).val().substr(0, $_MaxLength));
            TextLength = $_MaxLength;
        }
        $('#cntChars').html(TextLength);
    }).keyup(function()
    {
        $(this).keydown();
    }).change(function()
    {
        $(this).keydown();
    });

    $('#text').keydown();
});
</script>
<br/>
<form action="alliance.php?mode=apply&allyid={allyid}" method="post">
    <input type="hidden" name="send" value="yes"/>
    <table width="600">
        <tr>
            <td class="c" colspan="2">{Write_to_alliance}</td>
        </tr>
        <tr>
            <th style="width: 150px; padding: 2px;">
                {AApp_Message}
                <br />
                (<span id="cntChars">0</span> / {Insert_MaxLength} {AApp_Chars})
            </th>
            <th class="pad2">
                <textarea id="text" name="text" style="padding: 3px; width: 95%; height: 150px;">{text_apply}</textarea>
            </th>
        </tr>
        <tr>
            <th class="pad2">{AApp_Help}</th>
            <th><input type="submit" name="action" value="{AApp_UseExample}" class="pad2"/></th>
        </tr>
        <tr>
            <th colspan="2"><input type="submit" name="action" value="{AApp_Send}" class="button"/></th>
        </tr>
    </table>
</form>
