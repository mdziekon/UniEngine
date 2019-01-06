<style>
.sortHigh {
    border-bottom: 1px dashed;
}
.headPad {
    padding-right: 5px !important;
}
</style>
<br/>
<table width="650">
    <tr>
        <td class="c headPad" colspan="8">{Ally_ML_Title}<b style="float: right">({Ally_ML_Count}: {members_count})</b></td>
    </tr>
    <tr>
        <th>#</th>
        <th><a href="?mode=mlist&stype=1&smode={sortRev}" {sortByName}>{Ally_ML_Name}</a></th>
        <th>&nbsp;</th>
        <th><a href="?mode=mlist&stype=2&smode={sortRev}" {sortByRank}>{Ally_ML_Rank}</a></th>
        <th><a href="?mode=mlist&stype=3&smode={sortRev}" {sortByPoints}>{Ally_ML_Points}</a></th>
        <th><a href="?mode=mlist&stype=6&smode={sortRev}" {sortByPlanet}>{Ally_ML_Planet}</a></th>
        <th><a href="?mode=mlist&stype=4&smode={sortRev}" {sortByRegTime}>{Ally_ML_RegTime}</a></th>
        <th><a href="?mode=mlist&stype=5&smode={sortRev}" {sortByOnline}>{Ally_ML_Online}</a></th>
    </tr>
    {Rows}
    <tr>
        <td class="c" colspan="8">(<a href="alliance.php">&#171; {GoBack}</a>)</td>
    </tr>
</table>
