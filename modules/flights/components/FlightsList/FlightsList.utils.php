<?php

namespace UniEngine\Engine\Modules\Flights\Components\FlightsList\Utils;

use UniEngine\Engine\Modules\Flights\Enums;

abstract class ViewMode {
    const Phalanx = 'ViewMode::Phalanx';
    const Overview = 'ViewMode::Overview';
}

// --- Phalanx mode ---
function _isFleetStartPhalanxEntryVisible($params) {
    return true;
}
function _isFleetHoldPhalanxEntryVisible($params) {
    /**
     * No other conditions, because the assumption is that only "holding" missions
     * have "fleet_end_stay" set to something other than 0.
     */
    return $params['flight']['fleet_mission'] != Enums\FleetMission::Station;
}
function _isFleetEndPhalanxEntryVisible($params) {
    if ($params['flight']['fleet_mission'] == Enums\FleetMission::Station) {
        return false;
    }

    return $params['isTargetOwnersFleet'];
}

// --- Overview mode ---
function _isFleetStartOverviewEntryVisible($params) {
    if ($params['isViewingUserFleetOwner']) {
        return true;
    }

    // TODO: Verify this, enemy "harvest" missions probably won't be even fetched
    return $params['flight']['fleet_mission'] != Enums\FleetMission::Harvest;
}
function _isFleetHoldOverviewEntryVisible($params) {
    if ($params['isViewingUserFleetOwner']) {
        // TODO: Verify this, most likely this is redundant as no "stay" mission has hold time
        if ($params['flight']['fleet_mission'] == Enums\FleetMission::Station) {
            return false;
        }

        return true;
    }

    return $params['flight']['fleet_mission'] == Enums\FleetMission::Hold;
}
function _isFleetEndOverviewEntryVisible($params) {
    if (!$params['isViewingUserFleetOwner']) {
        return false;
    }

    if ($params['flight']['fleet_mission'] == Enums\FleetMission::Station) {
        // "stay" mission has been turned back
        return (
            $params['flight']['fleet_start_time'] < $params['currentTimestamp'] &&
            $params['flight']['fleet_end_time'] > $params['currentTimestamp']
        );
    }

    if ($params['flight']['fleet_mission'] == Enums\FleetMission::Colonize) {
        // "colonize" mission has not been calculated or is not a single ship
        return !(
            $params['flight']['fleet_mess'] == 0 &&
            $params['flight']['fleet_amount'] == 1
        );
    }

    return true;
}

/**
 * @param object $params
 * @param ViewMode $params['viewMode']
 * @param string $params['flight']
 * @param boolean $params['isViewingUserFleetOwner']
 * @param boolean $params['isTargetOwnersFleet']
 * @param number $params['currentTimestamp']
 */
function isFleetStartEntryVisible($params) {
    switch ($params['viewMode']) {
        case ViewMode::Phalanx:
            return _isFleetStartPhalanxEntryVisible($params);
        case ViewMode::Overview:
            return _isFleetStartOverviewEntryVisible($params);
    }
}

/**
 * @param object $params
 * @param ViewMode $params['viewMode']
 * @param string $params['flight']
 * @param boolean $params['isViewingUserFleetOwner']
 * @param boolean $params['isTargetOwnersFleet']
 * @param number $params['currentTimestamp']
 */
function isFleetHoldEntryVisible($params) {
    switch ($params['viewMode']) {
        case ViewMode::Phalanx:
            return _isFleetHoldPhalanxEntryVisible($params);
        case ViewMode::Overview:
            return _isFleetHoldOverviewEntryVisible($params);
    }
}

/**
 * @param object $params
 * @param ViewMode $params['viewMode']
 * @param string $params['flight']
 * @param boolean $params['isViewingUserFleetOwner']
 * @param boolean $params['isTargetOwnersFleet']
 * @param number $params['currentTimestamp']
 */
function isFleetEndEntryVisible($params) {
    switch ($params['viewMode']) {
        case ViewMode::Phalanx:
            return _isFleetEndPhalanxEntryVisible($params);
        case ViewMode::Overview:
            return _isFleetEndOverviewEntryVisible($params);
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
