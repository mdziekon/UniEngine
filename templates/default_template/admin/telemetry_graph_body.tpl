<style>
.tickLabel {
    color: white;
    font-size: 8px;
}
#Graph_legend {
    padding: 0px;
}
#Graph_legend > li {
    font-size: 10px;
    font-weight: 700;
    margin: 2px 0px !important;
}
.Graph_legend_label {
    padding-left: 5px;
}
#Graph_Tooltip {
    font-size: 10px !important;
}
</style>
<script>var UserLinking = {InsertUserLinkings}</script>
{InsertScripts}
<br /><table width="900">
    <tr>
        <td class="c" colspan="9"><a href="Telemetry.php">{Page_Title}</a> &#187; <a href="Telemetry.php?pid={ThisPID}{RefreshLinkArgs}">{CombinePlace}</a></td>
    </tr>
    <form action="" style="margin: 0px;" method="post">
        <tr>
            <th class="pad2" colspan="8">
                <input type="text" name="filter_where" style="width: 100%;" class="pad2" value="{Insert_Filter_Where_Val}"/>
            </th>
            <th class="pad2">{Tele_Filters_Where}</th>
        </tr>
        <tr>
            <th class="pad2" colspan="8">
                <input type="text" name="filter_order" style="width: 100%;" class="pad2" value="{Insert_Filter_Order_Val}"/>
            </th>
            <th class="pad2">{Tele_Filters_Order}</th>
        </tr>
        <tr>
            <th class="pad2" colspan="8">
                <input type="text" name="filter_limit" style="width: 100%;" class="pad2" value="{Insert_Filter_Limit_Val}"/>
            </th>
            <th class="pad2">{Tele_Filters_Limit}</th>
        </tr>
        <tr>
            <th class="pad2" colspan="8"><input type="checkbox" id="filter_jumps" name="filter_jumps" {Insert_Filter_Jumps_On}/> <label for="filter_jumps">{Tele_Filters_SkipJumps}</label> ({Tele_Filters_SkipJumps_Diff}: <input type="text" style="width: 50px;" name="filter_jumps_min" value="{Insert_Filter_JumpsMin_Val}"/>)</th>
            <th class="pad2"><input type="submit" class="pad2 lime" style="width: 100%; font-weight: 700;" value="{Tele_Filters_Submit}"/></th>
        </tr>
    </form>
    <tr>
        <th class="pad5" colspan="8" style="width: 750px;">
            {InsertGraph}
        </th>
        <th class="pad5" style="width: 150px; text-align: left; vertical-align: top;">
            {InsertLegend}
        </th>
    </tr>
</table>
