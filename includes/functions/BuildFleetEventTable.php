<?php

function BuildFleetEventTable($FleetRow, $Status, $Owner, $Label, $Record, $Phalanx = false)
{
    global $_Lang, $InsertJSChronoApplet_GlobalIncluded;
    static $InsertJSChronoApplet_Included = false, $Template = false, $ThisDate = false;

    $FleetStyle = array
    (
         1 => 'attack',
         2 => 'federation',
         3 => 'transport',
         4 => 'deploy',
         5 => 'hold',
         6 => 'espionage',
         7 => 'colony',
         8 => 'harvest',
         9 => 'destroy',
        10 => 'missile',
    );
    $FleetStatus = array(0 => 'flight', 1 => 'holding', 2 => 'return');

    if($Template === false)
    {
        global $_User;

        GlobalTemplate_AppendToAfterBody(gettemplate('_FleetTable_files'));
        $Template = gettemplate('_FleetTable_row');
        if(!empty($_User['settings_FleetColors']))
        {
            $TPL_FleetColors_Row = gettemplate('_FleetTable_fleetColors_row');
            $FleetColors = json_decode($_User['settings_FleetColors'], true);
            foreach($FleetColors as $TypeKey => $Missions)
            {
                if($TypeKey == 'ownfly')
                {
                    $ThisType = 'flight';
                    $ThisOwn = 'own';
                    $CheckHold = true;
                }
                else if($TypeKey == 'owncb')
                {
                    $ThisType = 'return';
                    $ThisOwn = 'own';
                    $CheckHold = false;
                }
                else if($TypeKey == 'nonown')
                {
                    $ThisType = 'flight';
                    $ThisOwn = '';
                    $CheckHold = true;
                }

                foreach($Missions as $MissionID => $MissionColor)
                {
                    if(!empty($MissionColor))
                    {
                        $MissionColors_Styles[] = parsetemplate($TPL_FleetColors_Row, array
                        (
                            'MissionType' => $ThisType,
                            'MissionName' => $ThisOwn.$FleetStyle[$MissionID],
                            'MissionColor' => $MissionColor,
                        ));
                        if($CheckHold === true AND $MissionID == 5)
                        {
                            $MissionColors_Styles[] = parsetemplate($TPL_FleetColors_Row, array
                            (
                                'MissionType' => 'holding',
                                'MissionName' => $ThisOwn.$FleetStyle[$MissionID],
                                'MissionColor' => $MissionColor,
                            ));
                        }
                    }
                }
            }

            if(!empty($MissionColors_Styles))
            {
                GlobalTemplate_AppendToAfterBody(parsetemplate(gettemplate('_FleetTable_fleetColors'), array('InsertStyles' => implode(' ', $MissionColors_Styles))));
            }
        }
    }
    if($ThisDate === false)
    {
        $ThisDate = date('d/m | ');
    }

    if($InsertJSChronoApplet_GlobalIncluded !== true)
    {
        if($InsertJSChronoApplet_Included === false)
        {
            include('InsertJavaScriptChronoApplet.php');
            $InsertJSChronoApplet_Included = true;
        }
    }

    if($Owner == true)
    {
        $FleetPrefix = 'own';
    }
    else
    {
        $FleetPrefix = '';
    }

    $MissionType    = $FleetRow['fleet_mission'];
    $FleetContent    = CreateFleetPopupedFleetLink($FleetRow, (($Owner === true) ? $_Lang['ov_fleet'] : $_Lang['ov_fleet2']));
    $FleetMission    = $_Lang['type_mission'][$MissionType];

    $StartType        = $FleetRow['fleet_start_type'];
    $TargetType        = $FleetRow['fleet_end_type'];

    if($Status != 2)
    {
        if($StartType == 1)
        {
            $StartID = $_Lang['ov_planet_to'];
        }
        else if($StartType == 3)
        {
            $StartID = $_Lang['ov_moon_to'];
        }
        $StartID .= $FleetRow['start_name'].' ';
        $StartID .= GetStartAdressLink($FleetRow, 'white', $Phalanx);

        if($MissionType != 15)
        {
            if($TargetType == 1)
            {
                $TargetID = $_Lang['ov_planet_to_target'];
            }
            else if($TargetType == 2)
            {
                $TargetID = $_Lang['ov_debris_to_target'];
            }
            else if($TargetType == 3)
            {
                $TargetID = $_Lang['ov_moon_to_target'];
            }
        }
        else
        {
            $TargetID = $_Lang['ov_explo_to_target'];
        }
        $TargetID .= $FleetRow['end_name'].' ';
        $TargetID .= GetTargetAdressLink($FleetRow, 'white', $Phalanx);
    }
    else
    {
        if($StartType == 1)
        {
            $StartID = $_Lang['ov_back_planet'];
        }
        else if($StartType == 3)
        {
            $StartID = $_Lang['ov_back_moon'];
        }
        $StartID .= $FleetRow['start_name'].' ';
        $StartID .= GetStartAdressLink($FleetRow, 'white', $Phalanx);

        if($MissionType != 15)
        {
            if($TargetType == 1)
            {
                $TargetID = $_Lang['ov_planet_from'];
            }
            else if($TargetType == 2)
            {
                $TargetID = $_Lang['ov_debris_from'];
            }
            else if($TargetType == 3)
            {
                $TargetID = $_Lang['ov_moon_from'];
            }
        }
        else
        {
            $TargetID = $_Lang['ov_explo_from'];
        }
        $TargetID .= $FleetRow['end_name'].' ';
        $TargetID .= GetTargetAdressLink($FleetRow, 'white', $Phalanx);
    }

    if($Owner == true)
    {
        if($Phalanx === false)
        {
            $EventString = $_Lang['ov_une'];
        }
        else
        {
            $EventString = $_Lang['ov_une_phalanx'];
        }
        $EventString .= $FleetContent;
    }
    else
    {
        $EventString = $_Lang['ov_une_hostile'];
        $EventString .= $FleetContent;
        $EventString .= $_Lang['ov_hostile'];
        $EventString .= BuildHostileFleetPlayerLink($FleetRow , $Phalanx);
    }

    if($Status == 0)
    {
        $Time = $FleetRow['fleet_start_time'];
        $Rest = $Time - time();
        $EventString .= $_Lang['ov_vennant'];
        $EventString .= $StartID;
        $EventString .= $_Lang['ov_atteint'];
        $EventString .= $TargetID;
        $EventString .= $_Lang['ov_mission'];
    }
    else if($Status == 1)
    {
        $Time = $FleetRow['fleet_end_stay'];
        $Rest = $Time - time();
        $EventString .= $_Lang['ov_vennant'];
        $EventString .= $StartID;
        if($FleetRow['fleet_mission'] == 5)
        {
            $EventString .= $_Lang['ov_onorbit_stay'];
        }
        else
        {
            $EventString .= $_Lang['ov_explo_stay'];
        }
        $EventString .= $TargetID;
        $EventString .= $_Lang['ov_explo_mission'];
    }
    else if($Status == 2)
    {
        $Time = $FleetRow['fleet_end_time'];
        $Rest = $Time - time();
        $EventString .= $_Lang['ov_rentrant'];
        $EventString .= $TargetID;
        $EventString .= $StartID;
        $EventString .= $_Lang['ov_mission'];
    }
    $EventString .= $FleetMission;

    $bloc['fleet_status']        = $FleetStatus[$Status];
    $bloc['fleet_prefix']        = $FleetPrefix;
    $bloc['fleet_style']        = $FleetStyle[$MissionType];
    $bloc['fleet_order']        = $Label.$Record;
    $bloc['fleet_countdown']    = pretty_time($Rest, true);
    $bloc['fleet_time']            = str_replace($ThisDate, '', date('d/m | H:i:s', $Time));
    $bloc['fleet_descr']        = $EventString;
    GlobalTemplate_AppendToAfterBody(InsertJavaScriptChronoApplet($Label, $Record, $Rest));

    return parsetemplate($Template, $bloc);
}

?>
