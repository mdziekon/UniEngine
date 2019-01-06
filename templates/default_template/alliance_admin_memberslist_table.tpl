<style>
.lime {
    color: lime !important;
}
.orange {
    color: orange !important;
}
.red {
    color: red !important;
}
.sortHigh {
    border-bottom: 1px dashed;
}
.headPad {
    padding-right: 5px !important;
}
.kick {
    background: url('images/delete.png') no-repeat 0pt 1px;
    padding-left: 12px;
}
.hide {
    display: none;
}
.inv {
    visibility: hidden;
}
.markChanged {
    background-color: #455B87;
    border-color: #526EA3;
}
.markChangedSel {
    background-color: #4E6797;
    border-color: #607BAF;
}
.subBut {
    font-weight: bold;
}
.subButDisabled {
    color: gray;
}
</style>
<script>
$(document).ready(function()
{
    var ChangesDone = 0;

    $('[name^=change_rank]').each(function()
    {
        $(this).data('defVal', $(this).val());
    });

    $('[name^=change_rank]').change(function()
    {
        if($(this).val() != $(this).data('defVal')){
            $(this).parent().parent().children().addClass('markChanged');
            $(this).addClass('markChangedSel');
            ChangesDone += 1;
            $('.subBut').removeClass('subButDisabled').attr('disabled', false);
        } else {
            $(this).parent().parent().children().removeClass('markChanged');
            $(this).removeClass('markChangedSel');
            ChangesDone -= 1;
            if(ChangesDone == 0){
                $('.subBut').addClass('subButDisabled').attr('disabled', true);
            }
        }
    }).keyup(function()
    {
        $(this).change();
    });
    $('.kick').tipTip({edgeOffset: 10, content: '{ADM_KickMember}', delay: 0}).click(function()
    {
        return confirm('{ADM_SureWantKick}');
    });
});
</script>
<br/>
<form action="" method="post">
<input type="hidden" name="send" value="yes"/>
    <table width="650">
        <tr class="{HideInfoBox}">
            <td class="c pad5 {InfoBoxColor}" colspan="9">{InfoBoxText}</td>
        </tr>
        <tr class="inv {HideInfoBox}">
            <td></td>
        </tr>

        <tr>
            <td class="c headPad" colspan="9">{Ally_ML_Title}<b style="float: right">({Ally_ML_Count}: {members_count})</b></td>
        </tr>
        <tr>
            <th>#</th>
            <th><a href="?mode=admin&amp;edit=members&amp;stype=1&amp;smode={sortRev}" {sortByName}>{Ally_ML_Name}</a></th>
            <th>&nbsp;</th>
            <th><a href="?mode=admin&amp;edit=members&amp;stype=2&amp;smode={sortRev}" {sortByRank}>{Ally_ML_Rank}</a></th>
            <th><a href="?mode=admin&amp;edit=members&amp;stype=3&amp;smode={sortRev}" {sortByPoints}>{Ally_ML_Points}</a></th>
            <th><a href="?mode=admin&amp;edit=members&amp;stype=6&amp;smode={sortRev}" {sortByPlanet}>{Ally_ML_Planet}</a></th>
            <th><a href="?mode=admin&amp;edit=members&amp;stype=4&amp;smode={sortRev}" {sortByRegTime}>{Ally_ML_RegTime}</a></th>
            <th><a href="?mode=admin&amp;edit=members&amp;stype=5&amp;smode={sortRev}" {sortByOnline}>{Ally_ML_Online}</a></th>
            <th>{Ally_ML_Action}</th>
        </tr>
        {Rows}
        <tr>
            <th colspan="9" class="pad5">
                <input type="submit" class="subBut subButDisabled" value="{ADM_SaveRankChanges}" disabled=""/>
            </th>
        </tr>
        <tr>
            <td class="c" colspan="9">(<a href="?mode=admin">&#171; {GoBack}</a>)</td>
        </tr>
    </table>
</form>
