<?php

namespace UniEngine\Engine\Modules\Flights\Utils\Initializers;

use UniEngine\Engine\Modules\Flights;

/**
 * @param array $params
 * @param string $params['combatTimestamp']
 * @param array $params['fleetData']
 * @param ref $params['fleetCache']
 * @param ref $params['localCache']
 */
function initDefenderDetails($params) {
    $combatTimestamp = $params['combatTimestamp'];
    $fleetData = $params['fleetData'];
    $fleetOwnerID = $fleetData['fleet_owner'];
    $fleetCache = &$params['fleetCache'];
    $localCache = &$params['localCache'];

    $combatTechnologies = Flights\Utils\Initializers\initCombatTechnologiesMap([
        'user' => $fleetData,
    ]);
    $userData = [
        'id' => $fleetOwnerID,
        'username' => $fleetData['username'],
        'techs' => Array2String($combatTechnologies),
        'pos' => _createFleetCoordinatesString($fleetData),
    ];

    if (!empty($fleetData['ally_tag'])) {
        $userData['ally'] = $fleetData['ally_tag'];
    }

    if (MORALE_ENABLED) {
        if (empty($localCache['MoraleCache'][$fleetOwnerID])) {
            if (!empty($fleetCache['MoraleCache'][$fleetOwnerID])) {
                $fleetData['morale_level'] = $fleetCache['MoraleCache'][$fleetOwnerID]['level'];
                $fleetData['morale_droptime'] = $fleetCache['MoraleCache'][$fleetOwnerID]['droptime'];
                $fleetData['morale_lastupdate'] = $fleetCache['MoraleCache'][$fleetOwnerID]['lastupdate'];
            }
            Morale_ReCalculate($fleetData, $combatTimestamp);
            $userData['morale'] = $fleetData['morale_level'];
            $userData['moralePoints'] = $fleetData['morale_points'];

            $localCache['MoraleCache'][$fleetOwnerID] = [
                'level' => $fleetData['morale_level'],
                'points' => $fleetData['morale_points']
            ];
        } else {
            $userData['morale'] = $localCache['MoraleCache'][$fleetOwnerID]['level'];
            $userData['moralePoints'] = $localCache['MoraleCache'][$fleetOwnerID]['points'];
        }

        $moraleCombatModifiers = Flights\Utils\Modifiers\calculateMoraleCombatModifiers([
            'moraleLevel' => $userData['morale'],
        ]);

        $combatTechnologies = array_merge(
            $combatTechnologies,
            $moraleCombatModifiers
        );
    }

    return [
        'fleetID' => $fleetData['fleet_id'],
        'ships' => String2Array($fleetData['fleet_array']),
        'combatTechnologies' => $combatTechnologies,
        'userData' => $userData,
    ];
}

function _createFleetCoordinatesString($fleetData) {
    return "{$fleetData['fleet_start_galaxy']}:{$fleetData['fleet_start_system']}:{$fleetData['fleet_start_planet']}";
}

?>
