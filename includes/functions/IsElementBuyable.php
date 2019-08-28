<?php

use UniEngine\Engine\Includes\Helpers\World\Elements;

function IsElementBuyable($TheUser, $ThePlanet, $ElementID, $Incremental = true, $ForDestroy = false, $GetPremiumData = false)
{
    if(isOnVacation($TheUser))
    {
        return false;
    }

    $elementCost = Elements\calculatePurchaseCost(
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

    foreach ($elementCost as $costResourceKey => $costValue) {
        if ($costValue > $ThePlanet[$costResourceKey]) {
            return false;
        }
    }

    if($GetPremiumData)
    {
        global $_Vars_PremiumBuildingPrices;
        if(isset($_Vars_PremiumBuildingPrices[$ElementID]) && $_Vars_PremiumBuildingPrices[$ElementID] > $TheUser['darkEnergy'])
        {
            return false;
        }
    }

    return true;
}

?>
