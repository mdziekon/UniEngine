<?php

namespace UniEngine\Engine\Modules\FlightControl\Components\FlightsList\Utils;

use UniEngine\Engine\Modules\Flights;

/**
 * @param object $params
 * @param object $params['fleetEntry'] Fleet or fleet-like (acs) entry
 * @param number $params['currentTimestamp']
 */
function getFleetBehaviorDetails($params) {
    global $_Lang;

    $fleetEntry = $params['fleetEntry'];
    $currentTimestamp = $params['currentTimestamp'];

    if ($fleetEntry['fleet_start_time'] >= $currentTimestamp) {
        return [
            'behavior' => $_Lang['fl_get_to_ttl'],
            'behaviorTxt' => $_Lang['fl_get_to'],
        ];
    }

    if (
        $fleetEntry['fleet_end_stay'] > 0 &&
        $fleetEntry['fleet_end_stay'] > $currentTimestamp
    ) {
        $isMissionExpedition = $fleetEntry['fleet_mission'] == Flights\Enums\FleetMission::Expedition;

        return [
            'behavior' => (
                $isMissionExpedition ?
                    $_Lang['fl_explore_to_ttl'] :
                    $_Lang['fl_stay_to_ttl']
            ),
            'behaviorTxt' => (
                $isMissionExpedition ?
                    $_Lang['fl_explore_to'] :
                    $_Lang['fl_stay_to']
            ),
        ];
    }

    if ($fleetEntry['fleet_end_time'] > $currentTimestamp) {
        return [
            'behavior' => $_Lang['fl_back_to_ttl'],
            'behaviorTxt' => $_Lang['fl_back_to'],
        ];
    }

    return [
        'behavior' => $_Lang['fl_cameback_ttl'],
        'behaviorTxt' => $_Lang['fl_cameback'],
    ];
}

?>
