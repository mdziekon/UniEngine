<style>
.buildImg {
    border: 2px solid #000;
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
var JSLang = {
    'Queue_CantCancel_Premium': '{Queue_CantCancel_Premium}',
    'Queue_ConfirmCancel': '{Queue_ConfirmCancel}',
    'Queue_Cancel_Go': '{Queue_Cancel_Go}'
};

function onQueuesFirstElementFinished () {
    $("#QueueCancel")
        .html(JSLang['Queue_Cancel_Go'])
        .attr("href", "buildings.php")
        .removeClass("cancelQueue")
        .addClass("lime");

    window.setTimeout(function () {
        document.location.href = "buildings.php";
    }, 1000);
}

$(document).ready(function()
{
    $('.tReqDiv').tipTip({attribute: 'title', delay: 50});

    $('.cancelQueue').click(function () {
        if (!$(this).hasClass('cancelQueue')) {
            return true;
        }

        if ($(this).hasClass('premblock')) {
            alert(JSLang['Queue_CantCancel_Premium']);
            return false;
        } else {
            return confirm(JSLang['Queue_ConfirmCancel']);
        }
    });
});
</script>
<br/>
{BuildListScript}
<table width="650">
    {BuildList}
    <tr>
        <th>{bld_usedcells}</th>
        <th>
            <span class="lime">{planet_field_current}</span> / <span class="red">{planet_field_max}</span> [{bld_theyare} {field_libre} {bld_cellfree}]
        </th>
        <th style="width: 100px;">&nbsp;</th>
    </tr>
    {BuildingsList}
</table>
<br/>
