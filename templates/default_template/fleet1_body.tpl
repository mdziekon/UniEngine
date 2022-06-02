<script>
var ServerClientDifference = ({Now} * 1000) - new Date().getTime();
var AllowCreateTimeCounters = true;
var FlightDuration = 0;
var maxIs = {'galaxy': {P_MaxGalaxy}, 'system': {P_MaxSystem}, 'planet': {P_MaxPlanet}};
var JSLang = {'fl1_targetGalaxy': '{fl1_targetGalaxy}', 'fl1_targetSystem': '{fl1_targetSystem}', 'fl1_targetPlanet': '{fl1_targetPlanet}'};
var shipsDetails = {P_ShipsDetailsJSON};
</script>
<script src="dist/js/_libCommon.cachebuster-1649555016585.min.js"></script>
<script src="dist/js/fleet1.cachebuster-1653350869468.min.js"></script>
<link rel="stylesheet" type="text/css" href="dist/css/fleet1.cachebuster-1546564327123.min.css" />

<form id="thisForm" action="fleet2.php" method="post">
    <input type="hidden" name="sending_fleet" value="1" />
    <input type="hidden" name="quickres" value="{P_SetQuickRes}" />
    <input type="hidden" id="MaxSpeed" value="{speedallsmin}" />
    <input type="hidden" id="ThisGalaxy" value="{ThisGalaxy}" />
    <input type="hidden" id="ThisSystem" value="{ThisSystem}" />
    <input type="hidden" id="ThisPlanet" value="{ThisPlanet}" />
    <input type="hidden" id="ThisType" value="{ThisPlanetType}" />
    <input type="hidden" id="SpeedFactor" value="{SpeedFactor}" />
    <input type="hidden" id="PlanetDeuterium" value="{ThisResource3}" />
    <input type="hidden" id="Storage" value="{Storage}"/>
    <input type="hidden" id="FuelStorage" value="{FuelStorage}"/>
    <input type="hidden" name="target_mission" value="{SetTargetMission}" />
    <input type="hidden" name="getacsdata" value="{SelectedACSID}"/>
    <input type="hidden" name="FleetArray" value="{FleetArray}"/>
    <input type="hidden" name="speed" value="{Insert_SpeedInput}"/>
    <input type="hidden" name="gobackVars" value="{P_GoBackVars}"/>
    <br />
    {P_SFBInfobox}
    <table width="750">
        <tr>
            <td colspan="2" class="c">{fl_fleet1_ttl}</td>
        </tr>
        <tr{P_HideACSJoining}>
            <th colspan="2" class="lime">{fl1_ACSJoiningFleet}</th>
        </tr>
        <tr>
            <th style="width: 30%;">{fl_dest}</th>
            <th>
                <input class="updateInfo" id="galaxy_selector" name="galaxy" size="3" maxlength="2" value="{SetPos_galaxy}" autocomplete="off" {P_DisableCoordSel} />
                <input class="updateInfo" id="system_selector" name="system" size="3" maxlength="3" value="{SetPos_system}" autocomplete="off" {P_DisableCoordSel} />
                <input class="updateInfo" id="select_planet" name="planet" size="3" maxlength="2" value="{SetPos_planet}" autocomplete="off" {P_DisableCoordSel} />
                <select class="updateInfo" id="type_selector" name="planettype" {P_DisableCoordSel}>
                    <option value="1" {SetPos_Type1Selected}>{fl_planet}</option>
                    <option value="2" {SetPos_Type2Selected}>{fl_ruins}</option>
                    <option value="3" {SetPos_Type3Selected}>{fl_moon}</option>
                </select>
            </th>
        </tr>
        <tr>
            <th>{fl_speed}</th>
            <th id="defCursor">{Insert_Speeds}</th>
        </tr>
        <tr>
            <th>{fl_dist}</th>
            <th><b id="distance">-</b></th>
        </tr>
        <tr>
            <th>{fl_fltime}</th>
            <th><b id="duration">-</b></th>
        </tr>
        <tr>
            <th>{fl1_currentTime}</th>
            <th><b id="curr_time"></b></th>
        </tr>
        <tr>
            <th>{fl_reachtime}</th>
            <th><b id="reach_time"></b></th>
        </tr>
        <tr>
            <th>{fl_comebacktime}</th>
            <th><b id="comeback_time"></b></th>
        </tr>
        <tr>
            <th>{fl_deute_need}</th>
            <th><b id="consumption">-</b></th>
        </tr>
        <tr>
            <th>{fl_speed_max}</th>
            <th>{MaxSpeedPretty}</th>
        </tr>
        <tr>
            <th>{fl_max_load}</th>
            <th><b id="storageShow">-</b></th>
        </tr>
        <tr>
            <td colspan="2" class="c">{fl_fast_link}<b class="flRi">(<a href="fleetshortcut.php">{fl_add_shortcut}</a>)</b></td>
        </tr>
        <tbody{P_HideFastLinks}>
            <tr>
                <th>{fl_your_planets}</th>
                <th>{FastLinks_Planets}</th>
            </tr>
            <tr>
                <th>{fl_your_shortcuts}</th>
                <th>{FastLinks_ShortCuts}</th>
            </tr>
        </tbody>
        <tr{P_HideNoFastLinks}>
            <th colspan="2">{fl_no_quick_links}</th>
        </tr>
        <tr>
            <th colspan="2">
                <input class="SendButtom orange" type="button" value="&laquo; {fl_goback}" id="goBack"/>
                <input class="SendButtom lime" type="submit" value="{fl_continue} &raquo;" />
            </th>
        </tr>
    </table>
</form>
