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
           $_Vars_GameElements, $_Vars_ElementCategories;

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
    $totalCapacities = getPlanetTotalStorageCapacities($CurrentPlanet);

    foreach ($totalCapacities as $resourceKey => $resourceCapacity) {
        $CurrentPlanet["{$resourceKey}_max"] = $resourceCapacity;
    }

    // -------------------------------------------------------------------------------------------------------
    $parse['resource_row'] = '';
    $CurrentPlanet['metal_perhour'] = 0;
    $CurrentPlanet['crystal_perhour'] = 0;
    $CurrentPlanet['deuterium_perhour'] = 0;
    $CurrentPlanet['energy_max'] = 0;
    $CurrentPlanet['energy_used'] = 0;

    $Loop = 0;
    foreach($_Vars_ElementCategories['prod'] as $ProdID)
    {
        if (_getElementProductionFormula($ProdID) == null) {
            continue;
        }

        if($CurrentPlanet[$_Vars_GameElements[$ProdID]] <= 0)
        {
            $CurrRow[$Loop]['zero_level'] = ' class="red"';
        }

        $elementProduction = getElementProduction(
            $ProdID,
            $CurrentPlanet,
            $CurrentUser,
            [
                'useCurrentBoosters' => true,
                'currentTimestamp' => $Now,
            ]
        );

        $CurrentPlanet['metal_perhour'] += $elementProduction['metal'];
        $CurrentPlanet['crystal_perhour'] += $elementProduction['crystal'];
        $CurrentPlanet['deuterium_perhour'] += $elementProduction['deuterium'];

        if ($elementProduction['energy'] > 0) {
            $CurrentPlanet['energy_max'] += $elementProduction['energy'];
        } else {
            $CurrentPlanet['energy_used'] += $elementProduction['energy'];
        }

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
        $CurrRow[$Loop]['metal_type'] = $elementProduction['metal'];
        $CurrRow[$Loop]['crystal_type'] = $elementProduction['crystal'];
        $CurrRow[$Loop]['deuterium_type'] = $elementProduction['deuterium'];
        $CurrRow[$Loop]['energy_type'] = $elementProduction['energy'];

        $Loop += 1;
    }

    $parse['Production_of_resources_in_the_planet'] = str_replace(
        '%s',
        (
            (
                ($CurrentPlanet['planet_type'] == 1) ?
                $_Lang['on_planet'] :
                $_Lang['on_moon']
            ) .
            ' ' .
            $CurrentPlanet['name']
        ),
        $_Lang['Production_of_resources_in_the_planet']
    );

    $thisProductionEfficiency = getPlanetsProductionEfficiency(
        $CurrentPlanet,
        $CurrentUser,
        [
            'isVacationCheckEnabled' => false
        ]
    );

    $parse['production_level'] = $thisProductionEfficiency;

    if (isOnVacation($CurrentUser)) {
        $parse['production_level'] = 0;
    }

    if ($parse['production_level'] > 50) {
        $parse['production_level_barcolor'] = 'lime';
    } else if($parse['production_level'] > 25) {
        $parse['production_level_barcolor'] = 'orange';
    } else {
        $parse['production_level_barcolor'] = 'red';
    }

    $parse['production_level'] = $parse['production_level'] . '%';
    if (isOnVacation($CurrentUser)) {
        $parse['production_level'] .= '<br/>'.$_Lang['VacationMode'];
    }

    if ($CurrentUser['geologist_time'] > $Now) {
        $parse['GeologistBonusPercent'] = '15';
    } else {
        $parse['GeologistBonusPercent'] = '0';
    }


    if(!empty($CurrRow))
    {
        foreach($CurrRow as $val)
        {
            if($val['ID'] < 4)
            {
                $val['metal_type'] = prettyColorNumber($val['metal_type'] * 0.01 * $thisProductionEfficiency);
                $val['crystal_type'] = prettyColorNumber($val['crystal_type'] * 0.01 * $thisProductionEfficiency);
                $val['deuterium_type'] = prettyColorNumber($val['deuterium_type'] * 0.01 * $thisProductionEfficiency);
            }
            else
            {
                $val['metal_type'] = prettyColorNumber($val['metal_type']);
                $val['crystal_type'] = prettyColorNumber($val['crystal_type']);
                $val['deuterium_type'] = prettyColorNumber($val['deuterium_type']);
            }
            $val['energy_type'] = prettyColorNumber($val['energy_type'], true);
            $parse['resource_row'] .= parsetemplate($RessRowTPL, $val);
        }
    }

    foreach ([ 'metal', 'crystal', 'deuterium' ] as $resourceKey) {
        $resourceSummaryData = createResourceSummaryData(
            $resourceKey,
            $CurrentPlanet,
            [
                'productionLevel' => $thisProductionEfficiency
            ]
        );
        $resourceSummaryTplData = createResourceSummaryTplData(
            $resourceKey,
            $resourceSummaryData
        );

        $parse = array_merge($parse, $resourceSummaryTplData);
    }

    $parse['energy_total'] = prettyColorNumber(floor(($CurrentPlanet['energy_max'] + $_GameConfig['energy_basic_income']) + $CurrentPlanet['energy_used']));

    foreach ([ 202, 203, 217 ] as $shipID) {
        $shipCargoHelperTplData = createShipsCargoHelperTplData(
            $shipID,
            $CurrentPlanet
        );

        $parse = array_merge($parse, $shipCargoHelperTplData);
    }

    $parse['PlanetData_type_langfrom'] = (
        ($CurrentPlanet['planet_type'] == 1) ?
        $_Lang['from_planet'] :
        $_Lang['from_moon']
    );
    $parse['PlanetData_name'] = $CurrentPlanet['name'];

    $parse['metal_basic_income'] = $_GameConfig['metal_basic_income'] * $_GameConfig['resource_multiplier'];
    $parse['crystal_basic_income'] = $_GameConfig['crystal_basic_income'] * $_GameConfig['resource_multiplier'];
    $parse['deuterium_basic_income'] = $_GameConfig['deuterium_basic_income'] * $_GameConfig['resource_multiplier'];
    $parse['energy_basic_income'] = $_GameConfig['energy_basic_income'];

    $parse['metal_basic_income'] = prettyNumber($parse['metal_basic_income']);
    $parse['crystal_basic_income'] = prettyNumber($parse['crystal_basic_income']);
    $parse['deuterium_basic_income'] = prettyNumber($parse['deuterium_basic_income']);
    $parse['energy_basic_income']= prettyNumber($parse['energy_basic_income']);

    $page = parsetemplate($RessBodyTPL, $parse);

    return $page;
}

//  Arguments:
//      - $resourceKey (String)
//      - $planet (&Object)
//      - $params (Object)
//          - productionLevel (Number)
//
function createResourceSummaryData ($resourceKey, &$planet, $params) {
    $productionLevel = $params['productionLevel'];

    $summaryData = [
        'maxCapacity' => [
            'value' => null,
            'isOverflowing' => null
        ],
        'totalIncome' => [
            'perHour' => null,
            'perDay' => null,
            'perWeek' => null,
            'perMonth' => null
        ],
        'storageLoad' => [
            'percent' => null
        ]
    ];

    $resourceCurrentAmount = $planet[$resourceKey];
    $storageMaxCapacity = $planet["{$resourceKey}_max"];

    $summaryData['maxCapacity']['value'] = $storageMaxCapacity;
    $summaryData['maxCapacity']['isOverflowing'] = ($storageMaxCapacity >= $resourceCurrentAmount);

    $theoreticalResourceIncomePerSecond = calculateTotalTheoreticalResourceIncomePerSecond(
        $resourceKey,
        $planet
    );
    $realResourceIncomePerSecond = calculateTotalRealResourceIncomePerSecond([
        'theoreticalIncomePerSecond' => $theoreticalResourceIncomePerSecond,
        'productionLevel' => $productionLevel
    ]);
    $totalResourceIncomePerSecond = array_sum($realResourceIncomePerSecond);

    $summaryData['totalIncome']['perHour'] = $totalResourceIncomePerSecond * TIME_HOUR;
    $summaryData['totalIncome']['perDay'] = $totalResourceIncomePerSecond * TIME_DAY;
    $summaryData['totalIncome']['perWeek'] = $totalResourceIncomePerSecond * TIME_DAY * 7;
    $summaryData['totalIncome']['perMonth'] = $totalResourceIncomePerSecond * TIME_DAY * 30;

    $storageLoadPercent = floor($resourceCurrentAmount / $storageMaxCapacity * 100);

    $summaryData['storageLoad']['percent'] = $storageLoadPercent;

    return $summaryData;
}

function createResourceSummaryTplData ($resourceKey, $summaryData) {
    global $_Lang;

    $summaryTplData = [
        'maxCapacity' => null,
        'totalIncome_perHour' => null,
        'totalIncome_perDay' => null,
        'totalIncome_perWeek' => null,
        'totalIncome_perMonth' => null,
        'storageLoad_percent' => null,
        'storageLoad_barWidthPx' => null,
        'storageLoad_barColor' => null,
    ];

    $summaryTplData['maxCapacity'] = (
        prettyNumber($summaryData['maxCapacity']['value'] / 1000) .
        " {$_Lang['k']}"
    );
    $summaryTplData['maxCapacity'] = (
        $summaryData['maxCapacity']['isOverflowing'] ?
        colorGreen($summaryTplData['maxCapacity']) :
        colorRed($summaryTplData['maxCapacity'])
    );

    $summaryTplData['totalIncome_perHour'] = prettyColorNumber($summaryData['totalIncome']['perHour']);
    $summaryTplData['totalIncome_perDay'] = prettyColorNumber($summaryData['totalIncome']['perDay']);
    $summaryTplData['totalIncome_perWeek'] = prettyColorNumber($summaryData['totalIncome']['perWeek']);
    $summaryTplData['totalIncome_perMonth'] = prettyColorNumber($summaryData['totalIncome']['perMonth']);

    $storageLoadPercent = $summaryData['storageLoad']['percent'];
    $storageLoadBarPixelsPerPercent = 250 / 100;
    $storageLoadBarColor = null;

    if ($storageLoadPercent >= 100) {
        $storageLoadBarColor = "red";
    } else if ($storageLoadPercent >= 80) {
        $storageLoadBarColor = "orange";
    } else {
        $storageLoadBarColor = "lime";
    }

    $summaryTplData['storageLoad_percent'] = $storageLoadPercent;
    $summaryTplData['storageLoad_barWidthPx'] = floor(
        min($storageLoadPercent, 100) *
        $storageLoadBarPixelsPerPercent
    );
    $summaryTplData['storageLoad_barColor'] = $storageLoadBarColor;

    $result = [];

    foreach ($summaryTplData as $entryKey => $entryValue) {
        $result["resourceSummary_{$resourceKey}_{$entryKey}"] = $entryValue;
    }

    return $result;
}

function createShipsCargoHelperTplData ($shipID, &$planet) {
    global $_Vars_GameElements, $_Vars_Prices, $_Lang;

    $shipCapacity = $_Vars_Prices[$shipID]['capacity'];
    $shipElementKey = $_Vars_GameElements[$shipID];

    $allResources = (
        $planet['metal'] +
        $planet['crystal'] +
        $planet['deuterium']
    );


    $requiredShipsCount = ceil($allResources / $shipCapacity);
    $availableShipsCount = $planet[$shipElementKey];

    $missingShipsCount = ($requiredShipsCount - $availableShipsCount);

    $summary = [
        'shipName' => $_Lang['tech'][$shipID],
        'requiredCount' => prettyNumber($requiredShipsCount),
        'availableCount' => prettyNumber($availableShipsCount),
        'missingCount' => (
            $missingShipsCount > 0 ?
            prettyNumber($missingShipsCount) :
            colorGreen($_Lang['no_need_more_transporters'])
        ),
    ];

    $result = [];

    foreach ($summary as $entryKey => $entryValue) {
        $result["cargohelper_{$shipID}_{$entryKey}"] = $entryValue;
    }

    return $result;
}

$Page = BuildRessourcePage($_User, $_Planet);
display($Page, $_Lang['Resources']);

?>
