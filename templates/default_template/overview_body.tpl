<script>
var JSLang = {
    'BuildJS_Continue': '{BuildJS_Continue}'
};

function onQueuesFirstElementFinished (planetID) {
    document.getElementById("dlink").innerHTML = (
        '<a href="overview.php?planet=' + planetID + '">' + JSLang['BuildJS_Continue'] + '</a>'
    );

    window.setTimeout(function () {
        document.location.href = "overview.php?planet=" + planetID;
    }, 1000);
}

$(document).ready(function()
{
    $('.tipRename').tipTip({maxWidth: 'auto', content: '{Rename_TipTip}', defaultPosition: 'bottom', delay: 200, edgeOffset: 5});
    $('.tipTipTitle').tipTip({maxWidth: 'auto', attribute: 'title', defaultPosition: 'bottom', delay: 200, edgeOffset: 5});
    $('#quickres').click(function() {
        window.location = 'fleet.php?quickres=1';
    });
});
</script>
<link rel="stylesheet" type="text/css" href="dist/css/overview.cachebuster-1546565145290.min.css" />
<br />
{P_SFBInfobox}
<table width="750">
    <tbody style="{MailChange_Hide}">
        <tr>
            <td colspan="3" class="c">{MailChange_Title}</td>
        </tr>
        <tr>
            <th colspan="3" class="c">{MailChange_Box}</th>
        </tr>
        <tr>
            <th class="inv">&nbsp;</th>
        </tr>
    </tbody>
    {VacationModeBox}
    {ActivationInfoBox}
    {NewUserBox}
    {AdminInfoBox}
    {ForumPropositions}
    <tr>
        <td colspan="3" class="c">{Overview} {overvier_type} "{planet_name}"</td>
    </tr>
    {SystemMsgBox}
    {FreePremiumItemsBox}
    {NewMsgBox}
    {NewPollsBox}
    {fleet_list}
    <tr>
        <th width="215">
            <table width="100%" align="center">
                <tr>
                    <td colspan="2" class="c pad3">{ServerInfo}</td>
                </tr>
                <tr>
                    <th style="width: 60%;">{Box_online}</th>
                    <th style="width: 40%;">{CurrentOnline}</th>
                </tr>
                <tr>
                    <th>{Box_todayActive}</th>
                    <th>{TodayOnline}</th>
                </tr>
                <tr>
                    <th>{Box_onlineRecord}</th>
                    <th>{ServerRecord}</th>
                </tr>
                <tr>
                    <th>{Box_userCount}</th>
                    <th>{TotalPlayerCount}</th>
                </tr>
            </table>
            <br/>
            <table width="100%" align="center">
                <tr>
                    <td class="c pad3">{StatsRecount}</td>
                </tr>
                <tr>
                    <th><span class="lime">{LastStatsRecount}</span><br/><span class="grey">{Info_CountedEvery}</span></th>
                </tr>
            </table>
            <br/>
            {Insert_MoraleBox}
            <table width="100%" align="center">
                <tr>
                    <td colspan="2" class="c pad3">{YourAccount}</td>
                </tr>
                <tr>
                    <th width="45%">{Box_youPlaySince}</th>
                    <th width="55%">{RegisterDate}<br/><span class="grey">({RegisterDays} {RegisterDaysTxt})</span></th>
                </tr>
                <tr>
                    <th>
                        <span class="lime">{Box_proAccount}</span><br/>
                        <span class="grey">(<a href="galacticshop.php#shop" class="grey">{ProAccLink}</a>)</span>
                    </th>
                    <th>{ProAccountInfoText}</th>
                </tr>
                <tr>
                    <th>{Box_refferedUsers}</th>
                    <th>
                        {RefferedCounter}<br/>
                        <span class="grey">(<a href="ref_table.php" class="grey">{RefferedList}</a>)</span>
                    </th>
                </tr>
            </table>
        </th>
        <th id="plBox">
            <div id="thisPlImg" style="background: url('{skinpath}planeten/{planet_image}.jpg');">
                <div class="divBg">
                    <a href="?mode=rename" class="tipRename">{planet_name}<img id="plRename" src="images/edit.png"/></a>
                </div>
                <div id="plQueue">
                    <div class="divBg" id="plQueueText">{building}</div>
                </div>
            </div>
            <br />
            <table align="center" width="100%">
                <tr>
                    <td colspan="2" class="c pad3">
                        {Box_planetData} {_planetData_type} (<a href="?mode=rename" class="orange">{_planetData_changename}</a><span{HideAbandonLink}> / <a href="?mode=abandon" class="red">{_planetData_leave}</a></span>)
                    </td>
                </tr>
                <tr>
                    <th style="width: 50%;">{Box_planetCoords}</th>
                    <th style="width: 48%;">
                        <a href="galaxy.php?mode=3&galaxy={galaxy_galaxy}&system={galaxy_system}&planet={galaxy_planet}">
                            [{galaxy_galaxy}:{galaxy_system}:{galaxy_planet}]
                        </a>
                    </th>
                </tr>
                <tr>
                    <th>{Box_planetDiameter}</th>
                    <th>{planet_diameter} {diameter_unit}</th>
                </tr>
                <tr>
                    <th>{Box_planetBuildFields}</th>
                    <th>{planet_field_current} / {planet_field_max} {fields} ({planet_field_used_percent}%)</th>
                </tr>
                <tr>
                    <th>{Box_planetTemps}</th>
                    <th>{ov_temp_from} {planet_temp_min}{ov_temp_unit} {ov_temp_to} {planet_temp_max}{ov_temp_unit}</th>
                </tr>
                <tr>
                    <th>{Box_planetOrbit}</th>
                    <th>{ShowWhatsOnOrbit}</th>
                </tr>
                <tr>
                    <th>
                        {Box_planetDebris}
                        <span {hide_debris}><br/>
                            <span class="grey">(<a href="galaxy.php?mode=0&galaxy={galaxy_galaxy}&system={galaxy_system}" class="grey">{_planetDebrisLink}</a>)</span>
                        </span>
                    </th>
                    <th>
                        <span {hide_debris}>
                            {Metal}: <span class="grey">{metal_debris}</span><br/>
                            {Crystal}: <span class="grey">{crystal_debris}</span>
                        </span>
                        <span class="grey" style="{hide_nodebris}">{NoDebris}</span>
                    </th>
                </tr>
            </table>
        </th>
        <th width="215">
            <table width="100%" align="center">
                <tr>
                    <td colspan="2" class="c pad3">{Statistics}</td>
                </tr>
                <tr>
                    <th><a href="stats.php?range={set_user_total_rank}">{Box_statGeneral}</a></th>
                    <th>{user_total_rank}<br/><span class="grey">{total_points} {_statUnit}</span></th>
                </tr>
                <tr>
                    <th><a href="stats.php?range={set_user_br}&amp;type=4">{Box_statBuildings}</a></th>
                    <th>{user_br}<br/><span class="grey">{user_points} {_statUnit}</span></th>
                </tr>
                <tr>
                    <th><a href="stats.php?range={set_user_fr}&amp;type=2">{Box_statFleet}</a></th>
                    <th>{user_fr}<br/><span class="grey">{user_fleet} {_statUnit}</span></th>
                </tr>
                <tr>
                    <th><a href="stats.php?range={set_user_dr}&amp;type=5">{Box_statDefense}</a></th>
                    <th>{user_dr}<br/><span class="grey">{user_defs} {_statUnit}</span></th>
                </tr>
                <tr>
                    <th><a href="stats.php?range={set_user_tr}&amp;type=3">{Box_statResearch}</a></th>
                    <th>{user_tr}<br/><span class="grey">{player_points_tech} {_statUnit}</span></th>
                </tr>
            </table>
            <br/>
            <table width="100%" align="center">
                <tr>
                    <td colspan="2" class="c pad3">{YourBattles}</td>
                </tr>
                <tr>
                    <th>{Box_battlesAll}</th>
                    <th>{raids}</th>
                </tr>
                <tr>
                    <th class="invNF">&nbsp;</th>
                </tr>
                <tr>
                    <th>{Box_battlesWon}</th>
                    <th>{raidswin}</th>
                </tr>
                <tr>
                    <th class="orange">{Box_battlesACSWon}</th>
                    <th>{raidacswin}</th>
                </tr>
                <tr>
                    <th>{Box_battlesDraw}</th>
                    <th>{raidsdraw}</th>
                </tr>
                <tr>
                    <th>{Box_battlesLost}</th>
                    <th>{raidsloose}</th>
                </tr>
                <tr>
                    <th>{Box_battlesInAlly}</th>
                    <th>{raidsinally}</th>
                </tr>
                <tr>
                    <th class="invNF">&nbsp;</th>
                </tr>
                <tr>
                    <th>{Box_missileAttacks}</th>
                    <th>{raidsmissileattacks}</th>
                </tr>
            </table>
        </th>
    </tr>
    <tr style="visibility: none;"><td></td></tr>
    <tr>
        <td colspan="3" class="c">{RefLinksHeader}</td>
    </tr>
    <tr>
        <th colspan="3" class="pad5">
        <img src="generate_sig.php?uid={UserUID}" width="468" height="60"/><br />
        {SignatureInfo}
        </th>
    </tr>
    <tr>
        <th>{SignatureTitle}</th>
        <th colspan="2">
            <input type="text" id="rlink1" class="refLink" onclick="$('#rlink1').select();" value="{referralLink1}"/>
        </th>
    </tr>
    <tr>
        <th>{RefLinkTitle}</th>
        <th colspan="2">
        <input type="text" id="rlink2" class="refLink" onclick="$('#rlink2').select();" value="{referralLink2}"/>
        </th>
    </tr>
    <tr class="inv"><td></td></tr>
    <tr>
        <td colspan="3" class="c pad3">{ResourcesTransport}</td>
    </tr>
    <tr>
        <th>&nbsp;</th>
        <th>{Box_transRequired}</th>
        <th>{Box_transMissing_Stay}</th>
    </tr>
    <tr>
        <th>{transportShips__202__name}</th>
        <th>{transportShips__202__requiredCount}</th>
        <th>{transportShips__202__remainingCount}</th>
    </tr>
    <tr>
        <th>{transportShips__203__name}</th>
        <th>{transportShips__203__requiredCount}</th>
        <th>{transportShips__203__remainingCount}</th>
    </tr>
    <tr>
        <th>{transportShips__217__name}</th>
        <th>{transportShips__217__requiredCount}</th>
        <th>{transportShips__217__remainingCount}</th>
    </tr>
    <tr{Hide_QuickResButton}>
        <th class="pad5" colspan="3">
            <input type="button" value="{QuickResSend_Button}" id="quickres"/>
        </th>
    </tr>
    <tr class="inv" {hide_other_planets}><td></td></tr>
    <tr {hide_other_planets}>
        <td colspan="3" class="c">{OtherPlanets_header}</td>
    </tr>
    <tr {hide_other_planets}>
        <th colspan="3">
            <table class="s" border="0" align="center">{OtherPlanets}</table>
        </th>
    </tr>
    <tr class="inv"><td></td></tr>
    <tr>
        <td colspan="3" class="c">{MoreInfo}</td>
    </tr>
    <tr>
        <th>{Box_fromAdmins}</th>
        <th colspan="2">{FromAdmins}<br /></th>
    </tr>
    <tr>
        <th>{Box_buttons}</th>
        <th colspan="2">{TopLists_box}<br/></th>
    </tr>
</table>
