<script>
var JSLang =
{
    'CMD_DelAll': '{CMD_DelAll}',
    'CMD_Delete_Title': '{CMD_Delete_Title}',
    'CMD_Show_Users_Title': '{CMD_Show_Users_Title}',
    'CMD_Show_MainUsers_Title': '{CMD_Show_MainUsers_Title}'
};
var CurrentPage = {CurrentPage};
</script>
<script type="text/javascript" src="../dist/js/admin/alertslist_body.cachebuster-1546739003831.min.js"></script>
<link rel="stylesheet" type="text/css" href="../dist/css/admin/alertslist_body.cachebuster-1546567692327.min.css" />
<br />
<table width="1000">
    {System_MSG}
    <tr>
        <td class="c" colspan="7"><span class="fl">{Alerts_List} (<a href="alerts_filters.php" class="orange">{CMD_GoToFiltersList}</a>)</span><span class="fr">[<a href="?deleteall=yes" class="red" id="CMD_DelAll">{Cmd_Deleteall}</a>]</span></td>
    </tr>
    <tr{HidePaginRow}>
        <th colspan="7">
            <form action="" method="post" class="pageForm">
                <b style="float: left;">
                    {PerPage}
                    <select class="perPage">
                        <option value="5" {perpage_select_5}>5</option>
                        <option value="10" {perpage_select_10}>10</option>
                        <option value="15" {perpage_select_15}>15</option>
                        <option value="20" {perpage_select_20}>20</option>
                        <option value="25" {perpage_select_25}>25</option>
                        <option value="50" {perpage_select_50}>50</option>
                        <option value="75" {perpage_select_75}>75</option>
                        <option value="100" {perpage_select_100}>100</option>
                    </select>
                </b>
                {Pagination}
            </form>
        </th>
    </tr>
    <tr>
        <th width="25px">{Alert_ID}</th>
        <th width="120px">{Alert_Origin}</th>
        <th width="100px">{Alert_Type}</th>
        <th width="40px">{Alert_Importance}</th>
        <th width="600px">{Alert_Data}</th>
        <th width="75px">{Alert_Status}</th>
        <th width="20px">&nbsp;</th>
    </tr>
        {Rows}
    <tr{HidePaginRow}>
        <th colspan="7">
            <form action="" method="post" class="pageForm">
                <b style="float: left;">
                    {PerPage}
                    <select class="perPage">
                        <option value="5" {perpage_select_5}>5</option>
                        <option value="10" {perpage_select_10}>10</option>
                        <option value="15" {perpage_select_15}>15</option>
                        <option value="20" {perpage_select_20}>20</option>
                        <option value="25" {perpage_select_25}>25</option>
                        <option value="50" {perpage_select_50}>50</option>
                        <option value="75" {perpage_select_75}>75</option>
                        <option value="100" {perpage_select_100}>100</option>
                    </select>
                </b>
                {Pagination}
            </form>
        </th>
    </tr>
</table>
