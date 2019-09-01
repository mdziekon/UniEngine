<?php

namespace UniEngine\Engine\Includes\Helpers\World\Elements;

abstract class PurchaseMode {
    const Upgrade = 0;
    const Downgrade = 1;
}

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

function isStorageStructure($elementID) {
    global $_Vars_ElementCategories;

    return in_array($elementID, $_Vars_ElementCategories['storages']);
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
        isConstructibleInHangar($elementID)
    );
}

function isUpgradeable($elementID) {
    return (
        isStructure($elementID) ||
        isTechnology($elementID)
    );
}

function isDowngradeable($elementID) {
    return isStructure($elementID);
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

function getElementPlanetaryCostFactor($elementID) {
    global $_Vars_Prices;

    return $_Vars_Prices[$elementID]['factor'];
}

function getElementCurrentLevel($elementID, &$planet, &$user) {
    global $_Vars_GameElements;

    $elementKey = $_Vars_GameElements[$elementID];

    if (
        isStructure($elementID) ||
        isConstructibleInHangar($elementID)
    ) {
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

//  Arguments:
//      - $elementID
//      - $planet
//      - $user
//      - $params (Object)
//          - purchaseMode (PurchaseMode::Upgrade | PurchaseMode::Downgrade | undefined)
//              [default: PurchaseMode::Upgrade]
//
//  Returns:
//      Object<resource: string, cost: number>
//
function calculatePurchasePlanetaryCost($elementID, &$planet, &$user, $params) {
    if (!isset($params['purchaseMode'])) {
        $params['purchaseMode'] = PurchaseMode::Upgrade;
    }

    $purchaseMode = $params['purchaseMode'];
    $costBase = getElementPlanetaryCostBase($elementID);
    $costFactor = getElementPlanetaryCostFactor($elementID);

    if (!isPurchaseable($elementID)) {
        throw new \Exception("UniEngine::calculatePurchasePlanetaryCost(): element with ID '{$elementID}' is not purchaseable");
    }

    if (
        $purchaseMode === PurchaseMode::Downgrade &&
        !isDowngradeable($elementID)
    ) {
        throw new \Exception("UniEngine::calculatePurchasePlanetaryCost(): element with ID '{$elementID}' is not downgradeable");
    }

    if (isConstructibleInHangar($elementID)) {
        return $costBase;
    }

    // Downgrade costs are calculated as previously paid upgrade cost, but halved
    $elementLevel = (
        ($purchaseMode === PurchaseMode::Upgrade) ?
        getElementCurrentLevel($elementID, $planet, $user) :
        getElementCurrentLevel($elementID, $planet, $user) - 1
    );

    if ($elementLevel < 0) {
        // Prevent negative level being used to calculate costs
        $elementLevel = 0;
    }

    $upgradeCost = array_map(
        function ($costValue) use ($elementLevel, $costFactor) {
            return floor($costValue * pow($elementLevel, $costFactor));
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
