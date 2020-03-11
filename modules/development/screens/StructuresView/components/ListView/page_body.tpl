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

    $(document).ready(function () {
        $('.tReqDiv').tipTip({attribute: 'title', delay: 50});
    });
    </script>
    <br/>
    <table width="650">
        {PHPInject_QueueHTML}
        <tr>
            <th>{BuildingsListView_ListTitle}</th>
            <th>
                <span class="lime">{Insert_Overview_Fields_Used}</span>
                /
                <span class="red">{Insert_Overview_Fields_Max}</span>
                [{BuildingsListView_FieldsLeft}: {Insert_Overview_Fields_Available}]
            </th>
            <th style="width: 100px;">&nbsp;</th>
        </tr>
        {PHPInject_ElementsListHTML}
    </table>
    <br/>
