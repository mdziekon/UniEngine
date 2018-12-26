<script>
$(document).ready(function()
{
    $('#thisForm').submit();
});
</script>
<style>
.hide {
    display: none;
}
</style>
<br/>
<table style="width: 500px;">
    <tr>
        <th class="pad5">{UserInfo}</th>
    </tr>
</table>
<form id="thisForm" action="{FilePath}" method="post">
{GenerateInputs}
</form>
