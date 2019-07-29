<?php

function PlanetResourceUpdate($CurrentUser, &$CurrentPlanet, $UpdateTime, $Simul = false) {
    global $_Vars_GameElements, $_Vars_ElementCategories, $_DontShowMenus, $SetPercents;

    $NeedUpdate = false;

    if (!empty($SetPercents[$CurrentPlanet['id']])) {
        foreach ($SetPercents[$CurrentPlanet['id']] as $Key => $Value) {
            $CurrentPlanet[$Key] = $Value['old'];
        }
    }

    $ProductionTime = ($UpdateTime - $CurrentPlanet['last_update']);

    // Update place for resources
    $CurrentPlanet['metal_max'] = (floor(BASE_STORAGE_SIZE * pow(1.7, $CurrentPlanet[$_Vars_GameElements[22]])));
    $CurrentPlanet['crystal_max'] = (floor(BASE_STORAGE_SIZE * pow(1.7, $CurrentPlanet[$_Vars_GameElements[23]])));
    $CurrentPlanet['deuterium_max'] = (floor(BASE_STORAGE_SIZE * pow(1.7, $CurrentPlanet[$_Vars_GameElements[24]])));

    // Start ResourceUpdating
    if ($CurrentPlanet['planet_type'] == 1) {
        $planetProduction = [
            'metal_perhour' => 0,
            'crystal_perhour' => 0,
            'deuterium_perhour' => 0,
            'energy_max' => 0,
            'energy_used' => 0
        ];

        foreach ($_Vars_ElementCategories['prod'] as $elementID) {
            $elementProduction = getElementProduction(
                $elementID,
                $CurrentPlanet,
                $CurrentUser,
                [
                    'isBoosted' => true,
                    'timerange' => [
                        'start' => $CurrentPlanet['last_update'],
                        'end' => $UpdateTime
                    ]
                ]
            );

            $planetProduction['metal_perhour'] += $elementProduction['metal'];
            $planetProduction['crystal_perhour'] += $elementProduction['crystal'];
            $planetProduction['deuterium_perhour'] += $elementProduction['deuterium'];

            if ($elementProduction['energy'] > 0) {
                $planetProduction['energy_max'] += $elementProduction['energy'];
            } else {
                $planetProduction['energy_used'] += $elementProduction['energy'];
            }
        }

        // Set current IncomeLevels
        // FIXME: check if these values should not already contain production levels applied
        $CurrentPlanet['metal_perhour'] = $planetProduction['metal_perhour'];
        $CurrentPlanet['crystal_perhour'] = $planetProduction['crystal_perhour'];
        $CurrentPlanet['deuterium_perhour'] = $planetProduction['deuterium_perhour'];
        $CurrentPlanet['energy_used'] = $planetProduction['energy_used'];
        $CurrentPlanet['energy_max'] = $planetProduction['energy_max'];

        $production_level = 0;

        if ($ProductionTime > 0) {
            // Calculate ProductionLevel
            if (!isOnVacation($CurrentUser)) {
                $energyAvailable = $planetProduction['energy_max'];
                $energyUsedAbs = abs($planetProduction['energy_used']);

                if ($energyUsedAbs == 0) {
                    $production_level = 100;
                } else if ($energyAvailable >= $energyUsedAbs) {
                    $production_level = 100;
                } else if ($energyAvailable == 0) {
                    $production_level = 0;
                } else {
                    $production_level = floor(
                        ($energyAvailable / $energyUsedAbs) *
                        100
                    );
                }
            } else {
                $production_level = 0;
            }

            $income = [
                'metal' => _calculateFinalResourceAmount(
                    'metal',
                    $CurrentPlanet,
                    [
                        'productionTime' => $ProductionTime,
                        'productionLevel' => $production_level
                    ]
                ),
                'crystal' => _calculateFinalResourceAmount(
                    'crystal',
                    $CurrentPlanet,
                    [
                        'productionTime' => $ProductionTime,
                        'productionLevel' => $production_level
                    ]
                ),
                'deuterium' => _calculateFinalResourceAmount(
                    'deuterium',
                    $CurrentPlanet,
                    [
                        'productionTime' => $ProductionTime,
                        'productionLevel' => $production_level
                    ]
                )
            ];

            foreach ($income as $resourceKey => $resourceIncomeResult) {
                $CurrentPlanet[$resourceKey] += $resourceIncomeResult['income'];
            }

            $NeedUpdate = true;
        }
    }

    // End of ResourceUpdate
    $CurrentPlanet['last_update'] = $UpdateTime;

    if ($Simul === false) {
        // Management of eventual shipyard Queue
        $Builded = HandleShipyardQueue($CurrentUser, $CurrentPlanet, $ProductionTime, $UpdateTime);

        // Update planet
        $QryUpdatePlanet = '';
        $QryUpdatePlanet .= "UPDATE {{table}} SET ";
        $QryUpdatePlanet .= "`metal` = '{$CurrentPlanet['metal']}', ";
        $QryUpdatePlanet .= "`crystal` = '{$CurrentPlanet['crystal']}', ";
        $QryUpdatePlanet .= "`deuterium` = '{$CurrentPlanet['deuterium']}', ";
        $QryUpdatePlanet .= "`last_update` = '{$CurrentPlanet['last_update']}', ";
        $QryUpdatePlanet .= "`shipyardQueue` = '{$CurrentPlanet['shipyardQueue']}', ";
        $QryUpdatePlanet .= "`metal_perhour` = '{$CurrentPlanet['metal_perhour']}', ";
        $QryUpdatePlanet .= "`crystal_perhour` = '{$CurrentPlanet['crystal_perhour']}', ";
        $QryUpdatePlanet .= "`deuterium_perhour` = '{$CurrentPlanet['deuterium_perhour']}', ";
        $QryUpdatePlanet .= "`energy_used` = '{$CurrentPlanet['energy_used']}', ";
        $QryUpdatePlanet .= "`energy_max` = '{$CurrentPlanet['energy_max']}', ";

        // Check if something has been built in Shipyard
        if (!empty($Builded)) {
            $NeedUpdate = true;
            foreach ($Builded as $Element => $Count) {
                if (!empty($_Vars_GameElements[$Element])) {
                    $QryUpdatePlanet .= "`{$_Vars_GameElements[$Element]}` = `{$_Vars_GameElements[$Element]}` + {$Count}, ";
                }
            }
        }
        $QryUpdatePlanet .= "`shipyardQueue_additionalWorkTime` = '{$CurrentPlanet['shipyardQueue_additionalWorkTime']}' ";
        $QryUpdatePlanet .= "WHERE ";
        $QryUpdatePlanet .= "`id` = {$CurrentPlanet['id']};";

        doquery('LOCK TABLE {{table}} WRITE, {{prefix}}errors WRITE', 'planets');

        $Last_DontShowMenus = $_DontShowMenus;
        $_DontShowMenus = true;

        doquery($QryUpdatePlanet, 'planets');
        doquery('UNLOCK TABLES', '');

        $_DontShowMenus = $Last_DontShowMenus;
    }

    if (
        !empty($SetPercents[$CurrentPlanet['id']]) &&
        $CurrentPlanet['planet_type'] == 1
    ) {
        _recalculateHourlyProductionLevels(
            $SetPercents[$CurrentPlanet['id']],
            $CurrentPlanet,
            $CurrentUser,
            [
                'start' => $CurrentPlanet['last_update'],
                'end' => $UpdateTime
            ]
        );
    }

    return $NeedUpdate;
}

//  Arguments
//      - $resourceKey (String)
//      - $planet (&Object)
//      - $params (Object)
//          - productionTime (Number)
//          - productionLevel (Number)
//
function _calculateFinalResourceAmount($resourceKey, &$planet, $params) {
    global $_GameConfig;

    $productionTime = $params['productionTime'];
    $productionLevel = $params['productionLevel'];

    $resourceCurrentAmount = $planet[$resourceKey];
    $resourceMaxStorage = ($planet["{$resourceKey}_max"] * MAX_OVERFLOW);
    $resourceIncomePerSecond = [
        'production' => ($planet["{$resourceKey}_perhour"] / 3600),
        'base' => (
            (
                $_GameConfig["{$resourceKey}_basic_income"] *
                $_GameConfig['resource_multiplier']
            ) /
            3600
        )
    ];

    if ($resourceCurrentAmount >= $resourceMaxStorage) {
        return [
            'isUpdated' => false,
            'income' => 0
        ];
    }

    $theoreticalIncome = [
        'production' => (
            $productionTime *
            $resourceIncomePerSecond['production'] *
            (0.01 * $productionLevel)
        ),
        'base' => (
            $productionTime *
            $resourceIncomePerSecond['base']
        )
    ];
    $totalTheoreticalIncome = $theoreticalIncome['production'] + $theoreticalIncome['base'];

    $theoreticalAmount = $resourceCurrentAmount + $totalTheoreticalIncome;

    if ($theoreticalAmount < 0) {
        $theoreticalAmount = 0;
    }

    $finalAmount = (
        $theoreticalAmount < $resourceMaxStorage ?
        $theoreticalAmount :
        $resourceMaxStorage
    );
    $finalIncome = ($finalAmount - $resourceCurrentAmount);

    return [
        'isUpdated' => ($finalIncome != 0),
        'income' => $finalIncome
    ];
}

function _recalculateHourlyProductionLevels($changedProductionFactors, &$planet, &$user, $timerange) {
    global $_Vars_ElementCategories, $_Vars_GameElements;

    foreach($changedProductionFactors as $elementWorkpercentKey => $elementProductionFactors) {
        $elementKey = str_replace('_workpercent', '', $elementWorkpercentKey);

        $matchedElementID = null;

        foreach($_Vars_ElementCategories['prod'] as $elementID) {
            if ($_Vars_GameElements[$elementID] !== $elementKey) {
                continue;
            }

            $matchedElementID = $elementID;

            break;
        }

        if (!$matchedElementID) {
            continue;
        }

        _recalculateHourlyProductionLevelOf($matchedElementID, $elementProductionFactors, $planet, $user, $timerange);
    }
}

function _recalculateHourlyProductionLevelOf($elementID, $productionFactors, &$planet, &$user, $timerange) {
    $oldElementProduction = getElementProduction(
        $elementID,
        $planet,
        $user,
        [
            'isBoosted' => true,
            'timerange' => [
                'start' => $timerange['start'],
                'end' => $timerange['end']
            ],
            'customProductionFactor' => $productionFactors['old']
        ]
    );

    $oldPlanetProduction = [
        'metal_perhour' => 0,
        'crystal_perhour' => 0,
        'deuterium_perhour' => 0,
        'energy_max' => 0,
        'energy_used' => 0
    ];

    $oldPlanetProduction['metal_perhour'] += $oldElementProduction['metal'];
    $oldPlanetProduction['crystal_perhour'] += $oldElementProduction['crystal'];
    $oldPlanetProduction['deuterium_perhour'] += $oldElementProduction['deuterium'];

    if ($oldElementProduction['energy'] > 0) {
        $oldPlanetProduction['energy_max'] += $oldElementProduction['energy'];
    } else {
        $oldPlanetProduction['energy_used'] += $oldElementProduction['energy'];
    }

    $newElementProduction = getElementProduction(
        $elementID,
        $planet,
        $user,
        [
            'isBoosted' => true,
            'timerange' => [
                'start' => $timerange['end'],
                'end' => $timerange['end']
            ],
            'customProductionFactor' => $productionFactors['new']
        ]
    );

    $newPlanetProduction = [
        'metal_perhour' => 0,
        'crystal_perhour' => 0,
        'deuterium_perhour' => 0,
        'energy_max' => 0,
        'energy_used' => 0
    ];

    $newPlanetProduction['metal_perhour'] += $newElementProduction['metal'];
    $newPlanetProduction['crystal_perhour'] += $newElementProduction['crystal'];
    $newPlanetProduction['deuterium_perhour'] += $newElementProduction['deuterium'];

    if ($newElementProduction['energy'] > 0) {
        $newPlanetProduction['energy_max'] += $newElementProduction['energy'];
    } else {
        $newPlanetProduction['energy_used'] += $newElementProduction['energy'];
    }

    foreach ($newPlanetProduction as $resourceKey => $resourceProduction) {
        $diff = $resourceProduction - $oldPlanetProduction[$resourceKey];

        $planet[$resourceKey] += $diff;
    }
}

?>
