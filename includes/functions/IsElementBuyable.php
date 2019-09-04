<?php

use UniEngine\Engine\Includes\Helpers\World\Elements;

function IsElementBuyable($TheUser, $ThePlanet, $ElementID, $ForDestroy = false, $GetPremiumData = false) {
    if (isOnVacation($TheUser)) {
        return false;
    }

    $elementPurchaseCost = Elements\calculatePurchaseCost(
        $ElementID,
        $ThePlanet,
        $TheUser,
        [
            'purchaseMode' => (
                !$ForDestroy ?
                Elements\PurchaseMode::Upgrade :
                Elements\PurchaseMode::Downgrade
            )
        ]
    );

    foreach ($elementPurchaseCost['planetary'] as $costResourceKey => $costValue) {
        if ($costValue > $ThePlanet[$costResourceKey]) {
            return false;
        }
    }

    if ($GetPremiumData) {
        foreach ($elementPurchaseCost['user'] as $costResourceKey => $costValue) {
            if ($costValue > $TheUser[$costResourceKey]) {
                return false;
            }
        }
    }

    return true;
}

?>
