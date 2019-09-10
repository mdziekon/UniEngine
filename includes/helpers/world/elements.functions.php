<?php

namespace UniEngine\Engine\Includes\Helpers\World\Elements;

use UniEngine\Engine\Common\Exceptions;

abstract class PurchaseMode {
    const Upgrade = 0;
    const Downgrade = 1;
}

class PurchaseCostCalculationException extends Exceptions\UniEngineException {};

function isStructure($elementID) {
    global $_Vars_ElementCategories;

    return in_array($elementID, $_Vars_ElementCategories['build']);
}

function isTechnology($elementID) {
    global $_Vars_ElementCategories;

    return in_array($elementID, $_Vars_ElementCategories['tech']);
}

function isShip($elementID) {
    global $_Vars_ElementCategories;

    return in_array($elementID, $_Vars_ElementCategories['fleet']);
}

function isDefenseSystem($elementID) {
    global $_Vars_ElementCategories;

    return in_array($elementID, $_Vars_ElementCategories['defense']);
}

function isMissile($elementID) {
    global $_Vars_ElementCategories;

    return in_array($elementID, $_Vars_ElementCategories['rockets']);
}

function isStructureAvailableOnPlanetType($elementID, $planetType) {
    global $_Vars_ElementCategories;

    if ($planetType != 1 && $planetType != 3) {
        throw new Exceptions\UniEngineException("Invalid planetType '{$planetType}'");
    }

    return in_array($elementID, $_Vars_ElementCategories['buildOn'][$planetType]);
}

function isPremiumStructure($elementID) {
    global $_Vars_PremiumBuildings;

    return (
        isset($_Vars_PremiumBuildings[$elementID]) &&
        $_Vars_PremiumBuildings[$elementID]
    );
}

function isStorageStructure($elementID) {
    global $_Vars_ElementCategories;

    return in_array($elementID, $_Vars_ElementCategories['storages']);
}

function isIndestructibleStructure($elementID) {
    global $_Vars_IndestructibleBuildings;

    return (
        isset($_Vars_IndestructibleBuildings[$elementID]) &&
        $_Vars_IndestructibleBuildings[$elementID]
    );
}

function isCancellableOnceInProgress($elementID) {
    return (
        !isPremiumStructure($elementID)
    );
}

function isConstructibleInHangar($elementID) {
    return (
        isShip($elementID) ||
        isDefenseSystem($elementID) ||
        isMissile($elementID)
    );
}

function isPurchaseable($elementID) {
    return (
        isStructure($elementID) ||
        isTechnology($elementID) ||
        isShip($elementID) ||
        isDefenseSystem($elementID) ||
        isMissile($elementID)
    );
}

function isPurchaseableByUnits($elementID) {
    return (
        isShip($elementID) ||
        isDefenseSystem($elementID) ||
        isMissile($elementID)
    );
}

function isUpgradeable($elementID) {
    return (
        isStructure($elementID) ||
        isTechnology($elementID)
    );
}

function isDowngradeable($elementID) {
    return (
        isStructure($elementID) &&
        !isIndestructibleStructure($elementID)
    );
}

function getElementMaxUpgradeLevel($elementID) {
    global $_Vars_MaxElementLevel;

    if (!isset($_Vars_MaxElementLevel[$elementID])) {
        return INF;
    }

    return $_Vars_MaxElementLevel[$elementID];
}

function getElementPlanetaryCostBase($elementID) {
    global $_Vars_Prices;

    $costResources = [
        'metal',
        'crystal',
        'deuterium',
        'energy_max'
    ];

    $baseCost = [];

    foreach ($costResources as $costResource) {
        if (!isset($_Vars_Prices[$elementID][$costResource])) {
            continue;
        }

        if ($_Vars_Prices[$elementID][$costResource] <= 0) {
            continue;
        }

        $baseCost[$costResource] = $_Vars_Prices[$elementID][$costResource];
    }

    return $baseCost;
}

function getElementUserCostBase($elementID) {
    global $_Vars_PremiumBuildingPrices;

    $costResources = [
        'darkEnergy',
    ];

    $baseCost = [];

    foreach ($costResources as $costResource) {
        switch ($costResource) {
            case 'darkEnergy':
                if (!isset($_Vars_PremiumBuildingPrices[$elementID])) {
                    continue;
                }

                if ($_Vars_PremiumBuildingPrices[$elementID] <= 0) {
                    continue;
                }

                $baseCost[$costResource] = $_Vars_PremiumBuildingPrices[$elementID];

                continue;
        }
    }

    return $baseCost;
}

function getElementPlanetaryCostFactor($elementID) {
    global $_Vars_Prices;

    return $_Vars_Prices[$elementID]['factor'];
}

function getElementUserCostFactor($elementID) {
    return 1;
}

function getElementState($elementID, &$planet, &$user) {
    if (
        isStructure($elementID) ||
        isTechnology($elementID)
    ) {
        return [
            'level' => getElementCurrentLevel($elementID, $planet, $user)
        ];
    }

    if (isConstructibleInHangar($elementID)) {
        return [
            'count' => getElementCurrentCount($elementID, $planet, $user)
        ];
    }

    throw new \Exception("UniEngine::getElementState(): cannot retrieve element's state of an element with ID '{$elementID}'");
}

function getElementCurrentLevel($elementID, &$planet, &$user) {
    global $_Vars_GameElements;

    $elementKey = $_Vars_GameElements[$elementID];

    if (isStructure($elementID)) {
        if (empty($planet[$elementKey])) {
            return 0;
        }

        return $planet[$elementKey];
    }

    if (isTechnology($elementID)) {
        if (empty($user[$elementKey])) {
            return 0;
        }

        return $user[$elementKey];
    }

    throw new \Exception("UniEngine::getElementCurrentLevel(): cannot retrieve element's level of an element with ID '{$elementID}'");
}

function getElementCurrentCount($elementID, &$planet, &$user) {
    global $_Vars_GameElements;

    $elementKey = $_Vars_GameElements[$elementID];

    if (isConstructibleInHangar($elementID)) {
        if (empty($planet[$elementKey])) {
            return 0;
        }

        return $planet[$elementKey];
    }

    throw new \Exception("UniEngine::getElementCurrentCount(): cannot retrieve element's level of an element with ID '{$elementID}'");
}

//  Arguments:
//      - $elementID
//      - $elementState
//      - $params (Object)
//          - purchaseMode (PurchaseMode::Upgrade | PurchaseMode::Downgrade | undefined)
//              [default: PurchaseMode::Upgrade]
//
//  Returns:
//      Object:
//          - planetary (Object<resource: string, cost: number>)
//          - user (Object<resource: string, cost: number>)
//
//  Notes:
//      - The returned arrays will contain only non-zero costs (> 0).
//
function calculatePurchaseCost($elementID, $elementState, $params) {
    if (!isset($params['purchaseMode'])) {
        $params['purchaseMode'] = PurchaseMode::Upgrade;
    }

    $purchaseMode = $params['purchaseMode'];

    if (!isPurchaseable($elementID)) {
        throw new PurchaseCostCalculationException("UniEngine::calculatePurchaseCost(): element with ID '{$elementID}' is not purchaseable");
    }

    if (
        $purchaseMode === PurchaseMode::Downgrade &&
        !isDowngradeable($elementID)
    ) {
        throw new PurchaseCostCalculationException("UniEngine::calculatePurchaseCost(): element with ID '{$elementID}' is not downgradeable");
    }

    $planetaryCostBase = getElementPlanetaryCostBase($elementID);
    $userCostBase = getElementUserCostBase($elementID);

    if (isPurchaseableByUnits($elementID)) {
        // This element is purchaseable on a unit basis, therefore there is no
        // upgrade price factor calculation required (nor it is wanted).
        return [
            'planetary' => $planetaryCostBase,
            'user' => $userCostBase
        ];
    }

    $planetaryCostFactor = getElementPlanetaryCostFactor($elementID);
    $userCostFactor = getElementUserCostFactor($elementID);

    // Downgrade costs are calculated as previously paid upgrade cost, but halved
    $elementLevel = (
        ($purchaseMode === PurchaseMode::Upgrade) ?
        $elementState['level'] :
        $elementState['level'] - 1
    );

    if ($elementLevel < 0) {
        // Prevent negative level being used to calculate costs
        $elementLevel = 0;
    }

    $maxUpgradeLevel = getElementMaxUpgradeLevel($elementID);

    if ($elementLevel >= $maxUpgradeLevel) {
        throw new PurchaseCostCalculationException("UniEngine::calculatePurchaseCost(): element with ID '{$elementID}' has reached its maximum upgrade level");
    }

    return [
        'planetary' => _calculateUpgradableElementPurchaseCosts(
            $elementID,
            [
                'elementLevel' => $elementLevel,
                'costBase' => $planetaryCostBase,
                'costFactor' => $planetaryCostFactor,
                'purchaseMode' => $purchaseMode
            ]
        ),
        'user' => _calculateUpgradableElementPurchaseCosts(
            $elementID,
            [
                'elementLevel' => $elementLevel,
                'costBase' => $userCostBase,
                'costFactor' => $userCostFactor,
                'purchaseMode' => $purchaseMode
            ]
        )
    ];
}

//  Arguments
//      - $elementID
//      - $params (Object)
//          - elementLevel (Number)
//          - costBase (Object<resource: string, value: number>)
//          - costFactor (Number)
//          - purchaseMode (PurchaseMode::Upgrade | PurchaseMode::Downgrade)
//
function _calculateUpgradableElementPurchaseCosts($elementID, $params) {
    $elementLevel = $params['elementLevel'];
    $costBase = $params['costBase'];
    $costFactor = $params['costFactor'];
    $purchaseMode = $params['purchaseMode'];

    $upgradeCost = array_map(
        function ($costValue) use ($elementLevel, $costFactor) {
            return floor($costValue * pow($costFactor, $elementLevel));
        },
        $costBase
    );

    if ($purchaseMode === PurchaseMode::Upgrade) {
        return $upgradeCost;
    }

    $downgradeCost = array_map(
        function ($costValue) {
            return floor($costValue / 2);
        },
        $upgradeCost
    );

    return $downgradeCost;
}

?>
