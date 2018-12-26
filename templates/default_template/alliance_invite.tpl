<style>
.inp {
    width: 90%;
    padding: 3px;
}
</style>
<script>
var $_MaxLength = {Insert_MaxLength};
$(document).ready(function()
{
    $('[name="username"]').data('oldVal', $('[name="username"]').val());

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

    $('#thisForm').submit(function()
    {
        if($('[name="username"]').val() != $('[name="username"]').data('oldVal'))
        {
            $('[name="unamechanged"]').val('1');
        }
    });
});
</script>
<br/>
<form action="?mode=invite&send=1" method="post" id="thisForm">
    <input type="hidden" name="uid" value="{Insert_UID}"/>
    <input type="hidden" name="unamechanged" value=""/>
    <table style="width: 650px;">
        {MsgBox}
        <tr>
            <td class="c" colspan="2"><b class="fl">{Ally_INV_Header}</b><b class="fr">(<a href="alliance.php">&#171; {GoBack}</a>)</b></td>
        </tr>
        <tr>
            <th style="width: 150px;">{Ally_INV_Username}</th>
            <th class="pad2"><input type="text" name="username" value="{Insert_Username}" class="inp" {Insert_LockUsername}/></th>
        </tr>
        <tr>
            <th class="pad2">
                {Ally_INV_Text}<br />
                ({Ally_INV_Length}: <span id="cntChars">0</span> / {Insert_MaxLength})<br />
                (<a href="#" id="clear">{Ally_INV_Clear}</a>)
            </th>
            <th>
                <textarea id="text" name="text" class="inp" style="height: 150px;">{Insert_Text}</textarea>
            </th>
        </tr>
        <tr>
            <th class="pad2" colspan="2"><input type="submit" value="{Ally_INV_Submit}" class="pad2 lime" style="width: 150px; font-weight: 700;"/></th>
        </tr>
    </table>
</form>
