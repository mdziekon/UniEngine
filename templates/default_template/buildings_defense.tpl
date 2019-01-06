<style>
.defImg {
    border: 2px solid black;
}
.input {
    text-align: center;
    width: 80px;
}
.tReqDiv {
    float: left;
    margin-right: 2px;
    cursor: help;
}
.tReqImg {
    width: 42px;
    height: 42px;
    border: 1px solid #000;
}
.tReqBg {
    background: #000;
}
</style>
<script>
$(document).ready(function()
{
    $('.tReqDiv').tipTip({attribute: 'title', delay: 50});

    $('[name^="fmenge"]').focus(function(){
        if($(this).val() == '0'){
            $(this).val('');
        }
    }).blur(function(){
        if($(this).val() == ''){
            $(this).val('0');
        }
    });
});
function setMax(id, max){
    limit = {QueueSize};
    if(max > limit){
        max = limit;
    }
    $('#'+id).val(max);
}
</script>
<br />
<form action="buildings.php?mode=defense" method="post">
    <table width="650">
        <tr>
            <th>{Defense}</th>
            <th>&nbsp;</th>
            <th style="width: 80px;">&nbsp;</th>
        </tr>
        {buildlist}
        <tr>
            <td class="c pad5" colspan="3" align="center"><input style="font-weight: 700; padding: 2px; width: 100%;" type="submit" value="{Construire}"/></td>
        </tr>
    </table>
</form>
{buildinglist}
