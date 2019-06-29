<?php

function ReadBattleReport($Report)
{
    global $_Lang, $_User, $_Vars_CombatData, $_Vars_Prices, $_Vars_CombatUpgrades;

    static $LangIncluded = false;
    if(!$LangIncluded)
    {
        includeLang('readBattleReport');
        $LangIncluded = true;
    }

    if(!isset($_User['rw_breakline']) || $_User['rw_breakline'] == 0)
    {
        $_User['rw_breakline'] = 999;
    }

    // Temp variables
    $Attacker = (isset($Report['id_owner1']) ? explode(',', $Report['id_owner1']) : array());
    if(in_array($_User['id'], $Attacker) AND $Report['disallow_attacker'] == '1')
    {
        return "<br/><th class=\"pad5 red\">{$_Lang['sys_mess_attacker_lost_in_1_round']}</th>";
    }
    $ReportData = json_decode($Report['report'], true);

    $Every1FromTeamDestroyed = false;
    // End of Temp variables

    // All Rounds
    $Rounds                    = $ReportData['rounds'];
    // Battle Result
    $Result                    = $ReportData['init']['result'];
    $Stolen['met']            = $ReportData['init']['met'];
    $Stolen['cry']            = $ReportData['init']['cry'];
    $Stolen['deu']            = $ReportData['init']['deu'];
    $Debris['met']            = $ReportData['init']['deb_met'];
    $Debris['cry']            = $ReportData['init']['deb_cry'];
    $MoonChance                = $ReportData['init']['moon_chance'];
    $TotalMoonChance        = $ReportData['init']['total_moon_chance'];
    $CreatedMoon            = $ReportData['init']['moon_created'];
    $MoonDestroyed            = $ReportData['init']['moon_destroyed'];
    $DestroyMoonChance        = $ReportData['init']['moon_des_chance'];
    $FleetDestroyed            = $ReportData['init']['fleet_destroyed'];
    $DestroyFleetChance        = $ReportData['init']['fleet_des_chance'];
    $PlanetName                = $ReportData['init']['planet_name'];
    $IsOnMoon                = $ReportData['init']['onMoon'];
    $LostUnits['atk']        = $ReportData['init']['atk_lost'];
    $LostUnits['def']        = $ReportData['init']['def_lost'];
    // Misc.
    $GenerateTime            = $ReportData['init']['time'];
    // UsersData
    $UsersData['atk']        = $ReportData['init']['usr']['atk'];
    $UsersData['def']        = $ReportData['init']['usr']['def'];
    $AddedUsernames = array('atk' => array(), 'def' => array());
    foreach($UsersData as $TypeKey => $AllUsers)
    {
        foreach($AllUsers as $UserKey => $UserVal)
        {
            if(empty($UserVal['username']))
            {
                $UserVal['username'] = '-';
            }
            if(!in_array($UserVal['username'], $AddedUsernames[$TypeKey]))
            {
                $AddedUsernames[$TypeKey][] = $UserVal['username'];
                $Usernames[$TypeKey][] = "<span class=\"fUsr\">{$UserVal['username']}</span>";
            }
        }
    }

    $Title = sprintf($_Lang['sys_attack_title'], prettyDate('(d m Y - H:i:s)', $ReportData['init']['date'], 1));
    $UsersFighting = implode(', ', $Usernames['atk']).' <b class="vsBig">'.$_Lang['sys_battle_versus'].'</b> '.implode(', ', $Usernames['def']);
    $ReportCode = '';
    $ReportCode .= "<table align=\"center\"><tr><th class=\"br1\"></th></tr><tr><th class=\"pad5\">{$Title}</th></tr><tr><th class=\"br0\"></th></tr><tr><tr><th class=\"pad5\">{$UsersFighting}</th></tr><th class=\"br3\"></th></tr>";

    if($Rounds === (array)$Rounds)
    {
        // General Templates
        $GeneralTPL['ships'] = array
        (
            "<tr><td class=\"c pad2 center\">{$_Lang['sys_ship_type']}</td>",
            "<tr><td class=\"c pad2 center\">{$_Lang['sys_ship_count']}</td>",
            "<tr><td class=\"c pad2 center\">{$_Lang['sys_ship_weapon']}</td>",
            "<tr><td class=\"c pad2 center\">{$_Lang['sys_ship_shield']}</td>",
            "<tr><td class=\"c pad2 center\">{$_Lang['sys_ship_armour']}</td>",
        );

        // Parse UsersData
        $Users = array();
        foreach($UsersData as $TypeKey => $AllUsers)
        {
            foreach($AllUsers as $UserKey => $UserVal)
            {
                if(empty($UserVal['username']))
                {
                    $UserVal['username'] = $_Lang['sys_attack_defender_abandoned_'.($IsOnMoon ? '3' : '1')];
                }
                $Pointer            = &$Users[$TypeKey][$UserKey];
                $Temp                = explode(':', $UserVal['pos']);
                $Pointer['pos']        = array('g' => $Temp[0], 's' => $Temp[1], 'p' => $Temp[2]);
                $Pointer['techs']    = String2Array($UserVal['techs']);
                $Pointer['name']    = sprintf(($TypeKey == 'atk' ? $_Lang['sys_attack_attacker_pos'] : $_Lang['sys_attack_defender_pos']), $UserVal['username'], (empty($UserVal['ally']) ? '' : "[{$UserVal['ally']}]"), $Temp[0], $Temp[1], $Temp[2], $Temp[0], $Temp[1], $Temp[2]);
                $Pointer['tech']    = sprintf($_Lang['sys_attack_techologies'], 100 + ($Pointer['techs'][109] * 10), 100 + ($Pointer['techs'][110] * 10), 100 + ($Pointer['techs'][111] * 10));
                $Pointer['techval'] = array('atk' => 1 + (0.1 * $Pointer['techs'][109]), 'shield' => 1 + (0.1 * $Pointer['techs'][110]), 'def' => 1 + (0.1 * $Pointer['techs'][111]));
                $Pointer['TotalForceFactor'] = 1;
                $Pointer['TotalShieldFactor'] = 1;

                $Pointer['morale'] = null;
                if(isset($UserVal['morale']) && $UserVal['morale'] !== null)
                {
                    // Bonuses
                    if($UserVal['morale'] >= MORALE_BONUS_FLEETPOWERUP1)
                    {
                        $Pointer['TotalForceFactor'] = MORALE_BONUS_FLEETPOWERUP1_FACTOR;
                    }
                    if($UserVal['morale'] >= MORALE_BONUS_FLEETSHIELDUP1)
                    {
                        $Pointer['TotalShieldFactor'] = MORALE_BONUS_FLEETSHIELDUP1_FACTOR;
                    }
                    // Penalties
                    if($UserVal['morale'] <= MORALE_PENALTY_FLEETPOWERDOWN1)
                    {
                        $Pointer['TotalForceFactor'] = MORALE_PENALTY_FLEETPOWERDOWN1_FACTOR;
                    }
                    if($UserVal['morale'] <= MORALE_PENALTY_FLEETPOWERDOWN2)
                    {
                        $Pointer['TotalForceFactor'] = MORALE_PENALTY_FLEETPOWERDOWN2_FACTOR;
                    }
                    if($UserVal['morale'] <= MORALE_PENALTY_FLEETSHIELDDOWN1)
                    {
                        $Pointer['TotalShieldFactor'] = MORALE_PENALTY_FLEETSHIELDDOWN1_FACTOR;
                    }
                    if($UserVal['morale'] <= MORALE_PENALTY_FLEETSHIELDDOWN2)
                    {
                        $Pointer['TotalShieldFactor'] = MORALE_PENALTY_FLEETSHIELDDOWN2_FACTOR;
                    }

                    $Pointer['morale'] = sprintf($_Lang['sys_attack_morale'], $UserVal['morale']);
                    $UserMap[$UserVal['id']] = &$UsersData[$TypeKey][$UserKey];
                }
                $Pointer['header'] = "<br/>{$Pointer['name']}<br/>{$Pointer['tech']}{$Pointer['morale']}";
            }
        }

        foreach($Rounds as $Number => $Data)
        {
            foreach($Data as $TypeKey => $InnerData)
            {
                if((array)$InnerData['ships'] === $InnerData['ships'])
                {
                    // If ShipsArray is OK (has some ships)
                    foreach($Users[$TypeKey] as $UserID => $UserData)
                    {
                        if(isset($Destroyed[$TypeKey][$UserID]))
                        {
                            continue;
                        }

                        $ThisRoundCode = "<tr><th>{$UserData['header']}";
                        if(!empty($InnerData['ships'][$UserID]))
                        {
                            // If User has Ships (not destroyed)
                            $ThisRoundCode .= '<table align="center"><tr><th class="br1"></th></tr>';
                            $ShipsLoop = 0;

                            $TableRow = $GeneralTPL['ships'];

                            $InnerData['ships'][$UserID] = String2Array($InnerData['ships'][$UserID]);
                            $ShipsCount = count($InnerData['ships'][$UserID]);
                            foreach($InnerData['ships'][$UserID] as $ShipID => $ShipCount)
                            {
                                $ShipsLoop += 1;
                                $ShipsCount -= 1;
                                if(!isset($ShipCalculations[$TypeKey][$UserID][$ShipID]))
                                {
                                    // Calculate ShipValues if it's our first time here
                                    $ForceUpgrade = 0;
                                    if(!empty($_Vars_CombatUpgrades[$ShipID]))
                                    {
                                        foreach($_Vars_CombatUpgrades[$ShipID] as $UpTech => $ReqLevel)
                                        {
                                            $TechAvailable = $UserData['techs'][$UpTech];
                                            if($TechAvailable > $ReqLevel)
                                            {
                                                $ForceUpgrade += ($TechAvailable - $ReqLevel) * 0.05;
                                            }
                                        }
                                    }
                                    $ShipCalculations[$TypeKey][$UserID][$ShipID] = array
                                    (
                                        'name'=> $_Lang['tech'][$ShipID],
                                        'force' => floor($_Vars_CombatData[$ShipID]['attack'] * ($UserData['techval']['atk'] + $ForceUpgrade) * $UserData['TotalForceFactor']),
                                        'shield'=> floor($_Vars_CombatData[$ShipID]['shield'] * $UserData['techval']['shield'] * $UserData['TotalShieldFactor']),
                                        'hull'=> floor((($_Vars_Prices[$ShipID]['metal'] + $_Vars_Prices[$ShipID]['crystal']) / 10) * $UserData['techval']['def'])
                                    );
                                }

                                $TableRow[0] .= "<td class=\"c pad2 center\">{$ShipCalculations[$TypeKey][$UserID][$ShipID]['name']}</td>";
                                $TableRow[1] .= '<th>'.prettyNumber($ShipCount).'</th>';
                                $TableRow[2] .= '<th>'.prettyNumber($ShipCalculations[$TypeKey][$UserID][$ShipID]['force'] * $ShipCount).'</th>';
                                $TableRow[3] .= '<th>'.prettyNumber($ShipCalculations[$TypeKey][$UserID][$ShipID]['shield'] * $ShipCount).'</th>';
                                $TableRow[4] .= '<th>'.prettyNumber($ShipCalculations[$TypeKey][$UserID][$ShipID]['hull'] * $ShipCount).'</th>';

                                if($ShipsLoop == $_User['rw_breakline'] AND $ShipsCount > 0)
                                {
                                    // Do inTable lineBreak
                                    $ShipsLoop = 0;
                                    foreach($TableRow as &$TableRowData)
                                    {
                                        $ThisRoundCode .= $TableRowData.'</tr>';
                                    }
                                    $ThisRoundCode .= "<tr><th class=\"br1\"></th></tr>";
                                    $TableRow = $GeneralTPL['ships'];
                                }
                            }
                            foreach($TableRow as &$TableRowData)
                            {
                                $ThisRoundCode .= $TableRowData.'</tr>';
                            }
                            $ThisRoundCode .= '</table>';
                        }
                        else
                        {
                            // If User has no ships (is destroyed)
                            $ThisRoundCode .= "<br/><br/><b class=\"orange\">{$_Lang['sys_destroyed']}</b><br/>";
                            $Destroyed[$TypeKey][$UserID] = true;
                        }
                        $ThisRoundCode .= '<br/></th></tr>';
                        $ThisRoundArray[] = $ThisRoundCode;
                        $ThisRoundArray[] = '<tr><th class="br0"></th></tr>';
                    }
                }
                else
                {
                    // ShipArray is empty (no ships at all)
                    foreach($Users[$TypeKey] as $UserID => $UserData)
                    {
                        if(isset($Destroyed[$TypeKey][$UserID]))
                        {
                            continue;
                        }

                        $ThisRoundCode = '';
                        $ThisRoundCode .= "<tr><th>{$UserData['header']}";
                        $ThisRoundCode .= "<br/><br/><b class=\"red\">{$_Lang['sys_destroyed']}</b><br/><br/></th></tr>";
                        $ThisRoundArray[] = $ThisRoundCode;
                        $ThisRoundArray[] = '<tr><th class="br0"></th></tr>';
                    }
                    $Every1FromTeamDestroyed = true;
                }
                array_pop($ThisRoundArray);
                $ThisRoundArray[] = '<tr><th class="br2"></th></tr>';
            }
            if($Every1FromTeamDestroyed === false AND $Number <= BATTLE_MAX_ROUNDS)
            {
                $AttackWaveStat = sprintf(
                    $_Lang['sys_attack_wave'],
                    (
                        (count($Data['atk']['ships']) > 1) ?
                        $_Lang['sys_msg_atk_fleets'] :
                        $_Lang['sys_msg_atk_fleet']
                    ),
                    prettyNumber($Data['atk']['count']),
                    (
                        ($Data['atk']['count'] > 1) ?
                        $_Lang['sys_x_times'] :
                        ''
                    ),
                    prettyNumber(floor($Data['atk']['force'])),
                    (
                        (count($Data['def']['ships']) > 1) ?
                        $_Lang['sys_msg_into_defs'] :
                        $_Lang['sys_msg_into_def']
                    ),
                    (
                        (count($Data['def']['ships']) > 1) ?
                        $_Lang['sys_msg_into_defs'] :
                        $_Lang['sys_msg_into_def_shield']
                    ),
                    prettyNumber(floor($Data['def']['shield']))
                );
                $DefendWavaStat = sprintf(
                    $_Lang['sys_attack_wave'],
                    (
                        (count($Data['def']['ships']) > 1) ?
                        $_Lang['sys_msg_def_fleets'] :
                        $_Lang['sys_msg_def_fleet']
                    ),
                    prettyNumber($Data['def']['count']),
                    (
                        ($Data['def']['count'] > 1) ?
                        $_Lang['sys_x_times'] :
                        ''
                    ),
                    prettyNumber(floor($Data['def']['force'])),
                    (
                        (count($Data['atk']['ships']) > 1) ?
                        $_Lang['sys_msg_into_atks'] :
                        $_Lang['sys_msg_into_atk']
                    ),
                    (
                        (count($Data['atk']['ships']) > 1) ?
                        $_Lang['sys_msg_into_atks'] :
                        $_Lang['sys_msg_into_atk']
                    ),
                    prettyNumber(floor($Data['atk']['shield']))
                );

                $ThisRoundArray[] = "<tr><th class=\"pad5 wave\">{$AttackWaveStat}<br/>{$DefendWavaStat}</th></tr>";
                $ThisRoundArray[] = '<tr><th class="br3"></th></tr>';
            }
        }
        array_pop($ThisRoundArray);
        $ReportCode .= implode('', $ThisRoundArray);
    }
    else
    {
        $ReportCode .= '<tr><th class="c center red" style="padding: 10px;">'.$_Lang['BattleReportReader_FatalError'].'</th></tr>';
    }

    $ReportCode .= '<tr><th class="br3"></th></tr><tr><th style="text-align: left; padding: 10px;">';

    $StrAttackerUnits = sprintf($_Lang['sys_attacker_lostunits'], prettyNumber ($LostUnits['atk']));
    $StrDefenderUnits = sprintf($_Lang['sys_defender_lostunits'], prettyNumber ($LostUnits['def']));
    $StrRuins = sprintf($_Lang['sys_gcdrunits'], prettyNumber($Debris['met']), $_Lang['Metal_rec'], prettyNumber($Debris['cry']), $_Lang['Crystal_rec']);
    $DebrisField = "{$StrAttackerUnits}<br/>{$StrDefenderUnits}<br/>{$StrRuins}";

    if($MoonChance !== false)
    {
        if($TotalMoonChance > $MoonChance)
        {
            $ChanceMoon = sprintf($_Lang['sys_moonproba_total'], $MoonChance, prettyNumber($TotalMoonChance));
        }
        else
        {
            $ChanceMoon = sprintf($_Lang['sys_moonproba'], $MoonChance);
        }
    }
    else
    {
        $ChanceMoon = false;
    }

    if($CreatedMoon === true)
    {
        $GottenMoon = sprintf($_Lang['sys_moonbuilt'], $PlanetName, $Users['def'][0]['pos']['g'], $Users['def'][0]['pos']['s'], $Users['def'][0]['pos']['p'], $Users['def'][0]['pos']['g'], $Users['def'][0]['pos']['s'], $Users['def'][0]['pos']['p']);
    }
    else
    {
        $GottenMoon = false;
    }

    switch($Result)
    {
        case COMBAT_ATK: // Attacker won the battle
            $Pillage = sprintf($_Lang['sys_stealed_ressources'], prettyNumber($Stolen['met']), $_Lang['Metal_rec'], prettyNumber($Stolen['cry']), $_Lang['Crystal_rec'], prettyNumber($Stolen['deu']), $_Lang['Deuterium_rec']);
            $ReportCode .= "{$_Lang['sys_attacker_won']}<br/>{$Pillage}<br/>";
            $ReportCode .= "{$DebrisField}<br/>";
            if($MoonDestroyed !== false)
            {
                if($MoonDestroyed !== -1)
                {
                    if($MoonDestroyed === 1 AND $FleetDestroyed !== true)
                    {
                        $MoonDestroyText = $_Lang['sys_destruction_onlymoon'];
                    }
                    elseif($MoonDestroyed === 1 AND $FleetDestroyed == true)
                    {
                        $MoonDestroyText = $_Lang['sys_destruction_both'];
                    }
                    elseif($MoonDestroyed === 0 AND $FleetDestroyed == true)
                    {
                        $MoonDestroyText = $_Lang['sys_destruction_onlyfleet'];
                    }
                    else
                    {
                        $MoonDestroyText = $_Lang['sys_destruction_nothing'];
                    }

                    $MoonDestroyText .= '<br/><br/>';
                    $MoonDestroyText .= sprintf($_Lang['sys_destruc_lune'], $DestroyMoonChance).'<br/>';
                    $MoonDestroyText .= sprintf($_Lang['sys_destruc_rip'], $DestroyFleetChance).'<br/>';
                }
                else
                {
                    $MoonDestroyText = $_Lang['sys_destruction_nothappened'];
                }
            }
            if($ChanceMoon)
            {
                $ReportCode .= "{$ChanceMoon}<br/>";
            }
            if($GottenMoon)
            {
                $ReportCode .= "{$GottenMoon}<br/>";
            }
            break;
        case COMBAT_DEF: // Defender won the battle
            $ReportCode .= "{$_Lang['sys_defender_won']}<br/>";
            $ReportCode .= "{$DebrisField}<br/>";
            if($ChanceMoon)
            {
                $ReportCode .= "{$ChanceMoon}<br/>";
            }
            if($GottenMoon)
            {
                $ReportCode .= "{$GottenMoon}<br/>";
            }
            break;
        case COMBAT_DRAW; // It's a draw!
            $ReportCode .= "{$_Lang['sys_both_won']}<br/>";
            $ReportCode .= "{$DebrisField}<br/>";
            if($ChanceMoon)
            {
                $ReportCode .= "{$ChanceMoon}<br/>";
            }
            if($GottenMoon)
            {
                $ReportCode .= "{$GottenMoon}<br/>";
            }
            break;
    }
    if($IsOnMoon)
    {
        $ReportCode .= $_Lang['sys_battle_onMoon'].'<br/>';
    }

    $ReportCode .= '</th></tr>';

    if(!empty($MoonDestroyText))
    {
        $ReportCode .= '<tr><th class="br1"></th></tr>';
        $ReportCode .= '<tr><th style="text-align: left; padding: 10px;">';
        $ReportCode .= $MoonDestroyText;
        $ReportCode .= '</th></tr>';
    }

    if(!empty($ReportData['morale']))
    {
        $ReportCode .= '<tr><th class="br1"></th></tr>';
        foreach($ReportData['morale'] as $ThisUserID => $ThisData)
        {
            if($ThisData['usertype'] == 'atk')
            {
                if($ThisData['type'] === MORALE_POSITIVE)
                {
                    $ThisMessage = $_Lang['BattleReport_Morale_Attacker_Positive'];
                }
                else
                {
                    $ThisMessage = $_Lang['BattleReport_Morale_Attacker_Negative'];
                }
            }
            else
            {
                $ThisMessage = $_Lang['BattleReport_Morale_Defender_Positive'];
            }

            $ReportCode .= '<tr><th style="text-align: left; padding: 10px;">';
            $ReportCode .= sprintf($ThisMessage, $UserMap[$ThisUserID]['username'], sprintf('%0.2f', $ThisData['factor']), $ThisData['level']);
            $ReportCode .= '</th></tr>';
        }
    }

    $ReportCode .= '<tr><th class="br1"></th></tr><tr><th class="pad5 center">';
    $ReportCode .= sprintf($_Lang['sys_rapport_build_time'], $GenerateTime);
    $ReportCode .= '<br/>'.$_Lang['sys_raport_build_engine'].'</th></tr></table>';

    return "<td>{$ReportCode}</td>";
}

?>
