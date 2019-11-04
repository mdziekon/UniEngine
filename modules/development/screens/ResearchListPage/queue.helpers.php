<?php

namespace UniEngine\Engine\Modules\Development\Screens\ResearchListPage\Helpers;

use UniEngine\Engine\Includes\Helpers\Planets;

//  Arguments
//      - $props (Object)
//          - user (Object)
//          - planet (Object)
//              The planet where research is conducted.
//          - timestamp (Number)
//
//  Returns: Object
//      - queuedResourcesToUse (Object<resourceKey: string, value: number>)
//      - queuedElementLevelModifiers (Object<elementID: string, levelModifier: number>)
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

    $queue = Planets\Queues\Research\parseQueueString($planet['techQueue']);

    $queueUnfinishedElementsCount = 0;

    foreach ($queue as $queueIdx => $queueElement) {
        if ($queueElement['endTimestamp'] < $currentTimestamp) {
            continue;
        }

        $elementID = $queueElement['elementID'];
        $isFirstQueueElement = ($queueIdx === 0);

        $elementPlanetKey = _getElementPlanetKey($elementID);

        if (!$isFirstQueueElement) {
            $temporaryLevelModifier = (
                isset($queuedElementLevelModifiers[$elementID]) ?
                $queuedElementLevelModifiers[$elementID] :
                0
            );

            $user[$elementPlanetKey] += $temporaryLevelModifier;

            $elementCost = GetBuildingPrice($user, $planet, $elementID, true, false);
            $queuedResourcesToUse['metal'] += $elementCost['metal'];
            $queuedResourcesToUse['crystal'] += $elementCost['crystal'];
            $queuedResourcesToUse['deuterium'] += $elementCost['deuterium'];

            $user[$elementPlanetKey] -= $temporaryLevelModifier;
        }

        if (!isset($queuedElementLevelModifiers[$elementID])) {
            $queuedElementLevelModifiers[$elementID] = 0;
        }

        $queuedElementLevelModifiers[$elementID] += 1;

        $queueUnfinishedElementsCount += 1;
    }

    return [
        'queuedResourcesToUse' => $queuedResourcesToUse,
        'queuedElementLevelModifiers' => $queuedElementLevelModifiers,
        'unfinishedElementsCount' => $queueUnfinishedElementsCount
    ];
}

?>
