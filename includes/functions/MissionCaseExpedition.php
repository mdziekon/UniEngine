<?php

use UniEngine\Engine\Modules\Flights;

function MissionCaseExpedition($fleetRow, &$_FleetCache) {
    global $UserDev_Log, $_Lang, $UserStatsData;

    /**
     * @param array $params
     * @param array $params['messageData']
     * @param EnumValue $params['fleetTimeMoment']
     */
    $sendExpeditionMessage = function ($params) use ($fleetRow) {
        $CONST_MESSAGES_SENDERID = '003';
        $CONST_MESSAGES_TITLEID = '007';

        $timestamp = null;

        switch ($params['fleetTimeMoment']) {
            case 'expeditionBeginning':
                $timestamp = $fleetRow['fleet_start_time'];
                break;
            case 'expeditionFinished':
                $timestamp = $fleetRow['fleet_end_stay'];
                break;
            case 'expeditionBackHome':
                $timestamp = $fleetRow['fleet_end_time'];
                break;
        }

        Cache_Message(
            $fleetRow['fleet_owner'],
            0,
            $timestamp,
            15,
            $CONST_MESSAGES_SENDERID,
            $CONST_MESSAGES_TITLEID,
            json_encode($params['messageData'])
        );
    };

    $result = [
        'FleetsToDelete' => [],
        'FleetArchive' => [],
    ];
    $calculationTimestamp = time();

    $thisFleetID = $fleetRow['fleet_id'];
    $thisFleetOwnerID = $fleetRow['fleet_owner'];

    if ($fleetRow['calcType'] == 1) {
        // Exploration just started, nothing to do here except for a couple of updates

        $result['FleetArchive'][$thisFleetID]['Fleet_Calculated_Mission'] = true;
        $result['FleetArchive'][$thisFleetID]['Fleet_Calculated_Mission_Time'] = $calculationTimestamp;

        $_FleetCache['fleetRowUpdate'][$thisFleetID]['fleet_mess'] = 1;
        $_FleetCache['fleetRowStatus'][$thisFleetID]['calcCounter'] += 1;
        $_FleetCache['fleetRowStatus'][$thisFleetID]['needUpdate'] = true;

        $sendExpeditionMessage([
            'messageData' => [
                'msg_id' => '106',
                'args' => [
                    $fleetRow['fleet_start_galaxy'],
                    $fleetRow['fleet_start_system'],
                    $fleetRow['fleet_start_galaxy'],
                    $fleetRow['fleet_start_system'],
                    (($fleetRow['fleet_end_stay'] - $fleetRow['fleet_start_time']) / TIME_HOUR)
                ],
            ],
            'fleetTimeMoment' => 'expeditionBeginning'
        ]);
    }

    if ($fleetRow['calcType'] == 2) {
        // Exploration has finished, create outcome event and apply to the FleetRow

        $fleetUpdateEntriesByID = [];
        $fleetsToDeleteByID = [];
        $fleetOwnerDevelopmentLogEntries = [];

        $_FleetCache['fleetRowUpdate'][$thisFleetID]['fleet_mess'] = 2;
        $_FleetCache['fleetRowStatus'][$thisFleetID]['calcCounter'] += 1;
        $_FleetCache['fleetRowStatus'][$thisFleetID]['needUpdate'] = true;

        if (empty($UserStatsData[$thisFleetOwnerID])) {
            $UserStatsData[$thisFleetOwnerID] = Flights\Utils\Initializers\initUserStatsMap();
        }
        $UserStatsData[$thisFleetOwnerID]['other_expeditions_count'] += 1;



        $expeditionEvent = Flights\Utils\Helpers\getRandomExpeditionEvent([]);
        $expeditionOutcome = Flights\Utils\Helpers\getExpeditionEventOutcome([
            'event' => $expeditionEvent
        ]);
        $expeditionFinalOutcome = $expeditionOutcome;

        $preExpeditionShips = String2Array($fleetRow['fleet_array']);
        $postExpeditionShips = String2Array($fleetRow['fleet_array']);

        $gainedResources = [];

        if (!empty($expeditionOutcome['gains']['planetaryResources'])) {
            $fleetPillageStorage = Flights\Utils\Calculations\calculatePillageStorage([
                'fleetRow' => $fleetRow,
                'ships' => $postExpeditionShips,
            ]);

            $resourcesPillage = Flights\Utils\Missions\calculateEvenResourcesPillage([
                'maxPillagePerResource' => Flights\Utils\Missions\calculateMaxPlanetPillage([
                    'planet' => $expeditionOutcome['gains']['planetaryResources'],
                    'maxPillagePercentage' => 1,
                ]),
                'fleetTotalStorage' => $fleetPillageStorage,
            ]);

            $gainedResources = $resourcesPillage;
            $expeditionFinalOutcome['gains']['planetaryResources'] = $resourcesPillage;
        }

        if (empty($postExpeditionShips)) {
            $fleetsToDeleteByID[] = $thisFleetID;

            $gainedResources = [];
            $expeditionFinalOutcome['gains']['planetaryResources'] = [];
        } else {
            $fleetUpdateEntriesByID[$thisFleetID] = Flights\Utils\Factories\createFleetUpdateEntry([
                'fleetID' => $thisFleetID,
                'state' => '2',
                'originalShips' => $preExpeditionShips,
                'postCombatShips' => $postExpeditionShips,
                'resourcesPillage' => $gainedResources,
            ]);
        }

        if (!empty($gainedResources)) {
            $result['FleetArchive'][$thisFleetID]['Fleet_End_Res_Metal'] = $gainedResources['metal'];
            $result['FleetArchive'][$thisFleetID]['Fleet_End_Res_Crystal'] = $gainedResources['crystal'];
            $result['FleetArchive'][$thisFleetID]['Fleet_End_Res_Deuterium'] = $gainedResources['deuterium'];
        }

        $fleetOwnerDevelopmentLogEntries[] = Flights\Utils\Factories\createFleetDevelopmentLogEntries([
            'originalShips' => $preExpeditionShips,
            'postCombatShips' => $postExpeditionShips,
            'resourcesPillage' => $gainedResources,
        ]);


        if (!empty($fleetOwnerDevelopmentLogEntries)) {
            foreach ($fleetOwnerDevelopmentLogEntries as $logEntry) {
                if (empty($logEntry)) {
                    continue;
                }

                $UserDev_Log[] = [
                    'UserID' => $thisFleetOwnerID,
                    'PlanetID' => '0',
                    'Date' => $fleetRow['fleet_end_stay'],
                    'Place' => 31,
                    'Code' => '0',
                    'ElementID' => $thisFleetID,
                    'AdditionalData' => implode(';', $logEntry)
                ];
            }
        }

        foreach ($fleetUpdateEntriesByID as $fleetID => $fleetUpdateEntry) {
            $serializedFleetArray = Array2String($fleetUpdateEntry['fleet_array']);
            $serializedFleetArrayLost = Array2String($fleetUpdateEntry['fleet_array_lost']);

            if (
                !empty($fleetUpdateEntry['fleet_array']) &&
                !empty($fleetUpdateEntry['fleet_array_lost'])
            ) {
                if (strlen($serializedFleetArray) > strlen($serializedFleetArrayLost)) {
                    $result['FleetArchive'][$fleetID]['Fleet_Array_Changes'] = "\"+D;{$serializedFleetArrayLost}|\"";
                } else {
                    $result['FleetArchive'][$fleetID]['Fleet_Array_Changes'] = "\"+L;{$serializedFleetArray}|\"";
                }

                $result['FleetArchive'][$fleetID]['Fleet_Info_HasLostShips'] = '!true';
            }

            if (
                $_FleetCache['fleetRowStatus'][$thisFleetID]['calcCount'] > $_FleetCache['fleetRowStatus'][$thisFleetID]['calcCounter']
            ) {
                // We know that we'll perform the "fleet returned to planet" event
                // in the same Fleets Handling run, so stash the result in local cache,
                // instead of sending entry to be persisted as fleet row update in DB.
                // `fleetRowUpdate` operates on real values.

                $CachePointer = &$_FleetCache['fleetRowUpdate'][$fleetID];
                $CachePointer['fleet_array'] = $serializedFleetArray;
                $CachePointer['fleet_amount'] = $fleetUpdateEntry['fleet_amount'];
                $CachePointer['fleet_mess'] = $fleetUpdateEntry['fleet_mess'];
                $CachePointer['fleet_resource_metal'] = (
                    $fleetRow['fleet_resource_metal'] +
                    $fleetUpdateEntry['fleet_resource_metal']
                );
                $CachePointer['fleet_resource_crystal'] = (
                    $fleetRow['fleet_resource_crystal'] +
                    $fleetUpdateEntry['fleet_resource_crystal']
                );
                $CachePointer['fleet_resource_deuterium'] = (
                    $fleetRow['fleet_resource_deuterium'] +
                    $fleetUpdateEntry['fleet_resource_deuterium']
                );
            } else {
                // Create UpdateFleet record for $_FleetCache
                // `updateFleets` operates on real values or on "offsets".

                $CachePointer = &$_FleetCache['updateFleets'][$fleetID];
                $CachePointer['fleet_array'] = $serializedFleetArray;
                $CachePointer['fleet_amount'] = $fleetUpdateEntry['fleet_amount'];
                $CachePointer['fleet_mess'] = $fleetUpdateEntry['fleet_mess'];
                $CachePointer['fleet_resource_metal'] = (
                    isset($CachePointer['fleet_resource_metal']) ?
                    $CachePointer['fleet_resource_metal'] + $fleetUpdateEntry['fleet_resource_metal'] :
                    $fleetUpdateEntry['fleet_resource_metal']
                );
                $CachePointer['fleet_resource_crystal'] = (
                    isset($CachePointer['fleet_resource_crystal']) ?
                    $CachePointer['fleet_resource_crystal'] + $fleetUpdateEntry['fleet_resource_crystal'] :
                    $fleetUpdateEntry['fleet_resource_crystal']
                );
                $CachePointer['fleet_resource_deuterium'] = (
                    isset($CachePointer['fleet_resource_deuterium']) ?
                    $CachePointer['fleet_resource_deuterium'] + $fleetUpdateEntry['fleet_resource_deuterium'] :
                    $fleetUpdateEntry['fleet_resource_deuterium']
                );
            }
        }

        foreach ($fleetsToDeleteByID as $fleetID) {
            $_FleetCache['fleetRowStatus'][$fleetID]['isDestroyed'] = true;

            if (!empty($_FleetCache['updateFleets'][$fleetID])) {
                unset($_FleetCache['updateFleets'][$fleetID]);
            }

            $result['FleetsToDelete'][] = $fleetID;
            $result['FleetArchive'][$fleetID]['Fleet_Destroyed'] = true;
            $result['FleetArchive'][$fleetID]['Fleet_Info_HasLostShips'] = true;
            $result['FleetArchive'][$fleetID]['Fleet_Destroyed_Reason'] = Flights\Enums\FleetDestructionReason::ONEXPEDITION_UNKNOWN;
        }

        $sendExpeditionMessage([
            'messageData' => Flights\Utils\Missions\Expeditions\createEventMessage([
                'eventType' => $expeditionEvent,
                'eventFinalOutcome' => $expeditionFinalOutcome,
            ]),
            'fleetTimeMoment' => 'expeditionFinished'
        ]);
    }

    if (
        $fleetRow['calcType'] == 3 &&
        (
            !isset($_FleetCache['fleetRowStatus'][$thisFleetID]['isDestroyed']) ||
            $_FleetCache['fleetRowStatus'][$thisFleetID]['isDestroyed'] !== true
        )
    ) {
        // Fleet has returned from exploration, restore to planet if not destroyed

        if (
            isset($_FleetCache['fleetRowStatus'][$thisFleetID]['needUpdate']) &&
            $_FleetCache['fleetRowStatus'][$thisFleetID]['needUpdate'] === true &&
            !empty($_FleetCache['fleetRowUpdate'][$thisFleetID])
        ) {
            foreach ($_FleetCache['fleetRowUpdate'][$thisFleetID] as $Key => $Value) {
                $fleetRow[$Key] = $Value;
            }
        }

        $result['FleetsToDelete'][] = $thisFleetID;
        $result['FleetArchive'][$thisFleetID]['Fleet_Calculated_ComeBack'] = true;
        $result['FleetArchive'][$thisFleetID]['Fleet_Calculated_ComeBack_Time'] = $calculationTimestamp;

        RestoreFleetToPlanet($fleetRow, true, $_FleetCache);

        $_FleetCache['fleetRowStatus'][$thisFleetID]['calcCounter'] += 1;
        $_FleetCache['fleetRowStatus'][$thisFleetID]['needUpdate'] = false;

        $sendExpeditionMessage([
            'messageData' => [
                'msg_id' => '107',
                'args' => [
                    ($fleetRow['fleet_start_type'] == 1 ? $_Lang['to_planet'] : $_Lang['to_moon']),
                    $fleetRow['attacking_planet_name'],
                    $fleetRow['fleet_start_galaxy'],
                    $fleetRow['fleet_start_system'],
                    $fleetRow['fleet_start_galaxy'],
                    $fleetRow['fleet_start_system'],
                    $fleetRow['fleet_start_planet'],
                ],
            ],
            'fleetTimeMoment' => 'expeditionBackHome'
        ]);
    }

    if ($_FleetCache['fleetRowStatus'][$thisFleetID]['calcCounter'] == $_FleetCache['fleetRowStatus'][$thisFleetID]['calcCount']) {
        if ($fleetRow['calcType'] != 3) {
            // TODO: How about moving that to a helper function?
            if (
                isset($_FleetCache['fleetRowStatus'][$thisFleetID]['needUpdate']) &&
                $_FleetCache['fleetRowStatus'][$thisFleetID]['needUpdate'] === true &&
                !empty($_FleetCache['fleetRowUpdate'][$thisFleetID])
            ) {
                foreach ($_FleetCache['fleetRowUpdate'][$thisFleetID] as $Key => $Value) {
                    $fleetRow[$Key] = $Value;
                }
            }

            $_FleetCache['updateFleets'][$thisFleetID]['fleet_mess'] = $fleetRow['fleet_mess'];
        }
    }

    return $result;
}

?>
