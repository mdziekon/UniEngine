<?php

use UniEngine\Engine\Modules\Flights;

function MissionCaseMIP($FleetRow, &$_FleetCache)
{
    global $_EnginePath, $_Lang, $_Vars_GameElements, $_Vars_ElementCategories, $UserStatsData, $UserDev_Log, $HPQ_PlanetUpdatedFields;
    static $FunctionIncluded = false;

    $Return = array();
    $Now = time();

    if($FleetRow['calcType'] == 1)
    {
        static $LangIncluded = false;
        if(!$LangIncluded)
        {
            includeLang('FleetMission_MissileAttack');
            $LangIncluded = true;
        }

        //Get the Interplanetary Missile ID
        $IPMissileID = 503;

        //Just setting the variable...
        $IPMissiles = 0;

        // Select Attacked Planet & User from $_FleetCache
        $IsAbandoned = ($FleetRow['fleet_target_owner'] > 0 ? false : true);
        $TargetPlanet = &$_FleetCache['planets'][$FleetRow['fleet_end_id']];
        $TargetUser = &$_FleetCache['users'][$FleetRow['fleet_target_owner']];
        $IsAllyFight = (($FleetRow['ally_id'] == 0 OR ($FleetRow['ally_id'] != $TargetUser['ally_id'])) ? false : true);

        // Update planet before attack begins
        $UpdateResult = HandleFullUserUpdate($TargetUser, $TargetPlanet, $_FleetCache['planets'][$TargetUser['techQueue_Planet']], $FleetRow['fleet_start_time'], true, true);
        if(!empty($UpdateResult))
        {
            foreach($UpdateResult as $PlanetID => $Value)
            {
                if($Value === true)
                {
                    $_FleetCache['updatePlanets'][$PlanetID] = true;
                }
            }
        }

        if(!$IsAbandoned)
        {
            $IdleHours = floor(($FleetRow['fleet_start_time'] - $TargetUser['onlinetime']) / TIME_HOUR);
            if($IdleHours > 0)
            {
                $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_End_Owner_IdleHours'] = $IdleHours;
            }
        }

        if (empty($UserStatsData[$FleetRow['fleet_owner']])) {
            $UserStatsData[$FleetRow['fleet_owner']] = Flights\Utils\Initializers\initUserStatsMap();
        }
        if(!$IsAllyFight)
        {
            $UserStatsData[$FleetRow['fleet_owner']]['raids_missileAttack'] += 1;
        }
        else
        {
            $UserStatsData[$FleetRow['fleet_owner']]['raids_inAlly'] += 1;
        }

        //Get sending planet name
        $SendingPlanetName = $FleetRow['attacking_planet_name'];
        //Get the attacker military technology level
        $AttackerMiliTech = $FleetRow['tech_weapons'];

        $DefSystemsPlanet = false;

        foreach($_Vars_ElementCategories['defense'] as $ElementID)
        {
            if(in_array($ElementID, $_Vars_ElementCategories['rockets']))
            {
                continue;
            }
            if($TargetPlanet[$_Vars_GameElements[$ElementID]] > 0)
            {
                $DefSystemsPlanet[$ElementID] = $TargetPlanet[$_Vars_GameElements[$ElementID]];
            }
        }

        $TheFleet = explode(';', $FleetRow['fleet_array']);
        $PrimaryTarget = false;
        foreach($TheFleet as $key => $val)
        {
            if($val != '')
            {
                $key = explode(',', $val);
                if($key[0] == $IPMissileID)
                {
                    $IPMissiles += $key[1];
                }
                else if($key[0] == 'primary_target')
                {
                    $PrimaryTarget = intval($key[1]);
                    if($PrimaryTarget == 0)
                    {
                        $PrimaryTarget = '0';
                    }
                }
            }
        }

        if($PrimaryTarget < 0 OR $PrimaryTarget > 99 OR $PrimaryTarget === false)
        {
            $PrimaryTarget = 0;
        }

        $InitialIPMissiles = $IPMissiles;
        $ICMissiles = $TargetPlanet['antiballistic_missile'];

        switch($FleetRow['fleet_start_type'])
        {
            case 1:
                $SendingType = $_Lang['sys_MIP_sending_planet'];
                break;
            case 2:
                $SendingType = $_Lang['sys_MIP_sending_moon'];
                break;
        }

        switch($FleetRow['fleet_end_type'])
        {
            case 1:
                $AttackedType = $_Lang['sys_MIP_attacked_planet'];
                break;
            case 2:
                $AttackedType = $_Lang['sys_MIP_attacked_moon'];
                break;
        }

        $SendingPlanetLink = CreatePlanetLink($FleetRow['fleet_start_galaxy'], $FleetRow['fleet_start_system'], $FleetRow['fleet_start_planet']);
        $AttackedPlanetLink = CreatePlanetLink($FleetRow['fleet_end_galaxy'], $FleetRow['fleet_end_system'], $FleetRow['fleet_end_planet']);

        $AttackerReport[] = array('{MIP_A_rp_h}', prettyNumber($InitialIPMissiles), $SendingType, $SendingPlanetName, $SendingPlanetLink, $AttackedType, $TargetPlanet['name'], $AttackedPlanetLink);
        $DefenderReport[] = array('{MIP_D_rp_h}', prettyNumber($InitialIPMissiles), $SendingType, $SendingPlanetName, $SendingPlanetLink, $AttackedType, $TargetPlanet['name'], $AttackedPlanetLink);

        if($IPMissiles <= $ICMissiles)
        {
            $AttackerReport[] = array('{MIP_A_M_Cap}', prettyNumber($IPMissiles));
            $DefenderReport[] = array('{MIP_D_M_Cap}', prettyNumber($IPMissiles));

            $TargetPlanet['antiballistic_missile'] -= $IPMissiles;
            $_FleetCache['updatePlanets'][$TargetPlanet['id']] = true;
            $HPQ_PlanetUpdatedFields[] = 'antiballistic_missile';
            $UserDev_UpPl[] = '502,'.$IPMissiles;

            $Defender_HasLoses = true;
        }
        else
        {
            if($ICMissiles > 0)
            {
                $IPMissiles -= $ICMissiles;

                $AttackerReport[] = array('{MIP_Cap_M}', prettyNumber($ICMissiles));
                $DefenderReport[] = array('{MIP_Cap_M}', prettyNumber($ICMissiles));

                $TargetPlanet['antiballistic_missile'] = 0;
                $_FleetCache['updatePlanets'][$TargetPlanet['id']] = true;
                $HPQ_PlanetUpdatedFields[] = 'antiballistic_missile';
                $UserDev_UpPl[] = '502,'.$IPMissiles;

                $Defender_HasLoses = true;
            }

            if($DefSystemsPlanet !== false)
            {
                if($FunctionIncluded === false)
                {
                    include($_EnginePath.'includes/functions/CalcInterplanetaryAttack.php');
                    $FunctionIncluded = true;
                }
                $Attack = CalcInterplanetaryAttack($TargetUser['tech_shielding'], $AttackerMiliTech, $IPMissiles, $DefSystemsPlanet, $PrimaryTarget);

                if($Attack['DestroyedTotal'] > 0)
                {
                    foreach($Attack['LeftDefs'] as $ElementID => $Value)
                    {
                        if($Value == 0)
                        {
                            $Value = '0';
                        }
                        $TargetPlanet[$_Vars_GameElements[$ElementID]] = $Value;
                        $_FleetCache['updatePlanets'][$TargetPlanet['id']] = true;
                        $HPQ_PlanetUpdatedFields[] = $_Vars_GameElements[$ElementID];
                    }

                    $DestroyedSomething = false;
                    foreach($Attack['Destroyed'] as $key => $val)
                    {
                        if($val > 0)
                        {
                            if($DestroyedSomething !== true)
                            {
                                $DestroyedSomething = true;
                                $AttackerReport[] = '{MIP_destroy}';
                                $DefenderReport[] = '{MIP_destroy}';

                                if (empty($UserStatsData[$TargetPlanet['id_owner']])) {
                                    $UserStatsData[$TargetPlanet['id_owner']] = Flights\Utils\Initializers\initUserStatsMap();
                                }
                            }
                            $CreateThisLine = '&bull; '.$_Lang['tech'][$key].' <b>( -'.prettyNumber($val).')</b> ['.prettyNumber($Attack['LeftDefs'][$key]).'/'.prettyNumber($Attack['LeftDefs'][$key] + $val).']';
                            $AttackerReport[] = $CreateThisLine;
                            $DefenderReport[] = $CreateThisLine;
                            $UserDev_UpPl[] = "{$key},{$val}";

                            if(!$IsAllyFight)
                            {
                                $UserStatsData[$FleetRow['fleet_owner']]['destroyed_'.$key] += $val;
                                $UserStatsData[$TargetPlanet['id_owner']]['lost_'.$key] += $val;
                            }
                        }
                    }

                    if($DestroyedSomething)
                    {
                        $Defender_HasLoses = true;
                    }

                    // Create debris field on the orbit
                    if($Attack['Debris']['metal'] > 0 OR $Attack['Debris']['crystal'] > 0)
                    {
                        if($Attack['Debris']['metal'] == 0)
                        {
                            $Attack['Debris']['metal'] = '0';
                        }
                        if($Attack['Debris']['crystal'] == 0)
                        {
                            $Attack['Debris']['crystal'] = '0';
                        }

                        $AttackerReport[] = '{MIP_Debris}';
                        $AttackerReport[] = prettyNumber($Attack['Debris']['metal']).' {MIP_Units} {MIP_DebMet}';
                        $AttackerReport[] = prettyNumber($Attack['Debris']['crystal']).' {MIP_Units} {MIP_DebCry}';

                        if(isset($_FleetCache['galaxyMap']['byPlanet'][$FleetRow['fleet_end_id']]) && $_FleetCache['galaxyMap']['byPlanet'][$FleetRow['fleet_end_id']] > 0)
                        {
                            if(!isset($_FleetCache['galaxy'][$_FleetCache['galaxyMap']['byPlanet'][$FleetRow['fleet_end_id']]]['metal']))
                            {
                                $_FleetCache['galaxy'][$_FleetCache['galaxyMap']['byPlanet'][$FleetRow['fleet_end_id']]]['metal'] = 0;
                            }
                            if(!isset($_FleetCache['galaxy'][$_FleetCache['galaxyMap']['byPlanet'][$FleetRow['fleet_end_id']]]['crystal']))
                            {
                                $_FleetCache['galaxy'][$_FleetCache['galaxyMap']['byPlanet'][$FleetRow['fleet_end_id']]]['crystal'] = 0;
                            }
                            $_FleetCache['galaxy'][$_FleetCache['galaxyMap']['byPlanet'][$FleetRow['fleet_end_id']]]['metal'] += $Attack['Debris']['metal'];
                            $_FleetCache['galaxy'][$_FleetCache['galaxyMap']['byPlanet'][$FleetRow['fleet_end_id']]]['crystal'] += $Attack['Debris']['crystal'];
                            $_FleetCache['galaxy'][$_FleetCache['galaxyMap']['byPlanet'][$FleetRow['fleet_end_id']]]['updated'] = true;
                            $_FleetCache['updated']['galaxy'] = true;
                        }
                        else
                        {
                            $Query_UpdateGalaxy = '';
                            $Query_UpdateGalaxy .= "UPDATE {{table}} SET `metal` = `metal` + {$Attack['Debris']['metal']}, `crystal` = `crystal` + {$Attack['Debris']['crystal']} ";
                            $Query_UpdateGalaxy .= "WHERE `id_planet` = {$FleetRow['fleet_end_id']} LIMIT 1; ";
                            $Query_UpdateGalaxy .= "-- MISSION MIP [Q01][FID: {$FleetRow['fleet_id']}]";
                            doquery($Query_UpdateGalaxy, 'galaxy');
                        }
                    }
                }
                else
                {
                    $AttackerReport[] = '{MIP_nodef}';
                    $DefenderReport[] = '{MIP_nodef}';
                }
            }
            else
            {
                $AttackerReport[] = '{MIP_nodef}';
                $DefenderReport[] = '{MIP_nodef}';
            }
        }

        // Morale System
        if(MORALE_ENABLED AND !$IsAbandoned AND !$IsAllyFight AND $IdleHours < (7 * 24) AND $Defender_HasLoses)
        {
            if(!empty($_FleetCache['MoraleCache'][$FleetRow['fleet_owner']]))
            {
                $FleetRow['morale_level'] = $_FleetCache['MoraleCache'][$FleetRow['fleet_owner']]['level'];
                $FleetRow['morale_droptime'] = $_FleetCache['MoraleCache'][$FleetRow['fleet_owner']]['droptime'];
                $FleetRow['morale_lastupdate'] = $_FleetCache['MoraleCache'][$FleetRow['fleet_owner']]['lastupdate'];
            }
            Morale_ReCalculate($FleetRow, $FleetRow['fleet_start_time']);

            $Morale_Factor = $FleetRow['morale_points'] / $TargetUser['morale_points'];
            if($Morale_Factor > MORALE_MINIMALFACTOR)
            {
                $Morale_Updated = Morale_AddMorale($FleetRow, MORALE_NEGATIVE, $Morale_Factor, MORALE_ROCKETATTACK_MODIFIER, MORALE_ROCKETATTACK_MODIFIER, $FleetRow['fleet_start_time']);
                if($Morale_Updated)
                {
                    $_FleetCache['MoraleCache'][$FleetRow['fleet_owner']]['level'] = $FleetRow['morale_level'];
                    $_FleetCache['MoraleCache'][$FleetRow['fleet_owner']]['droptime'] = $FleetRow['morale_droptime'];
                    $_FleetCache['MoraleCache'][$FleetRow['fleet_owner']]['lastupdate'] = $FleetRow['morale_lastupdate'];

                    $CreateAtkMsg_MoraleInfo = sprintf($_Lang['RocketReport_Morale_Attacker_Negative'], sprintf('%0.2f', $Morale_Factor), $FleetRow['morale_level']);
                }
            }
        }

        $first = true;
        foreach($AttackerReport as $val)
        {
            if($first === true)
            {
                $first = false;
            }
            else
            {
                $CreateAtkMsg[] = '<br/>';
            }
            $CreateAtkMsg[] = $val;
        }

        if(!empty($CreateAtkMsg_MoraleInfo))
        {
            $CreateAtkMsg[] = '<br/><br/>';
            $CreateAtkMsg[] = $CreateAtkMsg_MoraleInfo;
        }

        $first = true;
        foreach($DefenderReport as $val)
        {
            if($first === true)
            {
                $first = false;
            }
            else
            {
                $CreateDefMsg[] = '<br/>';
            }
            $CreateDefMsg[] = $val;
        }

        $Message = false;
        $Message['msg_text'] = $CreateAtkMsg;
        $Message = json_encode($Message);
        Cache_Message($FleetRow['fleet_owner'], 0, $FleetRow['fleet_start_time'], 5, '003', '006', $Message);

        if(!$IsAbandoned)
        {
            $Message = false;
            $Message['msg_text'] = $CreateDefMsg;
            $Message = json_encode($Message);
            Cache_Message($FleetRow['fleet_target_owner'], 0, $FleetRow['fleet_start_time'], 5, '003', '006', $Message);
        }

        if(!empty($UserDev_UpPl) AND !$IsAbandoned)
        {
            $UserDev_Log[] = array('UserID' => $TargetPlanet['id_owner'], 'PlanetID' => $TargetPlanet['id'], 'Date' => $FleetRow['fleet_start_time'], 'Place' => 16, 'Code' => '1', 'ElementID' => $FleetRow['fleet_id'], 'AdditionalData' => implode(';', $UserDev_UpPl));
        }

        $Return['FleetsToDelete'][] = $FleetRow['fleet_id'];
        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Calculated_Mission'] = true;
        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Calculated_Mission_Time'] = $Now;
        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Destroyed'] = true;
        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Destroyed_Reason'] = Flights\Enums\FleetDestructionReason::MISSILEATTACK;
    }

    return $Return;
}

?>
