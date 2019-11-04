<?php

namespace UniEngine\Engine\Modules\Development\Screens\ResearchListPage\ModernQueueLabUpgradeInfo;

//  Arguments
//      - $props (Object)
//          - planetsWithUnfinishedLabUpgrades
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_Lang;

    includeLang('worldElements.detailed');

    $planetsWithUnfinishedLabUpgrades = &$props['planetsWithUnfinishedLabUpgrades'];

    $hasPlanetsWithUnfinishedLabUpgrades = !empty($planetsWithUnfinishedLabUpgrades);

    $tplBodyCache = [
        'queue_topinfo_planetlink' => gettemplate('buildings_compact_queue_planetlink'),
    ];

    if (!$hasPlanetsWithUnfinishedLabUpgrades) {
        return [
            'componentHTML' => ''
        ];
    }

    $planetLinks = array_map(
        function ($planet) use (&$tplBodyCache) {
            $subcomponentTPLData = [
                'Data_planetID' => $planet['id'],
                'Data_planetName' => $planet['name'],
                'Data_planetCoords_galaxy' => $planet['galaxy'],
                'Data_planetCoords_system' => $planet['system'],
                'Data_planetCoords_planet' => $planet['planet'],
            ];

            return parsetemplate(
                $tplBodyCache['queue_topinfo_planetlink'],
                $subcomponentTPLData
            );
        },
        $planetsWithUnfinishedLabUpgrades
    );
    $planetLinksHTML = implode('<br/>', $planetLinks);

    return [
        'componentHTML' => sprintf(
            $_Lang['Queue_LabInQueue'],
            $planetLinksHTML
        )
    ];
}

?>
