<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Validators;

use UniEngine\Engine\Modules\FlightControl;

/**
 * @param array $props
 * @param number $props['timestamp']
 * @param array $props['fleetData']
 * @param number $props['fleetOwnerDetails']['userId']
 * @param number $props['fleetOwnerDetails']['planetId']
 * @param array $props['targetOwnerDetails']
 * @param number $props['targetOwnerDetails']['userId']
 * @param number $props['targetOwnerDetails']['planetId']
 * @param number $props['targetOwnerDetails']['onlinetime']
 * @param array $props['settings']
 * @param number $props['settings']['idleTime']
 */
function validateSmartFleetsBlockadeState ($props) {
    global $_Vars_FleetMissions;

    $isValid = function () {
        return [
            'isValid' => true,
        ];
    };
    $isInvalid = function ($errors) {
        return [
            'isValid' => false,
            'errors' => $errors,
        ];
    };

    $timestamp = $props['timestamp'];
    $fleetData = $props['fleetData'];
    $fleetMissionTypeId = $fleetData['Mission'];
    $targetIsOccupied = !empty($props['targetOwnerDetails']);
    $isTargetOwnerIdle = (
        $targetIsOccupied ?
        $props['targetOwnerDetails']['onlinetime'] <= ($timestamp - $props['settings']['idleTime']) :
        false
    );

    $blockadeEntries = FlightControl\Utils\Fetchers\fetchActiveSmartFleetsBlockadeEntries($props);

    if ($blockadeEntries->num_rows == 0) {
        return $isValid();
    }

    while ($blockadeEntry = $blockadeEntries->fetch_assoc()) {
        $isAllMissionsBlockade = $blockadeEntry['BlockMissions'] == '0';
        $blockedMissionTypeIds = (
            $isAllMissionsBlockade ?
            [] :
            explode(',', $blockadeEntry['BlockMissions'])
        );

        if (
            !$isAllMissionsBlockade &&
            !in_array($fleetMissionTypeId, $blockedMissionTypeIds)
        ) {
            continue;
        }

        if ($blockadeEntry['Type'] == 1) {
            // Check if blockade is still in effect
            if ($blockadeEntry['EndTime'] > $timestamp) {
                $isNotProtectedBecauseIdle = (
                    $blockadeEntry['DontBlockIfIdle'] == 1 &&
                    $targetIsOccupied &&
                    in_array($fleetMissionTypeId, $_Vars_FleetMissions['military']) &&
                    $isTargetOwnerIdle
                );

                if ($isNotProtectedBecauseIdle) {
                    continue;
                }

                return $isInvalid([
                    'blockType' => 'GLOBAL_ENDTIME',
                    'details' => [
                        'endTime' => $blockadeEntry['EndTime'],
                    ],
                ]);
            }

            // Check if post-blockade lock is still in effect
            if ($blockadeEntry['PostEndTime'] > $timestamp) {
                if (!$targetIsOccupied) {
                    continue;
                }
                if ($isTargetOwnerIdle) {
                    continue;
                }

                $isMilitaryMission = in_array($fleetMissionTypeId, $_Vars_FleetMissions['military']);

                if (!$isMilitaryMission) {
                    continue;
                }

                $wasOnlineAfterBlockadeStarted = ($props['targetOwnerDetails']['onlinetime'] >= $blockadeEntry['StartTime']);
                $wasOnlineAfterBlockadeEnded = ($props['targetOwnerDetails']['onlinetime'] >= $blockadeEntry['EndTime']);

                if (
                    ($isAllMissionsBlockade && $wasOnlineAfterBlockadeStarted) ||
                    (!$isAllMissionsBlockade && $wasOnlineAfterBlockadeEnded)
                ) {
                    continue;
                }

                return $isInvalid([
                    'blockType' => 'GLOBAL_POSTENDTIME',
                    'details' => [
                        'hardEndTime' => $blockadeEntry['PostEndTime'],
                    ],
                ]);
            }

            continue;
        }

        if ($blockadeEntry['Type'] == 2) {
            return $isInvalid([
                'blockType' => 'USER',
                'details' => [
                    'reason' => $blockadeEntry['Reason'],
                    'userId' => $blockadeEntry['ElementID'],
                    'endTime' => $blockadeEntry['EndTime'],
                ],
            ]);
        }

        if ($blockadeEntry['Type'] == 3) {
            return $isInvalid([
                'blockType' => 'PLANET',
                'details' => [
                    'reason' => $blockadeEntry['Reason'],
                    'planetId' => $blockadeEntry['ElementID'],
                    'endTime' => $blockadeEntry['EndTime'],
                ],
            ]);
        }
    }

    return $isValid();
}

?>
