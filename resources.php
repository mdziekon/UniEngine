<?php

define('INSIDE', true);

$_AllowInVacationMode = true;

$_EnginePath = './';
include($_EnginePath.'common.php');

loggedCheck();

$Now = time();

function BuildRessourcePage($CurrentUser, &$CurrentPlanet)
{
    global $_Lang, $_POST, $_GameConfig, $Now, $SetPercents, $UserDev_Log,
        $_Vars_ResProduction, $_Vars_GameElements, $_Vars_ElementCategories, $_Vars_Prices;

    includeLang('resources');

    $RessBodyTPL = gettemplate('resources');
    $RessRowTPL = gettemplate('resources_row');

    // Set Moon Basic Income
    if($CurrentPlanet['planet_type'] == 3)
    {
        $_GameConfig['metal_basic_income'] = 0;
        $_GameConfig['crystal_basic_income'] = 0;
        $_GameConfig['deuterium_basic_income'] = 0;
    }

    $ValidList['percent'] = array (0, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100);
    if($_POST)
    {
        if(isOnVacation())
        {
            message($_Lang['Vacation_WarnMsg'], $_Lang['Vacation']);
        }

        $Post_SetUsage2All = false;

        foreach($_POST as $Field => $Value)
        {
            if($Field == 'setUsage2All')
            {
                if($Value == 'on')
                {
                    $Post_SetUsage2All = true;
                }
                continue;
            }
            if(preg_match('/^[a-zA-Z\_]{1,}$/D', $Field))
            {
                $FieldName = $Field.'_workpercent';
                if(isset($CurrentPlanet[$FieldName]))
                {
                    if(!in_array($Value, $ValidList['percent']))
                    {
                        message($_Lang['Hacking_attempt'], $_Lang['Warning'], 'resources.php', 3);
                    }
                    $Value = $Value / 10;
                    if($CurrentPlanet[$FieldName] != $Value || $Post_SetUsage2All)
                    {
                        $SetPercents[$CurrentPlanet['id']][$FieldName] = array('old' => $CurrentPlanet[$FieldName], 'new' => $Value);
                        $CurrentPlanet[$FieldName] = $Value;
                        $UpdatePlanet[] = "`{$FieldName}` = '{$Value}'";
                        $FieldName = str_replace('_workpercent', '', $FieldName);
                        $InsertDevLog[] = "{$FieldName},{$Value}";
                    }
                }
            }
            else
            {
                message($_Lang['Hacking_attempt'], $_Lang['Warning'], 'resources.php', 3);
            }
        }
        if(!empty($UpdatePlanet))
        {
            $UpdatePercentIDs[] = $CurrentPlanet['id'];
            if($Post_SetUsage2All)
            {
                $SQLResult_SelectOtherPlanets = doquery(
                    "SELECT * FROM {{table}} WHERE `id_owner` = {$CurrentUser['id']} AND `planet_type` = 1 AND `id` != {$CurrentPlanet['id']};",
                    'planets'
                );

                if($SQLResult_SelectOtherPlanets->num_rows > 0)
                {
                    $CopySetPercents = $SetPercents;
                    $SetPercents = null;

                    $Results['planets'] = array();
                    while($PlanetData = $SQLResult_SelectOtherPlanets->fetch_assoc())
                    {
                        if(HandlePlanetUpdate($PlanetData, $CurrentUser, $Now, true) === true)
                        {
                            $Results['planets'][] = $PlanetData;
                        }

                        $UpdatePercentIDs[] = $PlanetData['id'];
                    }
                    HandlePlanetUpdate_MultiUpdate($Results, $CurrentUser);

                    $SetPercents = $CopySetPercents;
                }
                else
                {
                    $Post_SetUsage2All = false;
                    $_POST['setUsage2All'] = 'off';
                }
            }
            $QryUpdatePlanet = "UPDATE {{table}} SET ";
            $QryUpdatePlanet .= implode(', ', $UpdatePlanet);
            $QryUpdatePlanet .= " WHERE ";
            if($Post_SetUsage2All)
            {
                $SetCode = 2;
            }
            else
            {
                $SetCode = 1;
            }
            $QryUpdatePlanet .= "`id` IN (".implode(', ', $UpdatePercentIDs).");";
            doquery($QryUpdatePlanet, 'planets');

            $UserDev_Log[] = array('PlanetID' => $CurrentPlanet['id'], 'Date' => $Now, 'Place' => 22, 'Code' => $SetCode, 'ElementID' => '0', 'AdditionalData' => implode(';', $InsertDevLog));
        }
    }

    $parse = $_Lang;

    // -------------------------------------------------------------------------------------------------------
    // Calculate Storage
    $CurrentPlanet['metal_max'] = floor(BASE_STORAGE_SIZE * pow (1.7, $CurrentPlanet[$_Vars_GameElements[22]]));
    $CurrentPlanet['crystal_max'] = floor(BASE_STORAGE_SIZE * pow (1.7, $CurrentPlanet[$_Vars_GameElements[23]]));
    $CurrentPlanet['deuterium_max'] = floor(BASE_STORAGE_SIZE * pow (1.7, $CurrentPlanet[$_Vars_GameElements[24]]));

    // -------------------------------------------------------------------------------------------------------
    $parse['resource_row'] = '';
    $CurrentPlanet['metal_perhour'] = 0;
    $CurrentPlanet['crystal_perhour'] = 0;
    $CurrentPlanet['deuterium_perhour'] = 0;
    $CurrentPlanet['energy_max'] = 0;
    $CurrentPlanet['energy_used'] = 0;
    $BuildTemp = $CurrentPlanet['temp_max'];
    $Loop = 0;
    foreach($_Vars_ElementCategories['prod'] as $ProdID)
    {
        if(isset($_Vars_ResProduction[$ProdID]))
        {
            if($CurrentPlanet[$_Vars_GameElements[$ProdID]] <= 0)
            {
                $CurrRow[$Loop]['zero_level'] = ' class="red"';
            }

            $BuildLevelFactor = $CurrentPlanet[$_Vars_GameElements[$ProdID].'_workpercent'];
            $BuildLevel = $CurrentPlanet[$_Vars_GameElements[$ProdID]];

            $metal = abs(floor( eval ( $_Vars_ResProduction[$ProdID]['formule']['metal'] ) * ( $_GameConfig['resource_multiplier'] ) * (($CurrentUser['geologist_time'] > $Now) ? 1.15 : 1) ) );
            $crystal = abs(floor( eval ( $_Vars_ResProduction[$ProdID]['formule']['crystal'] ) * ( $_GameConfig['resource_multiplier'] ) * (($CurrentUser['geologist_time'] > $Now) ? 1.15 : 1) ) );
            if($ProdID != 12)
            {
                $deuterium = floor( eval ( $_Vars_ResProduction[$ProdID]['formule']['deuterium'] ) * ( $_GameConfig['resource_multiplier'] ) * (($CurrentUser['geologist_time'] > $Now) ? 1.15 : 1) );
            }
            else
            {
                $deuterium = floor( eval ( $_Vars_ResProduction[$ProdID]['formule']['deuterium'] ) * ( $_GameConfig['resource_multiplier'] ) );
            }

            // Calculate Energy
            if($ProdID < 4)
            {
                $energy = floor(eval($_Vars_ResProduction[$ProdID]['formule']['energy']));
            }
            else
            {
                if($ProdID != 12)
                {
                    $energy = floor(eval($_Vars_ResProduction[$ProdID]['formule']['energy']) * (($CurrentUser['engineer_time'] > $Now) ? 1.10 : 1));
                }
                else
                {
                    $MineDeuteriumUse = floor(eval($_Vars_ResProduction[$ProdID]['formule']['deuterium']) * ($_GameConfig['resource_multiplier'])) * (-1);
                    if($MineDeuteriumUse > 0)
                    {
                        if($CurrentPlanet['deuterium'] <= 0)
                        {
                            if(($CurrentPlanet['deuterium_perhour'] + $deuterium) == ($MineDeuteriumUse * (-1)))
                            {
                                // If no enough + production of deuterium
                                $energy = 0;
                            }
                            else
                            {
                                // If there is still some deuterium in + production to use
                                $FusionReactorMulti = $CurrentPlanet['deuterium_perhour'] / $MineDeuteriumUse;
                                if($FusionReactorMulti > 1)
                                {
                                    $FusionReactorMulti = 1;
                                }
                                $energy = floor(eval($_Vars_ResProduction[$ProdID]['formule']['energy']) * $FusionReactorMulti * (($CurrentUser['engineer_time'] > $Now) ? 1.10 : 1));
                            }
                        }
                        else
                        {
                            $FusionReactorMulti = $CurrentPlanet['deuterium'] / ($MineDeuteriumUse / 3600);
                            if($FusionReactorMulti > 1)
                            {
                                $FusionReactorMulti = 1;
                            }

                            $energy = floor(eval($_Vars_ResProduction[$ProdID]['formule']['energy']) * $FusionReactorMulti * (($CurrentUser['engineer_time'] > $Now) ? 1.10 : 1));
                        }
                    }
                    else
                    {
                        $energy = 0;
                    }
                }
            }

            if($energy > 0)
            {
                $CurrentPlanet['energy_max']+= $energy;
            }
            else
            {
                $CurrentPlanet['energy_used'] += $energy;
            }
            $CurrentPlanet['metal_perhour'] += $metal;
            $CurrentPlanet['crystal_perhour'] += $crystal;
            $CurrentPlanet['deuterium_perhour'] += $deuterium;

            $Field = $_Vars_GameElements[$ProdID]."_workpercent";
            $CurrRow[$Loop]['name'] = $_Vars_GameElements[$ProdID];
            $CurrRow[$Loop]['workpercent'] = $CurrentPlanet[$Field];
            $CurrRow[$Loop]['option'] = '';
            for($Option = 10; $Option >= 0; $Option -= 1)
            {
                $OptValue = $Option * 10;
                if($Option == $CurrRow[$Loop]['workpercent'])
                {
                    $OptSelected = ' selected=selected';
                }
                else
                {
                    $OptSelected = '';
                }
                $CurrRow[$Loop]['option'] .= "<option value=\"{$OptValue}\"{$OptSelected}>{$OptValue}%</option>";
            }
            $CurrRow[$Loop]['ID'] = $ProdID;
            $CurrRow[$Loop]['type'] = $_Lang['tech'][$ProdID];
            $CurrRow[$Loop]['level'] = ($ProdID > 200) ? $_Lang['quantity'] : $_Lang['level'];
            $CurrRow[$Loop]['level_type'] = $CurrentPlanet[ $_Vars_GameElements[$ProdID] ];
            if($ProdID > 200)
            {
                $CurrRow[$Loop]['level_type'] = prettyNumber($CurrRow[$Loop]['level_type']);
            }
            $CurrRow[$Loop]['metal_type'] = $metal;
            $CurrRow[$Loop]['crystal_type'] = $crystal;
            $CurrRow[$Loop]['deuterium_type'] = $deuterium;
            $CurrRow[$Loop]['energy_type'] = $energy;

            $Loop += 1;
        }
    }

    $parse['Production_of_resources_in_the_planet'] = str_replace('%s', (($CurrentPlanet['planet_type'] == 1) ? $_Lang['on_planet'] : $_Lang['on_moon']).' '.$CurrentPlanet['name'], $_Lang['Production_of_resources_in_the_planet']);
    if($CurrentPlanet['energy_max'] == 0 AND abs($CurrentPlanet['energy_used']) > 0)
    {
        $parse['production_level'] = 0;
    }
    else if($CurrentPlanet['energy_max'] > 0 AND abs($CurrentPlanet['energy_used']) > $CurrentPlanet['energy_max'])
    {
        $parse['production_level'] = floor(($CurrentPlanet['energy_max'] * 100) / abs($CurrentPlanet['energy_used']));
    }
    else
    {
        $parse['production_level'] = 100;
    }
    if($parse['production_level'] > 100)
    {
        $parse['production_level'] = 100;
    }

    if(!empty($CurrRow))
    {
        foreach($CurrRow as $val)
        {
            if($val['ID'] < 4)
            {
                $val['metal_type'] = prettyColorNumber($val['metal_type'] * 0.01 * $parse['production_level']);
                $val['crystal_type'] = prettyColorNumber($val['crystal_type'] * 0.01 * $parse['production_level']);
                $val['deuterium_type'] = prettyColorNumber($val['deuterium_type'] * 0.01 * $parse['production_level']);
            }
            else
            {
                $val['metal_type'] = prettyColorNumber($val['metal_type']);
                $val['crystal_type'] = prettyColorNumber($val['crystal_type']);
                $val['deuterium_type'] = prettyColorNumber($val['deuterium_type']);
            }
            $val['energy_type'] = prettyColorNumber($val['energy_type'], true);
            $parse['resource_row'] .= parsetemplate ( $RessRowTPL, $val );
        }
    }

    $parse['metal_basic_income'] = $_GameConfig['metal_basic_income'] * $_GameConfig['resource_multiplier'];
    $parse['crystal_basic_income'] = $_GameConfig['crystal_basic_income'] * $_GameConfig['resource_multiplier'];
    $parse['deuterium_basic_income'] = $_GameConfig['deuterium_basic_income'] * $_GameConfig['resource_multiplier'];
    $parse['energy_basic_income'] = $_GameConfig['energy_basic_income'];

    if($CurrentPlanet['metal_max'] < $CurrentPlanet['metal'])
    {
        $parse['metal_max'] = '<span class="red">';
    }
    else
    {
        $parse['metal_max'] = '<span class="lime">';
    }
    $parse['metal_max'] .= prettyNumber($CurrentPlanet['metal_max'] / 1000) ." {$_Lang['k']}</span>";

    if($CurrentPlanet['crystal_max'] < $CurrentPlanet['crystal'])
    {
        $parse['crystal_max'] = '<span class="red">';
    }
    else
    {
        $parse['crystal_max'] = '<span class="lime">';
    }
    $parse['crystal_max'] .= prettyNumber($CurrentPlanet['crystal_max'] / 1000) ." {$_Lang['k']}</span>";

    if($CurrentPlanet['deuterium_max'] < $CurrentPlanet['deuterium'])
    {
        $parse['deuterium_max'] = '<span class="red">';
    }
    else
    {
        $parse['deuterium_max'] = '<span class="lime">';
    }
    $parse['deuterium_max'] .= prettyNumber($CurrentPlanet['deuterium_max'] / 1000) ." {$_Lang['k']}</span>";

    $parse['metal_total'] = prettyColorNumber(floor(($CurrentPlanet['metal_perhour'] * 0.01 * $parse['production_level']) + ($_GameConfig['metal_basic_income'] * $_GameConfig['resource_multiplier'])));
    $parse['crystal_total'] = prettyColorNumber(floor(($CurrentPlanet['crystal_perhour'] * 0.01 * $parse['production_level']) + ($_GameConfig['crystal_basic_income'] * $_GameConfig['resource_multiplier'])));
    $parse['deuterium_total'] = prettyColorNumber(floor(($CurrentPlanet['deuterium_perhour'] * 0.01 * $parse['production_level']) + ($_GameConfig['deuterium_basic_income'] * $_GameConfig['resource_multiplier'])));
    $parse['energy_total'] = prettyColorNumber(floor(($CurrentPlanet['energy_max'] + $_GameConfig['energy_basic_income']) + $CurrentPlanet['energy_used']));

    $parse['daily_metal'] = floor(($CurrentPlanet['metal_perhour'] * 24* 0.01 * $parse['production_level']) + ($parse['metal_basic_income'] * 24));
    $parse['weekly_metal'] = floor(($CurrentPlanet['metal_perhour'] * 24 * 7* 0.01 * $parse['production_level']) + ($parse['metal_basic_income'] * 24 * 7));
    $parse['monthly_metal'] = floor(($CurrentPlanet['metal_perhour'] * 24 * 30 * 0.01 * $parse['production_level']) + ($parse['metal_basic_income'] * 24 * 30 ));

    $parse['daily_crystal'] = floor(($CurrentPlanet['crystal_perhour'] * 24* 0.01 * $parse['production_level']) + ($parse['crystal_basic_income'] * 24));
    $parse['weekly_crystal'] = floor(($CurrentPlanet['crystal_perhour'] * 24 * 7* 0.01 * $parse['production_level']) + ($parse['crystal_basic_income'] * 24 * 7));
    $parse['monthly_crystal'] = floor(($CurrentPlanet['crystal_perhour'] * 24 * 30 * 0.01 * $parse['production_level']) + ($parse['crystal_basic_income'] * 24 * 30 ));

    $parse['daily_deuterium'] = floor(($CurrentPlanet['deuterium_perhour'] * 24* 0.01 * $parse['production_level']) + ($parse['deuterium_basic_income'] * 24));
    $parse['weekly_deuterium'] = floor(($CurrentPlanet['deuterium_perhour'] * 24 * 7* 0.01 * $parse['production_level']) + ($parse['deuterium_basic_income'] * 24 * 7));
    $parse['monthly_deuterium'] = floor(($CurrentPlanet['deuterium_perhour'] * 24 * 30 * 0.01 * $parse['production_level']) + ($parse['deuterium_basic_income'] * 24 * 30 ));

    $parse['daily_metal'] = prettyColorNumber($parse['daily_metal']);
    $parse['weekly_metal'] = prettyColorNumber($parse['weekly_metal']);
    $parse['monthly_metal'] = prettyColorNumber($parse['monthly_metal']);

    $parse['daily_crystal'] = prettyColorNumber($parse['daily_crystal']);
    $parse['weekly_crystal'] = prettyColorNumber($parse['weekly_crystal']);
    $parse['monthly_crystal'] = prettyColorNumber($parse['monthly_crystal']);

    $parse['daily_deuterium'] = prettyColorNumber($parse['daily_deuterium']);
    $parse['weekly_deuterium'] = prettyColorNumber($parse['weekly_deuterium']);
    $parse['monthly_deuterium'] = prettyColorNumber($parse['monthly_deuterium']);

    $parse['metal_storage'] = prettyNumber(floor($CurrentPlanet['metal'] / $CurrentPlanet['metal_max'] * 100)) . $_Lang['o/o'];
    $parse['crystal_storage'] = prettyNumber(floor($CurrentPlanet['crystal'] / $CurrentPlanet['crystal_max'] * 100)) . $_Lang['o/o'];
    $parse['deuterium_storage'] = prettyNumber(floor($CurrentPlanet['deuterium'] / $CurrentPlanet['deuterium_max'] * 100)) . $_Lang['o/o'];
    $parse['metal_storage_bar'] = floor(($CurrentPlanet['metal'] / $CurrentPlanet['metal_max'] * 100) * 2.5);
    $parse['crystal_storage_bar'] = floor(($CurrentPlanet['crystal'] / $CurrentPlanet['crystal_max'] * 100) * 2.5);
    $parse['deuterium_storage_bar'] = floor(($CurrentPlanet['deuterium'] / $CurrentPlanet['deuterium_max'] * 100) * 2.5);

    if($parse['metal_storage_bar'] > (100 * 2.5))
    {
        $parse['metal_storage_bar'] = 250;
        $parse['metal_storage_barcolor'] = 'red';
    }
    else if($parse['metal_storage_bar'] > (80 * 2.5))
    {
        $parse['metal_storage_barcolor'] = 'orange';
    }
    else
    {
        $parse['metal_storage_barcolor'] = 'lime';
    }

    if($parse['crystal_storage_bar'] > (100 * 2.5))
    {
        $parse['crystal_storage_bar'] = 250;
        $parse['crystal_storage_barcolor'] = 'red';
    }
    else if($parse['crystal_storage_bar'] > (80 * 2.5))
    {
        $parse['crystal_storage_barcolor'] = 'orange';
    }
    else
    {
        $parse['crystal_storage_barcolor'] = 'lime';
    }

    if($parse['deuterium_storage_bar'] > (100 * 2.5))
    {
        $parse['deuterium_storage_bar'] = 250;
        $parse['deuterium_storage_barcolor'] = 'red';
    }
    else if($parse['deuterium_storage_bar'] > (80 * 2.5))
    {
        $parse['deuterium_storage_barcolor'] = 'orange';
    }
    else
    {
        $parse['deuterium_storage_barcolor'] = 'lime';
    }

    if(isOnVacation($CurrentUser))
    {
        $parse['production_level'] = 0;
    }

    if($parse['production_level'] > 50)
    {
        $parse['production_level_barcolor'] = 'lime';
    }
    else if($parse['production_level'] > 25)
    {
        $parse['production_level_barcolor'] = 'orange';
    }
    else
    {
        $parse['production_level_barcolor'] = 'red';
    }

    $parse['production_level_bar'] = $parse['production_level'] * 2.5;
    $parse['production_level'] = $parse['production_level'].'%';
    if(isOnVacation($CurrentUser))
    {
        $parse['production_level'] .= '<br/>'.$_Lang['VacationMode'];
    }

    //-----------------------------------------
    $NeedenSmallCargo = ceil(($CurrentPlanet['metal'] + $CurrentPlanet['crystal'] + $CurrentPlanet['deuterium']) / $_Vars_Prices[202]['capacity']);
    $NeedenBigCargo = ceil(($CurrentPlanet['metal'] + $CurrentPlanet['crystal'] + $CurrentPlanet['deuterium']) / $_Vars_Prices[203]['capacity']);
    $NeedenMegaCargo = ceil(($CurrentPlanet['metal'] + $CurrentPlanet['crystal'] + $CurrentPlanet['deuterium']) / $_Vars_Prices[217]['capacity']);
    // Calculate how many is missing
    $MissingSmallCargo = $NeedenSmallCargo - $CurrentPlanet['small_cargo_ship'];
    $MissingBigCargo = $NeedenBigCargo - $CurrentPlanet['big_cargo_ship'];
    $MissingMegaCargo = $NeedenMegaCargo - $CurrentPlanet['mega_cargo_ship'];
    // Show us pretty numbers
    $NoNeedMoreTransport = '<span class="lime">'.$_Lang['no_need_more_transporters'].'</span>';
    $parse['missing_s'] = ($MissingSmallCargo > 0) ? prettyNumber($MissingSmallCargo) : $NoNeedMoreTransport;
    $parse['missing_b'] = ($MissingBigCargo > 0) ? prettyNumber($MissingBigCargo) : $NoNeedMoreTransport;
    $parse['missing_m'] = ($MissingMegaCargo > 0) ? prettyNumber($MissingMegaCargo) : $NoNeedMoreTransport;

    $parse['have_s'] = prettyNumber($CurrentPlanet['small_cargo_ship']);
    $parse['have_b'] = prettyNumber($CurrentPlanet['big_cargo_ship']);
    $parse['have_m'] = prettyNumber($CurrentPlanet['mega_cargo_ship']);

    $parse['planet_type_res'] = ($CurrentPlanet['planet_type'] == 1) ? $_Lang['from_planet'] : $_Lang['from_moon'];
    $parse['nazwa'] = $CurrentPlanet['name'];
    $parse['trans_s'] = prettyNumber($NeedenSmallCargo);
    $parse['trans_b'] = prettyNumber($NeedenBigCargo);
    $parse['trans_m'] = prettyNumber($NeedenMegaCargo);

    $parse['small_cargo_name'] = $_Lang['tech'][202];
    $parse['big_cargo_name'] = $_Lang['tech'][203];
    $parse['mega_cargo_name'] = $_Lang['tech'][217];

    $parse['metal_basic_income'] = prettyNumber($parse['metal_basic_income']);
    $parse['crystal_basic_income'] = prettyNumber($parse['crystal_basic_income']);
    $parse['deuterium_basic_income'] = prettyNumber($parse['deuterium_basic_income']);
    $parse['energy_basic_income']= prettyNumber($parse['energy_basic_income']);

    if($CurrentUser['geologist_time'] > $Now)
    {
        $parse['GeologistBonusPercent'] = '15';
    }
    else
    {
        $parse['GeologistBonusPercent'] = '0';
    }

    $page = parsetemplate($RessBodyTPL, $parse);

    return $page;
}

$Page = BuildRessourcePage($_User, $_Planet);
display($Page, $_Lang['Resources']);

?>
