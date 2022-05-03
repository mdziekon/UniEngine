<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Factories;

/**
 * @param array $props
 * @param array $props['fleetOwner']
 * @param array $props['targetOwner']
 */
function createFleetUsersStatsData($props) {
    $fleetOwner = (
        isset($props['fleetOwner']) ?
            $props['fleetOwner'] :
            []
    );
    $targetOwner = (
        isset($props['targetOwner']) ?
            $props['targetOwner'] :
            []
    );

    $usersStats = [
        'fleetOwner' => [
            'totalRankPos' => 0,
            'points' => 0,
        ],
        'targetOwner' => [
            'totalRankPos' => 0,
            'points' => 0,
        ],
    ];

    if (empty($fleetOwner) && empty($targetOwner)) {
        return $usersStats;
    }

    $usersStats = [
        'fleetOwner' => [
            'totalRankPos' => $fleetOwner['total_rank'],
            'points' => (
                $fleetOwner['total_points'] > 0 ?
                    $fleetOwner['total_points'] :
                    0
            ),
        ],
        'targetOwner' => [
            'totalRankPos' => $targetOwner['total_rank'],
            'points' => (
                $targetOwner['total_points'] > 0 ?
                    $targetOwner['total_points'] :
                    0
            ),
        ],
    ];

    // Impersonate target user in terms of stat points & ranking pos
    if (false && CheckAuth('programmer')) {
        $usersStats['fleetOwner']['points'] = $usersStats['targetOwner']['points'];
        $usersStats['fleetOwner']['totalRankPos'] = $usersStats['targetOwner']['totalRankPos'];
    }

    return $usersStats;
}

?>
