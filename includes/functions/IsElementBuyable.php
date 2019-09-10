<?php

use UniEngine\Engine\Includes\Helpers\World\Elements;
use UniEngine\Engine\Includes\Helpers\World\Resources;

function IsElementBuyable($TheUser, $ThePlanet, $ElementID, $ForDestroy = false) {
    if (isOnVacation($TheUser)) {
        return false;
    }

    try {
        $elementPurchaseCost = Elements\calculatePurchaseCost(
            $ElementID,
            Elements\getElementState($ElementID, $ThePlanet, $TheUser),
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

    foreach ($elementPurchaseCost as $costResourceKey => $costValue) {
        $currentResourceState = Resources\getResourceState(
            $costResourceKey,
            $TheUser,
            $ThePlanet
        );

        if ($costValue > $currentResourceState) {
            return false;
        }
    }

    return true;
}

?>
