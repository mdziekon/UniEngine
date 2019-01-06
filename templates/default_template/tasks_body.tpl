<script>
if('{SetActiveTab}' != '')
{
    var OverrideTab = '{SetActiveTab}';
}
else
{
    var OverrideTab = 'list';
}
if('{SetActiveMode}' != '')
{
    var AddLocationMode = 'mode={SetActiveMode}&';
}
else
{
    var AddLocationMode = '';
}
var SetActiveTask = '{SetActiveTask}';
var SkipConfirmText = '{Tab01_CatSel_SkipConfirm}';
</script>
<style>
.taskTW
{
    width: {Input_TaskTabWidth}px;
}
</style>
<script src="dist/js/tasks.cachebuster-1545956361123.min.js"></script>
<link rel="stylesheet" type="text/css" href="dist/css/tasks.cachebuster-1546564327123.min.css" />

<table width="800" style="margin-top: 5px;">
    <tr {MsgBox_Hide}>
        <th class="pad5 {MsgBox_Colo}" colspan="2">{MsgBox_Text}</th>
    </tr>
    <tr class="inv">
        <td style="font-size: 4px;">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" class="c">{PageTitle}</td>
    </tr>
    <tr>
        <th class="pad5 tab" id="tab_list">{Tabs_ActiveTasks}</th>
        <th class="pad5 tab" id="tab_log">{Tabs_TasksLog}</th>
    </tr>
</table>
<table width="800">
{PageBody}
</table>
