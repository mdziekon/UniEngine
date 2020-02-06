<?php

namespace UniEngine\Engine\Modules\Development\Screens\ResearchListPage\ModernQueuePlanetInfo;

//  Arguments
//      - $props (Object)
//          - currentPlanet (Object)
//          - researchPlanet (Object)
//          - queue (Array<QueueElement>)
//              QueueElement: Object
//                  - elementID (Number)
//                  - level (Number)
//                  - duration (Number)
//                  - endTimestamp (Number)
//                  - mode (String)
//          - timestamp (Number)
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_Lang, $_SkinPath;

    includeLang('worldElements.detailed');

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $currentPlanet = &$props['currentPlanet'];
    $researchPlanet = &$props['researchPlanet'];
    $queue = $props['queue'];
    $currentTimestamp = $props['timestamp'];

    $isResearchConductedOnCurrentPlanet = ($currentPlanet['id'] === $researchPlanet['id']);

    $tplBodyCache = [
        'queue_topinfo_researchplanet' => $localTemplateLoader('row_infobox_researchplanet'),
    ];

    $queueUnfinishedElementsCount = 0;

    foreach ($queue as $queueElement) {
        if ($queueElement['endTimestamp'] < $currentTimestamp) {
            continue;
        }

        $queueUnfinishedElementsCount += 1;
    }

    if ($queueUnfinishedElementsCount === 0) {
        return [
            'componentHTML' => ''
        ];
    }

    $componentTPLData = [
        'Data_planetID'                 => $researchPlanet['id'],
        'Data_planetImg'                => $researchPlanet['image'],
        'Data_planetName'               => $researchPlanet['name'],
        'Data_planetLabelColorClass'    => (
            $isResearchConductedOnCurrentPlanet ?
            'lime' :
            'orange'
        ),
        'Data_planetCoords_galaxy'      => $researchPlanet['galaxy'],
        'Data_planetCoords_system'      => $researchPlanet['system'],
        'Data_planetCoords_planet'      => $researchPlanet['planet'],

        'Const_SkinPath'                => $_SkinPath,
        'Lang_ResearchOn'               => $_Lang['Queue_ResearchOn'],
    ];

    $componentHTML = parsetemplate(
        $tplBodyCache['queue_topinfo_researchplanet'],
        $componentTPLData
    );

    return [
        'componentHTML' => $componentHTML
    ];
}

?>
