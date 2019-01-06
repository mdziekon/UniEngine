<tbody id="ShowTask_{ID}">
    <tr>
        <th colspan="{Colspan}" class="pad5 {Color}">{Lang_Task} {No}: {Name} (<b class="{Color}">{TitleStatus}</b>)</th>
    </tr>
    <tr>
        <th colspan="{Colspan}" class="noPad">
            <table class="w100p">
                <tr>
                    <th class="noBor pad5 w120px" valign="top">
                        <img height="120" align="top" width="120" class="img" src="{Image}" />
                    </th>
                    <td class="noBorL w80p b pad5" valign="top">
                        <span class="{HideFirstToDo}">
                            <b class="orange">{Lang_FirstToDo}:</b><br /><ul style="margin: 0px; padding-left: 15px;">{Input_FirstToDo}</ul><br />
                        </span>
                        <b>{Lang_JobsToDo}:</b><br /><ul style="margin: 0px; padding-left: 15px;">{Input_JobsToDo}</ul>
                    </td>
                </tr>
            </table>
        </th>
    </tr>
    <td colspan="{Colspan}" class="pad5 lAl lime b {HideRewards}"><b style="float: left;">{Lang_Reward}:</b><span class="catCnt">{Input_Rewards}</span></td>
</tbody>
