<script>
var ShowElementOnStartup = '';
var RunQueueHandler = 'false';
var JSLang = {
    Queue_Cancel_Go: "{Queue_Cancel_Go}",
    Queue_CantCancel_Premium: "{Queue_CantCancel_Premium}",
    Queue_ConfirmCancel: "{Queue_ConfirmCancel}"
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
<script src="dist/js/structures.cachebuster-1569443741057.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="dist/css/structures.cachebuster-1546565145290.min.css" />

<br/>
<table width="650">
    {PHPInject_Queue}
    <tr>
        <th>{BuildingsLegacy_ListTitle}</th>
        <th>
            <span class="lime">{Insert_Overview_Fields_Used}</span> / <span class="red">{Insert_Overview_Fields_Max}</span>
            [{BuildingsLegacy_FieldsLeft}: {Insert_Overview_Fields_Available}]
        </th>
        <th style="width: 100px;">&nbsp;</th>
    </tr>
    {PHPInject_ElementsListHTML}
</table>
<br/>
