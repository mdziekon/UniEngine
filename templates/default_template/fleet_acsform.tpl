<table width="750">
    <tr{P_HideACSBoxOnError}>
        <td colspan="5" class="c"><span class="flLe">{fl_acs_title}</span><b class="flRi lime">({fl_acs_now_name}: {ACSName})</b></td>
    </tr>
    <tr{P_HideACSMSG}>
        <th colspan="5" class="pad5 {P_ACSMSGCOL}">{P_ACSMSG}</th>
    </tr>
    <tbody{P_HideACSBoxOnError}>
        <form action="" method="post" style="margin: 0;" id="ACSForm">
            <input type="hidden" name="fleet_id" value="{FleetID}"/>
            <input type="hidden" name="acsmanage" value="open"/>
            <input type="hidden" name="acsuserschanged" value="0"/>
            <input type="hidden" name="acs_users" value=""/>
            <tr>
                <th colspan="2" class="pad5">{fl_acs_changename}</th>
                <th colspan="3" class="pad5">
                    <input type="text" name="acs_name" class="pad2" style="width: 95%;" maxlength="100"/>
                </th>
            </tr>
            <tr>
                <td colspan="5" class="c"><span class="flLe">{fl_acs_users_management}</span> <span class="flRi orange">{fl_acs_warning_users}</span></td>
            </tr>
            <tr>
                <th colspan="2">{fl_acs_invited_title}</th>
                <th rowspan="2" class="pad5">
                    <input type="button" value="&#171;&#171;" id="ACSUserAdd" style="font-size: 20px; font-weight: 700; width: 75%;" title="{fl_acs_addplayer}"/><br /><br />
                    <input type="button" value="&#187;&#187;" id="ACSUserRmv" style="font-size: 20px; font-weight: 700; width: 75%;" title="{fl_acs_removeplayer}"/>
                </th>
                <th colspan="2">{fl_acs_users_2invite}</th>
            </tr>
            <tr>
                <th colspan="2" class="pad5" style="width: 40%;">
                    <select style="width: 95%;" size="6" id="ACSUser_Invited">{UsersInvited}</select>
                </th>
                <th colspan="2" class="pad5" style="width: 40%;">
                    <select style="width: 95%;" size="6" id="ACSUser_2Invite">{Users2Invite}</select>
                </th>
            </tr>
            <tr>
                <th colspan="5" class="pad2"><input type="submit" value="{fl_acs_changename_save}" class="pad2 OrderButton" style="width: 120px;"/></th>
            </tr>
        </form>
    </tbody>

    <tr class="inv"><td style="font-size: 8px;">&nbsp;</td></tr>
</table>
