<?php

use UniEngine\Engine\Includes\Helpers\World\Elements;
use UniEngine\Engine\Includes\Helpers\World\Resources;

//  Arguments:
//      - $user (Object)
//      - $planet (Object)
//      - $elementID (String | Number)
//      - $isDestruction (Boolean)
//
//  Returns:
//      Boolean
//
//  Notes:
//      - This function does not throw in case of cost calculation errors
//        (eg. when a structure is not upgradeable anymore)
//
function IsElementBuyable($user, $planet, $elementID, $isDestruction) {
    if (isOnVacation($user)) {
        return false;
    }

    try {
        $elementPurchaseCost = Elements\calculatePurchaseCost(
            $elementID,
            Elements\getElementState($elementID, $planet, $user),
            [
                'purchaseMode' => (
                    !$isDestruction ?
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
            $user,
            $planet
        );

        if ($costValue > $currentResourceState) {
            return false;
        }
    }

    return true;
}

?>
