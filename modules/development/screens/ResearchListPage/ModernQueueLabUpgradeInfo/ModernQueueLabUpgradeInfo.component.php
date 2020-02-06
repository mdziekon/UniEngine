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

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $planetsWithUnfinishedLabUpgrades = &$props['planetsWithUnfinishedLabUpgrades'];

    $hasPlanetsWithUnfinishedLabUpgrades = !empty($planetsWithUnfinishedLabUpgrades);

    $tplBodyCache = [
        'queue_topinfo_planetlink' => $localTemplateLoader('row_infobox_planetlink'),
        'queue_topinfo_otherplanetbox' => $localTemplateLoader('row_infobox_otherplanetbox'),
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
        'componentHTML' => parsetemplate(
            $tplBodyCache['queue_topinfo_otherplanetbox'],
            [
                'Lang_LabInQueue' => $_Lang['Queue_LabInQueue'],
                'Data_planetLinks' => $planetLinksHTML
            ]
        )
    ];
}

?>
