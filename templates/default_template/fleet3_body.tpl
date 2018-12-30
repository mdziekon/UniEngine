<script>
var useQuickRes = {useQuickRes};
var jsLang = {'fl_coordplanet': '{fl_coordplanet}', 'fl_coordmoon': '{fl_coordmoon}', 'fl_coorddebris': '{fl_coorddebris}'};
</script>
<script src="dist/js/fleet3.cachebuster-1545956361123.min.js"></script>
<link rel="stylesheet" type="text/css" href="css/fleet3.min.css" />

<br/>
<table id="resTbl">
    <tr>
        <td class="c" colspan="2"><span class="success">{fl_fleet_send}</span></td>
    </tr>
    <tr>
        <th class="pad">{fl_mission}</th>
        <th class="pad">{FleetMission}</th>
    </tr>
    <tr>
        <th class="pad">{fl_dist}</th>
        <th class="pad">{FleetDistance}</th>
    </tr>
    <tr>
        <th class="pad">{fl_speed}</th>
        <th class="pad">{FleetSpeed}</th>
    </tr>
    <tr>
        <th class="pad">{fl_deute_need}</th>
        <th class="pad">{FleetFuel}</th>
    </tr>
    <tr>
        <th class="pad">{fl_from}</th>
        <th class="pad"><a href="galaxy.php?mode=3&galaxy={StartGalaxy}&system={StartSystem}&planet={StartPlanet}">[{StartGalaxy}:{StartSystem}:{StartPlanet}]</a><b class="{StartType}">&nbsp;</b></th>
    </tr>
    <tr>
        <th class="pad">{fl_dest}</th>
        <th class="pad"><a href="galaxy.php?mode=3&galaxy={TargetGalaxy}&system={TargetSystem}&planet={TargetPlanet}">[{TargetGalaxy}:{TargetSystem}:{TargetPlanet}]</a><b class="{TargetType}">&nbsp;</b></th>
    </tr>
    <tr>
        <th class="pad">{fl3_TargetReach}</th>
        <th class="pad">{FleetStartTime}</th>
    </tr>
    <tr>
        <th class="pad">{fl3_BackTime}</th>
        <th class="pad">{FleetEndTime}</th>
    </tr>
    <tr>
        <td class="c" colspan="2">{fl3_ShipsList}</td>
    </tr>
    {ShipsRows}
</table>
