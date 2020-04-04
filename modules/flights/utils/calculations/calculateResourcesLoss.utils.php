<?php

namespace UniEngine\Engine\Modules\Flights\Utils\Calculations;

use UniEngine\Engine\Includes\Helpers\World\Resources;
use UniEngine\Engine\Includes\Helpers\World\Elements;

/**
 *
 * @param array $params
 * @param array $params['unitsLost']
 * @param array $params['debrisRecoveryPercentages']
 * @param number $params['debrisRecoveryPercentages']['ships']
 * @param number $params['debrisRecoveryPercentages']['defenses']
 */
function calculateResourcesLoss($params) {
    global $_Vars_Prices;

    $unitsLost = (
        !empty($params['unitsLost']) ?
        $params['unitsLost'] :
        []
    );
    $debrisRecoveryPercentages = $params['debrisRecoveryPercentages'];

    $spendableResourceKeys = Resources\getKnownSpendableResourceKeys();
    $recoverableResourceKeys = Resources\getKnownDebrisRecoverableResourceKeys();

    $realLossAccumulator = [];
    $recoverableLossAccumulator = [];

    foreach ($spendableResourceKeys as $resourceKey) {
        $realLossAccumulator[$resourceKey] = 0;
    }
    foreach ($recoverableResourceKeys as $resourceKey) {
        $recoverableLossAccumulator[$resourceKey] = 0;
    }

    foreach ($unitsLost as $unitID => $unitAmount) {
        if (
            !Elements\isShip($unitID) &&
            !Elements\isDefenseSystem($unitID) &&
            !Elements\isMissile($unitID)
        ) {
            continue;
        }

        $unitTypeRecoveryFactor = (
            Elements\isShip($unitID) ?
            $debrisRecoveryPercentages['ships'] :
            $debrisRecoveryPercentages['defenses']
        );

        foreach ($spendableResourceKeys as $resourceKey) {
            if (!isset($_Vars_Prices[$unitID][$resourceKey])) {
                continue;
            }

            $unitsRealResourceLoss = ($_Vars_Prices[$unitID][$resourceKey] * $unitAmount);

            $realLossAccumulator[$resourceKey] += floor($unitsRealResourceLoss);

            if (in_array($resourceKey, $recoverableResourceKeys)) {
                $recoverableLossAccumulator[$resourceKey] += floor(
                    $unitsRealResourceLoss *
                    $unitTypeRecoveryFactor
                );
            }
        }
    }

    return [
        'realLoss' => $realLossAccumulator,
        'recoverableLoss' => $recoverableLossAccumulator,
    ];
}

?>
