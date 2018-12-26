<script>
$(document).ready(function()
{
    $('.chgSub').change(function()
    {
        $('#cvForm').submit();
    });
});
</script>
<center>
    <table width="100%">
        <tr>
            <td class="c pad2 center" colspan="2">{Title}</td>
        </tr>
        <form action="" method="post" id="cvForm">
            <tr>
                <th class="pad5" style="width: 40%;">{Set_ColorTheme}</th>
                <th class="pad5"><select class="chgSub" name="colorTheme" style="padding: 2px; width: 100%;"><option value="1" {Set_ColorTheme_1_Check}>{Set_ColorTheme_1}</option><option value="2" {Set_ColorTheme_2_Check}>{Set_ColorTheme_2}</option></select></th>
            </tr>
        </form>
        <tr>
            <th class="pad5" colspan="2">
                <textarea style="width: 100%; height: 180px;" onclick="this.select(); this.execCommand('copy');">{ReportCode}</textarea>
            </th>
        </tr>
    </table>
</center>
