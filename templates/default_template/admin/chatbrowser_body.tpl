<script>
var JSLang =
{
    'JSAlert_NothingSelected': '{JSAlert_NothingSelected}',
    'JSTip_SelectAll': '{JSTip_SelectAll}',
    'JSTip_DeleteID': '{JSTip_DeleteID}'
};
</script>
<script src="../dist/js/admin/chatbrowser_body.cachebuster-1545956361123.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="../dist/css/admin/chatbrowser_body.cachebuster-1546567692327.min.css" />
<br/>
<form id="thisForm" action="?rid={Insert_RoomID}&amp;page={Insert_ThisPage}" method="post">
    <input type="hidden" name="sent" value="1"/>
    <input type="hidden" name="cmd" value=""/>
    <table style="width: 1000px;">
        <tr>
            <td class="c center" colspan="5">{Page_Title}</td>
        </tr>
        {Insert_MsgBox}
        <tr {Insert_HideOnNoMessages}>
            <th class="pagination" colspan="5">{Insert_Pagination}</th>
        </tr>
        <tr {Insert_HideOnNoMessages}>
            <th class="checkBox">
                <input type="checkbox" class="SelectAll_Page"/>
            </th>
            <th class="pad2" colspan="4">
                <input type="button" class="cmd_DelSelected button red" value="{CMD_DeleteSelected}"/>
            </th>
        </tr>
        <tbody id="Messages">{Insert_ChatRows}</tbody>
        <tr {Insert_HideOnNoMessages} {Insert_HideOnFewMessages}>
            <th class="checkBox">
                <input type="checkbox" class="SelectAll_Page"/>
            </th>
            <th class="pad2" colspan="4">
                <input type="button" class="cmd_DelSelected button red" value="{CMD_DeleteSelected}"/>
            </th>
        </tr>
        <tr {Insert_HideOnNoMessages} {Insert_HideOnFewMessages}>
            <th class="pagination" colspan="5">{Insert_Pagination}</th>
        </tr>
    </table>
</form>
