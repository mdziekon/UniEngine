<style>
.expand {
    background: url('images/expand.png') no-repeat 0pt 0pt;
}
.collapse {
    background: url('images/collapse.png') no-repeat 0pt 0pt;
}
.expand, .collapse {
    padding-left: 16px;
    cursor: pointer;
}
.mark {
    cursor: pointer;
}
.hide {
    display: none;
}
.markHover {
    background-color: #455B87;
    border-color: #526EA3;
}
.markSelect {
    background-color: #4E6797;
    border-color: #607BAF;
}
.sortHigh {
    border-bottom: 1px white dashed;
}
.radOpt {
    padding-left: 150px;
}
</style>
<script>
$(document).ready(function()
{
    $('.collapsed').hide();

    $(".mark").hover(function()
    {
        $(this).children().addClass('markHover');
    }, function()
    {
        $(this).children().removeClass('markHover');
    })
    .click(function()
    {
        var Selector = '#form_'+$(this).attr('id').substr(5);
        if($(Selector).hasClass('collapsed')){
            $(this).find('.expand').removeClass('expand').addClass('collapse');
            $(Selector).show().removeClass('collapsed');
        } else {
            $(this).find('.collapse').removeClass('collapse').addClass('expand');
            $(Selector).hide().addClass('collapsed');
        }
    })
    .tipTip({maxWidth: 'auto', content: '{AFP_CollExp}', defaultPosition: 'left', delay: 500, edgeOffset: 8})
    .children().addClass('pad5');
});
</script>
<br/>
<table width="650">
    <tr class="{HideInfoBox}">
        <td class="c pad5 {InfoBoxColor}" colspan="4">{InfoBoxText}</td>
    </tr>
    <tr class="inv {HideInfoBox}">
        <td></td>
    </tr>

    <tr>
        <td class="c" colspan="4">{ADM_ReqList_Title}<b style="float: right">({ADM_RequestsCount}: {RequestCount})</b></td>
    </tr>
    <tr>
        <th width="20px">&nbsp;</th>
        <th width="200px"><a href="?mode=admin&amp;edit=reqlist&amp;stype=1&amp;smode={sortRev}" {sortByName}>{ADM_RL_Username}</a></th>
        <th width="230px"><a href="?mode=admin&amp;edit=reqlist&amp;stype=2&amp;smode={sortRev}" {sortByStats}>{ADM_RL_StatPos}</a></th>
        <th width="200px"><a href="?mode=admin&amp;edit=reqlist&amp;stype=3&amp;smode={sortRev}" {sortBySendDate}>{ADM_RL_SendDate}</a></th>
    </tr>
    {RequestRows}

    <tr class="{HideNoRequests}">
        <th class="c pad5 red" colspan="4">{ADM_RL_NoRequests}</th>
    </tr>
    <tr>
        <td class="c" colspan="4">(<a href="{GoBackLink}">&#171; {GoBack}</a>)</td>
    </tr>
</table>
