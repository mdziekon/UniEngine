<?php

function ConvertBattleReport($Report, $Settings)
{
    global $_Lang, $_User, $_Vars_ElementCategories;

    static $LangIncluded = false;
    if(!$LangIncluded)
    {
        //includeLang('readBattleReport');
        $LangIncluded = true;
    }

    $Date = $Report['time'];
    $Attacker = explode(',', $Report['id_owner1']);
    $Defender = explode(',', $Report['id_owner2']);
    $Disallow_Attacker = $Report['disallow_attacker'];
    if((!in_array($_User['id'], $Attacker) && !in_array($_User['id'], $Defender)) || (in_array($_User['id'], $Attacker) && $Disallow_Attacker == '1'))
    {
        message("<b class=\"red\">{$_Lang['BattleReportConverter_CannotConvert']}</b>", $_Lang['Title_System']);
    }
    $ReportData = json_decode($Report['report'], true);

    // All Rounds
    $Rounds                    = $ReportData['rounds'];
    $RoundsCount            = count($Rounds);

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
    $IsOnMoon                = $ReportData['init']['onMoon'];
    $LostUnits['atk']        = $ReportData['init']['atk_lost'];
    $LostUnits['def']        = $ReportData['init']['def_lost'];

    // Misc.
    $GenerateTime            = $ReportData['init']['time'];

    // Users data
    $Users                    = $ReportData['init']['usr'];

    ////////////////////////////////////////////////////////////////////////////////////
    // Let's convert the Battle Report!

    $ReportCode = '[align=center][size=medium][b]'.sprintf($_Lang['sys_attack_title'], prettyDate("d m Y - H:i:s", $Date, 1)).'[/b][/size]'."\n\n";

    $TempColors = $Settings['colorArray'];
    foreach($_Vars_ElementCategories['fleet'] as $ElementID)
    {
        $UnitColors[$ElementID] = array_shift($TempColors);
        $TempColors[] = $UnitColors[$ElementID];
    }
    $TempColors = $Settings['colorArray'];
    foreach($_Vars_ElementCategories['defense'] as $ElementID)
    {
        $UnitColors[$ElementID] = array_shift($TempColors);
        $TempColors[] = $UnitColors[$ElementID];
    }

    foreach($Rounds as $Number => $Data)
    {
        if($Number != 1 AND $Number != $RoundsCount)
        {
            if($AddedAfterXRound !== true)
            {
                $ReportCode .= sprintf($_Lang['Conv_after_x_rounds'], ($RoundsCount - 1));
                $ReportCode .= "\n\n\n";
                $AddedAfterXRound = true;
            }
            continue;
        }
        if($Number == 2 AND $RoundsCount == 2)
        {
            $ReportCode .= $_Lang['Conv_in_next_round'];
            $ReportCode .= "\n\n\n";
        }

        foreach($Users as $TypeKey => $UsersData)
        {
            foreach($UsersData as $UserID => $UserData)
            {
                $ThisLists_Fleet = false;
                $ThisLists_Defense = false;

                if(empty($UsersParsed[$TypeKey][$UserID]))
                {
                    if(empty($UserData['username']))
                    {
                        $UserData['username'] = $_Lang['Conv_def_abandoned_'.($IsOnMoon ? '3' : '1')];
                    }
                    $UsersParsed[$TypeKey][$UserID]['name'] = sprintf('[u]'.($TypeKey == 'atk' ? $_Lang['Conv_att_user'] : $_Lang['Conv_def_user']).'[/u]', $UserData['username'], (empty($UserData['ally']) ? '' : "[b][color=orange][{$UserData['ally']}][/color][/b]"), 'x', 'xxx', 'xx');
                    $UsersParsed[$TypeKey][$UserID]['tech'] = $_Lang['Conv_tech'];
                }
                $ReportCode .= "{$UsersParsed[$TypeKey][$UserID]['name']}\n[size=xx-small]{$UsersParsed[$TypeKey][$UserID]['tech']}[/size]\n";

                if($Number == 1 AND !empty($Data[$TypeKey]['ships'][$UserID]))
                {
                    $InitState[$TypeKey][$UserID] = String2Array($Data[$TypeKey]['ships'][$UserID]);
                }
                if(!empty($InitState[$TypeKey][$UserID]))
                {
                    if($Number == 1)
                    {
                        $Data[$TypeKey]['ships'][$UserID] = $InitState[$TypeKey][$UserID];
                    }
                    else
                    {
                        $Data[$TypeKey]['ships'][$UserID] = String2Array($Data[$TypeKey]['ships'][$UserID]);
                    }

                    foreach($InitState[$TypeKey][$UserID] as $ShipID => $ShipInitCount)
                    {
                        if($ThisLists_Fleet === false AND in_array($ShipID, $_Vars_ElementCategories['fleet']))
                        {
                            $ReportCode .= "\n[u][i]{$_Lang['Conv_fleet']}[/i][/u]\n";
                            $ThisLists_Fleet = true;
                        }
                        if($ThisLists_Defense === false AND in_array($ShipID, $_Vars_ElementCategories['defense']))
                        {
                            $ReportCode .= "\n[u][i]{$_Lang['Conv_defense']}[/i][/u]\n";
                            $ThisLists_Defense = true;
                        }
                        $DiffCounter = '';
                        $CurrentCount = (isset($Data[$TypeKey]['ships'][$UserID][$ShipID]) ? $Data[$TypeKey]['ships'][$UserID][$ShipID] : 0);
                        if($CurrentCount < $ShipInitCount)
                        {
                            $DiffCounter = ' (- '.prettyNumber($ShipInitCount - $CurrentCount).')';
                            if($CurrentCount == 0)
                            {
                                $DiffCounter = "[color=red]{$DiffCounter}[/color]";
                            }
                        }
                        $ReportCode .= "[b][color=#{$UnitColors[$ShipID]}]{$_Lang['tech'][$ShipID]}: ".prettyNumber($CurrentCount)."[/color]{$DiffCounter}[/b]\n";
                    }
                    $ReportCode .= "\n";
                }
                else
                {
                    $ReportCode .= "\n[b][color=red]{$_Lang['sys_destroyed']}[/color][/b]\n";
                }
            }
            $ReportCode .= "\n";
        }
    }

    $ReportCode .= "\n";

    $AtkLostColStart = $AtkLostColEnd = $DefLostColStart = $DefLostColEnd = '';
    if($LostUnits['atk'] > $LostUnits['def'])
    {
        $AtkLostColStart = '[color=red]';
        $AtkLostColEnd = '[/color]';
    }
    else if($LostUnits['atk'] == $LostUnits['def'])
    {
        $AtkLostColStart = '[color=orange]';
        $AtkLostColEnd = '[/color]';
        $DefLostColStart = '[color=orange]';
        $DefLostColEnd = '[/color]';
    }
    else
    {
        $DefLostColStart = '[color=red]';
        $DefLostColEnd = '[/color]';
    }
    $TotalLostUnits = $LostUnits['atk'] + $LostUnits['def'];

    $StrAttackerUnits = sprintf($_Lang['Conv_atk_lostUnits'], $AtkLostColStart.prettyNumber($LostUnits['atk']).$AtkLostColEnd, ($TotalLostUnits > 0 ? sprintf('%0.2f', ($LostUnits['atk'] / $TotalLostUnits) * 100) : '0.00'));
    $StrDefenderUnits = sprintf($_Lang['Conv_def_lostUnits'], $DefLostColStart.prettyNumber($LostUnits['def']).$DefLostColEnd, ($TotalLostUnits > 0 ? sprintf('%0.2f', ($LostUnits['def'] / $TotalLostUnits) * 100) : '0.00'));
    $StrRuins = sprintf($_Lang['Conv_DebrisField'], '[b]'.prettyNumber($Debris['met']).'[/b]', $_Lang['Metal_rec'], '[b]'.prettyNumber($Debris['cry']).'[/b]', $_Lang['Crystal_rec']);
    $DebrisField = "{$StrAttackerUnits}\n{$StrDefenderUnits}\n{$StrRuins}";

    $MoonCreationChanceText = false;
    if($MoonChance !== false)
    {
        if($TotalMoonChance > $MoonChance)
        {
            $MoonCreationChanceText = sprintf($_Lang['Conv_moon_high'], $MoonChance, prettyNumber($TotalMoonChance));
        }
        else
        {
            $MoonCreationChanceText = sprintf($_Lang['Conv_moon_reg'], $MoonChance);
        }
    }

    if($CreatedMoon === true)
    {
        $MoonCreationText = $_Lang['Conv_moonbuild'];
    }
    else
    {
        $MoonCreationText = false;
    }

    $MoonDestroyText = '';
    switch($Result)
    {
        case COMBAT_ATK:
        {
            // Attacker won the battle
            $Pillage = sprintf($_Lang['sys_stealed_ressources'], '[b]'.prettyNumber($Stolen['met']).'[/b]', $_Lang['Metal_rec'], '[b]'.prettyNumber($Stolen['cry']).'[/b]', $_Lang['Crystal_rec'], '[b]'.prettyNumber($Stolen['deu']).'[/b]', $_Lang['Deuterium_rec']);
            $ReportCode .= "[b]{$_Lang['sys_attacker_won']}[/b]\n{$Pillage}\n";
            $ReportCode .= $DebrisField."\n";
            if($MoonDestroyed !== false)
            {
                if($MoonDestroyed === 1 AND $FleetDestroyed !== true)
                {
                    $MoonDestroyText = $_Lang['sys_destruction_onlymoon'];
                }
                else if($MoonDestroyed === 1 AND $FleetDestroyed == true)
                {
                    $MoonDestroyText = $_Lang['sys_destruction_both'];
                }
                else if($MoonDestroyed === 0 AND $FleetDestroyed == true)
                {
                    $MoonDestroyText = $_Lang['sys_destruction_onlyfleet'];
                }
                else
                {
                    $MoonDestroyText = $_Lang['sys_destruction_nothing'];
                }

                $MoonDestroyText = '';
                $MoonDestroyText .= "[b]{$MoonDestroyText}[/b]\n";
                $MoonDestroyText .= sprintf($_Lang['sys_destruc_lune'], '[b]'.$DestroyMoonChance.'[/b]')."\n";
                $MoonDestroyText .= sprintf($_Lang['sys_destruc_rip'], '[b]'.$DestroyFleetChance.'[/b]')."\n";
            }
            break;
        }
        case COMBAT_DEF:
        {
            // Defender won the battle
            $ReportCode .= "[b]{$_Lang['sys_defender_won']}[/b]\n";
            $ReportCode .= $DebrisField."\n";
            break;
        }
        case COMBAT_DRAW:
        {
            // It's a draw!
            $ReportCode .= "[b]{$_Lang['sys_both_won']}[/b]\n";
            $ReportCode .= $DebrisField."\n";
            break;
        }
    }
    if($MoonCreationChanceText)
    {
        $ReportCode .= $MoonCreationChanceText."\n";
    }
    if($MoonCreationText)
    {
        $ReportCode .= $MoonCreationText."\n";
    }
    if($IsOnMoon)
    {
        $ReportCode .= $_Lang['Conv_battleonMoon']."\n";
    }

    $ReportCode .= $MoonDestroyText;

    $ReportCode .= sprintf($_Lang['sys_rapport_build_time'], '[b]'.$GenerateTime.'[/b]');
    $ReportCode .= "\n".$_Lang['Conv_generated_by'];
    $ReportCode .= '[/align]';

    return $ReportCode;
}

?>
