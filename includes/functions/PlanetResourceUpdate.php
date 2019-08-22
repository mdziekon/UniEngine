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
    $totalCapacities = getPlanetTotalStorageCapacities($CurrentPlanet);

    foreach ($totalCapacities as $resourceKey => $resourceCapacity) {
        $CurrentPlanet["{$resourceKey}_max"] = $resourceCapacity;
    }

    // Start ResourceUpdating
    if ($CurrentPlanet['planet_type'] == 1) {
        $thisUpdateTimerange = [
            'start' => $CurrentPlanet['last_update'],
            'end' => $UpdateTime
        ];

        $geologistTimelineSubperiods = [];
        $engineerTimelineSubperiods = [];

        $geologistBoosterEndtime = _getBoosterEndtime('geologist', $CurrentUser);
        $engineerBoosterEndtime = _getBoosterEndtime('engineer', $CurrentUser);

        if ($geologistBoosterEndtime > $thisUpdateTimerange['start']) {
            $geologistTimelineSubperiods[] = [
                'start' => $thisUpdateTimerange['start'],
                'end' => (
                    $geologistBoosterEndtime > $thisUpdateTimerange['end'] ?
                    $thisUpdateTimerange['end'] :
                    $geologistBoosterEndtime
                ),
                'data' => [
                    'hasGeologist' => true
                ]
            ];
        }
        if ($engineerBoosterEndtime > $thisUpdateTimerange['start']) {
            $engineerTimelineSubperiods[] = [
                'start' => $thisUpdateTimerange['start'],
                'end' => (
                    $engineerBoosterEndtime > $thisUpdateTimerange['end'] ?
                    $thisUpdateTimerange['end'] :
                    $engineerBoosterEndtime
                ),
                'data' => [
                    'hasEngineer' => true
                ]
            ];
        }

        $timeranges = mergeTimelines([
            createTimeline(
                $thisUpdateTimerange,
                $geologistTimelineSubperiods
            ),
            createTimeline(
                $thisUpdateTimerange,
                $engineerTimelineSubperiods
            )
        ]);

        foreach ($timeranges as $timerange) {
            $income = calculateTotalResourcesIncome(
                $CurrentPlanet,
                $CurrentUser,
                $timerange,
                [
                    'isVacationCheckEnabled' => true
                ]
            );

            foreach ($income as $resourceKey => $resourceIncome) {
                $CurrentPlanet[$resourceKey] += $resourceIncome['income'];
            }

            $hasAnyIncome = !empty($income);

            $NeedUpdate = $NeedUpdate || $hasAnyIncome;
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
            'useCustomBoosters' => true,
            'boosters' => $timerange['data'],
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
            'useCustomBoosters' => true,
            'boosters' => $timerange['data'],
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
