<?php

define('INSIDE', true);
define('IN_ADMIN', true);

$_SetAccessLogPreFilename = 'admin/';
$_SetAccessLogPath = '../';
$_EnginePath = './../';

include($_EnginePath.'common.php');

if(CheckAuth('sgo'))
{
    includeLang('admin/FleetControl');
    $Now = time();
    $PageTPL = gettemplate('admin/FleetControl_body');
    $RowTPL = gettemplate('admin/FleetControl_row');

    $Parse = $_Lang;
    $Parse['ChronoApplets'] = '';

    if(isset($_POST['process']) && $_POST['process'] == 1)
    {
        if(isset($_POST['cmd']) && ($_POST['cmd'] == 1 || $_POST['cmd'] == 2))
        {
            $UseFallback = ($_POST['cmd'] == 1 ? false : true);

            if(CheckAuth('supportadmin'))
            {
                if(!empty($_POST['f']))
                {
                    foreach($_POST['f'] as $FleetID => $IsOn)
                    {
                        if($IsOn == 'on')
                        {
                            $FleetID = floor(floatval($FleetID));
                            if($FleetID > 0)
                            {
                                $ActionArray[] = $FleetID;
                            }
                        }
                    }
                }
                if(!empty($ActionArray))
                {
                    include($_EnginePath.'includes/functions/FleetControl_Retreat.php');
                    $Result = FleetControl_Retreat("`fleet_id` IN (".implode(', ', $ActionArray).")", $UseFallback);
                    if(isset($Result['Updates']['Fleets']) && $Result['Updates']['Fleets'] > 0)
                    {
                        $MessageUse = ($UseFallback ? $_Lang['SysMsg_XFleetsFallenBack'] : $_Lang['SysMsg_XFleetsRetreated']);
                        $SysMessage[] = array('color' => 'lime', 'text' => sprintf($MessageUse, prettyNumber($Result['Updates']['Fleets']), prettyNumber($Result['Rows'])));
                        if((isset($Result['Updates']['ACS']) && $Result['Updates']['ACS'] > 0)  || (isset($Result['Deletes']['ACS']) && $Result['Deletes']['ACS'] > 0))
                        {
                            $MessageUse = ($UseFallback ? $_Lang['SysMsg_XACSChanged'] : $_Lang['SysMsg_XACSChanged']);
                            $SysMessage[] = array('color' => 'lime', 'text' => sprintf($MessageUse, prettyNumber($Result['Updates']['ACS']), prettyNumber($Result['Deletes']['ACS'])));
                        }

                        if(isset($_POST['sendNotice']) && $_POST['sendNotice'] == 'on')
                        {
                            $GetOwnersIDs = implode(', ', array_keys($Result['Types']));
                            $GetOwnersQuery = '';
                            $GetOwnersQuery .= "SELECT `fleet_owner`, COUNT(`fleet_id`) AS `Count`, '1' AS `Type` ";
                            $GetOwnersQuery .= "FROM {{table}} ";
                            $GetOwnersQuery .= "WHERE `fleet_id` IN ({$GetOwnersIDs}) GROUP BY `fleet_owner` ";
                            $GetOwnersQuery .= "UNION ";
                            $GetOwnersQuery .= "SELECT `fleet_owner`, COUNT(`fleet_id`) AS `Count`, '2' AS `Type` ";
                            $GetOwnersQuery .= "FROM {{table}} GROUP BY `fleet_owner`;";

                            $SQLResult_GetOwners = doquery($GetOwnersQuery, 'fleets');

                            if($SQLResult_GetOwners->num_rows > 0)
                            {
                                while($FleetOwner = $SQLResult_GetOwners->fetch_assoc())
                                {
                                    $OwnersArray[$FleetOwner['fleet_owner']][$FleetOwner['Type']] = $FleetOwner['Count'];
                                }
                            }
                            $ThisMsgID = ($UseFallback ? '094' : '093');
                            foreach($OwnersArray as $UserID => $UserData)
                            {
                                if($UserData['1'] <= 0)
                                {
                                    continue;
                                }

                                Cache_Message($UserID, '0', $Now, 80, '002', '022', json_encode(array('msg_id' => $ThisMsgID, 'args' => array(prettyNumber($UserData['1']), prettyNumber($UserData['2'])))));
                            }
                        }
                    }
                    else
                    {
                        $MessageUse = ($UseFallback ? $_Lang['SysMsg_0FleetsFallenBack'] : $_Lang['SysMsg_0FleetsRetreated']);
                        $SysMessage[] = array('color' => 'orange', 'text' => sprintf($MessageUse, prettyNumber($Result['Rows'])));
                    }
                }
                else
                {
                    $SysMessage[] = array('color' => 'red', 'text' => $_Lang['SysMsg_NoValidFleetsGiven']);
                }
            }
            else
            {
                $SysMessage[] = array('color' => 'red', 'text' => $_Lang['SysMsg_AccessDenied']);
            }
        }
    }

    if(!empty($SysMessage))
    {
        foreach($SysMessage as $Message)
        {
            $Parse['MessageText'][] = "<span class=\"{$Message['color']}\">{$Message['text']}</span>";
        }
        $Parse['MessageText'] = implode('<br/>', $Parse['MessageText']);
    }
    else
    {
        $Parse['Hide_Message'] = 'inv';
    }

    $HiddenOptions = 0;
    if(!CheckAuth('supportadmin'))
    {
        $Parse['Hide_OptionRetreat'] = 'hide';
        $Parse['Hide_OptionFallback'] = 'hide';
        $HiddenOptions += 2;
    }

    if($HiddenOptions >= 2)
    {
        $Parse['Hide_AllOptions'] = 'hide';
    }

    $Query = "SELECT * FROM {{table}} ORDER BY `fleet_start_time` ASC, `fleet_id` ASC;";
    $SQLResult_GetFleets = doquery($Query, 'fleets');

    if($SQLResult_GetFleets->num_rows == 0)
    {
        $Parse['Rows'] = "<tr><th colspan=\"10\" class=\"red pad\">{$_Lang['Error_NoFleetsFound']}</th></tr>";
        $Parse['Hide_AllOptions'] = 'hide';
    }
    else
    {
        while($Fleets = $SQLResult_GetFleets->fetch_assoc())
        {
            $GetACSData[] = $Fleets['fleet_id'];
            $Data[$Fleets['fleet_id']] = $Fleets;
            if(empty($GetUserNicks) OR !in_array($Fleets['fleet_owner'], $GetUserNicks))
            {
                $GetUserNicks[] = $Fleets['fleet_owner'];
            }
            if(empty($GetUserNicks) OR !in_array($Fleets['fleet_target_owner'], $GetUserNicks) AND $Fleets['fleet_target_owner'] > 0)
            {
                $GetUserNicks[] = $Fleets['fleet_target_owner'];
            }
        }
        if(!empty($GetUserNicks))
        {
            $Query = "SELECT `id`, `username` FROM {{table}} WHERE `id` IN (".implode(', ', $GetUserNicks).");";
            $SQLResult_GetUsers = doquery($Query, 'users');
            if($SQLResult_GetUsers->num_rows > 0)
            {
                while($UserData = $SQLResult_GetUsers->fetch_assoc())
                {
                    $UsersNicks[$UserData['id']] = $UserData['username'];
                }
            }
        }
        if(!empty($GetACSData))
        {
            foreach($GetACSData as $ACSTempData)
            {
                $CheckJoinedFleets[] = "`fleets_id` LIKE '%|{$ACSTempData}|%'";
            }
            $CheckJoinedFleets = implode(' OR ', $CheckJoinedFleets);

            $Query = "SELECT `id`, `main_fleet_id`, `fleets_id` FROM {{table}} WHERE `main_fleet_id` IN (".implode(', ', $GetACSData).") OR {$CheckJoinedFleets};";

            $SQLResult_GetACSData = doquery($Query, 'acs');

            if($SQLResult_GetACSData->num_rows > 0)
            {
                while($TempACSData = $SQLResult_GetACSData->fetch_assoc())
                {
                    $Temp1 = explode(',', str_replace('|', '', $TempACSData['fleets_id']));
                    foreach($Temp1 as $TempData)
                    {
                        $ACSData[$TempData] = $TempACSData['id'];
                    }
                    $ACSData[$TempACSData['main_fleet_id']] = $TempACSData['id'];
                }
            }
        }

        $Now = time();

        include("{$_EnginePath}/includes/functions/InsertJavaScriptChronoApplet.php");

        $AllFleetParse = '';
        foreach($Data as $FleetID => $FleetData)
        {
            $FleetParse = false;
            $FleetArray = false;
            $FleetShipsTemp = false;
            $FleetCount = 0;
            $FleetShipsParsed = false;

            $FleetParse['Fleet_ID'] = $FleetID;
            $FleetParse['Fleet_Owner']= "<a href=\"user_info.php?uid={$FleetData['fleet_owner']}\">{$UsersNicks[$FleetData['fleet_owner']]}<br/>[{$FleetData['fleet_owner']}]</a>";

            $FleetParse['Fleet_Mission'] = $_Lang['type_mission'][$FleetData['fleet_mission']];
            if(isset($ACSData[$FleetID]) && $ACSData[$FleetID] > 0)
            {
                if($FleetData['fleet_mission'] == 1)
                {
                    $FleetParse['Fleet_Mission'] = $_Lang['type_mission'][2];
                }
                $FleetParse['Fleet_Mission'] = "{$FleetParse['Fleet_Mission']}<br/>[ACS: {$ACSData[$FleetID]}]";
                if($FleetData['fleet_mission'] == 1)
                {
                    $FleetParse['Fleet_Mission'] = "<span class=\"orange help\" title=\"{$_Lang['Main_ACS_Fleet']}\">{$FleetParse['Fleet_Mission']}</span>";
                }
            }
            if(($FleetData['fleet_mess'] == 0 AND $FleetData['fleet_start_time'] <= $Now) OR ($FleetData['fleet_mess'] != 0 AND $FleetData['fleet_end_time'] <= $Now))
            {
                $FleetParse['Fleet_Mission'] = "{$FleetParse['Fleet_Mission']}<br/>[<b class=\"orange\">{$_Lang['Not_calculated']}</b>]";
            }
            if(($FleetData['fleet_start_time'] <= $Now AND $FleetData['fleet_mission'] != 5) OR ($FleetData['fleet_mission'] == 5 AND $FleetData['fleet_end_stay'] <= $Now))
            {
                $FleetParse['Fleet_Mission'] .= "<br/>[{$_Lang['Coming_back']}]";
            }
            if($FleetData['fleet_mission'] == 5)
            {
                if($FleetData['fleet_start_time'] <= $Now AND $FleetData['fleet_end_stay'] > $Now)
                {
                    $FleetParse['Fleet_Mission'] .= "<br/>(<span id=\"bxxs{$FleetID}\">".pretty_time($FleetData['fleet_end_stay'] - $Now, true, 'D')."</span>)";
                    $Parse['ChronoApplets'] .= InsertJavaScriptChronoApplet('s', $FleetID, $FleetData['fleet_end_stay'] - $Now);
                }
            }

            $FleetArray = explode(';', $FleetData['fleet_array']);
            foreach($FleetArray as $FleetShipsTemp)
            {
                if(!empty($FleetShipsTemp))
                {
                    $FleetShipsTemp = explode(',', $FleetShipsTemp);
                    $FleetCount += $FleetShipsTemp[1];
                    $FleetShipsParsed[] = "<tr><th class='help_th'>{$_Lang['tech'][$FleetShipsTemp[0]]}</th><th class='help_th'>".prettyNumber($FleetShipsTemp[1])."</th></tr>";
                }
            }
            $FleetCount = prettyNumber($FleetCount);
            $FleetParse['Fleet_Ships'] = "<table>".implode('', $FleetShipsParsed)."</table>";
            $FleetParse['Fleet_Array'] = "{$FleetCount}<br/>(?)";
            if($FleetData['fleet_resource_metal'] == 0 AND $FleetData['fleet_resource_crystal'] == 0 AND $FleetData['fleet_resource_deuterium'] == 0)
            {
                $FleetParse['Fleet_Cargo'] = $_Lang['No_cargo'];
            }
            else
            {
                $FleetParse['Fleet_Cargo'] = $_Lang['See_cargo'];
                $FleetParse['Fleet_Resources'] = "<table><tr><th class='help_th cargo_res'>{$_Lang['Metal']}</th><th class='help_th'>".prettyNumber($FleetData['fleet_resource_metal'])."</th></tr><tr><th class='help_th cargo_res'>{$_Lang['Crystal']}</th><th class='help_th'>".prettyNumber($FleetData['fleet_resource_crystal'])."</th></tr><tr><th class='help_th cargo_res'>{$_Lang['Deuterium']}</th><th class='help_th'>".prettyNumber($FleetData['fleet_resource_deuterium'])."</th></tr></table>";
                $FleetParse['Fleet_Cargo_class'] = ' fCar';
            }
            $FleetParse['Fleet_Start_Title'] = ($FleetData['fleet_start_type'] == '1' ? $_Lang['Start_from_planet'] : $_Lang['Start_from_moon']);
            $FleetParse['Fleet_Start'] = "<a href=\"../galaxy.php?mode=3&amp;galaxy={$FleetData['fleet_start_galaxy']}&amp;system={$FleetData['fleet_start_system']}&amp;planet={$FleetData['fleet_start_planet']}\">[{$FleetData['fleet_start_galaxy']}:{$FleetData['fleet_start_system']}:{$FleetData['fleet_start_planet']}] ".($FleetData['fleet_start_type'] == '1' ? $_Lang['Planet_sign'] : $_Lang['Moon_sign'])."</a>";
            $FleetParse['Fleet_Start'] .= "<br/>".date('d.m.Y', $FleetData['fleet_send_time'])."<br/>".date('H:i:s', $FleetData['fleet_send_time'])."<br/>(<span id=\"bxxa{$FleetID}\">".pretty_time($Now - $FleetData['fleet_send_time'], true, 'D')."</span> {$_Lang['_ago']})";
            $Parse['ChronoApplets'] .= InsertJavaScriptChronoApplet('a', $FleetID, $FleetData['fleet_send_time'], true, true);

            $FleetParse['Fleet_End_Title'] = ($FleetData['fleet_end_type'] == '1' ? $_Lang['Target_is_planet'] : ($FleetData['fleet_end_type'] == '2' ? $_Lang['Target_is_debris'] : $_Lang['Target_is_moon']));
            $FleetParse['Fleet_End'] = "<a href=\"../galaxy.php?mode=3&amp;galaxy={$FleetData['fleet_end_galaxy']}&amp;system={$FleetData['fleet_end_system']}&amp;planet={$FleetData['fleet_end_planet']}\">[{$FleetData['fleet_end_galaxy']}:{$FleetData['fleet_end_system']}:{$FleetData['fleet_end_planet']}] ".($FleetData['fleet_end_type'] == '1' ? $_Lang['Planet_sign'] : ($FleetData['fleet_end_type'] == '2' ? $_Lang['Debris_sign'] : $_Lang['Moon_sign']))."</a>";
            if($FleetData['fleet_start_time'] <= $Now)
            {
                $FleetParse['Fleet_end_time_set'] = "<span class=\"lime\">{$_Lang['TargetAchieved']}</span>";
            }
            else
            {
                $FleetParse['Fleet_end_time_set'] = pretty_time($FleetData['fleet_start_time'] - $Now, true, 'D');
                $Parse['ChronoApplets'] .= InsertJavaScriptChronoApplet('b', $FleetID, $FleetData['fleet_start_time'] - $Now);
            }
            $FleetParse['Fleet_End'] .= "<br/>".date('d.m.Y', $FleetData['fleet_start_time'])."<br/>".date('H:i:s', $FleetData['fleet_start_time'])."<br/>(<span id=\"bxxb{$FleetID}\">{$FleetParse['Fleet_end_time_set']}</span>)";

            if($FleetData['fleet_target_owner'] > 0)
            {
                $FleetParse['Fleet_End_owner'] = "{$UsersNicks[$FleetData['fleet_target_owner']]}<br/>[{$FleetData['fleet_target_owner']}]";
                if($FleetData['fleet_target_owner'] == $FleetData['fleet_owner'])
                {
                    $FleetParse['Fleet_End_owner_color'] = 'lime';
                }
                else
                {
                    if(in_array($FleetData['fleet_mission'], array(1, 2, 6, 9, 10, 11)))
                    {
                        $FleetParse['Fleet_End_owner_color'] = 'red';
                    }
                    else
                    {
                        $FleetParse['Fleet_End_owner_color'] = 'blue';
                    }
                }
                $FleetParse['Fleet_End_owner'] = "<a class=\"{$FleetParse['Fleet_End_owner_color']}\" href=\"user_info.php?uid={$FleetData['fleet_target_owner']}\">{$FleetParse['Fleet_End_owner']}</a>";
            }
            else
            {
                $FleetParse['Fleet_End_owner'] = $_Lang['None'];
            }

            if($FleetData['fleet_end_time'] <= $Now)
            {
                $FleetParse['Fleet_back_time_set'] = "<span class=\"orange\">{$_Lang['FleetCameBack']}</span>";
            }
            else
            {
                $FleetParse['Fleet_back_time_set'] = pretty_time($FleetData['fleet_end_time'] - $Now, true, 'D');
                $Parse['ChronoApplets'] .= InsertJavaScriptChronoApplet('c', $FleetID, $FleetData['fleet_end_time'] - $Now);
            }
            $FleetParse['Fleet_Back_time'] = date('d.m.Y', $FleetData['fleet_end_time'])."<br/>".date('H:i:s', $FleetData['fleet_end_time'])."<br/>(<span id=\"bxxc{$FleetID}\">{$FleetParse['Fleet_back_time_set']}</span>)";

            $AllFleetParse .= parsetemplate($RowTPL, $FleetParse);
        }

        $Parse['Rows'] = $AllFleetParse;
    }

    $Page = parsetemplate($PageTPL, $Parse);

    display($Page, $_Lang['PageTitle'], false, true);
}
else
{
    AdminMessage($_Lang['sys_noalloaw'], $_Lang['sys_noaccess']);
}

?>
