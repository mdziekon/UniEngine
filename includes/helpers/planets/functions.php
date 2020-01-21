<?php

use UniEngine\Engine\Includes\Helpers\World\Elements;

//  Notes:
//      - The algorithm does not take into account that buildings which use
//        other resources as "production materials" (eg. Fusion Reactor uses Deuterium)
//        might be receiving less than needed of that materials or even
//        no production materials at all at given time.
//
//        However, in certain cases, such a thing would be nearly impossible to
//        calculate properly (eg. in case of the Fusion Reactor, the fuel income
//        would depend on the energy production, which in turn would affect...
//        the fuel income, meaning it would be a recursive dependency, impossible
//        to calculate in one iteration). It should also be noted that the same
//        behaviour is currently found in the original game, so even the original
//        creators are either not aware of the "problem", or they simply don't care.
//
//      - See "calculateRealResourceIncome" documentation (same assumptions apply).
//  Arguments:
//      - $elementID (Number)
//      - $planet (&Object)
//      - $user (&Object)
//      - $params (Object)
//          - useCurrentBoosters (Boolean) [default: false]
//              Should currently enabled ($user dependent) boosters be applied.
//          - useCustomBoosters (Boolean) [default: false]
//              Should custom boosters be applied.
//          - currentTimestamp (Number) [default: 0]
//              Timestamp to use when determining currently enabled boosters.
//              See "useCurrentBoosters".
//          - boosters (Object) [default: []]
//              - hasGeologist (Boolean) [default: false]
//              - hasEngineer (Boolean) [default: false]
//          - customLevel (Number) [default: null]
//          - customProductionFactor (Number) [default: null]
//
function getElementProduction($elementID, &$planet, &$user, $params) {
    global $_GameConfig;

    $production = [
        'metal' => 0,
        'crystal' => 0,
        'deuterium' => 0,
        'energy' => 0
    ];

    if (!isset($params['useCurrentBoosters'])) {
        $params['useCurrentBoosters'] = false;
    }
    if (!isset($params['useCustomBoosters'])) {
        $params['useCustomBoosters'] = false;
    }
    if (!isset($params['currentTimestamp'])) {
        $params['currentTimestamp'] = 0;
    }
    if (!isset($params['boosters'])) {
        $params['boosters'] = [];
    }
    if (!isset($params['boosters']['hasGeologist'])) {
        $params['boosters']['hasGeologist'] = false;
    }
    if (!isset($params['boosters']['hasEngineer'])) {
        $params['boosters']['hasEngineer'] = false;
    }
    if (!isset($params['customLevel'])) {
        $params['customLevel'] = null;
    }
    if (!isset($params['customProductionFactor'])) {
        $params['customProductionFactor'] = null;
    }

    $useCurrentBoosters = $params['useCurrentBoosters'];
    $useCustomBoosters = $params['useCustomBoosters'];

    $isBoosted = (
        $useCurrentBoosters ||
        $useCustomBoosters
    );
    $boosters = null;

    if ($isBoosted) {
        $boosters = (
            $useCustomBoosters ?
            $params['boosters'] :
            _getCurrentBoosters($user, $params['currentTimestamp'])
        );
    }

    $hasCustomLevel = ($params['customLevel'] !== null);
    $customLevel = $params['customLevel'];
    $hasCustomProductionFactor = ($params['customProductionFactor'] !== null);
    $customProductionFactor = $params['customProductionFactor'];

    if (
        !Elements\isStructure($elementID) &&
        !Elements\isShip($elementID)
    ) {
        return $production;
    }

    $productionFormula = _getElementProductionFormula($elementID);

    if (!is_callable($productionFormula)) {
        return $production;
    }

    $elementPlanetKey = _getElementPlanetKey($elementID);
    $elementParams = [
        'level' => (
            $hasCustomLevel ?
            $customLevel :
            $planet[$elementPlanetKey]
        ),
        'productionFactor' => (
            $hasCustomProductionFactor ?
            $customProductionFactor :
            _getElementPlanetProductionFactor($elementID, $planet)
        ),
        'planetTemp' => $planet['temp_max']
    ];

    $elementProduction = $productionFormula($elementParams);

    $boostersIncrease = [
        'geologist' => 0,
        'engineer' => 0
    ];

    if ($isBoosted) {
        if ($boosters['hasGeologist']) {
            $boostersIncrease['geologist'] = 0.15;
        }
        if ($boosters['hasEngineer']) {
            $boostersIncrease['engineer'] = 0.10;
        }
    }

    foreach ($elementProduction as $resourceKey => $resourceProduction) {
        if (_isResourceProductionSpeedMultiplicable($resourceKey)) {
            $resourceProduction *= $_GameConfig['resource_multiplier'];
        }

        if ($resourceProduction <= 0) {
            $production[$resourceKey] = $resourceProduction;

            continue;
        }

        if (_isResourceBoosterApplicable($resourceKey, 'geologist')) {
            $resourceProduction *= (1 + $boostersIncrease['geologist']);
        }

        if (_isResourceBoosterApplicable($resourceKey, 'engineer')) {
            $resourceProduction *= (1 + $boostersIncrease['engineer']);
        }

        $production[$resourceKey] = $resourceProduction;
    }

    foreach ($production as $resourceKey => $resourceProduction) {
        $production[$resourceKey] = floor($resourceProduction);
    }

    return $production;
}

function getElementProducedResourceKeys($elementID) {
    $theoreticalProduction = _getTheoreticalElementProduction($elementID);

    $producedResourceKeys = [];

    foreach ($theoreticalProduction as $resourceKey => $resourceProduction) {
        if ($resourceProduction <= 0) {
            continue;
        }

        $producedResourceKeys[] = $resourceKey;
    }

    return $producedResourceKeys;
}

function getElementConsumedResourceKeys($elementID) {
    $theoreticalProduction = _getTheoreticalElementProduction($elementID);

    $consumedResourceKeys = [];

    foreach ($theoreticalProduction as $resourceKey => $resourceProduction) {
        if ($resourceProduction >= 0) {
            continue;
        }

        $consumedResourceKeys[] = $resourceKey;
    }

    return $consumedResourceKeys;
}

function getElementStoredResourceKeys($elementID) {
    $theoreticalCapacities = _getTheoreticalElementCapacities($elementID);

    $storedResourceKeys = [];

    foreach ($theoreticalCapacities as $resourceKey => $resourceCapacity) {
        if ($resourceCapacity <= 0) {
            continue;
        }

        $storedResourceKeys[] = $resourceKey;
    }

    return $storedResourceKeys;
}

//  Arguments:
//      - $planetProduction (&Object)
//      - $user (&Object)
//      - $options (Object) [default: []]
//          - isVacationCheckEnabled (Boolean) [default: false]
//              Should perform vacation check when calculating production level,
//              based on current user's state.
//
function getPlanetsProductionEfficiency(&$planetProduction, &$user, $options) {
    $productionLevel = 0;

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

    if (
        $options['isVacationCheckEnabled'] &&
        isOnVacation($user)
    ) {
        $productionLevel = 0;
    }

    return $productionLevel;
}

//  Arguments:
//      - $elementID (Number)
//      - $planet (&Object)
//      - $params (Object)
//          - customLevel (Number) [default: null]
//
function getElementStorageCapacities($elementID, &$planet, $params) {
    global $_GameConfig;

    $capacities = [
        'metal' => 0,
        'crystal' => 0,
        'deuterium' => 0
    ];

    if (!isset($params['customLevel'])) {
        $params['customLevel'] = null;
    }

    $hasCustomLevel = ($params['customLevel'] !== null);
    $customLevel = $params['customLevel'];

    if (!Elements\isStorageStructure($elementID)) {
        return $capacities;
    }

    $capacityFormula = _getElementStorageCapacityFormula($elementID);

    if (!is_callable($capacityFormula)) {
        return $capacities;
    }

    $elementPlanetKey = _getElementPlanetKey($elementID);
    $elementParams = [
        'level' => (
            $hasCustomLevel ?
            $customLevel :
            $planet[$elementPlanetKey]
        )
    ];

    $elementCapacities = $capacityFormula($elementParams);

    foreach ($elementCapacities as $resourceKey => $resourceCapacity) {
        $capacities[$resourceKey] = $resourceCapacity;
    }

    foreach ($capacities as $resourceKey => $resourceCapacity) {
        $capacities[$resourceKey] = floor($resourceCapacity);
    }

    return $capacities;
}

function getPlanetTotalStorageCapacities(&$planet) {
    global $_Vars_ElementCategories;

    $totalCapacities = [
        'metal' => 0,
        'crystal' => 0,
        'deuterium' => 0
    ];

    foreach ($_Vars_ElementCategories['storages'] as $elementID) {
        $elementCapacities = getElementStorageCapacities($elementID, $planet, []);

        foreach ($totalCapacities as $resourceKey => $_resourceCapacity) {
            $totalCapacities[$resourceKey] += $elementCapacities[$resourceKey];
        }
    }

    return $totalCapacities;
}

function _getTheoreticalElementProduction($elementID) {
    if (
        !Elements\isStructure($elementID) &&
        !Elements\isShip($elementID)
    ) {
        return [];
    }

    $productionFormula = _getElementProductionFormula($elementID);

    if (!is_callable($productionFormula)) {
        return [];
    }

    $elementParams = [
        'level' => 1,
        'productionFactor' => 10,
        'planetTemp' => 100
    ];

    return $productionFormula($elementParams);
}

function _getTheoreticalElementCapacities($elementID) {
    if (!Elements\isStorageStructure($elementID)) {
        return [];
    }

    $capacityFormula = _getElementStorageCapacityFormula($elementID);

    if (!is_callable($capacityFormula)) {
        return [];
    }

    $elementParams = [
        'level' => 1
    ];

    return $capacityFormula($elementParams);
}

function _isResourceProductionSpeedMultiplicable($resourceKey) {
    $multiplicableResources = [
        'metal',
        'crystal',
        'deuterium'
    ];

    return in_array($resourceKey, $multiplicableResources);
}

function _isResourceBoosterApplicable($resourceKey, $boosterKey) {
    $applicabilityMatrix = [
        'geologist' => [
            'metal',
            'crystal',
            'deuterium'
        ],
        'engineer' => [
            'energy'
        ]
    ];

    $applicabilityTable = $applicabilityMatrix[$boosterKey];

    return in_array($resourceKey, $applicabilityTable);
}

function _getElementPlanetKey($elementID) {
    global $_Vars_GameElements;

    return $_Vars_GameElements[$elementID];
}

function _getElementUserKey($elementID) {
    global $_Vars_GameElements;

    return $_Vars_GameElements[$elementID];
}

function _getElementPlanetProductionFactor($elementID, &$planet) {
    $elementPlanetKey = _getElementPlanetKey($elementID);
    $productionFactorKey = $elementPlanetKey . '_workpercent';

    return $planet[$productionFactorKey];
}

function _getElementProductionFormula($elementID) {
    global $_Vars_ResProduction;

    if (!isset($_Vars_ResProduction[$elementID]['production'])) {
        return null;
    }

    return $_Vars_ResProduction[$elementID]['production'];
}

function _getElementStorageCapacityFormula($elementID) {
    global $_Vars_ResStorages;

    if (!isset($_Vars_ResStorages[$elementID]['capacity'])) {
        return null;
    }

    return $_Vars_ResStorages[$elementID]['capacity'];
}

function _getBoosterEndtime($boosterKey, &$user) {
    $boosterEndtimeKey = $boosterKey . '_time';

    return $user[$boosterEndtimeKey];
}

function _isBoosterActive($boosterKey, &$user, $timestamp) {
    return (_getBoosterEndtime($boosterKey, $user) >= $timestamp);
}

function _getCurrentBoosters(&$user, $timestamp) {
    return [
        'hasGeologist' => _isBoosterActive('geologist', $user, $timestamp),
        'hasEngineer' => _isBoosterActive('engineer', $user, $timestamp),
    ];
}

//  Assumptions:
//      - See "calculateRealResourceIncome" documentation (same assumptions apply).
//
//  Arguments:
//      - $planet (&Object)
//      - $user (&Object)
//      - $timerange (Object)
//          - start (Number)
//          - end (Number)
//          - data (Object)
//              - hasGeologist (true | null)
//              - hasEngineer (true | null)
//      - $options (Object) [default: []]
//          - isVacationCheckEnabled (Boolean) [default: false]
//              Should perform vacation check when calculating production level,
//              based on current user's state.
//
//  Returns: Array<$resourceKey: string, $resourceIncome: Object>
//
function calculateTotalResourcesIncome(&$planet, &$user, $timerange, $options = []) {
    global $_Vars_ElementCategories;

    if (!isset($options['isVacationCheckEnabled'])) {
        $options['isVacationCheckEnabled'] = false;
    }

    $productionTime = ($timerange['end'] - $timerange['start']);

    if ($productionTime <= 0) {
        return [];
    }

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
                'useCustomBoosters' => true,
                'boosters' => $timerange['data'],
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

    $productionLevel = getPlanetsProductionEfficiency(
        $planetProduction,
        $user,
        [
            'isVacationCheckEnabled' => $options['isVacationCheckEnabled']
        ]
    );

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

    return $income;
}

//  Assumptions:
//      - Planet's hourly extraction of the resource is already calculated
//        and available in $planet["{$resourceKey}_perhour"] property.
//        This includes any boosters that affect the extraction (eg. Geologist).
//      - Planet's storage capacity of the resource is already calculated
//        and available in $planet["{$resourceKey}_max"] property.
//      - Planet's resources state represents the current state,
//        which includes any previous incomes if the update happens in sequence.
//
//  Arguments
//      - $resourceKey (String)
//      - $planet (&Object)
//      - $params (Object)
//          - productionTime (Number)
//          - productionLevel (Number)
//
function calculateRealResourceIncome($resourceKey, &$planet, $params) {
    $productionTime = $params['productionTime'];
    $productionLevel = $params['productionLevel'];

    $resourceCurrentAmount = $planet[$resourceKey];
    $resourceMaxStorage = ($planet["{$resourceKey}_max"] * MAX_OVERFLOW);

    if ($resourceCurrentAmount >= $resourceMaxStorage) {
        return [
            'isUpdated' => false,
            'income' => 0
        ];
    }

    $theoreticalResourceIncomePerSecond = calculateTotalTheoreticalResourceIncomePerSecond(
        $resourceKey,
        $planet
    );
    $realResourceIncomePerSecond = calculateTotalRealResourceIncomePerSecond([
        'theoreticalIncomePerSecond' => $theoreticalResourceIncomePerSecond,
        'productionLevel' => $productionLevel
    ]);

    $theoreticalIncome = [
        'production' => (
            $productionTime *
            $realResourceIncomePerSecond['production']
        ),
        'base' => (
            $productionTime *
            $realResourceIncomePerSecond['base']
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

//  Assumptions:
//      - Planet's hourly extraction of the resource is already calculated
//        and available in $planet["{$resourceKey}_perhour"] property.
//        This includes any boosters that affect the extraction (eg. Geologist).
//
function calculateTotalTheoreticalResourceIncomePerSecond($resourceKey, &$planet) {
    global $_GameConfig;

    return [
        'production' => ($planet["{$resourceKey}_perhour"] / 3600),
        'base' => (
            (
                $_GameConfig["{$resourceKey}_basic_income"] *
                $_GameConfig['resource_multiplier']
            ) /
            3600
        )
    ];
}

//  Arguments:
//      - $params (Object)
//          - theoreticalIncomePerSecond (Object)
//              - production (Number)
//              - base (Number)
//          - productionLevel (Number)
//
function calculateTotalRealResourceIncomePerSecond($params) {
    $theoreticalIncomePerSecond = $params['theoreticalIncomePerSecond'];
    $productionLevel = $params['productionLevel'];

    return [
        'production' => (
            $theoreticalIncomePerSecond['production'] *
            (0.01 * $productionLevel)
        ),
        'base' => (
            $theoreticalIncomePerSecond['base']
        )
    ];
}

?>
