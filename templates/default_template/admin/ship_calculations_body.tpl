<style>
.pad3 {
    padding: 3px !important;
}
.pad2 {
    padding: 2px !important;
}
.pad1 {
    padding: 1px !important;
}
.hide {
    display: none;
}
.break {
    width: 1px;
    font-size: 1px !important;
    padding: 1px !important;
}
.current > th {
    background-color: #5773A8 !important;
    border-color: #7189B7 !important;
}
.lime > th {
    color: lime;
}
.orange > th {
    color: orange;
}
.hover, .hoverV {
    background-color: #455C87;
    border-color: #526EA3;
}
.hoverTD {
    background-color: #2C4068 !important;
    border-color: #496292 !important;
}
.sortasc {
    background: url('../images/expand.png') no-repeat right 1pt;
}
.sortdesc {
    background: url('../images/collapse.png') no-repeat right 1pt;
}
.header {
    cursor: pointer;
}
</style>
<script>
$(document).ready(function()
{
    $('.ship').hover(
    function(){
        $(this).children('th').addClass('hover');
    },
    function(){
        $(this).children('th').removeClass('hover');
    });
    $('.ship').each(function()
    {
        var Idx = 1;
        $(this).children('th:not(.break)').each(function()
        {
            $(this).addClass('col'+Idx);
            Idx += 1;
        });
    });
    $('.headers').each(function()
    {
        var Idx = 1;
        $(this).children('td:not(.break)').each(function()
        {
            $(this).addClass('tdcol'+Idx);
            Idx += 1;
        });
    });

    $('[class*="col"]').hover(
    function(){
        var ThisCol = $(this).attr('class').match(/col[0-9]{1,}/);
        $('.'+ThisCol).addClass('hoverV');
        $('.td'+ThisCol).addClass('hoverTD');
    },
    function(){
        $('.hoverV').removeClass('hoverV');
        $('.hoverTD').removeClass('hoverTD');
    });

    if('{Input_EnableProfitabilitySort}' == 'true'){
        var InitSort = [[15,1]];
    } else {
        var InitSort = [];
    }

    $('#tableSort').tablesorter({
        headers: {
            0: {sorter: false},
            1: {sorter: false},
            2: {sorter: false},
            7: {sorter: false},
            11: {sorter: false},
            14: {sorter: false},
        },
        cssAsc: 'sortasc',
        cssDesc: 'sortdesc',
        sortList: InitSort
    });

});
</script>
<script type="text/javascript" src="../libs/jquery-tablesorter/jquery.tablesorter.min.js"></script>
<br />
<form action="" method="get" style="margin-bottom: 1px;">
    <table style="width: 1100px;">
        <tr>
            <td class="c" colspan="3">{Selector_Title}</td>
        </tr>
        <tr>
            <th class="pad2" style="width: 20%;">{Selector_MainShip}</th>
            <th class="pad2" style="width: 60%;">
                <select name="ship" style="width: 100%; padding: 2px;">
                    {Input_Selector_ShipList}
                </select>
            </th>
            <th class="pad2" style="width: 20%;" rowspan="2">
                <input type="submit" value="{Selector_Send}" style="width: 100%; padding: 2px; font-weight: 700;"/>
            </th>
        </tr>
        <tr>
            <th class="pad2">{Selector_SecondShip}</th>
            <th class="pad2">
                <select name="ship2" style="width: 100%; padding: 2px;">
                    <option value="0">------------------------------------</option>
                    {Input_Selector_ShipList2}
                    <option value="0">------------------------------------</option>
                </select>
            </th>
        </tr>
    </table>
</form>
<table style="width: 1100px;" id="tableSort">

    <thead>
        {Input_InsertHeaders}
    </thead>
    <tbody>
        {Input_InsertRows}
    </tbody>
</table>
