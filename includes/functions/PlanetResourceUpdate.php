<?php

function PlanetResourceUpdate($CurrentUser, &$CurrentPlanet, $UpdateTime, $Simul = false) {
    global $_Vars_GameElements, $_DontShowMenus, $SetPercents;

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
        $hasAnyIncome = _calculateAndApplyPlanetResourcesIncome(
            $CurrentPlanet,
            $CurrentUser,
            [
                'start' => $CurrentPlanet['last_update'],
                'end' => $UpdateTime
            ]
        );

        $NeedUpdate = $hasAnyIncome;
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

function _calculateAndApplyPlanetResourcesIncome(&$planet, &$user, $timerange) {
    global $_Vars_ElementCategories;

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
            $planet,
            $user,
            [
                'isBoosted' => true,
                'timerange' => [
                    'start' => $timerange['start'],
                    'end' => $timerange['end']
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
    $planet['metal_perhour'] = $planetProduction['metal_perhour'];
    $planet['crystal_perhour'] = $planetProduction['crystal_perhour'];
    $planet['deuterium_perhour'] = $planetProduction['deuterium_perhour'];
    $planet['energy_used'] = $planetProduction['energy_used'];
    $planet['energy_max'] = $planetProduction['energy_max'];

    $productionTime = ($timerange['end'] - $timerange['start']);
    $productionLevel = 0;

    if ($productionTime <= 0) {
        return false;
    }

    // Calculate ProductionLevel
    if (!isOnVacation($user)) {
        $energyAvailable = $planetProduction['energy_max'];
        $energyUsedAbs = abs($planetProduction['energy_used']);

        if ($energyUsedAbs == 0) {
            $productionLevel = 100;
        } else if ($energyAvailable >= $energyUsedAbs) {
            $productionLevel = 100;
        } else if ($energyAvailable == 0) {
            $productionLevel = 0;
        } else {
            $productionLevel = floor(
                ($energyAvailable / $energyUsedAbs) *
                100
            );
        }
    } else {
        $productionLevel = 0;
    }

    $income = [
        'metal' => calculateRealResourceIncome(
            'metal',
            $planet,
            [
                'productionTime' => $productionTime,
                'productionLevel' => $productionLevel
            ]
        ),
        'crystal' => calculateRealResourceIncome(
            'crystal',
            $planet,
            [
                'productionTime' => $productionTime,
                'productionLevel' => $productionLevel
            ]
        ),
        'deuterium' => calculateRealResourceIncome(
            'deuterium',
            $planet,
            [
                'productionTime' => $productionTime,
                'productionLevel' => $productionLevel
            ]
        )
    ];

    foreach ($income as $resourceKey => $resourceIncomeResult) {
        $planet[$resourceKey] += $resourceIncomeResult['income'];
    }

    return true;
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
