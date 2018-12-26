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

    $('#clear').click(function()
    {
        $('#text').val('').keydown();
        return false;
    });
});
</script>
<br/>
<form action="?mode=sendmsg&send=1" method="post">
    <table style="width: 650px;">
        {MsgBox}
        <tr>
            <td class="c" colspan="2"><b class="fl">{Ally_WR_Title}</b><b class="fr">(<a href="alliance.php">&#171; {GoBack}</a>)</b></td>
        </tr>
        <tr>
            <th style="width: 150px;">{Ally_WR_SendTo}</th>
            <th class="pad2">
                <select name="rank_select" style="width: 90%;" class="pad2">
                    <option value="all" {SelectedAll}>{Ally_WR_Every1}</option>
                    <option value="-">----------</option>
                    {OtherRanks}
                </select>
            </th>
        </tr>
        <tr>
            <th class="pad2">
                {Ally_WR_Text}<br />
                (<span id="cntChars">0</span> / {Insert_MaxLength} {Ally_WR_Chars})<br />
                (<a href="#" id="clear">{Ally_WR_Clear}</a>)
            </th>
            <th>
                <textarea id="text" name="text" class="pad2" style="width: 90%; height: 150px;">{PutMessage}</textarea>
            </th>
        </tr>
        <tr>
            <th class="pad2" colspan="2"><input type="submit" value="{Ally_WR_Button}" class="pad2" style="width: 150px; font-weight: 700;"/></th>
        </tr>
    </table>
</form>
