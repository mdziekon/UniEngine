<script>
$(document).ready(function()
{
    $('.warbalance').tipTip({maxWidth: 200, content: '<div class="center"><b>{Table_WarBalanceTip}</b></div>', defaultPosition: 'top', delay: 200, edgeOffset: 3});
});
</script>
<link rel="stylesheet" type="text/css" href="dist/css/profile.cachebuster-1546564327123.min.css" />
<br />
<table style="width: 700px;">
    <tr>
        <td class="c" colspan="2">{PageTitle}</td>
    </tr>
    <tr>
        <th class="pad5" style="width: 350px;">

            <div class="plImg" style="background: url('{User_Avatar}') no-repeat;">
                <div{HideNoAvatar}>
                    <div class="w100p" style="height: 85px;"></div>
                    <div class="divBg">{Table_NoAvatar}</div>
                </div>
            </div>
        </th>
        <th class="pad5" style="width: 350px;">
            <table class="w100p">
                <tr>
                    <td class="c pad2" style="width: 45%;">{Table_Username}</td>
                    <th class="pad2" style="width: 55%;">{User_Username}</th>
                </tr>
                <tr>
                    <td class="c pad2">{Table_Gamerank}</td>
                    <th class="pad2">{User_Gamerank}</th>
                </tr>
                <tr>
                    <td class="c pad2">{Table_Allyname}</td>
                    <th class="pad2">{User_Allyname}</th>
                </tr>
                <tr>
                    <td class="c pad2">{Table_Allyrank}</td>
                    <th class="pad2">{User_Allyrank}</th>
                </tr>
                <tr>
                    <td class="c pad2">{Table_MotherPlanet}</td>
                    <th class="pad2"><a href="galaxy.php?mode=3&amp;galaxy={User_PlanetGalaxy}&amp;system={User_PlanetSystem}&amp;planet={User_PlanetPlanet}">{User_MotherPlanet} [{User_PlanetGalaxy}:{User_PlanetSystem}:{User_PlanetPlanet}]</a></th>
                </tr>
                <tr class="inv">
                    <td></td>
                </tr>
                <tr{HideVacations}>
                    <th colspan="2" class="pad5 skyblue">{Table_UserOnVacations}</th>
                </tr>
                <tr{HideBanned}>
                    <th colspan="2" class="pad5">
                        <a class="red" href="banned.php">{Table_UserIsBanned}</a>
                    </th>
                </tr>
                <tr>
                    <th colspan="2" class="pad5">
                        <a href="messages.php?mode=write&uid={User_ID}"><img src="{skinpath}img/m.gif" class="icon"/>{Table_WritePM}</a>
                    </th>
                </tr>
                <tr{HideBuddy}>
                    <th colspan="2" class="pad5">
                        {User_ShowBuddyOption}
                    </th>
                </tr>
                <tr{HideAllyInvite}>
                    <th colspan="2" class="pad5">
                        <a href="alliance.php?mode=invite&uid={User_ID}"><img src="images/newmail.png" class="icon"/>{Table_AllyInvite}</a>
                    </th>
                </tr>
                <tr{HideIgnore}>
                    <th colspan="2" class="pad5">
                        <a href="settings.php?{User_IgnoreLink}"><img src="images/ban.png" class="icon"/>{User_IgnoreText}</a>
                    </th>
                </tr>
                <tr{HideReport}>
                    <th colspan="2" class="pad5">
                        <a href="report.php?type=4&amp;uid={User_ID}"><img src="images/warning.png" class="icon"/>{Table_Report}</a>
                    </th>
                </tr>
                {Insert_AdminInfoLink}
            </table>
        </th>
    </tr>
    <tr>
        <th class="pad5" valign="top">
            <table class="w100p">
                <tr>
                    <td class="c center pad2" colspan="3">{Table_WarStats}</td>
                </tr>
                <tr>
                    <td class="c pad2 w42p">{Table_FightsTotal}</td>
                    <th class="pad2 w58p" colspan="2">{User_FightsTotal}</th>
                </tr>
                <tr class="inv">
                    <th></th>
                </tr>
                <tr>
                    <td class="c pad2 w42p">{Table_FightsWon}</td>
                    <th class="pad2" style="width: 38%;">{User_FightsWon}</th>
                    <th class="pad2" style="width: 20%;">{User_FightsWonP}%</th>
                </tr>
                <tr>
                    <td class="c pad2">{Table_FightsWonACS}</td>
                    <th class="pad2">{User_FightsWonACS}</th>
                    <th class="pad2">{User_FightsWonACSP}%</th>
                </tr>
                <tr>
                    <td class="c pad2">{Table_FightsDraw}</td>
                    <th class="pad2">{User_FightsDraw}</th>
                    <th class="pad2">{User_FightsDrawP}%</th>
                </tr>
                <tr>
                    <td class="c pad2">{Table_FightsLost}</td>
                    <th class="pad2">{User_FightsLost}</th>
                    <th class="pad2">{User_FightsLostP}%</th>
                </tr>
                <tr>
                    <td class="c pad2">{Table_FightsInAlly}</td>
                    <th class="pad2">{User_FightsInAlly}</th>
                    <th class="pad2">{User_FightsInAllyP}%</th>
                </tr>
                <tr class="inv">
                    <th></th>
                </tr>
                <tr>
                    <td class="c pad2">{Table_MissileAttacks}</td>
                    <th class="pad2" colspan="2">{User_MissileAttacks}</th>
                </tr>
                <tr class="inv">
                    <th></th>
                </tr>
                <tr>
                    <td class="c pad2">{Table_MoonsCreated}</td>
                    <th class="pad2" colspan="2">{User_MoonsCreated}</th>
                </tr>
                <tr>
                    <td class="c pad2">{Table_MoonsDestroyed}</td>
                    <th class="pad2" colspan="2">{User_MoonsDestroyed}</th>
                </tr>
                <tr class="inv">
                    <th></th>
                </tr>
                <tr>
                    <td class="c pad2 warbalance help">{Table_WarBalance} (?)</td>
                    <th class="pad2 {User_WarBalanceColor}" colspan="2">{User_WarBalance} {Table_WarBalancePt}</th>
                </tr>
                <tr>
                    <td class="c pad2">{Table_ShotDownUnits}</td>
                    <th class="pad2" colspan="2">{User_ShotDownUnits}</th>
                </tr>
                <tr>
                    <td class="c pad2">{Table_LostUnits}</td>
                    <th class="pad2" colspan="2">{User_LostUnits}</th>
                </tr>
            </table>
        </th>
        <th class="pad5" valign="top">
            <table class="w100p">
                <tr>
                    <td class="c center pad2" colspan="3">{Table_NormalStats}</td>
                </tr>
                <tr>
                    <td class="c pad2 w42p">{Table_Position}</td>
                    <th class="pad2 w58p" colspan="2"><a href="stats.php?range={User_StatRange}">{User_GlobalPosition}</a></th>
                </tr>
                <tr>
                    <td class="c pad2">{Table_Points}</td>
                    <th class="pad2" colspan="2"><a href="stats.php?range={User_StatRange}">{User_Points}</a></th>
                </tr>
                <tr class="inv">
                    <th></th>
                </tr>
                <tbody class="{Insert_MoraleHide}">
                <tr>
                    <td class="c pad2">{Table_MoralePoints}</td>
                    <th class="pad2" colspan="2">{User_MoralePoints}</th>
                </tr>
                <tr class="inv">
                    <th></th>
                </tr>
                </tbody>
                <tr>
                    <td class="c pad2 w42p">{Table_PositionFleets}</td>
                    <th class="pad2 w58p" colspan="2"><a href="stats.php?type=2&amp;range={User_FleetsRange}">{User_FleetsPosition}</a></th>
                </tr>
                <tr>
                    <td class="c pad2">{Table_PointsFleets}</td>
                    <th class="pad2" colspan="2"><a href="stats.php?type=2&amp;range={User_FleetsRange}">{User_PointsFleets}</a></th>
                </tr>
                <tr class="inv">
                    <th></th>
                </tr>
                <tr>
                    <td class="c pad2 w42p">{Table_PositionResearch}</td>
                    <th class="pad2 w58p" colspan="2"><a href="stats.php?type=3&amp;range={User_ResearchRange}">{User_ResearchPosition}</a></th>
                </tr>
                <tr>
                    <td class="c pad2">{Table_PointsResearch}</td>
                    <th class="pad2" colspan="2"><a href="stats.php?type=3&amp;range={User_ResearchRange}">{User_PointsResearch}</a></th>
                </tr>
                <tr class="inv">
                    <th></th>
                </tr>
                <tr>
                    <td class="c pad2 w42p">{Table_PositionBuildings}</td>
                    <th class="pad2 w58p" colspan="2"><a href="stats.php?type=4&amp;range={User_BuildingsRange}">{User_BuildingsPosition}</a></th>
                </tr>
                <tr>
                    <td class="c pad2">{Table_PointsBuildings}</td>
                    <th class="pad2" colspan="2"><a href="stats.php?type=4&amp;range={User_BuildingsRange}">{User_PointsBuildings}</a></th>
                </tr>
                <tr class="inv">
                    <th></th>
                </tr>
                <tr>
                    <td class="c pad2 w42p">{Table_PositionDefense}</td>
                    <th class="pad2 w58p" colspan="2"><a href="stats.php?type=5&amp;range={User_DefenseRange}">{User_DefensePosition}</a></th>
                </tr>
                <tr>
                    <td class="c pad2">{Table_PointsDefense}</td>
                    <th class="pad2" colspan="2"><a href="stats.php?type=5&amp;range={User_DefenseRange}">{User_PointsDefense}</a></th>
                </tr>
            </table>
        </th>
    </tr>
</table>
