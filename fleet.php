<?php

define('INSIDE', true);

$_EnginePath = './';

include($_EnginePath.'common.php');

loggedCheck();

if(!$_Planet)
{
    message($_Lang['fl_noplanetrow'], $_Lang['fl_error']);
}

include($_EnginePath.'/includes/functions/InsertJavaScriptChronoApplet.php');

$Now = time();
includeLang('fleet');
$BodyTPL                = gettemplate('fleet_body');
$FleetRowTPL            = gettemplate('fleet_frow');
$FleetDetTPL            = gettemplate('fleet_fdetail');
$FleetResTPL            = gettemplate('fleet_fresinfo');
$FleetAddTPL            = parsetemplate(gettemplate('fleet_faddinfo'), $_Lang);
$ShipRowTPL             = gettemplate('fleet_srow');
$TPL_Orders_Retreat     = gettemplate('fleet_orders_retreat');
$TPL_Orders_ACS         = gettemplate('fleet_orders_acs');
$TPL_Orders_JoinToACS   = gettemplate('fleet_orders_jointoacs');

$FleetRowTPL = str_replace(
    array('fl_fleetinfo_ships', 'fl_flytargettime', 'fl_flygobacktime', 'fl_flystaytime', 'fl_flyretreattime'),
    array($_Lang['fl_fleetinfo_ships'], $_Lang['fl_flytargettime'], $_Lang['fl_flygobacktime'], $_Lang['fl_flystaytime'], $_Lang['fl_flyretreattime']),
    $FleetRowTPL
);
$FleetResTPL = str_replace(
    array('TitleMain', 'TitleMetal', 'TitleCrystal', 'TitleDeuterium'),
    array($_Lang['fl_fleetinfo_resources'], $_Lang['Metal'], $_Lang['Crystal'], $_Lang['Deuterium']),
    $FleetResTPL
);
$ShipRowTPL = str_replace(
    array('fl_fleetspeed', 'fl_selmax', 'fl_selnone'),
    array($_Lang['fl_fleetspeed'], $_Lang['fl_selmax'], $_Lang['fl_selnone']),
    $ShipRowTPL
);

$_Lang['ShipsRow'] = '';
$_Lang['FlyingFleetsRows'] = '';
$InsertChronoApplets = '';

$Hide = ' class="hide"';

$FlyingFleetsCount = 0;
$FlyingExpeditions = 0;

$missiontype = array
(
    1 => $_Lang['type_mission'][1],
    2 => $_Lang['type_mission'][2],
    3 => $_Lang['type_mission'][3],
    4 => $_Lang['type_mission'][4],
    5 => $_Lang['type_mission'][5],
    6 => $_Lang['type_mission'][6],
    7 => $_Lang['type_mission'][7],
    8 => $_Lang['type_mission'][8],
    9 => $_Lang['type_mission'][9],
    15 => $_Lang['type_mission'][15]
);

if($_User['settings_useprettyinputbox'] == 1)
{
    $_Lang['P_AllowPrettyInputBox'] = 'true';
}
else
{
    $_Lang['P_AllowPrettyInputBox'] = 'false';
}
$_Lang['InsertACSUsers'] = 'new Object()';
$_Lang['InsertACSUsersMax'] = MAX_ACS_JOINED_PLAYERS;

// Fleet Blockade Info (here, only for Global Block)
$GetSFBData = doquery("SELECT `ID`, `EndTime`, `BlockMissions`, `DontBlockIfIdle`, `Reason` FROM {{table}} WHERE `Type` = 1 AND `StartTime` <= UNIX_TIMESTAMP() AND (`EndTime` > UNIX_TIMESTAMP() OR `PostEndTime` > UNIX_TIMESTAMP()) ORDER BY `EndTime` DESC LIMIT 1;", 'smart_fleet_blockade', true);
if($GetSFBData['ID'] > 0)
{
    // Fleet Blockade is Active
    include($_EnginePath.'includes/functions/CreateSFBInfobox.php');
    $_Lang['P_SFBInfobox'] = CreateSFBInfobox($GetSFBData, array('standAlone' => true, 'Width' => 750, 'MarginBottom' => 10));
}

// Show RetreatBox (when fleet was retreated)
if(isset($_GET['ret']))
{
    if(!isset($_GET['m']))
    {
        $_GET['m'] = 0;
    }
    switch($_GET['m'])
    {
        case 1:
            $RetreatMessage = $_Lang['fl_notback'];
            break;
        case 2:
            $RetreatMessage = $_Lang['fl_isback'];
            break;
        case 3:
            $RetreatMessage = $_Lang['fl_isback2'];
            break;
        case 4:
            $RetreatMessage = $_Lang['fl_missiles_cannot_go_back'];
            break;
        case 5:
            $RetreatMessage = $_Lang['fl_onlyyours'];
            break;
        default:
            $RetreatMessage = $_Lang['fl_notback'];
            break;
    }
    if(!isset($_GET['c']))
    {
        $_GET['c'] = 0;
    }
    switch($_GET['c'])
    {
        case 1:
            $RetreatColor = 'red';
            break;
        case 2:
            $RetreatColor = 'lime';
            break;
        default:
            $RetreatColor = 'red';
            break;
    }

    $_Lang['RetreatBox_Color'] = $RetreatColor;
    $_Lang['RetreatBox_Text'] = $RetreatMessage;
}
else
{
    $_Lang['P_HideRetreatBox'] = $Hide;
}

// Get FlyingFleets Count
$SQLResult_GetFlyingFleets = doquery("SELECT `fleet_mission` FROM {{table}} WHERE `fleet_owner` = {$_User['id']};", 'fleets');
while($FleetData = $SQLResult_GetFlyingFleets->fetch_assoc())
{
    $FlyingFleetsCount += 1;
    if($FleetData['fleet_mission'] == 15)
    {
        $FlyingExpeditions += 1;
    }
}

// Get Available Slots for Fleets (1 + ComputerTech + 2 on Admiral)
// Get Available Slots for Expeditions (1 + floor(ExpeditionTech / 3))
$_Lang['P_MaxFleetSlots']        = 1 + $_User[$_Vars_GameElements[108]] + (($_User['admiral_time'] > $Now) ? 2 : 0);
$_Lang['P_MaxExpedSlots']        = 1 + floor($_User[$_Vars_GameElements[124]] / 3);
$_Lang['P_FlyingFleetsCount']    = (string)($FlyingFleetsCount + 0);
$_Lang['P_FlyingExpeditions']    = (string)($FlyingExpeditions + 0);

// Get own fleets
$FL = 'fleet_';
$FS = $FL.'start_';
$FE = $FL.'end_';
$FR = $FL.'resource_';
$Query_GetFleets = '';
$Query_GetFleets .= "SELECT `{$FL}id`, `{$FL}mess`, `{$FL}mission`, `{$FL}start_time`, `{$FL}end_time`, `{$FL}end_stay`, `{$FL}send_time`, `{$FL}array`, `{$FL}amount`, ";
$Query_GetFleets .= "`{$FS}galaxy`, `{$FS}system`, `{$FS}planet`, `{$FS}type`, ";
$Query_GetFleets .= "`{$FE}stay`, `{$FE}galaxy`, `{$FE}system`, `{$FE}planet`, `{$FE}type`, ";
$Query_GetFleets .= "`{$FR}metal`, `{$FR}crystal`, `{$FR}deuterium` ";
$Query_GetFleets .= "FROM {{table}} WHERE `fleet_owner` = {$_User['id']} ORDER BY `{$FS}time` ASC, `fleet_id` ASC;";
$Result_GetFleets = doquery($Query_GetFleets, 'fleets');

$i = 0;
$ACSCounter = 1;

$CheckACSFields = '`t`.`id`, `t`.`main_fleet_id`, `t`.`owner_id`, `t`.`fleets_id`, `t`.`start_time`, `t`.`end_galaxy`, `t`.`end_system`, `t`.`end_planet`, `t`.`end_type`, `userst`.`username`, `fleets`.`fleet_amount`, `fleets`.`fleet_array`, `fleet_start_galaxy`, `fleet_start_system`, `fleet_start_planet`, `fleet_start_type`, `fleet_start_time`';

$SQLResult_GetAvailableAlliedFlights = doquery(
    "SELECT {$CheckACSFields} FROM {{table}} AS `t` LEFT JOIN {{prefix}}users as `userst` ON `owner_id` = `userst`.`id` LEFT JOIN {{prefix}}fleets as `fleets` ON `main_fleet_id` = `fleets`.`fleet_id` WHERE (`users` LIKE '%|{$_User['id']}|%' OR `owner_id` = {$_User['id']}) AND `t`.`start_time` > UNIX_TIMESTAMP();",
    'acs'
);

$AddJoinButton = array();

if($SQLResult_GetAvailableAlliedFlights->num_rows > 0)
{
    $CheckACSForFleets = doquery(
        "SELECT `main_fleet_id`, `fleets_id` FROM {{table}} WHERE (`users` LIKE '%|{$_User['id']}|%' OR `owner_id` = {$_User['id']}) AND `start_time` > UNIX_TIMESTAMP();",
        'acs'
    );

    if($CheckACSForFleets->num_rows > 0)
    {
        while($ACSFleetsData = $CheckACSForFleets->fetch_assoc())
        {
            if(!empty($ACSFleetsData['fleets_id']))
            {
                $ACSFleetsID[$ACSFleetsData['main_fleet_id']] = explode(',', str_replace('|', '', $ACSFleetsData['fleets_id']));
                foreach($ACSFleetsID[$ACSFleetsData['main_fleet_id']] as $GetFleetID)
                {
                    $ACSFleetsIDIn[] = $GetFleetID;
                    $ACSFleetsIDs[$GetFleetID] = $ACSFleetsData['main_fleet_id'];
                }
            }
        }
    }

    if(!empty($ACSFleetsIDIn))
    {
        $SQLResult_GetACSFlyingFleetsData = doquery(
            "SELECT `fleet_id`, `fleet_array`, `fleet_amount` FROM {{table}} WHERE `fleet_id` IN (".implode(', ', $ACSFleetsIDIn).");",
            'fleets'
        );

        if($SQLResult_GetACSFlyingFleetsData->num_rows > 0)
        {
            while($ACSFleetsData = $SQLResult_GetACSFlyingFleetsData->fetch_assoc())
            {
                $ACSFleetsFillData[$ACSFleetsIDs[$ACSFleetsData['fleet_id']]][] = array('array' => $ACSFleetsData['fleet_array'], 'count' => $ACSFleetsData['fleet_amount']);
            }
        }
    }

    while($ACSData = $SQLResult_GetAvailableAlliedFlights->fetch_assoc())
    {
        if($ACSData['owner_id'] == $_User['id'])
        {
            $AddJoinButton[$ACSData['id']] = $ACSData['main_fleet_id'];
            if(!empty($ACSData['fleets_id']))
            {
                $ChangeMission[$ACSData['main_fleet_id']] = true;
            }
            continue;
        }

        $AdditionalFleets = array();
        $AdditionalFleetsCount = 0;

        $FleetRow = array();
        $FleetRow['FleetDetails'] = '';

        $i += 1;
        $FleetRow['FleetNo'] = $i;
        $FleetRow['FleetMissionColor'] = 'orange';
        $FleetRow['FleetMission'] = $missiontype[2];
        $FleetRow['ACSOwner'] = '<br/>('.$ACSData['username'].')';

        $ACSNoCounters[$ACSData['main_fleet_id']] = $ACSCounter;
        $FleetRow['FleetMission'] .= " #{$ACSCounter}";
        $ACSCounter += 1;

        if($ACSData['fleet_start_time'] < $Now)
        {
            if($ACSData['fleet_end_stay'] > 0 AND $ACSData['fleet_end_stay'] > $Now)
            {
                if($ACSData['fleet_mission'] == 15)
                {
                    $FleetRow['FleetBehaviour'] = $_Lang['fl_explore_to_ttl'];
                    $FleetRow['FleetBehaviourTxt'] = $_Lang['fl_explore_to'];
                }
                else
                {
                    $FleetRow['FleetBehaviour'] = $_Lang['fl_stay_to_ttl'];
                    $FleetRow['FleetBehaviourTxt'] = $_Lang['fl_stay_to'];
                }
            }
            else
            {
                if($ACSData['fleet_end_time'] > $Now)
                {
                    $FleetRow['FleetBehaviour'] = $_Lang['fl_back_to_ttl'];
                    $FleetRow['FleetBehaviourTxt'] = $_Lang['fl_back_to'];
                }
                else
                {
                    $FleetRow['FleetBehaviour'] = $_Lang['fl_cameback_ttl'];
                    $FleetRow['FleetBehaviourTxt'] = $_Lang['fl_cameback'];
                }
            }
        }
        else
        {
            $FleetRow['FleetBehaviour'] = $_Lang['fl_get_to_ttl'];
            $FleetRow['FleetBehaviourTxt'] = $_Lang['fl_get_to'];
        }

        if(!empty($ACSFleetsFillData[$ACSData['main_fleet_id']]))
        {
            foreach($ACSFleetsFillData[$ACSData['main_fleet_id']] as $FData)
            {
                $FDataArray = explode(';', $FData['array']);
                foreach($FDataArray as $Ships)
                {
                    if(!empty($Ships))
                    {
                        $Ships = explode(',', $Ships);
                        if(!isset($AdditionalFleets[$Ships[0]]))
                        {
                            $AdditionalFleets[$Ships[0]] = 0;
                        }
                        $AdditionalFleets[$Ships[0]] += $Ships[1];
                    }
                }
                $AdditionalFleetsCount += $FData['count'];
            }
        }

        $FleetArray = explode(';', $ACSData['fleet_array']);
        foreach($FleetArray as $Ships)
        {
            if(!empty($Ships))
            {
                $Ships = explode(',', $Ships);
                if(!isset($AdditionalFleets[$Ships[0]]))
                {
                    $AdditionalFleets[$Ships[0]] = 0;
                }
                $AdditionalFleets[$Ships[0]] += $Ships[1];
            }
        }

        foreach($AdditionalFleets as $Ship => $Count)
        {
            $FleetRow['FleetDetails'] .= parsetemplate($FleetDetTPL, array('Ship' => $_Lang['tech'][$Ship], 'Count' => prettyNumber($Count)));
        }

        $FleetRow['FleetCount'] = prettyNumber($ACSData['fleet_amount'] + $AdditionalFleetsCount);
        $FleetRow['FleetOriGalaxy'] = $ACSData['fleet_start_galaxy'];
        $FleetRow['FleetOriSystem'] = $ACSData['fleet_start_system'];
        $FleetRow['FleetOriPlanet'] = $ACSData['fleet_start_planet'];
        $FleetRow['FleetOriStart'] = date('d.m.Y<\b\r/>H:i:s', $ACSData['start_time']);
        $FleetRow['FleetOriType'] = ($ACSData['fleet_start_type'] == 1 ? 'planet' : ($ACSData['fleet_start_type'] == 3 ? 'moon' : 'debris'));

        $FleetRow['FleetDesGalaxy'] = $ACSData['end_galaxy'];
        $FleetRow['FleetDesSystem'] = $ACSData['end_system'];
        $FleetRow['FleetDesPlanet'] = $ACSData['end_planet'];
        $FleetRow['FleetDesArrive'] = date('d.m.Y<\b\r/>H:i:s', $ACSData['fleet_start_time']);
        $FleetRow['FleetDesType'] = ($ACSData['end_type'] == 1 ? 'planet' : ($ACSData['end_type'] == 3 ? 'moon' : 'debris'));

        $FleetRow['FleetEndTime'] = '-';

        $FleetRow['FleetFlyTargetTime'] = $ACSData['fleet_start_time'] - $Now;
        $InsertChronoApplets .= InsertJavaScriptChronoApplet('ft_', $i, $FleetRow['FleetFlyTargetTime']);
        $FleetRow['FleetFlyTargetTime'] = '<b class="lime flRi" id="bxxft_'.$i.'">'.pretty_time($FleetRow['FleetFlyTargetTime'], true, 'D').'</b>';
        $FleetRow['FleetHideComeBackTime'] = $FleetRow['FleetHideTargetorBackTime'] = $FleetRow['FleetHideStayTime'] = $FleetRow['FleetHideRetreatTime'] = $Hide;


        $JoinThisACS = '';
        if((isset($_GET['joinacs']) && $_GET['joinacs'] == $ACSData['id']) || (isset($_POST['getacsdata']) && $_POST['getacsdata'] > 0 && isset($_POST['getacsdata']) && $_POST['getacsdata'] == $ACSData['id']))
        {
            $JoinThisACS = ' checked';
            $_Lang['SetJoiningACSID'] = $ACSData['id'];
        }
        $FleetRow['FleetOrders'] = "<input type=\"radio\" value=\"{$ACSData['id']}\" class=\"setACS_ID pad5\" name=\"acs_select\"{$JoinThisACS}><br/>{$_Lang['fl_acs_joinnow']}";


        $_Lang['FlyingFleetsRows'] .= parsetemplate($FleetRowTPL, $FleetRow);
    }
}

while($f = $Result_GetFleets->fetch_assoc())
{
    $FleetRow = array();
    $FleetRow['FleetDetails'] = '';
    $AdditionalFleets = array();
    $AdditionalFleetsCount = 0;
    $Orders = '';

    $i += 1;
    $FleetRow['FleetNo'] = $i;
    if(in_array($f['fleet_id'], $AddJoinButton))
    {
        $FleetRow['FleetMissionColor']= 'orange';
    }
    if(isset($ChangeMission[$f['fleet_id']]))
    {
        $f['fleet_mission'] = 2;
    }
    $FleetRow['FleetMission'] = $missiontype[$f['fleet_mission']];

    if($f['fleet_start_time'] < $Now)
    {
        if($f['fleet_end_stay'] > 0 AND $f['fleet_end_stay'] > $Now)
        {
            if($f['fleet_mission'] == 15)
            {
                $FleetRow['FleetBehaviour'] = $_Lang['fl_explore_to_ttl'];
                $FleetRow['FleetBehaviourTxt'] = $_Lang['fl_explore_to'];
            }
            else
            {
                $FleetRow['FleetBehaviour'] = $_Lang['fl_stay_to_ttl'];
                $FleetRow['FleetBehaviourTxt'] = $_Lang['fl_stay_to'];
            }
        }
        else
        {
            if($f['fleet_end_time'] > $Now)
            {
                $FleetRow['FleetBehaviour'] = $_Lang['fl_back_to_ttl'];
                $FleetRow['FleetBehaviourTxt'] = $_Lang['fl_back_to'];
            }
            else
            {
                $FleetRow['FleetBehaviour'] = $_Lang['fl_cameback_ttl'];
                $FleetRow['FleetBehaviourTxt'] = $_Lang['fl_cameback'];
            }
        }
    }
    else
    {
        $FleetRow['FleetBehaviour'] = $_Lang['fl_get_to_ttl'];
        $FleetRow['FleetBehaviourTxt'] = $_Lang['fl_get_to'];
    }

    if(isset($ChangeMission[$f['fleet_id']]))
    {
        $f['fleet_mission'] = 1;
    }

    if(!empty($ACSFleetsFillData[$f['fleet_id']]))
    {
        $ACSNoCounters[$f['fleet_id']] = $ACSCounter;
        $FleetRow['FleetMission'] .= " #{$ACSCounter}";
        $ACSCounter += 1;

        foreach($ACSFleetsFillData[$f['fleet_id']] as $FData)
        {
            $FDataArray = explode(';', $FData['array']);
            foreach($FDataArray as $Ships)
            {
                if(!empty($Ships))
                {
                    $Ships = explode(',', $Ships);
                    if(!isset($AdditionalFleets[$Ships[0]]))
                    {
                        $AdditionalFleets[$Ships[0]] = 0;
                    }
                    $AdditionalFleets[$Ships[0]] += $Ships[1];
                }
            }
            $AdditionalFleetsCount += $FData['count'];
        }
    }
    else
    {
        if($f['fleet_mission'] == 2)
        {
            $FleetRow['FleetMission'] .= " #{$ACSNoCounters[$ACSFleetsIDs[$f['fleet_id']]]}";
        }
    }

    $FleetArray = explode(';', $f['fleet_array']);
    foreach($FleetArray as $Ships)
    {
        if(!empty($Ships))
        {
            $Ships = explode(',', $Ships);
            $FleetRow['FleetDetails'] .= parsetemplate($FleetDetTPL, array('Ship' => $_Lang['tech'][$Ships[0]], 'Count' => prettyNumber($Ships[1])));
        }
    }
    $FleetRow['FleetResInfo'] = parsetemplate($FleetResTPL, array('FleetMetal' => prettyNumber($f['fleet_resource_metal']), 'FleetCrystal' => prettyNumber($f['fleet_resource_crystal']), 'FleetDeuterium' => prettyNumber($f['fleet_resource_deuterium'])));

    if(!empty($AdditionalFleets))
    {
        $FleetRow['FleetAddShipsInfo'] = $FleetAddTPL;
        foreach($AdditionalFleets as $Ship => $Count)
        {
            $FleetRow['FleetAddShipsInfo'] .= parsetemplate($FleetDetTPL, array('Ship' => $_Lang['tech'][$Ship], 'Count' => prettyNumber($Count)));
        }
    }

    $FleetRow['FleetCount'] = prettyNumber($f['fleet_amount'] + $AdditionalFleetsCount);
    $FleetRow['FleetOriGalaxy'] = $f['fleet_start_galaxy'];
    $FleetRow['FleetOriSystem'] = $f['fleet_start_system'];
    $FleetRow['FleetOriPlanet'] = $f['fleet_start_planet'];
    $FleetRow['FleetOriStart'] = date('d.m.Y<\b\r/>H:i:s', $f['fleet_send_time']);
    $FleetRow['FleetOriType'] = ($f['fleet_start_type'] == 1 ? 'planet' : ($f['fleet_start_type'] == 3 ? 'moon' : 'debris'));

    $FleetRow['FleetDesGalaxy'] = $f['fleet_end_galaxy'];
    $FleetRow['FleetDesSystem'] = $f['fleet_end_system'];
    $FleetRow['FleetDesPlanet'] = $f['fleet_end_planet'];
    $FleetRow['FleetDesArrive'] = date('d.m.Y<\b\r/>H:i:s', $f['fleet_start_time']);
    $FleetRow['FleetDesType'] = ($f['fleet_end_type'] == 1 ? 'planet' : ($f['fleet_end_type'] == 3 ? 'moon' : 'debris'));

    $FleetRow['FleetEndTime'] = date('d.m.Y<\b\r/>H:i:s', $f['fleet_end_time']);

    $FleetTargetIn = $f['fleet_start_time'] - $Now;
    if($FleetTargetIn <= 0)
    {
        $FleetTargetInT = '-';
        $FleetRow['FleetHideTargetTime'] = $FleetRow['FleetHideTargetorBackTime'] = $Hide;
    }
    else
    {
        $InsertChronoApplets .= InsertJavaScriptChronoApplet('ft_', $i, $FleetTargetIn, false, false);
        $FleetTargetInT = '<b class="lime flRi" id="bxxft_'.$i.'">'.pretty_time($FleetTargetIn, true, 'D').'</b>';
        $FleetRow['FleetFlyTargetTime'] = $FleetTargetInT;
    }

    if($f['fleet_mission'] == 4 AND $f['fleet_mess'] == 0)
    {
        $FleetRow['FleetHideComeBackTime'] = $FleetRow['FleetHideTargetorBackTime'] = $Hide;
    }
    else
    {
        $FleetBackIn = $f['fleet_end_time'] - $Now;
        if($FleetBackIn <= 0)
        {
            $FleetBackInT = '<b class="lime flRi" id="bxxfb_'.$i.'">'.$_Lang['fl_already_cameback'].'</b>';
        }
        else
        {
            $InsertChronoApplets .= InsertJavaScriptChronoApplet('fb_', $i, $FleetBackIn);
            $FleetBackInT = '<b class="lime flRi" id="bxxfb_'.$i.'">'.pretty_time($FleetBackIn, true, 'D').'</b>';
        }
        $FleetRow['FleetFlyBackTime'] = $FleetBackInT;
    }

    if($f['fleet_end_stay'] > 0 AND $f['fleet_end_stay'] > $Now)
    {
        $InsertChronoApplets .= InsertJavaScriptChronoApplet('fs_', $i, $f['fleet_end_stay'] - $Now);
        $FleetRow['FleetFlyStayTime'] = '<b class="lime flRi" id="bxxfs_'.$i.'">'.pretty_time($f['fleet_end_stay'] - $Now, true, 'D').'</b>';
    }
    else
    {
        $FleetRow['FleetHideStayTime'] = $Hide;
    }

    if($f['fleet_mess'] == 0)
    {
        $FleetRow['AllowRetreat'] = true;
        $FleetRow['RetreatType'] = 1;
        $Orders = '';
        $Orders .= parsetemplate($TPL_Orders_Retreat, array('FleetID' => $f['fleet_id'], 'ButtonText' => $_Lang['fl_sback']));
        if($f['fleet_mission'] == 1)
        {
            $Orders .= parsetemplate($TPL_Orders_ACS, array('FleetID' => $f['fleet_id'], 'ButtonText' => $_Lang['fl_associate']));
        }
        if(in_array($f['fleet_id'], $AddJoinButton))
        {
            $ACS_GetID = array_keys($AddJoinButton, $f['fleet_id']);
            if(isset($_POST['getacsdata']) && $_POST['getacsdata'] > 0 && $_POST['getacsdata'] == $ACS_GetID[0])
            {
                $JoinThisACS = ' checked';
            }
            else
            {
                $JoinThisACS = '';
            }
            $Orders .= parsetemplate($TPL_Orders_JoinToACS, array('ACS_ID' => $ACS_GetID[0], 'checked' => $JoinThisACS, 'Text' => $_Lang['fl_acs_joinnow']));
        }
        else
        {
            $Orders .= "{AddACSJoin_{$f['fleet_id']}}";
        }
    }
    else
    {
        if($f['fleet_mission'] == 5 AND $f['fleet_end_stay'] > $Now)
        {
            $FleetRow['AllowRetreat'] = true;
            $FleetRow['RetreatType'] = 2;
            $Orders = '';
            $Orders .= parsetemplate($TPL_Orders_Retreat, array('FleetID' => $f['fleet_id'], 'ButtonText' => $_Lang['fl_back_from_stay']));
        }
        else
        {
            $Orders = '&nbsp;';
        }
    }
    if(isset($FleetRow['AllowRetreat']) && $FleetRow['AllowRetreat'] === true)
    {
        if($FleetRow['RetreatType'] == 1 AND $FleetTargetIn > 0)
        {
            $InsertChronoApplets .= InsertJavaScriptChronoApplet(
                'fr_',
                $i,
                $f['fleet_send_time'],
                true,
                true,
                false,
                [
                    'reverseEndTimestamp' => $f['fleet_start_time']
                ]
            );
            $FleetRow['FleetRetreatTime'] = '<b class="flRi" id="bxxfr_'.$i.'">'.pretty_time($Now - $f['fleet_send_time'], true, 'D').'</b>';
        }
        else
        {
            $FleetRow['FleetRetreatTime'] = '<b class="flRi">'.pretty_time($f['fleet_start_time'] - $f['fleet_send_time'], true, 'D').'</b>';
        }
    }
    else
    {
        $FleetRow['FleetHideRetreatTime'] = $Hide;
    }
    $FleetRow['FleetOrders'] = $Orders;

    $_Lang['FlyingFleetsRows'] .= parsetemplate($FleetRowTPL, $FleetRow);
}

$_Lang['P_HideNoFreeSlots'] = $Hide;
if($i == 0)
{
    $_Lang['FlyingFleetsRows'] = '<tr><th colspan="8">-</th></tr>';
}
else
{
    if($FlyingFleetsCount >= $_Lang['P_MaxFleetSlots'])
    {
        $_Lang['P_HideNoFreeSlots'] = '';
    }
}

if(isset($_POST['acsmanage']) && $_POST['acsmanage'] == 'open')
{
    $ACSMsgCol = 'red';
    $_Lang['P_HideACSBoxOnError'] = $Hide;
    $FleetID = intval($_POST['fleet_id']);
    $_Lang['FleetID'] = $FleetID;
    if($FleetID > 0)
    {
        $QryGetFleet4ACSFields = '`fleet`.*, `planet`.`name`';
        $QryGetFleet4ACS = "SELECT {$QryGetFleet4ACSFields} FROM {{table}} AS `fleet` LEFT JOIN {{prefix}}planets AS `planet` ON `planet`.`id` = `fleet`.`fleet_end_id` WHERE `fleet`.`fleet_id` = {$FleetID} LIMIT 1;";
        $Fleet4ACS = doquery($QryGetFleet4ACS, 'fleets', true);
        if($Fleet4ACS['fleet_id'] == $FleetID AND $Fleet4ACS['fleet_owner'] == $_User['id'])
        {
            if($Fleet4ACS['fleet_mission'] == 1 AND $Fleet4ACS['fleet_mess'] == 0)
            {
                if($Fleet4ACS['fleet_start_time'] > $Now)
                {
                    $_Lang['P_HideACSBoxOnError'] = '';

                    $GetACSRow = doquery("SELECT * FROM {{table}} WHERE `main_fleet_id` = {$FleetID} LIMIT 1;", 'acs', true);
                    if($GetACSRow['id'] <= 0)
                    {
                        $ACSJustCreated = true;

                        $CreateACSName = substr($_User['username'].' '.date('d.m.Y H:i', $Now), 0, 50);
                        $QryCreateACSRow = '';
                        $QryCreateACSRow .= "INSERT INTO {{table}} SET `name` = '{$CreateACSName}', `main_fleet_id` = {$FleetID}, `owner_id` = {$_User['id']}, ";
                        $QryCreateACSRow .= "`start_time_org` = {$Fleet4ACS['fleet_start_time']}, `start_time` = `start_time_org`, `end_target_id` = {$Fleet4ACS['fleet_end_id']}, ";
                        $QryCreateACSRow .= "`end_galaxy` = {$Fleet4ACS['fleet_end_galaxy']}, `end_system` = {$Fleet4ACS['fleet_end_system']}, `end_planet` = {$Fleet4ACS['fleet_end_planet']}, `end_type` = {$Fleet4ACS['fleet_end_type']};";
                        doquery($QryCreateACSRow, 'acs');

                        $GetLastID = doquery("SELECT LAST_INSERT_ID() AS `ID`;", '', true);
                        $GetLastID = $GetLastID['ID'];
                        $GetACSRow = array
                        (
                            'id' => $GetLastID, 'name' => $CreateACSName, 'main_fleet_id' => $FleetID, 'owner_id' => $_User['id'],
                            'start_time_org' => $Fleet4ACS['fleet_start_time'], 'start_time' => $Fleet4ACS['fleet_start_time'], 'end_target_id' => $Fleet4ACS['fleet_end_id'],
                            'end_galaxy' => $Fleet4ACS['fleet_end_galaxy'], 'end_system' => $Fleet4ACS['fleet_end_system'], 'end_planet' => $Fleet4ACS['fleet_end_planet'], 'end_type' => $Fleet4ACS['fleet_end_type'],
                        );

                        doquery("UPDATE {{table}} SET `Fleet_ACSID` = {$GetLastID} WHERE `Fleet_ID` = {$FleetID};", 'fleet_archive');

                        if(strstr($_Lang['FlyingFleetsRows'], 'AddACSJoin_') !== false)
                        {
                            $_Lang['FlyingFleetsRows'] = str_replace('{AddACSJoin_'.$FleetID.'}', "<input type=\"radio\" value=\"{$GetLastID}\" class=\"setACS_ID pad5\" name=\"acs_select\"><br/>{$_Lang['fl_acs_joinnow']}", $_Lang['FlyingFleetsRows']);
                        }
                    }

                    $JSACSUsers[$_User['id']] = array('name' => $_User['username'], 'status' => $_Lang['fl_acs_leader'], 'canmove' => false, 'place' => 1);

                    if($_User['ally_id'] > 0)
                    {
                        $Data_GetInvitableUsers['AllyID'][] = $_User['ally_id'];

                        $Query_GetAllyPacts = '';
                        $Query_GetAllyPacts .= "SELECT IF(`AllyID_Sender` = {$_User['ally_id']}, `AllyID_Owner`, `AllyID_Sender`) AS `AllyID`, `Type` ";
                        $Query_GetAllyPacts .= "FROM {{table}} WHERE ";
                        $Query_GetAllyPacts .= "(`AllyID_Sender` = {$_User['ally_id']} OR `AllyID_Owner` = {$_User['ally_id']}) AND `Active` = 1 AND `Type` >= ".ALLYPACT_MILITARY;
                        $Query_GetAllyPacts .= "; -- fleet.php|GetAllyPacts";

                        $Result_GetAllyPacts = doquery($Query_GetAllyPacts, 'ally_pacts');

                        if($Result_GetAllyPacts->num_rows > 0)
                        {
                            while($FetchData = $Result_GetAllyPacts->fetch_assoc())
                            {
                                $Data_GetInvitableUsers['AllyID'][] = $FetchData['AllyID'];
                            }
                        }

                        $Data_GetInvitableUsers['AllyID'] = implode(', ', $Data_GetInvitableUsers['AllyID']);
                        $Query_GetInvitableUsers[0] = '';
                        $Query_GetInvitableUsers[0] .= "(";
                        $Query_GetInvitableUsers[0] .= "SELECT `id`, `username` FROM `{{prefix}}users` ";
                        $Query_GetInvitableUsers[0] .= "WHERE `ally_id` IN ({$Data_GetInvitableUsers['AllyID']}) AND `id` != {$_User['id']}";
                        $Query_GetInvitableUsers[0] .= ")";
                    }

                    $Query_GetInvitableUsers[1] = '';
                    $Query_GetInvitableUsers[1] .= "(";
                    $Query_GetInvitableUsers[1] .= "SELECT ";
                    $Query_GetInvitableUsers[1] .= "IF(`buddy`.`sender` = {$_User['id']}, `buddy`.`owner`, `buddy`.`sender`) AS `id`, ";
                    $Query_GetInvitableUsers[1] .= "`user`.`username` AS `username` ";
                    $Query_GetInvitableUsers[1] .= "FROM `{{prefix}}buddy` AS `buddy` ";
                    $Query_GetInvitableUsers[1] .= "LEFT JOIN `{{prefix}}users` AS `user` ON ";
                    $Query_GetInvitableUsers[1] .= "`user`.`id` = IF(`buddy`.`sender` = {$_User['id']}, `buddy`.`owner`, `buddy`.`sender`) ";
                    $Query_GetInvitableUsers[1] .= "WHERE (`buddy`.`sender` = {$_User['id']} OR `buddy`.`owner` = {$_User['id']}) AND `active` = 1";
                    $Query_GetInvitableUsers[1] .= ")";

                    $Query_GetInvitableUsers = implode(' UNION ', $Query_GetInvitableUsers);
                    $Query_GetInvitableUsers .= "; -- fleet.php|GetInvitableUsers";

                    $SQLResult_GetInvitableUsers = doquery($Query_GetInvitableUsers, 'users');

                    if($SQLResult_GetInvitableUsers->num_rows > 0)
                    {
                        while($InvitableUser = $SQLResult_GetInvitableUsers->fetch_assoc())
                        {
                            $InvitableUsers[$InvitableUser['id']] = $InvitableUser;
                            $JSACSUsers[$InvitableUser['id']] = array('name' => $InvitableUser['username'], 'status' => '', 'canmove' => true, 'place' => 2);
                        }
                    }

                    if(!isset($ACSJustCreated) || $ACSJustCreated !== true)
                    {
                        if(!empty($GetACSRow['users']))
                        {
                            $Users = str_replace('|', '', $GetACSRow['users']);
                            $Users = explode(',', $Users);
                            foreach($Users as $UsersID)
                            {
                                if($UsersID > 0)
                                {
                                    if(empty($JSACSUsers[$UsersID]['name']))
                                    {
                                        $Data_GetEmptyUsernames['ids'][] = $UsersID;
                                    }
                                    $Status = ((strstr($GetACSRow['user_joined'], "|{$UsersID}|") !== FALSE) ? $_Lang['fl_acs_joined'] : $_Lang['fl_acs_invited']);
                                    $JSACSUsers[$UsersID]['status'] = $Status;
                                    $JSACSUsers[$UsersID]['place'] = 1;
                                    if($Status == $_Lang['fl_acs_joined'])
                                    {
                                        $HasToBeInNewArray[] = $UsersID;
                                    }
                                }
                            }
                        }

                        if(!empty($Data_GetEmptyUsernames))
                        {
                            $Data_GetEmptyUsernames['count'] = count($Data_GetEmptyUsernames['ids']);
                            $Data_GetEmptyUsernames['ids'] = implode(',', $Data_GetEmptyUsernames['ids']);
                            $Query_GetEmptyUsernames = '';
                            $Query_GetEmptyUsernames .= "SELECT `id`, `username` FROM {{table}} ";
                            $Query_GetEmptyUsernames .= "WHERE `id` IN ({$Data_GetEmptyUsernames['ids']}) ";
                            $Query_GetEmptyUsernames .= "LIMIT {$Data_GetEmptyUsernames['count']}; -- fleet.php|GetEmptyUsernames";

                            $Result_GetEmptyUsernames = doquery($Query_GetEmptyUsernames, 'users');

                            if($Result_GetEmptyUsernames->num_rows > 0)
                            {
                                while($FetchData = $Result_GetEmptyUsernames->fetch_assoc())
                                {
                                    $JSACSUsers[$FetchData['id']]['name'] = $FetchData['username'];
                                }
                            }
                        }

                        if(!empty($_POST['acs_name']))
                        {
                            $NewName = trim($_POST['acs_name']);
                            $NewName = preg_replace('#[^a-zA-Z'.REGEXP_POLISHSIGNS.'0-9\_\-\.\ \:]#si', '', $NewName);
                            if($NewName != $GetACSRow['name'])
                            {
                                if(strlen($NewName) > 3)
                                {
                                    doquery("UPDATE {{table}} SET `name` = '{$NewName}' WHERE `id` = {$GetACSRow['id']};", 'acs');
                                    $GetACSRow['name'] = $NewName;
                                    $ACSMsgCol = 'lime';
                                    $ACSMsg = $_Lang['fl_acs_changesSaved'];
                                }
                                else
                                {
                                    $ACSMsgCol = 'red';
                                    $ACSMsg = $_Lang['fl_acs_error_shortname'];
                                }
                            }
                        }
                        if(isset($_POST['acsuserschanged']) && $_POST['acsuserschanged'] == '1')
                        {
                            if(!empty($_POST['acs_users']))
                            {
                                $NewUsersArray = array();
                                $ExplodeUsers = explode(',', $_POST['acs_users']);
                                $UsersCount = 0;
                                foreach($ExplodeUsers as $ACSUserID)
                                {
                                    $ACSUserID = intval($ACSUserID);
                                    if(isset($InvitableUsers[$ACSUserID]['id']) && $InvitableUsers[$ACSUserID]['id'] > 0)
                                    {
                                        if($UsersCount < MAX_ACS_JOINED_PLAYERS)
                                        {
                                            $NewUsersArray[$ACSUserID] = $ACSUserID;
                                            $UsersCount += 1;
                                        }
                                        else
                                        {
                                            break;
                                        }
                                    }
                                }
                                if(!empty($HasToBeInNewArray))
                                {
                                    foreach($HasToBeInNewArray as $UsersID)
                                    {
                                        if(!in_array($UsersID, $NewUsersArray))
                                        {
                                            $BreakUsersUpdate = true;
                                            $ACSMsg = $_Lang['fl_acs_cantkick_joined'];
                                            break;
                                        }
                                    }
                                }
                                if(!isset($BreakUsersUpdate) || $BreakUsersUpdate !== true)
                                {
                                    foreach($JSACSUsers as $UserID => $UserData)
                                    {
                                        if($UserData['canmove'] === false)
                                        {
                                            continue;
                                        }
                                        if(!in_array($UserID, $NewUsersArray))
                                        {
                                            if($UserData['place'] != 2)
                                            {
                                                $ChangedInUserArray = true;
                                                $JSACSUsers[$UserID]['place'] = 2;
                                                $JSACSUsers[$UserID]['status'] = '';
                                            }
                                        }
                                        else
                                        {
                                            if($UserData['place'] == 2)
                                            {
                                                $ChangedInUserArray = true;
                                                $JSACSUsers[$UserID]['place'] = 1;
                                                $JSACSUsers[$UserID]['status'] = $_Lang['fl_acs_invited'];
                                                $MessagesToSend[] = $UserID;
                                            }
                                        }
                                    }
                                    if($ChangedInUserArray === true)
                                    {
                                        foreach($NewUsersArray as $UserID)
                                        {
                                            $NewUserList[] = "|{$UserID}|";
                                        }
                                        if(!empty($NewUserList))
                                        {
                                            $NewUserList = implode(',', $NewUserList);
                                            $NewUserCount = count($NewUsersArray);
                                        }
                                        else
                                        {
                                            $NewUserList = '';
                                            $NewUserCount = '0';
                                        }
                                        doquery("UPDATE {{table}} SET `users` = '{$NewUserList}', `invited_users` = '{$NewUserCount}' WHERE `id` = {$GetACSRow['id']};", 'acs');
                                        if(!empty($MessagesToSend))
                                        {
                                            $Message = false;
                                            $Message['msg_id'] = '069';
                                            $Message['args'] = array
                                            (
                                                $_User['username'], (($Fleet4ACS['fleet_end_type'] == 1) ? $_Lang['to_planet'] : $_Lang['to_moon']),
                                                $Fleet4ACS['name'], $Fleet4ACS['fleet_end_galaxy'], $Fleet4ACS['fleet_end_system'],
                                                $Fleet4ACS['fleet_end_galaxy'], $Fleet4ACS['fleet_end_system'], $Fleet4ACS['fleet_end_planet'],
                                                (($Fleet4ACS['fleet_end_type'] == 1) ? $_Lang['to_this_planet'] : $_Lang['to_this_moon']),
                                                $Fleet4ACS['fleet_end_galaxy'], $Fleet4ACS['fleet_end_system'], $Fleet4ACS['fleet_end_planet'], $Fleet4ACS['fleet_end_type'],
                                                $GetACSRow['id'], $GetACSRow['name']
                                            );
                                            $Message = json_encode($Message);
                                            Cache_Message($MessagesToSend, 0, '', 1, '007', '018', $Message);
                                        }

                                        $ACSMsgCol = 'lime';
                                        $ACSMsg = $_Lang['fl_acs_changesSaved'];
                                    }
                                }
                            }
                        }
                    }

                    if(!empty($JSACSUsers))
                    {
                        $_Lang['InsertACSUsers'] = json_encode($JSACSUsers);
                        foreach($JSACSUsers as $UserID => $UserData)
                        {
                            if($UserData['place'] == 1)
                            {
                                $Pointer = &$_Lang['UsersInvited'];
                            }
                            else
                            {
                                $Pointer = &$_Lang['Users2Invite'];
                            }
                            if($UserData['canmove'] === false OR $UserData['status'] == $_Lang['fl_acs_joined'])
                            {
                                $IsDisabled = ' disabled';
                            }
                            else
                            {
                                $IsDisabled = '';
                            }
                            if(!empty($UserData['status']))
                            {
                                $Status = " ({$UserData['status']})";
                            }
                            else
                            {
                                $Status = '';
                            }
                            $Pointer .= "<option value=\"{$UserID}\"{$IsDisabled}>{$UserData['name']}{$Status}</option>";
                        }
                    }

                    if(empty($GetACSRow['name']))
                    {
                        $_Lang['ACSName'] = $_Lang['fl_acs_noname'];
                    }
                    else
                    {
                        $_Lang['ACSName'] = $GetACSRow['name'];
                    }
                }
                else
                {
                    $ACSMsg = $_Lang['fl_acs_timeup'];
                }
            }
            else
            {
                $ACSMsg = $_Lang['fl_acs_badmission'];
            }
        }
        else
        {
            $ACSMsg = $_Lang['fl_acs_noexist'];
        }
    }
    else
    {
        $ACSMsg = $_Lang['fl_acs_noid'];
    }

    if(!empty($ACSMsg))
    {
        $_Lang['P_HideACSMSG'] = '';
        $_Lang['P_ACSMSG'] = $ACSMsg;
        $_Lang['P_ACSMSGCOL']= $ACSMsgCol;
    }
    else
    {
        $_Lang['P_HideACSMSG'] = $Hide;
    }

    $_Lang['Insert_ACSForm'] = parsetemplate(gettemplate('fleet_acsform'), $_Lang);
}

$_Lang['FlyingFleetsRows'] = preg_replace('#\{AddACSJoin\_[0-9]+\}#si', '', $_Lang['FlyingFleetsRows']);

$_Lang['InsertJSShipSet'] = "var JSShipSet = false;";
if(!isPro())
{
    // Don't Allow to use this function to NonPro Players
    $_GET['quickres'] = 0;
}
if(isset($_GET['quickres']) && $_GET['quickres'] == 1)
{
    $_Lang['P_SetQuickRes'] = '1';
    $TotalResStorage = floor($_Planet['metal']) + floor($_Planet['crystal']) + floor($_Planet['deuterium']);
    $TotalShipsStorage[217] = $_Planet[$_Vars_GameElements[217]] * $_Vars_Prices[217]['capacity'];
    $TotalShipsStorage[203] = $_Planet[$_Vars_GameElements[203]] * $_Vars_Prices[203]['capacity'];
    $TotalShipsStorage[202] = $_Planet[$_Vars_GameElements[202]] * $_Vars_Prices[202]['capacity'];
    $TotalShipsStorage['all'] = array_sum($TotalShipsStorage);
    if($TotalResStorage >= $TotalShipsStorage['all'])
    {
        $JSSetShipsCount[217] = 'max';
        $JSSetShipsCount[203] = 'max';
        $JSSetShipsCount[202] = 'max';
    }
    else
    {
        if($TotalResStorage >= $TotalShipsStorage[217])
        {
            $JSSetShipsCount[217] = 'max';
            $TotalResStorage -= $TotalShipsStorage[217];
            if($TotalResStorage >= $TotalShipsStorage[203])
            {
                $JSSetShipsCount[203] = 'max';
                $TotalResStorage -= $TotalShipsStorage[203];
                if($TotalResStorage >= $TotalShipsStorage[202])
                {
                    $JSSetShipsCount[202] = 'max';
                }
                else
                {
                    $JSSetShipsCount[202] = $TotalResStorage / $_Vars_Prices[202]['capacity'];
                }
            }
            else
            {
                $JSSetShipsCount[203] = $TotalResStorage / $_Vars_Prices[203]['capacity'];
            }
        }
        else
        {
            $JSSetShipsCount[217] = $TotalResStorage / $_Vars_Prices[217]['capacity'];
        }
    }

    if(!empty($JSSetShipsCount))
    {
        $_Lang['InsertJSShipSet'] = "var JSShipSet = new Object;\n";
        foreach($JSSetShipsCount as $ShipID => $ShipCount)
        {
            $ShipCount = ceil($ShipCount);
            if($_Planet[$_Vars_GameElements[$ShipID]] > 0)
            {
                if($ShipCount == 'max')
                {
                    $ShipCount = $_Planet[$_Vars_GameElements[$ShipID]];
                }
                else if($ShipCount > $_Planet[$_Vars_GameElements[$ShipID]])
                {
                    $ShipCount = $_Planet[$_Vars_GameElements[$ShipID]];
                }
                $_Lang['InsertJSShipSet'] .= "JSShipSet['{$ShipID}'] = {$ShipCount};\n";
            }
        }
    }
}
else
{
    $_Lang['P_SetQuickRes'] = '0';
}

if(isset($_POST['gobackUsed']))
{
    if(!empty($_POST['FleetArray']))
    {
        $PostFleet = explode(';', $_POST['FleetArray']);
        foreach($PostFleet as $Data)
        {
            if(!empty($Data))
            {
                $Data = explode(',', $Data);
                if(in_array($Data[0], $_Vars_ElementCategories['fleet']))
                {
                    $InsertShipCount[$Data[0]] = prettyNumber($Data[1]);
                }
            }
        }
    }
}

foreach($_Vars_ElementCategories['fleet'] as $ID)
{
    if($_Planet[$_Vars_GameElements[$ID]] > 0)
    {
        if(empty($_Vars_Prices[$ID]['engine']))
        {
            continue;
        }
        $ThisShip = array();

        $ThisShip['ID'] = $ID;
        $ThisShip['Speed'] = prettyNumber(getShipsCurrentSpeed($ID, $_User));
        $ThisShip['Name'] = $_Lang['tech'][$ID];
        $ThisShip['Count'] = prettyNumber($_Planet[$_Vars_GameElements[$ID]]);
        if($ID == 210)
        {
            $ShipsData['storage'][$ID] = 0;
        }
        else
        {
            $ShipsData['storage'][$ID] = $_Vars_Prices[$ID]['capacity'];
        }
        $ShipsData['count'][$ID] = $_Planet[$_Vars_GameElements[$ID]];

        $ThisShip['MaxCount'] = explode('.', sprintf('%f', floor($_Planet[$_Vars_GameElements[$ID]])));
        $ThisShip['MaxCount'] = (string)$ThisShip['MaxCount'][0];

        if(!empty($InsertShipCount[$ID]))
        {
            $ThisShip['InsertShipCount'] = $InsertShipCount[$ID];
        }
        else
        {
            $ThisShip['InsertShipCount'] = '0';
        }

        $_Lang['ShipsRow'] .= parsetemplate($ShipRowTPL, $ThisShip);
    }
}
$_Lang['Insert_ShipsData'] = json_encode(isset($ShipsData) ? $ShipsData : null);

$_Lang['P_HideNoSlotsInfo'] = $Hide;
$_Lang['P_HideSendShips'] = $Hide;
$_Lang['P_HideNoShipsInfo'] = $Hide;
if(!empty($_Lang['ShipsRow']))
{
    if($FlyingFleetsCount >= $_Lang['P_MaxFleetSlots'])
    {
        $_Lang['P_HideNoSlotsInfo'] = '';
    }
    else
    {
        $_Lang['P_HideSendShips'] = '';
    }
}
else
{
    $_Lang['P_HideNoShipsInfo'] = '';
}

$_Lang['ChronoAppletsScripts'] = $InsertChronoApplets;

if(isset($_POST['gobackUsed']))
{
    $GoBackVars = array
    (
        'speed' => $_POST['speed'],
    );
    if(!empty($_POST['gobackVars']))
    {
        $_Lang['P_GoBackVars'] = json_decode(base64_decode($_POST['gobackVars']), true);
        if((array)$_Lang['P_GoBackVars'] === $_Lang['P_GoBackVars'])
        {
            $GoBackVars = array_merge($_Lang['P_GoBackVars'], $GoBackVars);
        }
    }

    $_Lang['SetJoiningACSID'] = (isset($_POST['getacsdata']) ? $_POST['getacsdata'] : null);
    $_Lang['P_Galaxy'] = (isset($_POST['galaxy']) ? $_POST['galaxy'] : null);
    $_Lang['P_System'] = (isset($_POST['system']) ? $_POST['system'] : null);
    $_Lang['P_Planet'] = (isset($_POST['planet']) ? $_POST['planet'] : null);
    $_Lang['P_PlType'] = (isset($_POST['planettype']) ? $_POST['planettype'] : null);
    $_Lang['P_Mission'] = (isset($_POST['target_mission']) ? $_POST['target_mission'] : null);
    $_Lang['P_SetQuickRes'] = (isset($_POST['quickres']) ? $_POST['quickres'] : null);
    $_Lang['P_GoBackVars'] = base64_encode(json_encode($GoBackVars));
}
else
{
    $_Lang['P_Galaxy'] = (isset($_GET['galaxy']) ? intval($_GET['galaxy']) : null);
    $_Lang['P_System'] = (isset($_GET['system']) ? intval($_GET['system']) : null);
    $_Lang['P_Planet'] = (isset($_GET['planet']) ? intval($_GET['planet']) : null);
    $_Lang['P_PlType'] = (isset($_GET['planettype']) ? intval($_GET['planettype']) : null);
    $_Lang['P_Mission'] = (isset($_GET['target_mission']) ? intval($_GET['target_mission']) : null);
    if(isset($_GET['quickres']) && $_GET['quickres'] == 1)
    {
        if(!isset($_GET['target_mission']) || $_GET['target_mission'] != 3)
        {
            if($_User['settings_mainPlanetID'] != $_Planet['id'])
            {
                $GetQuickResPlanet = doquery("SELECT `galaxy`, `system`, `planet` FROM {{table}} WHERE `id` = {$_User['settings_mainPlanetID']};", 'planets', true);
            }
            $_Lang['P_Galaxy'] = $GetQuickResPlanet['galaxy'];
            $_Lang['P_System'] = $GetQuickResPlanet['system'];
            $_Lang['P_Planet'] = $GetQuickResPlanet['planet'];
            $_Lang['P_PlType'] = 1;
            $_Lang['P_Mission'] = 3;
        }
    }
}

if(!isPro())
{
    $_Lang['P_HideQuickRes'] = 'hide';
}

$_Lang['P_TotalPlanetResources'] = (string)(floor($_Planet['metal']) + floor($_Planet['crystal']) + floor($_Planet['deuterium']) + 0);
if($_Lang['P_TotalPlanetResources'] == '0')
{
    $_Lang['P_StorageColor'] = 'lime';
}
else
{
    $_Lang['P_StorageColor'] = 'orange';
}

$Page = parsetemplate($BodyTPL, $_Lang);
display($Page, $_Lang['fl_title']);

?>
