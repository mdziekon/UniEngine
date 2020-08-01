<script>
var AllowPrettyInputBox  = {P_AllowPrettyInputBox};
var TotalPlanetResources = {P_TotalPlanetResources};
var ACSUsersMax = {InsertACSUsersMax};
var ACSUsers = {InsertACSUsers};
var JSLang = {'fl_coordplanet': '{fl_coordplanet}', 'fl_coordmoon': '{fl_coordmoon}', 'fl_coorddebris': '{fl_coorddebris}'};
var ShipsData = {Insert_ShipsData};
{InsertJSShipSet}
</script>
<script src="dist/js/fleet0.cachebuster-1545956361123.min.js"></script>
<link rel="stylesheet" type="text/css" href="dist/css/fleet0.cachebuster-1546565145290.min.css" />

{ChronoAppletsScripts}
<br />
{P_SFBInfobox}
<table width="750">
    <tbody{P_HideRetreatBox}>
        <tr>
            <th class="pad5 {RetreatBox_Color}" colspan="8">{RetreatBox_Text}</th>
        </tr>
        <tr>
            <th style="visibility: hidden; font-size: 5px;">&nbsp;</th>
        </tr>
    </tbody>
    <tr>
        <td colspan="8" class="c">
            <b class="flLe">{fl_title} {P_FlyingFleetsCount} / {P_MaxFleetSlots}</b>
            <b class="flRi">
                <span style="{P_Expeditions_isHidden_style}">
                    {P_FlyingExpeditions} / {P_MaxExpedSlots} {fl_expttl}
                </span>
            </b>
        </td>
    </tr>
    <tr>
        <th style="width: 20px;">{fl_id}</th>
        <th style="width: 70px;">{fl_mission}</th>
        <th style="width: 70px;">{fl_count}</th>
        <th style="width: 80px;">{fl_from}</th>
        <th style="width: 80px;">{fl_dest}</th>
        <th style="width: 80px;">{fl_dest_t}</th>
        <th style="width: 185px;">{fl_back_in}</th>
        <th style="width: 90px;" class="noMarg">{fl_order}</th>
    </tr>
    {FlyingFleetsRows}
    <tr{P_HideNoFreeSlots}>
        <th colspan="8">
            <b class="red">{fl_noslotfree}</b>
        </th>
    </tr>
</table>
<br/>
{Insert_ACSForm}
<form action="fleet1.php" method="post">
    <input type="hidden" name="getacsdata" value="{SetJoiningACSID}"/>
    <table width="750">
        <tr>
            <td colspan="4" class="c" style="text-align: center;">{fl_new_miss}</td>
        </tr>
        <tr>
            <th style="width: 230px;">{fl_fleet_typ}</th>
            <th style="width: 150px;">{fl_fleet_count}</th>
            <th style="width: 90px;">-</th>
            <th style="width: 180px;">-</th>
        </tr>
        {ShipsRow}
        <tbody{P_HideNoShipsInfo}>
            <tr>
                <th colspan="4" class="red pad5">{fl_noships}</th>
            </tr>
        </tbody>
        <tbody{P_HideNoSlotsInfo}>
            <tr>
                <th colspan="4" class="red pad5">{fl_nofreeslots}</th>
            </tr>
        </tbody>
        <tbody{P_HideSendShips}>
            <tr>
                <th colspan="2" class="help_left pad5" style="padding-left: 25px !important;">
                    {fl_totalStorage}: <b class="{P_StorageColor}" id="calcStorage">0</b>
                    <span class="fr {P_HideQuickRes}"><a href="?quickres=1" class="lime">({fl_SendAllResources})</a></span>
                </th>
                <th colspan="2" class="pad5"><a class="maxShipAll">{fl_selectall}</a> / <a class="noShipAll">{fl_unselectall}</a></th>
            </tr>
            <tr>
                <th colspan="4" class="pad2"><input class="SendButtom lime" type="submit" value="{fl_continue} &raquo;" /></th>
            </tr>
        </tbody>
    </table>
    <input type="hidden" name="galaxy" value="{P_Galaxy}"/>
    <input type="hidden" name="system" value="{P_System}"/>
    <input type="hidden" name="planet" value="{P_Planet}"/>
    <input type="hidden" name="planet_type" value="{P_PlType}"/>
    <input type="hidden" name="target_mission" value="{P_Mission}"/>
    <input type="hidden" name="quickres" value="{P_SetQuickRes}"/>
    <input type="hidden" name="gobackVars" value="{P_GoBackVars}"/>
    <input type="hidden" name="sending_fleet" value="1"/>
</form>
