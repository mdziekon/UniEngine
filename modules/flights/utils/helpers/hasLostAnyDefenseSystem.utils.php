<?php

namespace UniEngine\Engine\Modules\Flights\Utils\Helpers;

use UniEngine\Engine\Includes\Helpers\World\Elements;

/**
 * @param array $params
 * @param array $params['originalShips'] Ships that have participated in the combat
 * @param array $params['postCombatShips'] Ships that have survived the combat
 */
function hasLostAnyDefenseSystem($params) {
    $originalShips = $params['originalShips'];
    $postCombatShips = $params['postCombatShips'];

    if (empty($originalShips[0])) {
        return false;
    }

    $hasLostAnyDefenseSystems = false;

    foreach ($originalShips[0] as $shipID => $shipOriginalCount) {
        if (!Elements\isDefenseSystem($shipID)) {
            continue;
        }

        $shipPostCombatCount = (
            isset($postCombatShips[0][$shipID]) ?
                $postCombatShips[0][$shipID] :
                0
        );

        if ($shipPostCombatCount >= $shipOriginalCount) {
            continue;
        }

        $hasLostAnyDefenseSystems = true;

        break;
    }

    return $hasLostAnyDefenseSystems;
}

?>
