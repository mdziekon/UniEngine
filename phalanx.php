<?php

define('INSIDE', true);

$_DontShowMenus = true;
$_DontShowRulesBox = true;
$_DontCheckPolls = true;
$_BlockFleetHandler = true;

$_EnginePath = './';
include($_EnginePath.'common.php');

loggedCheck();

includeLang('overview');
includeLang('phalanx');
$PageTPL = gettemplate('phalanx_body');
$PageTitle = $_Lang['Page_Title'];

$ThisMoon = &$_Planet;
$Now = time();
$ScanCost = PHALANX_DEUTERIUMCOST;
if(CheckAuth('supportadmin'))
{
    $ScanCost = 0;
    $ThisMoon['sensor_phalanx'] = 50;
}

if($ThisMoon['planet_type'] == 3)
{
    if($ThisMoon['sensor_phalanx'] > 0)
    {
        $parse = $_Lang;
        $ThisCoords = array
        (
            'galaxy' => $ThisMoon['galaxy'],
            'system' => $ThisMoon['system'],
            'planet' => $ThisMoon['planet']
        );
        $ThisPhalanx = $ThisMoon['sensor_phalanx'];
        $TargetData = array
        (
            'galaxy' => (isset($_GET['galaxy']) ? intval($_GET['galaxy']) : 0),
            'system' => (isset($_GET['system']) ? intval($_GET['system']) : 0),
            'planet' => (isset($_GET['planet']) ? intval($_GET['planet']) : 0)
        );

        include($_EnginePath.'includes/functions/GetPhalanxRange.php');
        $RangeDown = $ThisCoords['system'] - GetPhalanxRange($ThisPhalanx);
        $RangeUp = $ThisCoords['system'] + GetPhalanxRange($ThisPhalanx);

        $DenyScan = false;
        if($TargetData['galaxy'] < 1 OR $TargetData['galaxy'] > MAX_GALAXY_IN_WORLD OR $TargetData['system'] < 1 OR $TargetData['system'] > MAX_SYSTEM_IN_GALAXY OR $TargetData['planet'] < 1 OR $TargetData['planet'] > MAX_PLANET_IN_SYSTEM)
        {
            $DenyScan = true;
            $WhyDoNotScan = $_Lang['PhalanxError_BadCoordinates'];
        }
        if($TargetData['galaxy'] != $ThisCoords['galaxy'])
        {
            $DenyScan = true;
            $WhyDoNotScan = $_Lang['PhalanxError_GalaxyOutOfRange'];
        }
        if($TargetData['system'] > $RangeUp OR $TargetData['system'] < $RangeDown)
        {
            $DenyScan = true;
            $WhyDoNotScan = $_Lang['PhalanxError_TargetOutOfRange'];
        }
        if(CheckAuth('supportadmin'))
        {
            $DenyScan = false;
        }

        if($DenyScan !== true)
        {
            $Query_GetTarget = '';
            $Query_GetTarget .= "SELECT `pl`.`id`, `pl`.`id_owner`, `pl`.`name`, `pl`.`galaxy`, `pl`.`system`, `pl`.`planet`, `users`.`username` ";
            $Query_GetTarget .= "FROM {{table}} AS `pl` ";
            $Query_GetTarget .= "LEFT JOIN {{prefix}}users AS `users` ON `users`.`id` = `pl`.`id_owner` ";
            $Query_GetTarget .= "WHERE ";
            $Query_GetTarget .= "`pl`.`galaxy` = {$TargetData['galaxy']} AND ";
            $Query_GetTarget .= "`pl`.`system` = {$TargetData['system']} AND ";
            $Query_GetTarget .= "`pl`.`planet` = {$TargetData['planet']} AND ";
            $Query_GetTarget .= "`pl`.`planet_type` = 1 ";
            $Query_GetTarget .= "LIMIT 1; -- Phalanx|GetTarget";
            $Result_GetTarget = doquery($Query_GetTarget, 'planets', true);

            $TargetName = $Result_GetTarget['name'];
            $TargetID = $Result_GetTarget['id'];
            if($TargetID > 0)
            {
                // Calculate Fleets
                FlyingFleetHandler($ThisMoon, array($TargetID));
                $_DontShowMenus = true;
                if($ThisMoon['id'] > 0)
                {
                    if($ThisMoon['deuterium'] >= $ScanCost)
                    {
                        if($ScanCost > 0)
                        {
                            $ThisMoon['deuterium'] -= $ScanCost;
                            doquery("UPDATE {{table}} SET `deuterium` = `deuterium` - {$ScanCost} WHERE `id` = {$_User['current_planet']};", 'planets');

                            $UserDev_Log[] = array('PlanetID' => $ThisMoon['id'], 'Date' => $Now, 'Place' => 29, 'Code' => '0', 'ElementID' => '0', 'AdditionalData' => '');
                        }

                        $parse['Insert_Coord_Galaxy'] = $Result_GetTarget['galaxy'];
                        $parse['Insert_Coord_System'] = $Result_GetTarget['system'];
                        $parse['Insert_Coord_Planet'] = $Result_GetTarget['planet'];
                        if($Result_GetTarget['id_owner'] > 0)
                        {
                            $parse['Insert_OwnerName'] = "({$Result_GetTarget['username']})";
                            $parse['Insert_TargetName'] = $TargetName;
                        }
                        else
                        {
                            $parse['Table_Title2'] = '';
                            $parse['Insert_TargetName'] = "<b class=\"red\">{$_Lang['Abandoned_planet']}</b>";
                        }
                        $parse['Insert_My_Galaxy'] = $ThisCoords['galaxy'];
                        $parse['Insert_My_System'] = $ThisCoords['system'];
                        $parse['Insert_My_Planet'] = $ThisCoords['planet'];
                        $parse['Insert_MyMoonName'] = $ThisMoon['name'];
                        $parse['Insert_DeuteriumAmount'] = prettyNumber($ThisMoon['deuterium']);
                        if($ThisMoon['deuterium'] >= $ScanCost)
                        {
                            $parse['Insert_DeuteriumColor'] = 'lime';
                        }
                        else
                        {
                            $parse['Insert_DeuteriumColor'] = 'red';
                        }
                        $parse['skinpath'] = $_SkinPath;

                        $JoinStartNames = "LEFT JOIN {{prefix}}planets AS `planet1` ON `planet1`.`id` = {{table}}.`fleet_start_id` ";
                        $JoinEndNames = "LEFT JOIN {{prefix}}planets AS `planet2` ON `planet2`.`id` = {{table}}.`fleet_end_id` ";
                        $JoinOwnerName = "LEFT JOIN {{prefix}}users AS `usr` ON `usr`.`id` = {{table}}.`fleet_owner`";
                        $JoinACS = "LEFT JOIN {{prefix}}acs AS `get_acs` ON `main_fleet_id` = `fleet_id`";

                        $Query_GetFleets = '';
                        $Query_GetFleets .= "SELECT {{table}}.*, `planet1`.`name` as `start_name`, `planet2`.`name` as `end_name`, `get_acs`.`fleets_id`, `usr`.`username` AS `owner_name` ";
                        $Query_GetFleets .= "FROM {{table}} ";
                        $Query_GetFleets .= "{$JoinStartNames} {$JoinEndNames} {$JoinOwnerName} {$JoinACS} ";
                        $Query_GetFleets .= "WHERE ";
                        $Query_GetFleets .= "`fleet_start_id` = {$TargetID} OR `fleet_end_id` = {$TargetID} ";
                        $Query_GetFleets .= "; -- Phalanx|GetFleets";
                        $Result_GetFleets = doquery($Query_GetFleets, 'fleets');

                        $parse['phl_fleets_table'] = $_Lang['PhalanxInfo_NoMovements'];
                        if($Result_GetFleets->num_rows > 0)
                        {
                            include($_EnginePath.'includes/functions/BuildFleetEventTable.php');
                            $Record = 0;
                            while($FleetRow = $Result_GetFleets->fetch_assoc())
                            {
                                $Record += 1;

                                $StartTime = $FleetRow['fleet_start_time'];
                                $StayTime = $FleetRow['fleet_end_stay'];
                                $EndTime = $FleetRow['fleet_end_time'];

                                if($FleetRow['fleet_owner'] == $Result_GetTarget['id_owner'])
                                {
                                    $FleetType = true;
                                }
                                else
                                {
                                    $FleetType = false;
                                }

                                $FleetRow['fleet_resource_metal'] = 0;
                                $FleetRow['fleet_resource_crystal'] = 0;
                                $FleetRow['fleet_resource_deuterium'] = 0;
                                if(!empty($FleetRow['fleets_id']))
                                {
                                    $FleetRow['fleet_mission'] = 2;
                                }
                                if($StartTime > $Now)
                                {
                                    $Label = 'fs';
                                    $fpage[$StartTime.str_pad($FleetRow['fleet_id'], 20, '0', STR_PAD_LEFT)] = BuildFleetEventTable($FleetRow, 0, $FleetType, $Label, $Record, true);
                                }
                                if($FleetRow['fleet_mission'] != 4)
                                {
                                    $Label = 'ft';
                                    if($StayTime > $Now)
                                    {
                                        $fpage[$StayTime.str_pad($FleetRow['fleet_id'], 20, '0', STR_PAD_LEFT)] = BuildFleetEventTable($FleetRow, 1, $FleetType, $Label, $Record, true);
                                    }
                                    if($FleetType == true)
                                    {
                                        $Label = 'fe';
                                        if($EndTime > $Now)
                                        {
                                            $fpage[$EndTime.str_pad($FleetRow['fleet_id'], 20, '0', STR_PAD_LEFT)] = BuildFleetEventTable($FleetRow, 2, $FleetType, $Label, $Record, true);
                                        }
                                    }
                                }
                            }
                            if(!empty($fpage))
                            {
                                ksort($fpage, SORT_STRING);
                                $parse['phl_fleets_table'] = implode('', $fpage);
                            }
                        }
                        $page = parsetemplate($PageTPL, $parse);
                    }
                    else
                    {
                        message(sprintf($_Lang['PhalanxError_NoEnoughFuel'], prettyNumber($ScanCost)), $PageTitle);
                    }
                }
                else
                {
                    message($_Lang['PhalanxError_MoonDestroyed'], $PageTitle);
                }
            }
            else
            {
                message($_Lang['PhalanxError_CoordsEmpty'], $PageTitle);
            }
        }
        else
        {
            message($WhyDoNotScan, $PageTitle);
        }
    }
    else
    {
        message($_Lang['PhalanxError_NoPhalanxHere'], $PageTitle);
    }
}
else
{
    message($_Lang['PhalanxError_ScanOnlyFromMoon'], $PageTitle);
}

display($page, $PageTitle, false);

?>
