<?php

use UniEngine\Engine\Includes\Helpers\World\Elements;

function IsElementBuyable($TheUser, $ThePlanet, $ElementID, $ForDestroy = false, $GetPremiumData = false)
{
    if(isOnVacation($TheUser))
    {
        return false;
    }

    $elementPlanetaryCost = Elements\calculatePurchasePlanetaryCost(
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

    foreach ($elementPlanetaryCost as $costResourceKey => $costValue) {
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
