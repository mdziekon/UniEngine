<?php

namespace UniEngine\Engine\Modules\Flights\Components\FlightsList\Utils;

abstract class ViewMode {
    const Phalanx = 'ViewMode::Phalanx';
}

function _isFleetStartPhalanxEntryVisible($params) {
    return true;
}
function _isFleetHoldPhalanxEntryVisible($params) {
    /**
     * No other conditions, because the assumption is that only "holding" missions
     * have "fleet_end_stay" set to something other than 0.
     */
    return $params['flight']['fleet_mission'] != 4;
}
function _isFleetEndPhalanxEntryVisible($params) {
    if ($params['flight']['fleet_mission'] == 4) {
        return false;
    }

    return $params['isTargetOwnersFleet'];
}

/**
 * @param object $params
 * @param ViewMode $params['viewMode']
 * @param string $params['flight']
 * @param boolean $params['isTargetOwnersFleet']
 */
function isFleetStartEntryVisible($params) {
    switch ($params['viewMode']) {
        case ViewMode::Phalanx:
            return _isFleetStartPhalanxEntryVisible($params);
    }
}

/**
 * @param object $params
 * @param ViewMode $params['viewMode']
 * @param string $params['flight']
 * @param boolean $params['isTargetOwnersFleet']
 */
function isFleetHoldEntryVisible($params) {
    switch ($params['viewMode']) {
        case ViewMode::Phalanx:
            return _isFleetHoldPhalanxEntryVisible($params);
    }
}

/**
 * @param object $params
 * @param ViewMode $params['viewMode']
 * @param string $params['flight']
 * @param boolean $params['isTargetOwnersFleet']
 */
function isFleetEndEntryVisible($params) {
    switch ($params['viewMode']) {
        case ViewMode::Phalanx:
            return _isFleetEndPhalanxEntryVisible($params);
    }
}

/**
 * @param object $params
 * @param string $params['fleetId']
 * @param number $params['eventTimestamp']
 */
function createFleetSortKey($params) {
    return implode('', [
        $params['eventTimestamp'],
        str_pad($params['fleetId'], 20, '0', STR_PAD_LEFT)
    ]);
}

?>
