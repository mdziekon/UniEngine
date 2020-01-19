<?php

namespace UniEngine\Engine\Includes\Helpers\World\Elements;

use UniEngine\Engine\Common\Exceptions;
use UniEngine\Engine\Includes\Helpers\World\Resources;

abstract class PurchaseMode {
    const Upgrade = 0;
    const Downgrade = 1;
}

class PurchaseCostCalculationException extends Exceptions\UniEngineException {};

function getElementPlanetaryCostBase($elementID) {
    global $_Vars_Prices;

    $costResources = Resources\getKnownPlanetaryResourceKeys();

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

    $costResources = Resources\getKnownUserResourceKeys();

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

//  Arguments:
//      - $elementID
//      - $elementState
//      - $params (Object)
//          - purchaseMode (PurchaseMode::Upgrade | PurchaseMode::Downgrade | undefined)
//              [default: PurchaseMode::Upgrade]
//
//  Returns:
//      Object<resource: string, cost: number>
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
        throw new PurchaseCostCalculationException("Element with ID '{$elementID}' is not purchaseable");
    }

    if (
        $purchaseMode === PurchaseMode::Downgrade &&
        !isDowngradeable($elementID)
    ) {
        throw new PurchaseCostCalculationException("Element with ID '{$elementID}' is not downgradeable");
    }

    $planetaryCostBase = getElementPlanetaryCostBase($elementID);
    $userCostBase = getElementUserCostBase($elementID);

    if (isPurchaseableByUnits($elementID)) {
        // This element is purchaseable on a unit basis, therefore there is no
        // upgrade price factor calculation required (nor it is wanted).
        return array_merge(
            $planetaryCostBase,
            $userCostBase
        );
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
        throw new PurchaseCostCalculationException("Element with ID '{$elementID}' has reached its maximum upgrade level");
    }

    return array_merge(
        _calculateUpgradableElementPurchaseCosts(
            $elementID,
            [
                'elementLevel' => $elementLevel,
                'costBase' => $planetaryCostBase,
                'costFactor' => $planetaryCostFactor,
                'purchaseMode' => $purchaseMode
            ]
        ),
        _calculateUpgradableElementPurchaseCosts(
            $elementID,
            [
                'elementLevel' => $elementLevel,
                'costBase' => $userCostBase,
                'costFactor' => $userCostFactor,
                'purchaseMode' => $purchaseMode
            ]
        )
    );
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
