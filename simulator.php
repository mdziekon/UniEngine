<?php

define('INSIDE', true);

$_EnginePath = './';
include($_EnginePath.'common.php');

loggedCheck();

includeLang('simulator');
$_Lang['rows'] = '';
$_Lang['SimResult'] = '';

$TechEquivalents = array
(
    1 => 109,
    2 => 110,
    3 => 111,
    4 => 120,
    5 => 121,
    6 => 122,
    7 => 125,
    8 => 126,
    9 => 199,
);
$TechCount = count($TechEquivalents);
$MaxACSSlots = ACS_MAX_JOINED_FLEETS + 1;
$MaxStringLength = 30;

if(!empty($_POST['spyreport']))
{
    $_POST['spyreport'] = json_decode(stripslashes($_POST['spyreport']), true);
    $_POST['def_techs'][1] = (isset($_POST['spyreport']['tech']) ? $_POST['spyreport']['tech'] : null);
    $_POST['def_ships'][1] = (isset($_POST['spyreport']['ships']) ? $_POST['spyreport']['ships'] : null);
    $_POST['spyreport'] = null;
}

if(isset($_POST['simulate']) && $_POST['simulate'] == 'yes')
{
    $Calculate = true;

    if(!empty($_POST['atk_techs']))
    {
        foreach($_POST['atk_techs'] as $User => $Vals)
        {
            $UserTemp = $User - 1;
            foreach($TechEquivalents as $TechID => $TechKey)
            {
                if(!isset($Vals[$TechID]) || $Vals[$TechID] <= 0)
                {
                    $Value = 0;
                }
                else
                {
                    $Value = intval($Vals[$TechID]);
                }
                $AttackingTechs[$UserTemp][$TechKey] = $Value;
            }
            $AttackersData[$UserTemp] = array
            (
                'username' => $_Lang['Attacker_Txt'].$User,
                'techs' => Array2String($AttackingTechs[$UserTemp]),
                'pos' => '0:0:0'
            );
        }
    }
    if(!empty($_POST['def_techs']))
    {
        foreach($_POST['def_techs'] as $User => $Vals)
        {
            $UserTemp = $User - 1;
            foreach($TechEquivalents as $TechID => $TechKey)
            {
                if(!isset($Vals[$TechID]) || $Vals[$TechID] <= 0)
                {
                    $Value = 0;
                }
                else
                {
                    $Value = intval($Vals[$TechID]);
                }
                $DefendingTechs[$UserTemp][$TechKey] = $Value;
            }
            $DefendersData[$UserTemp] = array
            (
                'username' => $_Lang['Defender_Txt'].$User,
                'techs' => Array2String($DefendingTechs[$UserTemp]),
                'pos' => '0:0:0'
            );
        }
    }
    if(!empty($_POST['atk_ships']))
    {
        foreach($_POST['atk_ships'] as $User => $Vals)
        {
            $UserTemp = $User - 1;
            foreach($Vals as $ID => $Count)
            {
                $Count = str_replace(array('.', ','), '', $Count);
                if($Count > 0)
                {
                    if(strlen($Count) > $MaxStringLength)
                    {
                        $Count = substr($Count, 0, $MaxStringLength);
                    }
                    $AttackingFleets[$UserTemp][$ID] = floor($Count);
                }
            }
            if(empty($AttackingFleets[$UserTemp]))
            {
                unset($AttackingFleets[$UserTemp]);
                unset($AttackingTechs[$UserTemp]);
                unset($AttackersData[$UserTemp]);
            }
            else
            {
                if(empty($AttackersData[$UserTemp]))
                {
                    foreach($TechEquivalents as $TechID => $TechKey)
                    {
                        if(!isset($Vals[$TechID]) || $Vals[$TechID] <= 0)
                        {
                            $Value = 0;
                        }
                        else
                        {
                            $Value = intval($Vals[$TechID]);
                        }
                        $AttackingTechs[$UserTemp][$TechKey] = $Value;
                    }
                    $AttackersData[$UserTemp] = array
                    (
                        'username' => $_Lang['Attacker_Txt'].$User,
                        'techs' => Array2String($AttackingTechs[$UserTemp]),
                        'pos' => '0:0:0'
                    );
                }
            }
        }
    }
    else
    {
        $Calculate = false;
        $BreakMSG = $_Lang['Break_noATKShips'];
    }
    if(!empty($_POST['def_ships']))
    {
        foreach($_POST['def_ships'] as $User => $Vals)
        {
            $UserTemp = $User - 1;
            foreach($Vals as $ID => $Count)
            {
                $Count = str_replace(array('.', ','), '', $Count);
                if($Count > 0)
                {
                    if(strlen($Count) > $MaxStringLength)
                    {
                        $Count = substr($Count, 0, $MaxStringLength);
                    }
                    $DefendingFleets[$UserTemp][$ID] = floor($Count);
                }
            }
            if(empty($DefendingFleets[$UserTemp]))
            {
                unset($DefendingFleets[$UserTemp]);
                unset($DefendingTechs[$UserTemp]);
                unset($DefendersData[$UserTemp]);
            }
            else
            {
                if(empty($DefendersData[$UserTemp]))
                {
                    foreach($TechEquivalents as $TechID => $TechKey)
                    {
                        if(!isset($Vals[$TechID]) || $Vals[$TechID] <= 0)
                        {
                            $Value = 0;
                        }
                        else
                        {
                            $Value = intval($Vals[$TechID]);
                        }
                        $DefendingTechs[$UserTemp][$TechKey] = $Value;
                    }
                    $DefendersData[$UserTemp] = array
                    (
                        'username' => $_Lang['Defender_Txt'].$User,
                        'techs' => Array2String($DefendingTechs[$UserTemp]),
                        'pos' => '0:0:0'
                    );
                }
            }
        }
    }
    else
    {
        $Calculate = false;
        $BreakMSG = $_Lang['Break_noDEFShips'];
    }

    if(isset($AttackersData))
    {
        foreach($AttackersData as $UserTemp => $Data)
        {
            if(empty($AttackingFleets[$UserTemp]))
            {
                unset($AttackingFleets[$UserTemp]);
                unset($AttackingTechs[$UserTemp]);
                unset($AttackersData[$UserTemp]);
            }
        }
    }
    if(isset($DefendersData))
    {
        foreach($DefendersData as $UserTemp => $Data)
        {
            if(empty($DefendingFleets[$UserTemp]))
            {
                unset($DefendingFleets[$UserTemp]);
                unset($DefendingTechs[$UserTemp]);
                unset($DefendersData[$UserTemp]);
            }
        }
    }

    if(empty($AttackingFleets) OR (empty($DefendingFleets)))
    {
        if(empty($AttackingFleets))
        {
            $BreakMSG = $_Lang['Break_noATKShips'];
        }
        else if(empty($DefendingFleets))
        {
            $BreakMSG = $_Lang['Break_noDEFShips'];
        }
        $Calculate = false;
    }

    if(MORALE_ENABLED)
    {
        if(!empty($AttackingFleets))
        {
            foreach($AttackingFleets as $ThisUser => $ThisData)
            {
                $ThisMoraleLevel = intval($_POST['atk_morale'][($ThisUser + 1)]);
                if($ThisMoraleLevel > 100)
                {
                    $ThisMoraleLevel = 100;
                }
                else if($ThisMoraleLevel < -100)
                {
                    $ThisMoraleLevel = -100;
                }
                $AttackersData[$ThisUser]['morale'] = $ThisMoraleLevel;

                // Bonuses
                if($ThisMoraleLevel >= MORALE_BONUS_FLEETPOWERUP1)
                {
                    $AttackingTechs[$ThisUser]['TotalForceFactor'] = MORALE_BONUS_FLEETPOWERUP1_FACTOR;
                }
                if($ThisMoraleLevel >= MORALE_BONUS_FLEETSHIELDUP1)
                {
                    $AttackingTechs[$ThisUser]['TotalShieldFactor'] = MORALE_BONUS_FLEETSHIELDUP1_FACTOR;
                }
                if($ThisMoraleLevel >= MORALE_BONUS_FLEETSDADDITION)
                {
                    $AttackingTechs[$ThisUser]['SDAdd'] = MORALE_BONUS_FLEETSDADDITION_VALUE;
                }
                // Penalties
                if($ThisMoraleLevel <= MORALE_PENALTY_FLEETPOWERDOWN1)
                {
                    $AttackingTechs[$ThisUser]['TotalForceFactor'] = MORALE_PENALTY_FLEETPOWERDOWN1_FACTOR;
                }
                if($ThisMoraleLevel <= MORALE_PENALTY_FLEETPOWERDOWN2)
                {
                    $AttackingTechs[$ThisUser]['TotalForceFactor'] = MORALE_PENALTY_FLEETPOWERDOWN2_FACTOR;
                }
                if($ThisMoraleLevel <= MORALE_PENALTY_FLEETSHIELDDOWN1)
                {
                    $AttackingTechs[$ThisUser]['TotalShieldFactor'] = MORALE_PENALTY_FLEETSHIELDDOWN1_FACTOR;
                }
                if($ThisMoraleLevel <= MORALE_PENALTY_FLEETSHIELDDOWN2)
                {
                    $AttackingTechs[$ThisUser]['TotalShieldFactor'] = MORALE_PENALTY_FLEETSHIELDDOWN2_FACTOR;
                }
                if($ThisMoraleLevel <= MORALE_PENALTY_FLEETSDDOWN)
                {
                    $AttackingTechs[$ThisUser]['SDFactor'] = MORALE_PENALTY_FLEETSDDOWN_FACTOR;
                }
            }
        }
        if(!empty($DefendingFleets))
        {
            foreach($DefendingFleets as $ThisUser => $ThisData)
            {
                $ThisMoraleLevel = intval($_POST['def_morale'][($ThisUser + 1)]);
                if($ThisMoraleLevel > 100)
                {
                    $ThisMoraleLevel = 100;
                }
                else if($ThisMoraleLevel < -100)
                {
                    $ThisMoraleLevel = -100;
                }
                $DefendersData[$ThisUser]['morale'] = $ThisMoraleLevel;

                // Bonuses
                if($ThisMoraleLevel >= MORALE_BONUS_FLEETPOWERUP1)
                {
                    $DefendingTechs[$ThisUser]['TotalForceFactor'] = MORALE_BONUS_FLEETPOWERUP1_FACTOR;
                }
                if($ThisMoraleLevel >= MORALE_BONUS_FLEETSHIELDUP1)
                {
                    $DefendingTechs[$ThisUser]['TotalShieldFactor'] = MORALE_BONUS_FLEETSHIELDUP1_FACTOR;
                }
                if($ThisMoraleLevel >= MORALE_BONUS_FLEETSDADDITION)
                {
                    $DefendingTechs[$ThisUser]['SDAdd'] = MORALE_BONUS_FLEETSDADDITION_VALUE;
                }
                // Penalties
                if($ThisMoraleLevel <= MORALE_PENALTY_FLEETPOWERDOWN1)
                {
                    $DefendingTechs[$ThisUser]['TotalForceFactor'] = MORALE_PENALTY_FLEETPOWERDOWN1_FACTOR;
                }
                if($ThisMoraleLevel <= MORALE_PENALTY_FLEETPOWERDOWN2)
                {
                    $DefendingTechs[$ThisUser]['TotalForceFactor'] = MORALE_PENALTY_FLEETPOWERDOWN2_FACTOR;
                }
                if($ThisMoraleLevel <= MORALE_PENALTY_FLEETSHIELDDOWN1)
                {
                    $DefendingTechs[$ThisUser]['TotalShieldFactor'] = MORALE_PENALTY_FLEETSHIELDDOWN1_FACTOR;
                }
                if($ThisMoraleLevel <= MORALE_PENALTY_FLEETSHIELDDOWN2)
                {
                    $DefendingTechs[$ThisUser]['TotalShieldFactor'] = MORALE_PENALTY_FLEETSHIELDDOWN2_FACTOR;
                }
                if($ThisMoraleLevel <= MORALE_PENALTY_FLEETSDDOWN)
                {
                    $DefendingTechs[$ThisUser]['SDFactor'] = MORALE_PENALTY_FLEETSDDOWN_FACTOR;
                }
            }
        }
    }

    if($Calculate === true)
    {
        $Loop = 1;

        if(!isset($IncludeCombatEngine))
        {
            include($_EnginePath.'includes/CombatEngineAres.php');
            include($_EnginePath.'includes/functions/CreateBattleReport.php');
            $IncludeCombatEngine = true;
        }

        $SimData['atk_win'] = 0;
        $SimData['def_win'] = 0;
        $SimData['draw'] = 0;
        $SimData['rounds'] = 0;
        $SimData['max_rounds'] = 0;
        $SimData['min_rounds'] = 99;

        $SimData['total_lost_atk'] = array('met' => 0, 'cry' => 0, 'deu' => 0);
        $SimData['total_lost_def'] = array('met' => 0, 'cry' => 0, 'deu' => 0);
        $SimData['ship_lost_atk'] = 0;
        $SimData['ship_lost_def'] = 0;
        $SimData['ship_lost_atk_min'] = 99999999999999999999.0;
        $SimData['ship_lost_atk_max'] = 0;
        $SimData['ship_lost_def_min'] = 99999999999999999999.0;
        $SimData['ship_lost_def_max'] = 0;

        $TotalTime = 0;
        for($i = 1; $i <= $Loop; $i += 1)
        {
            $MoonHasBeenCreated = false;
            $MoonHasBeenDestroyed = false;
            $chance = 0;
            $RIPDestroyedByMoon = false;
            $chance2 = 0;
            $Temp['ship_lost_atk'] = 0;
            $Temp['ship_lost_def'] = 0;

            $StartTime = microtime(true);

            // Now start Combat calculations
            $Combat = Combat($AttackingFleets, $DefendingFleets, $AttackingTechs, $DefendingTechs, true);

            $EndTime = microtime(true);
            $TimeNow = $EndTime - $StartTime;
            $TotalTime += $TimeNow;
            $totaltime = sprintf('%0.6f', $TimeNow);

            $RoundsData = $Combat['rounds'];

            $RoundCount = count($RoundsData) - 1;
            if($RoundCount > $SimData['max_rounds'])
            {
                $SimData['max_rounds'] = $RoundCount;
            }
            if($RoundCount < $SimData['min_rounds'])
            {
                $SimData['min_rounds'] = $RoundCount;
            }
            $SimData['rounds'] += $RoundCount;

            $Result = $Combat['result'];

            $DebrisMetalAtk = 0;
            $DebrisCrystalAtk = 0;
            $RealDebrisMetalAtk = 0;
            $RealDebrisCrystalAtk = 0;
            $RealDebrisDeuteriumAtk = 0;
            $DebrisMetalDef = 0;
            $DebrisCrystalDef = 0;
            $RealDebrisMetalDef = 0;
            $RealDebrisCrystalDef = 0;
            $RealDebrisDeuteriumDef = 0;

            $TotalLostMetal = 0;
            $TotalLostCrystal = 0;

            $AtkShips = $Combat['AttackerShips'];
            $DefShips = $Combat['DefenderShips'];
            $AtkLost = $Combat['AtkLose'];
            $DefLost = $Combat['DefLose'];
            $DefSysLost = $Combat['DefSysLost'];

            if(!empty($AtkLost))
            {
                foreach($AtkLost as $ID => $Count)
                {
                    if($ID > 200 AND $ID < 300 AND $_GameConfig['Fleet_Cdr'] > 0)
                    {
                        $DebrisMetalAtk += floor($_Vars_Prices[$ID]['metal'] * $Count * ($_GameConfig['Fleet_Cdr'] / 100));
                        $DebrisCrystalAtk += floor($_Vars_Prices[$ID]['crystal'] * $Count * ($_GameConfig['Fleet_Cdr'] / 100));
                        $RealDebrisMetalAtk += floor($_Vars_Prices[$ID]['metal'] * $Count);
                        $RealDebrisCrystalAtk += floor($_Vars_Prices[$ID]['crystal'] * $Count);
                        $RealDebrisDeuteriumAtk += floor($_Vars_Prices[$ID]['deuterium'] * $Count);
                        $SimData['ship_lost_atk'] += $Count;
                        $Temp['ship_lost_atk'] += $Count;
                    }
                }
                $TotalLostMetal = $DebrisMetalAtk;
                $TotalLostCrystal = $DebrisCrystalAtk;
            }

            $SimData['total_lost_atk']['met'] += $RealDebrisMetalAtk;
            $SimData['total_lost_atk']['cry'] += $RealDebrisCrystalAtk;
            $SimData['total_lost_atk']['deu'] += $RealDebrisDeuteriumAtk;

            // Calculate looses - defender
            if(!empty($DefLost))
            {
                foreach($DefLost as $ID => $Count)
                {
                    if($ID > 200 AND $ID < 300 AND $_GameConfig['Fleet_Cdr'] > 0)
                    {
                        $DebrisMetalDef += floor($_Vars_Prices[$ID]['metal'] * $Count * ($_GameConfig['Fleet_Cdr'] / 100));
                        $DebrisCrystalDef += floor($_Vars_Prices[$ID]['crystal'] * $Count * ($_GameConfig['Fleet_Cdr'] / 100));
                    }
                    else if($ID > 400 AND $_GameConfig['Defs_Cdr'] > 0)
                    {
                        $DebrisMetalDef += floor($_Vars_Prices[$ID]['metal'] * $Count * ($_GameConfig['Defs_Cdr'] / 100));
                        $DebrisCrystalDef += floor($_Vars_Prices[$ID]['crystal'] * $Count * ($_GameConfig['Defs_Cdr'] / 100));
                    }
                    $RealDebrisMetalDef += floor($_Vars_Prices[$ID]['metal'] * $Count);
                    $RealDebrisCrystalDef += floor($_Vars_Prices[$ID]['crystal'] * $Count);
                    $RealDebrisDeuteriumDef += floor($_Vars_Prices[$ID]['deuterium'] * $Count);
                    $SimData['ship_lost_def'] += $Count;
                    $Temp['ship_lost_def'] += $Count;
                }
                $TotalLostMetal += $DebrisMetalDef;
                $TotalLostCrystal += $DebrisCrystalDef;
            }

            $SimData['total_lost_def']['met'] += $RealDebrisMetalDef;
            $SimData['total_lost_def']['cry'] += $RealDebrisCrystalDef;
            $SimData['total_lost_def']['deu'] += $RealDebrisDeuteriumDef;

            switch($Result)
            {
                case COMBAT_ATK:
                    $SimData['atk_win'] += 1;
                    break;
                case COMBAT_DEF:
                    $SimData['def_win'] += 1;
                    break;
                case COMBAT_DRAW:
                    $SimData['draw'] +=1 ;
                    break;
            }

            switch($Result)
            {
                case COMBAT_ATK:
                    $_Lang['Winner_Color'] = 'red';
                    $_Lang['Winner_Name'] = $_Lang['WonBy_Attacker'];
                    break;
                case COMBAT_DEF:
                    $_Lang['Winner_Color'] = 'lime';
                    $_Lang['Winner_Name'] = $_Lang['WonBy_Defender'];
                    break;
                case COMBAT_DRAW:
                    $_Lang['Winner_Color'] = 'orange';
                    $_Lang['Winner_Name'] = $_Lang['WonBy_Draw'];
                    break;
            }

            $FleetDebris = $TotalLostCrystal + $TotalLostMetal;
            $MoonChance = floor($FleetDebris / COMBAT_MOONPERCENT_RESOURCES);
            $TotalMoonChance = $MoonChance;
            if($MoonChance > 20)
            {
                $MoonChance = 20;
            }

            $ReportData = array();

            $ReportData['init']['usr']['atk'] = $AttackersData;
            $ReportData['init']['usr']['def'] = $DefendersData;

            $ReportData['init']['time'] = $totaltime;
            $ReportData['init']['date'] = time();

            $ReportData['init']['result'] = $Result;
            $ReportData['init']['met'] = 0;
            $ReportData['init']['cry'] = 0;
            $ReportData['init']['deu'] = 0;
            $ReportData['init']['deb_met'] = $TotalLostMetal;
            $ReportData['init']['deb_cry'] = $TotalLostCrystal;
            $ReportData['init']['moon_chance'] = $MoonChance;
            $ReportData['init']['total_moon_chance'] = $TotalMoonChance;
            $ReportData['init']['moon_created'] = $MoonHasBeenCreated;
            $ReportData['init']['moon_destroyed'] = $MoonHasBeenDestroyed;
            $ReportData['init']['moon_des_chance'] = ($chance >= 0 ? (($chance == 0) ? '0' : $chance) : false);
            $ReportData['init']['fleet_destroyed'] = $RIPDestroyedByMoon;
            $ReportData['init']['fleet_des_chance'] = ($chance2 >= 0 ? (($chance2 == 0) ? '0' : $chance2) : false);
            $ReportData['init']['planet_name'] = 'Planeta';
            $ReportData['init']['onMoon'] = false;
            $ReportData['init']['atk_lost'] = $RealDebrisMetalAtk + $RealDebrisCrystalAtk + $RealDebrisDeuteriumAtk;
            $ReportData['init']['def_lost'] = $RealDebrisMetalDef + $RealDebrisCrystalDef + $RealDebrisDeuteriumDef;

            foreach($RoundsData as $RoundKey => $RoundData)
            {
                foreach($RoundData as $MainKey => $RoundData2)
                {
                    if(!empty($RoundData2['ships']))
                    {
                        foreach($RoundData2['ships'] as $UserKey => $UserData)
                        {
                            $RoundsData[$RoundKey][$MainKey]['ships'][$UserKey] = Array2String($UserData);
                        }
                    }
                }
            }
            $ReportData['rounds'] = $RoundsData;

            $ReportID = CreateBattleReport($ReportData, array('atk' => $_User['id'], 'def' => 0), 0, true);

            $parse = $_Lang;
            $parse['id'] = $ReportID;

            $AllReports[] = $ReportID;
            if($i == $Loop)
            {
                $parse['time'] = sprintf('%0.6f', $TotalTime);
            }
            else
            {
                $parse['time'] = $totaltime;
            }

            if($Temp['ship_lost_atk'] < $SimData['ship_lost_atk_min'])
            {
                $SimData['ship_lost_atk_min'] = $Temp['ship_lost_atk'];
            }
            if($Temp['ship_lost_atk'] > $SimData['ship_lost_atk_max'])
            {
                $SimData['ship_lost_atk_max'] = $Temp['ship_lost_atk'];
            }

            if($Temp['ship_lost_def'] < $SimData['ship_lost_def_min'])
            {
                $SimData['ship_lost_def_min'] = $Temp['ship_lost_def'];
            }
            if($Temp['ship_lost_def'] > $SimData['ship_lost_def_max'])
            {
                $SimData['ship_lost_def_max'] = $Temp['ship_lost_def'];
            }
        }

        $SimData['ship_lost_def_max'] = prettyNumber($SimData['ship_lost_def_max']);
        $SimData['ship_lost_def_min'] = prettyNumber($SimData['ship_lost_def_min']);
        $SimData['ship_lost_atk_max'] = prettyNumber($SimData['ship_lost_atk_max']);
        $SimData['ship_lost_atk_min'] = prettyNumber($SimData['ship_lost_atk_min']);
        $SimData['total_lost_atk_met'] = prettyNumber(round($SimData['total_lost_atk']['met'] / $Loop));
        $SimData['total_lost_atk_cry'] = prettyNumber(round($SimData['total_lost_atk']['cry'] / $Loop));
        $SimData['total_lost_atk_deu'] = prettyNumber(round($SimData['total_lost_atk']['deu'] / $Loop));
        $SimData['total_lost_def_met'] = prettyNumber(round($SimData['total_lost_def']['met'] / $Loop));
        $SimData['total_lost_def_cry'] = prettyNumber(round($SimData['total_lost_def']['cry'] / $Loop));
        $SimData['total_lost_def_deu'] = prettyNumber(round($SimData['total_lost_def']['deu'] / $Loop));
        $SimData['ship_lost_atk'] = prettyNumber(round($SimData['ship_lost_atk'] / $Loop));
        $SimData['ship_lost_def'] = prettyNumber(round($SimData['ship_lost_def'] / $Loop));
        $SimData['rounds'] = round($SimData['rounds']/$Loop);

        $SimData['sim_loop'] = $Loop;
        $SimData['AddInfo'] = ((!empty($SimData['AddInfo'])) ? implode('<br/>', $SimData['AddInfo']).'<br/><br/>' : '');
        $parse = array_merge($parse, $SimData);

        $_Lang['SimResult'] .= parsetemplate(gettemplate('simulator_result'), $parse);

        // Trigger Tasks Check
        Tasks_TriggerTask($_User, 'USE_SIMULATOR');
    }
    else
    {
        $parse = $_Lang;
        $parse['msg'] = $BreakMSG;
        $_Lang['SimResult'] .= parsetemplate(gettemplate('simulator_result_warn'), $parse);
    }

}

$TPL_Slot = gettemplate('simulator_slot');
$TPL_SingleRow = gettemplate('simulator_single_row');
$TPL_NoBoth = gettemplate('simulator_row_noboth');
$TPL_Row = gettemplate('simulator_row');
$TPL_NoLeft = gettemplate('simulator_row_noleft');

$Offsets;

for($i = 1; $i <= $MaxACSSlots; $i += 1)
{
    $ThisSlot = array();
    $ThisSlot['SlotID'] = $i;
    $ThisSlot['txt'] = '';

    $InsertTabIndex1 = 1;
    $InsertTabIndex2 = 1;
    $parse = $_Lang;
    $parse['i'] = $i;
    if($i > 1)
    {
        $ThisSlot['SlotHidden'] = 'hide';
    }

    if(MORALE_ENABLED)
    {
        $parse['RowText'] = $_Lang['Morale'];
        $ThisSlot['txt'] .= parsetemplate($TPL_SingleRow, $parse);
        $parse['RowText'] = $_Lang['Morale_Level'];
        $parse['RowInput'] = "<input type=\"text\" tabindex=\"{REP1_O{$i}_{$InsertTabIndex1}}\" name=\"atk_morale[{$i}]\" value=\"{$_POST['atk_morale'][$i]}\" autocomplete=\"off\" />%";
        $parse['RowText2'] = $_Lang['Morale_Level'];
        $parse['RowInput2'] = "<input type=\"text\" tabindex=\"{REP2_O{$i}_{$InsertTabIndex2}}\" name=\"def_morale[{$i}]\" value=\"{$_POST['def_morale'][$i]}\" autocomplete=\"off\" />%";

        $ThisSlot['txt'] .= parsetemplate($TPL_Row, $parse);
        $InsertTabIndex1 += 1;
        $InsertTabIndex2 += 1;
    }

    $parse['RowText'] = $_Lang['Technology'];
    $ThisSlot['txt'] .= parsetemplate($TPL_SingleRow, $parse);
    $parse['RowText'] = '<a class="orange point fillTech_atk">'.$_Lang['FillMyTechs'].'</a> / <a class="orange point clnTech_atk">'.$_Lang['Fill_Clean'].'</a>';
    $parse['RowText2'] = '<a class="orange point fillTech_def">'.$_Lang['FillMyTechs'].'</a> / <a class="orange point clnTech_def">'.$_Lang['Fill_Clean'].'</a>';
    $ThisSlot['txt'] .= parsetemplate($TPL_NoBoth, $parse);

    for($techs = 1; $techs <= $TechCount; $techs += 1)
    {
        $ThisRow_InsertValue_Atk = isset($_POST['atk_techs'][$i][$techs]) ? $_POST['atk_techs'][$i][$techs] : null;
        $ThisRow_InsertValue_Def = isset($_POST['def_techs'][$i][$techs]) ? $_POST['def_techs'][$i][$techs] : null;

        $parse['RowText'] = $_Lang['Techs'][$techs];
        $parse['RowInput'] = "<input type=\"text\" tabindex=\"{REP1_O{$i}_{$InsertTabIndex1}}\" name=\"atk_techs[{$i}][{$techs}]\" value=\"{$ThisRow_InsertValue_Atk}\" autocomplete=\"off\" />";
        $parse['RowText2'] = $_Lang['Techs'][$techs];
        $parse['RowInput2'] = "<input type=\"text\" tabindex=\"{REP2_O{$i}_{$InsertTabIndex2}}\" name=\"def_techs[{$i}][{$techs}]\" value=\"{$ThisRow_InsertValue_Def}\" autocomplete=\"off\" />";

        $ThisSlot['txt'] .= parsetemplate($TPL_Row, $parse);
        $InsertTabIndex1 += 1;
        $InsertTabIndex2 += 1;
    }

    $parse['RowText'] = $_Lang['Fleets'];
    $ThisSlot['txt'] .= parsetemplate($TPL_SingleRow, $parse);
    $parse['RowText'] = '<a class="orange point fillShip_atk">'.$_Lang['FillMyFleets'].'</a> / <a class="orange point clnShip_atk">'.$_Lang['Fill_Clean'].'</a>';
    $parse['RowText2'] = '<a class="orange point fillShip_def">'.$_Lang['FillMyFleets'].'</a> / <a class="orange point clnShip_def">'.$_Lang['Fill_Clean'].'</a>';
    $ThisSlot['txt'] .= parsetemplate($TPL_NoBoth, $parse);

    foreach($_Vars_ElementCategories['fleet'] as $Ships)
    {
        $ThisRow_InsertValue_Def = isset($_POST['def_ships'][$i][$Ships]) ? $_POST['def_ships'][$i][$Ships] : null;

        if(!empty($_Vars_Prices[$Ships]['engine']))
        {
            $ThisRow_InsertValue_Atk = isset($_POST['atk_ships'][$i][$Ships]) ? $_POST['atk_ships'][$i][$Ships] : null;

            $parse['RowText'] = $_Lang['tech'][$Ships];
            $parse['RowInput'] = "<input type=\"text\" tabindex=\"{REP1_O{$i}_{$InsertTabIndex1}}\" name=\"atk_ships[{$i}][{$Ships}]\" value=\"{$ThisRow_InsertValue_Atk}\" autocomplete=\"off\" class=\"pad2 fl\" /> <span class=\"fr\">(<span class=\"clnOne point\">{$_Lang['Button_Min']}</span> / <span class=\"maxOne point\">{$_Lang['Button_Max']}</span>)</span>";
            $parse['RowText2'] = $_Lang['tech'][$Ships];
            $parse['RowInput2'] = "<input type=\"text\" tabindex=\"{REP2_O{$i}_{$InsertTabIndex2}}\" name=\"def_ships[{$i}][{$Ships}]\" value=\"{$ThisRow_InsertValue_Def}\" autocomplete=\"off\" class=\"pad2 fl\" /> <span class=\"fr\">(<span class=\"clnOne point\">{$_Lang['Button_Min']}</span> / <span class=\"maxOne point\">{$_Lang['Button_Max']}</span>)</span>";

            $ThisSlot['txt'] .= parsetemplate($TPL_Row, $parse);
            $InsertTabIndex1 += 1;
            $InsertTabIndex2 += 1;
        }
        else
        {
            $parse['RowText'] = '-';
            $parse['RowText2'] = $_Lang['tech'][$Ships];
            $parse['RowInput2'] = "<input type=\"text\" tabindex=\"{REP2_O{$i}_{$InsertTabIndex2}}\" name=\"def_ships[{$i}][{$Ships}]\" value=\"{$ThisRow_InsertValue_Def}\" autocomplete=\"off\" class=\"pad2 fl\" /> <span class=\"fr\">(<span class=\"clnOne point\">{$_Lang['Button_Min']}</span> / <span class=\"maxOne point\">{$_Lang['Button_Max']}</span>)</span>";

            $ThisSlot['txt'] .= parsetemplate($TPL_NoLeft, $parse);
            $InsertTabIndex2 += 1;
        }
    }

    if($i == 1)
    {
        $parse['RowText'] = $_Lang['Defense'];
        $ThisSlot['txt'] .= parsetemplate($TPL_SingleRow, $parse);

        foreach($_Vars_ElementCategories['defense'] as $Ships)
        {
            if(in_array($Ships, $_Vars_ElementCategories['rockets']))
            {
                continue;
            }

            $ThisRow_InsertValue_Def = isset($_POST['def_ships'][$i][$Ships]) ? $_POST['def_ships'][$i][$Ships] : null;

            $parse['RowText'] = '-';
            $parse['RowText2'] = $_Lang['tech'][$Ships];
            $parse['RowInput2'] = "<input type=\"text\" tabindex=\"{REP2_O{$i}_{$InsertTabIndex2}}\" name=\"def_ships[{$i}][{$Ships}]\" value=\"{$ThisRow_InsertValue_Def}\" autocomplete=\"off\" class=\"pad2 fl\" /> <span class=\"fr\">(<span class=\"clnOne point\">{$_Lang['Button_Min']}</span> / <span class=\"maxOne point\">{$_Lang['Button_Max']}</span>)</span>";

            $ThisSlot['txt'] .= parsetemplate($TPL_NoLeft, $parse);
            $InsertTabIndex2 += 1;
        }
    }
    $Offsets[$i] = $InsertTabIndex1 - 1;

    $_Lang['rows'] .= parsetemplate($TPL_Slot, $ThisSlot);
}

$_Lang['rows'] = preg_replace_callback(
    '#\{REP1_O([0-9]{1,})_([0-9]{1,})\}#Ssi',
    function ($matches) {
        return ($matches[1] * 1000) + $matches[2];
    },
    $_Lang['rows']
);
$_Lang['rows'] = preg_replace_callback(
    '#\{REP2_O([0-9]{1,})_([0-9]{1,})\}#Ssi',
    function ($matches) use ($Offsets) {
        return ($matches[1] * 1000) + $Offsets[$matches[1]] + $matches[2];
    },
    $_Lang['rows']
);

$_Lang['fill_with_mytechs'] = "var MyTechs = new Array();\nMyTechs[1] = ".(string)($_User['tech_weapons'] + 0).";\nMyTechs[3] = ".(string)($_User['tech_shielding'] + 0).";\nMyTechs[2] = ".(string)($_User['tech_armour'] + 0).";\nMyTechs[4] = ".(string)($_User['tech_laser'] + 0).";\nMyTechs[5] = ".(string)($_User['tech_ion'] + 0).";\nMyTechs[6] = ".(string)($_User['tech_plasma'] + 0).";\nMyTechs[7] = ".(string)($_User['tech_antimatter'] + 0).";\nMyTechs[8] = ".(string)($_User['tech_disintegration'] + 0).";\nMyTechs[9] = ".(string)($_User['tech_graviton'] + 0).";\n";
$_Lang['fill_with_myfleets'] = "var MyFleets = new Array();\n";

$UsingPrettyInputBox = ($_User['settings_useprettyinputbox'] == 1 ? true : false);

foreach($_Vars_ElementCategories['fleet'] as $ID)
{
    if($_Planet[$_Vars_GameElements[$ID]] > 0)
    {
        $_Lang['fill_with_myfleets'] .= "MyFleets[{$ID}] = '".($UsingPrettyInputBox === true ? prettyNumber($_Planet[$_Vars_GameElements[$ID]]) : $_Planet[$_Vars_GameElements[$ID]])."';\n";
    }
}
foreach($_Vars_ElementCategories['defense'] as $ID)
{
    if(in_array($ID, $_Vars_ElementCategories['rockets']))
    {
        continue;
    }

    if($_Planet[$_Vars_GameElements[$ID]] > 0)
    {
        $_Lang['fill_with_myfleets'] .= "MyFleets[{$ID}] = '".($UsingPrettyInputBox === true ? prettyNumber($_Planet[$_Vars_GameElements[$ID]]) : $_Planet[$_Vars_GameElements[$ID]])."';\n";
    }
}
$_Lang['fill_with_myfleets'] .= "\n";
$_Lang['AllowPrettyInputBox'] = ($_User['settings_useprettyinputbox'] == 1 ? 'true' : 'false');

//Display page
$page = parsetemplate(gettemplate('simulator'), $_Lang);

display($page,$_Lang['Title'], false);

?>
