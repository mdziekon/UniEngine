<?php

use UniEngine\Engine\Includes\Helpers\World\Elements;

function IsElementBuyable($TheUser, $ThePlanet, $ElementID, $ForDestroy = false) {
    if (isOnVacation($TheUser)) {
        return false;
    }

    try {
        $elementPurchaseCost = Elements\calculatePurchaseCost(
            $ElementID,
            Elements\getElementCurrentLevel($ElementID, $ThePlanet, $TheUser),
            [
                'purchaseMode' => (
                    !$ForDestroy ?
                    Elements\PurchaseMode::Upgrade :
                    Elements\PurchaseMode::Downgrade
                )
            ]
        );
    } catch (Elements\PurchaseCostCalculationException $exception) {
        return false;
    }

    foreach ($elementPurchaseCost['planetary'] as $costResourceKey => $costValue) {
        if ($costValue > $ThePlanet[$costResourceKey]) {
            return false;
        }
    }

    foreach ($elementPurchaseCost['user'] as $costResourceKey => $costValue) {
        if ($costValue > $TheUser[$costResourceKey]) {
            return false;
        }
    }

    return true;
}

?>
