<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Factories;

use UniEngine\Engine\Includes\Helpers\Common\Collections;
use UniEngine\Engine\Modules\FlightControl;

/**
 * @param array $props
 * @param string $props['fleetId']
 * @param object $props['fleetData']
 * @param object $props['fleetOwner']
 * @param object $props['targetOwner']
 * @param object $props['foundIpIntersections']
 * @param object $props['multiIpDeclarationId']
 * @param bool $props['hasBlockedFleet']
 */
function createMultiAlert($props) {
    $fleetId = $props['fleetId'];
    $fleetData = $props['fleetData'];
    $fleetOwner = $props['fleetOwner'];
    $targetOwner = $props['targetOwner'];
    $foundIpIntersections = $props['foundIpIntersections'];
    $multiIpDeclarationId = $props['multiIpDeclarationId'];
    $hasBlockedFleet = $props['hasBlockedFleet'];

    $fleetOwnerId = $fleetOwner['id'];
    $targetOwnerId = $targetOwner['id'];
    $ipLogData = $foundIpIntersections['IPLogData'];

    $ipIntersections = array_map_withkeys(
        $foundIpIntersections['Intersect'],
        function ($ipId) use ($fleetOwnerId, $targetOwnerId, &$ipLogData) {
            return [
                'IPID' => $ipId,
                'SenderData' => $ipLogData[$fleetOwnerId][$ipId],
                'TargetData' => $ipLogData[$targetOwnerId][$ipId],
            ];
        }
    );

    $otherUsersWithMatchingIp = FlightControl\Utils\Fetchers\fetchUsersWithMatchingIp([
        'requiredIpIds' => $foundIpIntersections['Intersect'],
        'excludedUserIds' => [
            $fleetOwnerId,
            $targetOwnerId,
        ],
    ]);
    $otherUsersWithMatchingIp = array_map_withkeys(
        $otherUsersWithMatchingIp,
        function ($entry) {
            return $entry['User_ID'];
        }
    );

    $alertData = [
        'MissionID' => $fleetData['Mission'],
        'FleetID' => (
            !empty($fleetId) ?
                $fleetId :
                null
        ),
        'TargetUserID' => $targetOwnerId,
        'Intersect' => $ipIntersections,
        'DeclarationID' => (
            !empty($multiIpDeclarationId) ?
                $multiIpDeclarationId :
                null
        ),
        'OtherUsers' => (
            !empty($otherUsersWithMatchingIp) ?
                $otherUsersWithMatchingIp :
                null
        ),
        'Blocked' => $hasBlockedFleet,
    ];

    $alertData = Collections\compact($alertData, [ 'isStrictNullCheck' => true ]);

    return $alertData;
}

?>
