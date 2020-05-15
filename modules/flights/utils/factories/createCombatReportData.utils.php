<?php

namespace UniEngine\Engine\Modules\Flights\Utils\Factories;

use UniEngine\Engine\Modules\Flights;
use UniEngine\Engine\Includes\Helpers\Common\Collections;
use UniEngine\Engine\Includes\Helpers\World\Resources;

/**
 * @param array $params
 * @param array $params['fleetRow']
 * @param array $params['targetPlanet']
 * @param array $params['usersData']
 * @param array $params['usersData']['attackers']
 *              See: _packUserData() @arg $params
 * @param array $params['usersData']['defenders']
 *              See: _packUserData() @arg $params
 * @param array $params['combatData']
 * @param number $params['combatCalculationTime']
 * @param array | null $params['moraleData']
 * @param array $params['totalResourcesPillage']
 * @param array $params['resourceLosses']
 * @param array $params['resourceLosses']['attackers']
 * @param array $params['resourceLosses']['defenders']
 * @param array $params['moonCreationData']
 * @param boolean $params['moonCreationData']['hasBeenCreated']
 * @param number $params['moonCreationData']['normalizedChance']
 * @param number $params['moonCreationData']['totalChance']
 * @param array | null $params['moonDestructionData']
 * @param boolean $params['moonDestructionData']['hasDestroyedMoon']
 * @param boolean $params['moonDestructionData']['hasDestroyedFleet']
 * @param number $params['moonDestructionData']['moonDestructionChance']
 * @param number $params['moonDestructionData']['fleetDestructionChance']
 */
function createCombatReportData($params) {
    $fleetRow = $params['fleetRow'];
    $targetPlanet = $params['targetPlanet'];
    $usersData = $params['usersData'];
    $combatData = $params['combatData'];
    $moraleData = (
        !empty($params['moraleData']) ?
            $params['moraleData'] :
            null
    );
    $totalResourcesPillage = $params['totalResourcesPillage'];
    $resourceLosses = $params['resourceLosses'];
    $moonCreationData = $params['moonCreationData'];
    $moonDestructionData = (
        isset($params['moonDestructionData']) ?
            $params['moonDestructionData'] :
            [
                'hasDestroyedMoon' => false,
                'hasDestroyedFleet' => false,
                'moonDestructionChance' => 0,
                'fleetDestructionChance' => 0,
            ]
    );

    $isCombatOnMoon = ($fleetRow['fleet_end_type'] == 3);
    $roundsData = _packRoundsData($combatData['rounds']);

    $debrisField = Collections\mapEntries(
        Resources\getKnownDebrisRecoverableResourceKeys(),
        function ($resourceKey) use ($resourceLosses) {
            $resourceRecoverableLosses = array_map(
                function ($groupResourceLosses) use ($resourceKey) {
                    return $groupResourceLosses['recoverableLoss'][$resourceKey];
                },
                $resourceLosses
            );

            return [
                $resourceKey,
                array_sum($resourceRecoverableLosses),
            ];
        }
    );
    $combatGroupRealLosses = Collections\map(
        $resourceLosses,
        function ($groupResourceLosses) {
            return array_sum($groupResourceLosses['realLoss']);
        }
    );

    $reportData = [
        'init' => [
            'usr' => [
                'atk' => _packUsersReportData([ 'users' => $usersData['attackers'] ]),
                'def' => _packUsersReportData([ 'users' => $usersData['defenders'] ]),
            ],
            'time' => $params['combatCalculationTime'],
            'date' => $fleetRow['fleet_start_time'],
            'planet_name' => $targetPlanet['name'],
            'onMoon' => $isCombatOnMoon,

            'result' => $combatData['result'],

            'met' => $totalResourcesPillage['metal'],
            'cry' => $totalResourcesPillage['crystal'],
            'deu' => $totalResourcesPillage['deuterium'],

            'deb_met' => $debrisField['metal'],
            'deb_cry' => $debrisField['crystal'],

            'moon_created' => $moonCreationData['hasBeenCreated'],
            'moon_chance' => $moonCreationData['normalizedChance'],
            'total_moon_chance' => $moonCreationData['totalChance'],

            'moon_destroyed' => $moonDestructionData['hasDestroyedMoon'],
            'moon_des_chance' => $moonDestructionData['moonDestructionChance'],
            'fleet_destroyed' => $moonDestructionData['hasDestroyedFleet'],
            'fleet_des_chance' => $moonDestructionData['fleetDestructionChance'],

            'atk_lost' => $combatGroupRealLosses['attackers'],
            'def_lost' => $combatGroupRealLosses['defenders'],
        ],
        'morale' => $moraleData,
        'rounds' => $roundsData,
    ];

    return $reportData;
}

function _packRoundsData($roundsData) {
    $packedRoundsData = $roundsData;

    foreach ($roundsData as $roundNo => $roundData) {
        foreach ($roundData as $usersType => $usersRoundData) {
            if (empty($usersRoundData['ships'])) {
                continue;
            }

            foreach ($usersRoundData['ships'] as $userID => $userShipsData) {
                $packedRoundsData[$roundNo][$usersType]['ships'][$userID] = Array2String($userShipsData);
            }
        }
    }

    return $packedRoundsData;
}

/**
 * @param array $params
 * @param array $params['users']
 *              See: _packUserData() @arg $params
 */
function _packUsersReportData($params) {
    return Collections\map(
        $params['users'],
        function ($userData) {
            return _packUserData($userData);
        }
    );
}

/**
 * @param array $params
 * @param array $params['fleetRow']
 * @param array $params['user']
 * @param array $params['moraleData']
 */
function _packUserData($params) {
    $fleetRow = $params['fleetRow'];
    $user = $params['user'];
    $moraleData = $params['moraleData'];

    $userCombatTechs = Flights\Utils\Initializers\initCombatTechnologiesMap([
        'user' => $user,
    ]);

    $packedUserData = [
        'id' => $fleetRow['fleet_owner'],
        'username' => $user['username'],
        'techs' => Array2String($userCombatTechs),
        'pos' => "{$fleetRow['fleet_start_galaxy']}:{$fleetRow['fleet_start_system']}:{$fleetRow['fleet_start_planet']}",
    ];

    if (!empty($user['ally_tag'])) {
        $packedUserData['ally'] = $user['ally_tag'];
    }

    if (MORALE_ENABLED) {
        $packedUserData['morale'] = $moraleData['morale_level'];
        $packedUserData['moralePoints'] = $moraleData['morale_points'];
    }

    return $packedUserData;
}

?>
