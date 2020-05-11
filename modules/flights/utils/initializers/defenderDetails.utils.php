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

    if (MORALE_ENABLED) {
        if (empty($localCache['MoraleCache'][$fleetOwnerID])) {
            if (!empty($fleetCache['MoraleCache'][$fleetOwnerID])) {
                $fleetData['morale_level'] = $fleetCache['MoraleCache'][$fleetOwnerID]['level'];
                $fleetData['morale_droptime'] = $fleetCache['MoraleCache'][$fleetOwnerID]['droptime'];
                $fleetData['morale_lastupdate'] = $fleetCache['MoraleCache'][$fleetOwnerID]['lastupdate'];
            }
            Morale_ReCalculate($fleetData, $combatTimestamp);

            $localCache['MoraleCache'][$fleetOwnerID] = [
                'level' => $fleetData['morale_level'],
                'points' => $fleetData['morale_points'],
                'droptime' => $fleetData['morale_droptime'],
                'lastupdate' => $fleetData['morale_lastupdate'],
            ];
        }

        $moraleCombatModifiers = Flights\Utils\Modifiers\calculateMoraleCombatModifiers([
            'moraleLevel' => $localCache['MoraleCache'][$fleetOwnerID]['level'],
        ]);

        $combatTechnologies = array_merge(
            $combatTechnologies,
            $moraleCombatModifiers
        );
    }

    return [
        'fleetID' => $fleetData['fleet_id'],
        'fleetOwnerID' => $fleetOwnerID,
        'ships' => String2Array($fleetData['fleet_array']),
        'combatTechnologies' => $combatTechnologies,
    ];
}

?>
