<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Factories;

use UniEngine\Engine\Includes\Helpers\Common\Collections;

/**
 * @param array $props
 * @param string $props['fleetId']
 * @param object $props['fleetData']
 * @param object $props['fleetOwner']
 * @param object $props['targetOwner']
 * @param object $props['targetInfo']
 * @param object $props['statsData']
 */
function createPushAlert($props) {
    $fleetId = $props['fleetId'];
    $fleetData = $props['fleetData'];
    $fleetOwner = $props['fleetOwner'];
    $targetOwner = $props['targetOwner'];
    $targetInfo = $props['targetInfo'];
    $statsData = $props['statsData'];

    $pushAlertData = [
        'TargetUserID' => $targetOwner['id'],
        'SameAlly' => (
            $targetInfo['isPlanetOwnerAlly'] ?
                $targetOwner['ally_id'] :
                null
        ),
        'AllyPact' => (
            $targetInfo['isPlanetOwnerNonAggressiveAllianceMember'] ?
            [
                'SenderAlly' => $fleetOwner,
                'TargetAlly' => $targetOwner['ally_id'],
            ] :
            null
        ),
        'BuddyFriends' => $targetInfo['isPlanetOwnerBuddy'],
        'FleetID' => $fleetId,
        'Stats' => [
            'Sender' => [
                'Points' => $statsData['fleetOwner']['points'],
                'Position' => $statsData['fleetOwner']['totalRankPos'],
            ],
            'Target' => [
                'Points' => $statsData['targetOwner']['points'],
                'Position' => $statsData['targetOwner']['totalRankPos'],
            ],
        ],
        'Resources' => [
            'Metal' => floatval($fleetData['resources']['metal']),
            'Crystal' => floatval($fleetData['resources']['crystal']),
            'Deuterium' => floatval($fleetData['resources']['deuterium']),
        ],
    ];

    $pushAlertData = Collections\compact($pushAlertData, [ 'isStrictNullCheck' => true ]);

    return $pushAlertData;
}

?>
