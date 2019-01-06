<tr>
    <td colspan="{Input_TabFullLen}" class="c"><a href="?{Input_SetMode}">{Tab01_CatList_TabTitle}</a> &#187; {Tab01_CatSel_TabTitle}</td>
</tr>
<tr>
    <th colspan="{Input_TabFullLen}" class="pad5 skyblue" style="font-size: 15px;">{Input_TasksCat}</th>
</tr>

<tr{Input_HideCatRewardsOrSkip}>
    <th colspan="{Input_TabFullLen}" class="noPad">
        <table class="w100p">
            <tr>
                <td class="noBor {Input_CatRewardsTDClass} b pad5" valign="top">
                    <span {Input_HideCatRewards}>
                        <b><u class="lime">{Tab01_CatSel_CatRewards}:</u></b><br /><ul style="margin: 0px; padding-left: 15px;">{Input_CatRewards}</ul>
                    </span>
                </td>
                <td class="{Input_CatSkipTDClass} b pad5 center">
                    <span {Input_HideCatSkip}>
                        <input type="button" style="padding: 3px; font-weight: 700; color: red;" id="skip_{Input_CatID}" class="help" title="{Tab01_CatSel_SkipInfo}" value="{Tab01_CatSel_DoSkip} (?)"/>
                    </span>
                </td>
            </tr>
        </table>
    </th>
</tr>

<tr>
    <th class="taskTW" rowspan="{Insert_TaskListRowspan}">&nbsp;</th>
    <th class="pad5 taskTWR tab1 help" id="tPrev" title="{Tab_Prev}" rowspan="{Insert_TaskListRowspan}">&#171;</th>
    {Input_CreateTasksList_FirstRow}
    <th class="pad5 taskTWR tab1 help" id="tNext" title="{Tab_Next}" rowspan="{Insert_TaskListRowspan}">&#187;</th>
    <th class="taskTW" rowspan="{Insert_TaskListRowspan}">&nbsp;</th>
</tr>
{Input_CreateTasksList_FurtherRows}
{Input_CreateTaskRows}
