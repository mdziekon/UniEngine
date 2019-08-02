<?php

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
        !_isElementStructure($elementID) &&
        !_isElementShip($elementID)
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
            // TODO: correctly calculate when there is fuel usage
            // eg. as in Fusion Reactor

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

function _getTheoreticalElementProduction($elementID) {
    if (
        !_isElementStructure($elementID) &&
        !_isElementShip($elementID)
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

function _isElementStructure($elementID) {
    global $_Vars_ElementCategories;

    return in_array($elementID, $_Vars_ElementCategories['build']);
}

function _isElementShip($elementID) {
    global $_Vars_ElementCategories;

    return in_array($elementID, $_Vars_ElementCategories['fleet']);
}

function _getElementPlanetKey($elementID) {
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
//      - Planet's hourly extraction of the resource is already calculated
//        and available in $planet["{$resourceKey}_perhour"] property.
//        This includes any boosters that affect the extraction (eg. Geologist).
//      - Planet's storage capacity of the resource is already calculated
//        and available in $planet["{$resourceKey}_max"] property.
//
//  Arguments
//      - $resourceKey (String)
//      - $planet (&Object)
//      - $params (Object)
//          - productionTime (Number)
//          - productionLevel (Number)
//
function calculateRealResourceIncome($resourceKey, &$planet, $params) {
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

?>
