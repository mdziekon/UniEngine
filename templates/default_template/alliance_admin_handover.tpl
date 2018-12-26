<style>
.fallBack {
    color: orange;
}
.handOver, .fallBack {
    font-weight: bold;
}
th {
    padding: 5px !important;
}
.inv {
    visibility: hidden;
}
.hide {
    display: none;
}
.red {
    color: red !important;
}
</style>
<script>
$(document).ready(function()
{
    var FallingBack = false;
    $('#handOverForm').submit(function()
    {
        if(FallingBack === false){
            if($('[name=new_owner]').val() != '-'){
                return confirm('{ADM_SureHandOver}');
            } else {
                alert('{ADM_NoUserSelect}');
                return false;
            }
        }
    });
    $('.fallBack').click(function()
    {
        FallingBack = true;
        $('#handOverForm').attr('action', '?mode=admin').submit();
    });
});
</script>
<br />
<form id="handOverForm" action="" method="post">
    <input type="hidden" name="send" value="yes"/>
    <table width="650">
        <tr class="{HideError}">
            <td class="c pad5 red" colspan="2">{ErrorText}</td>
        </tr>
        <tr class="inv {HideError}"><td></td></tr>
        <tr>
            <td class="c" colspan="2">{ADM_HandOverTitle}</td>
        </tr>
        <tr>
            <th width="150px">{ADM_SelectNewOwner}</th>
            <th>
                <select name="new_owner" style="text-align: center">
                    <option value="-">--- {ADM_SelectFirstOption} ---</option>
                    {UserList}
                </select>
            </th>
        </tr>
        <tr>
            <th colspan="2">
                <input type="submit" class="handOver" value="{ADM_DoHandOver}"/>
            </th>
        </tr>
        <tr>
            <th colspan="2">
                <input type="button" class="fallBack" value="{ADM_FallBack}"/>
            </th>
        </tr>
    </table>
</form>
