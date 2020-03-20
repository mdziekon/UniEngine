<style>
.techImg {
    border: 2px solid black;
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
function onQueuesFirstElementFinished () {
    window.setTimeout(function () {
        document.location.href = "buildings.php?mode=research";
    }, 500);
}

$(document).ready(function()
{
    $('.tReqDiv').tipTip({attribute: 'title', delay: 50});
});
</script>
<br />
<table width="650">
    <tbody style="{Input_HideNoResearch}">
        <tr>
            <th colspan="3" class="pad5 red">{labo_on_update}</th>
        </tr>
        <tr>
            <td style="font-size: 1px;">&nbsp;</td>
        </tr>
    </tbody>
    {Insert_QueueInfo}
    {Data_QueueComponentHTML}
    <tr>
        <th>{ResearchTitle}</th>
        <th>&nbsp;</th>
        <th style="width: 80px;">&nbsp;</th>
    </tr>
    {technolist}
</table>
