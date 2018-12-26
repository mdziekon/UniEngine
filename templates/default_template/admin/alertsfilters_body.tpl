<style>
.tipTitle, .true, .false, .dash {
    cursor: help;
}
.dash {
    border-bottom: 1px dashed;
}
.delete {
    background: url('../images/delete.png') 0pt 1pt no-repeat;
}
.edit {
    background: url('../images/edit.png') 0pt 0pt no-repeat;
}
.deleteB {
    background: url('../images/delete.png') 2pt 1.5pt no-repeat;
    padding-left: 16px;
    font-weight: bold;
}
.true {
    background: url('../images/tick.green.png') 0pt 0pt no-repeat;
}
.false {
    background: url('../images/tick.red.png') 0pt 0pt no-repeat;
}
.delete, .true, .false, .edit {
    padding-left: 16px;
}
.red {
    color: red;
}
.orange {
    color: orange;
}
.lime {
    color: lime;
}
.fatB {
    font-weight: bold;
    text-decoration: underline;
}
.pageForm {
    margin: 0px;
    padding: 6px;
}
.inv {
    visibility: hidden;
}
.hide {
    display: none;
}
</style>
<script>
$(document).ready(
function()
{
    var CurrentPage = {CurrentPage};
    $('.selectAll').tipTip({delay: 0, content: '{CMD_SelectAll}', edgeOffset: 10});
    $('.tipTitle').tipTip({delay: 0, maxWidth: "200px", attribute: 'title'});
    $('.delete').tipTip({delay: 0, content: '{CMD_Delete_Title}', edgeOffset: 10});
    $('.edit').tipTip({delay: 0, content: '{CMD_Edit_Title}', edgeOffset: 10});
    $('.false').tipTip({delay: 0, content: '{Disabled}', edgeOffset: 10});
    $('.true').tipTip({delay: 0, content: '{Enabled}', edgeOffset: 10});

    $('.info1').tipTip({delay: 0, content: '{Condition_1}', edgeOffset: 10});
    $('.info2').tipTip({delay: 0, content: '{Condition_2}', edgeOffset: 10});
    $('.info3').tipTip({delay: 0, content: '{Condition_3}', edgeOffset: 10});
    $('.info4').tipTip({delay: 0, content: '{Condition_4}', edgeOffset: 10});
    $('.info5').tipTip({delay: 0, content: '<center>{Condition_5}</center>', edgeOffset: 10});
    $('.info6').tipTip({delay: 0, content: '{Condition_6}', edgeOffset: 10});
    $('.info7').tipTip({delay: 0, content: '{Condition_7}', edgeOffset: 10});

    $('.pagin').click(function()
    {
        $(this).parent().attr('action', '?page='+$(this).attr('name').replace('goto_', '')).submit();
    });
    $('.perPage').change(function()
    {
        if(CurrentPage > 1){
            AddPageAction = 'page='+CurrentPage+'&';
        } else {
            AddPageAction = '';
        }
        getPerPage = $(this).val();
        $(this).parent().parent().attr('action', '?'+AddPageAction+'pp='+getPerPage).submit();
    });
    $('.selectAll').click(
    function()
    {
        $("input[name^='f'], .selectAll").attr('checked', $(this).attr('checked'));
    });
    $('.delete').click(function()
    {
        return confirm('{AreYouSure_Delete1}');
    });
    $('.deleteB').click(function()
    {
        var SelectedCount = 0;
        $('input[name^="f"]').each(function()
        {
            if($(this).is(':checked') === true){
                SelectedCount += 1;
            }
        });
        if(SelectedCount > 0){
            return confirm('{AreYouSure_DeleteSelected}');
        } else {
            alert('{Error_NothingSelected}');
            return false;
        }
    });
});
</script>
<br />
<table width="1000">
        {System_MSG}
    <tr>
        <td class="c" colspan="8"><span style="float: left;">{Filters_List} (<a href="?cmd=add">{Filters_GoAdd}</a>)</span><span style="float: right">(<a href="alerts_list.php" class="orange">{Cmd_GoToAlerts}</a>)</span></td>
    </tr>
    <tr{HidePaginRow}>
        <th colspan="8">
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
                    </select>
                </b>
                {Pagination}
            </form>
        </th>
    </tr>
    <tr>
        <th width="25px"><b class="{BlankCellFix}">&nbsp;</b><input class="selectAll {HideSelectors}" type="checkbox"/></th>
        <th width="30px">{Filter_ID}</th>
        <th width="70px">{Filter_Date}</th>
        <th width="25px">{Filter_Enabled}</th>
        <th width="200px">{Filter_ActionType}</th>
        <th width="550px">{Filter_Conditions}</th>
        <th width="50px">{Filter_UseCount}</th>
        <th width="50px">{Filter_Actions}</th>
    </tr>
    <form action="?cmd=delpost" method="post">
        {Rows}
    <tr class="{HideSelectors}">
        <th><input class="selectAll" type="checkbox"/></th>
        <th colspan="8">
            <input type="submit" value="{CMD_DeleteAll}" class="deleteB"/>
        </th>
    </tr>
    </form>
    <tr{HidePaginRow}>
        <th colspan="8">
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
                    </select>
                </b>
                {Pagination}
            </form>
        </th>
    </tr>
</table>
