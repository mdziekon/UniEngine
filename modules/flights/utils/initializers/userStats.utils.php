<?php

namespace UniEngine\Engine\Modules\Flights\Utils\Initializers;

use UniEngine\Engine\Includes\Helpers\World\Elements;

/**
 * @see FlyingFleetHandler.php
 * (this function must generate elements in the same order as keys in the stats updating query!)
 */
function initUserStatsMap() {
    global $_Vars_ElementCategories;

    $userStatsMap = [
        'raids_won'                 => '0',
        'raids_draw'                => '0',
        'raids_lost'                => '0',
        'raids_acs_won'             => '0',
        'raids_inAlly'              => '0',
        'raids_missileAttack'       => '0',
        'moons_destroyed'           => '0',
        'moons_created'             => '0',
        'other_expeditions_count'   => '0',
    ];

    foreach ([ 'fleet', 'defense' ] as $elementCategory) {
        foreach ($_Vars_ElementCategories[$elementCategory] as $elementID) {
            if (
                !Elements\isShip($elementID) &&
                !Elements\isDefenseSystem($elementID)
            ) {
                continue;
            }

            $unitsDestroyedKey = "destroyed_{$elementID}";
            $unitsLostKey = "lost_{$elementID}";

            $userStatsMap[$unitsDestroyedKey] = '0';
            $userStatsMap[$unitsLostKey] = '0';
        }
    }

    return $userStatsMap;
}

?>
