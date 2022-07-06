<?php

namespace UniEngine\Engine\Modules\Overview\Screens\Overview\Components\PlanetsListElement;

use UniEngine\Engine\Includes\Helpers\Planets\Queues\Structures;

/**
 * @param array $props
 * @param arrayRef $props['planet']
 * @param number $props['currentTimestamp']
 */
function render($props) {
    global $_Lang, $_SkinPath;

    $planet = &$props['planet'];
    $currentTimestamp = $props['currentTimestamp'];

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);
    $tplBodyCache = [
        'body' => $localTemplateLoader('body'),
        'queueStateBusy' => $localTemplateLoader('queueStateBusy'),
        'queueStateEmpty' => $localTemplateLoader('queueStateEmpty'),
    ];

    $queueStateHTML = null;

    if ($planet['buildQueue_firstEndTime'] > 0) {
        $buildingsQueue = Structures\parseQueueString(
            Structures\getQueueString($planet)
        );
        $firstQueueElement = $buildingsQueue[0];
        $elementId = $firstQueueElement['elementID'];
        $elementLevel = $firstQueueElement['level'];

        $queueStateHTML = parsetemplate(
            $tplBodyCache['queueStateBusy'],
            [
                'elementName' => $_Lang['tech'][$elementId],
                'elementLevel' => $elementLevel,
                'elementConstructionRestTime' => pretty_time(
                    $firstQueueElement['endTimestamp'] - $currentTimestamp
                ),
            ]
        );
    } else {
        $queueStateHTML = parsetemplate(
            $tplBodyCache['queueStateEmpty'],
            $_Lang
        );
    }

    $tplBodyParams = [
        'skinPath'          => $_SkinPath,
        'planetName'        => $planet['name'],
        'planetId'          => $planet['id'],
        'planetImg'         => $planet['image'],
        'queueStateHTML'    => $queueStateHTML,
    ];
    $tplBodyParams = array_merge($_Lang, $tplBodyParams);

    $componentHTML = parsetemplate(
        $tplBodyCache['body'],
        $tplBodyParams
    );

    return [
        'componentHTML' => $componentHTML,
    ];
}

?>
