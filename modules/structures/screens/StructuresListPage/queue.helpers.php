<?php

namespace UniEngine\Engine\Modules\Structures\Screens\StructuresListPage\Helpers;

use UniEngine\Engine\Includes\Helpers\Planets;

//  Arguments
//      - $props (Object)
//          - user (Object)
//          - planet (Object)
//          - timestamp (Number)
//
//  Returns: Object
//      - queuedResourcesToUse (Object<resourceKey: string, value: number>)
//      - queuedElementLevelModifiers (Object<elementID: string, levelModifier: number>)
//      - fieldsModifier (Number)
//      - unfinishedElementsCount (Number)
//
function getQueueStateDetails ($props) {
    $planet = &$props['planet'];
    $user = &$props['user'];
    $currentTimestamp = $props['timestamp'];

    $queuedResourcesToUse = [
        'metal' => 0,
        'crystal' => 0,
        'deuterium' => 0
    ];
    $queuedElementLevelModifiers = [];
    $fieldsModifierByQueuedDowngrades = 0;

    $buildingsQueue = Planets\Queues\parseStructuresQueueString($planet['buildQueue']);

    $queueUnfinishedElementsCount = 0;

    foreach ($buildingsQueue as $queueIdx => $queueElement) {
        if ($queueElement['endTimestamp'] < $currentTimestamp) {
            continue;
        }

        $elementID = $queueElement['elementID'];
        $elementLevel = $queueElement['level'];
        $isUpgrading = ($queueElement['mode'] == 'build');
        $isFirstQueueElement = ($queueIdx === 0);

        if (!$isUpgrading) {
            $elementLevel += 1;
        }

        $elementPlanetKey = _getElementPlanetKey($elementID);

        if (!$isFirstQueueElement) {
            $temporaryLevelModifier = (
                isset($queuedElementLevelModifiers[$elementID]) ?
                $queuedElementLevelModifiers[$elementID] :
                0
            );

            $planet[$elementPlanetKey] += $temporaryLevelModifier;

            $elementCost = GetBuildingPrice($user, $planet, $elementID, true, !$isUpgrading);
            $queuedResourcesToUse['metal'] += $elementCost['metal'];
            $queuedResourcesToUse['crystal'] += $elementCost['crystal'];
            $queuedResourcesToUse['deuterium'] += $elementCost['deuterium'];

            $planet[$elementPlanetKey] -= $temporaryLevelModifier;
        }

        if (!isset($queuedElementLevelModifiers[$elementID])) {
            $queuedElementLevelModifiers[$elementID] = 0;
        }

        if (!$isUpgrading) {
            $queuedElementLevelModifiers[$elementID] -= 1;
            $fieldsModifierByQueuedDowngrades += 2;
        } else {
            $queuedElementLevelModifiers[$elementID] += 1;
        }

        $queueUnfinishedElementsCount += 1;
    }

    return [
        'queuedResourcesToUse' => $queuedResourcesToUse,
        'queuedElementLevelModifiers' => $queuedElementLevelModifiers,
        'fieldsModifier' => $fieldsModifierByQueuedDowngrades,
        'unfinishedElementsCount' => $queueUnfinishedElementsCount
    ];
}

?>
