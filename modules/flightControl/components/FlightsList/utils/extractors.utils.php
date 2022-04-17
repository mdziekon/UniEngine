<?php

namespace UniEngine\Engine\Modules\FlightControl\Components\FlightsList\Utils;

function extractRelatedFleetsFromAcsUnions($acsUnions) {
    $relatedFleets = [];

    array_walk($acsUnions, function ($acsUnion) use (&$relatedFleets) {
        if (empty($acsUnion['fleets_id'])) {
            return;
        }

        $mainFleetId = $acsUnion['main_fleet_id'];
        $fleetIds = explode(',', str_replace('|', '', $acsUnion['fleets_id']));

        foreach ($fleetIds as $fleetId) {
            $relatedFleets[] = [
                'fleetId' => $fleetId,
                'mainFleetId' => $mainFleetId,
            ];
        }
    });

    return $relatedFleets;
}

//  Arguments
//      - $params (Object)
//          - relatedAcsFleets (Object[])
//          - fleetsBaseDetails (Object[])
//
function extractAcsUnionsExtraSquads($params) {
    $acsUnions = [];

    foreach ($params['relatedAcsFleets'] as $relatedAcsFleet) {
        $fleetId = $relatedAcsFleet['fleet_id'];
        $fleetBaseDetails = array_find($params['fleetsBaseDetails'], function ($entry) use ($fleetId) {
            return $entry['fleetId'] == $fleetId;
        });

        if (empty($fleetBaseDetails)) {
            continue;
        }

        $mainAcsFleetId = $fleetBaseDetails['mainFleetId'];

        if (empty($acsUnions[$mainAcsFleetId])) {
            $acsUnions[$mainAcsFleetId] = [];
        }

        $acsUnions[$mainAcsFleetId][] = [
            'array' => String2Array($relatedAcsFleet['fleet_array']),
            'count' => $relatedAcsFleet['fleet_amount'],
        ];
    }

    return $acsUnions;
}

?>
