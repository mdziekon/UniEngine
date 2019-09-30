<script>
var JSLang = {

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

</script>
<link rel="stylesheet" type="text/css" href="dist/css/structures.cachebuster-1546565145290.min.css" />

<!--
    language values to change:

    bld_usedcells
    bld_theyare
    bld_cellfree
 -->

<br/>
<table width="650">
    {PHPInject_Queue}
    <tr>
        <th>{bld_usedcells}</th>
        <th>
            <span class="lime">{Insert_Overview_Fields_Used}</span> / <span class="red">{Insert_Overview_Fields_Max}</span>
            [{bld_theyare} {Insert_Overview_Fields_Available} {bld_cellfree}]
        </th>
        <th style="width: 100px;">&nbsp;</th>
    </tr>
    {PHPInject_ElementsListHTML}
</table>
<br/>
