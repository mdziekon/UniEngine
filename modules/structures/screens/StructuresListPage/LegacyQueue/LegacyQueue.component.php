<?php

namespace UniEngine\Engine\Modules\Structures\Screens\StructuresListPage\LegacyQueue;

use UniEngine\Engine\Includes\Helpers\World\Elements;
use UniEngine\Engine\Includes\Helpers\Planets;

//  Arguments
//      - $props (Object)
//          - planet (Object)
//          - currentTimestamp (Number)
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_Lang, $_EnginePath;

    $tplBodyCache = [
        'queue_body' => gettemplate('buildings_legacy_queue_body'),
        'queue_element_first_body' => gettemplate('buildings_legacy_queue_element_first_body'),
        'queue_element_next_body' => gettemplate('buildings_legacy_queue_element_next_body'),
    ];

    $planet = $props['planet'];
    $currentTimestamp = $props['currentTimestamp'];

    $planetID = $planet['id'];

    $queueElements = Planets\Queues\parseStructuresQueueString($planet['buildQueue']);

    $queueElementsTplData = [];
    $queueUnfinishedElementsCount = 0;

    foreach ($queueElements as $queueIdx => $queueElement) {
        if ($queueElement['endTimestamp'] < $currentTimestamp) {
            continue;
        }

        $listID = ($queueUnfinishedElementsCount + 1);
        $elementID = $queueElement['elementID'];
        $elementLevel = $queueElement['level'];
        $progressEndTime = $queueElement['endTimestamp'];
        $progressTimeLeft = $progressEndTime - $currentTimestamp;
        $isUpgrading = ($queueElement['mode'] == 'build');
        $isFirstQueueElement = ($queueIdx === 0);

        if (!$isUpgrading) {
            $elementLevel += 1;
        }

        $elementCancellableClass = (
            !Elements\isCancellableOnceInProgress($elementID) ?
            'queue_element_cancel_blocked red' :
            ''
        );

        $hideIsDowngradeLabelClass = (
            $isUpgrading ?
            'hide' :
            ''
        );

        $elementChronoAppletScript = '';

        if ($isFirstQueueElement) {
            include_once($_EnginePath . '/includes/functions/InsertJavaScriptChronoApplet.php');

            $elementChronoAppletScript = InsertJavaScriptChronoApplet(
                'QueueFirstTimer',
                '',
                $progressEndTime,
                true,
                false,
                'function() { onQueuesFirstElementFinished(); }'
            );
        }

        $queueElementTplData = [
            'Data_ListID'                           => $listID,
            'Data_ElementName'                      => $_Lang['tech'][$elementID],
            'Data_ElementLevel'                     => $elementLevel,
            'Data_PlanetID'                         => $planetID,
            'Data_BuildTimeEndFormatted'            => pretty_time($progressTimeLeft, true),
            'Data_ElementProgressEndTimeDatepoint'  => date('d/m | H:i:s', $progressEndTime),
            'Data_ElementCancellableClass'          => $elementCancellableClass,

            'Data_HideIsDowngradeLabelClass'        => $hideIsDowngradeLabelClass,

            'PHPInject_ChronoAppletScriptCode'      => $elementChronoAppletScript,

            'Lang_Level'                            => $_Lang['level'],
            'Lang_DowngradeLabel'                   => $_Lang['destroy'],
            'Lang_DeleteFirstElement'               => $_Lang['DelFirstQueue'],
            'Lang_DeleteNextElement'                => $_Lang['DelFromQueue']
        ];

        $queueElementsTplData[] = $queueElementTplData;

        $queueUnfinishedElementsCount += 1;
    }

    $componentTPLData = [
        'Data_QueueElements' => '',
    ];

    if (!empty($queueElementsTplData)) {
        $queueElementsHTMLParts = [];

        foreach ($queueElementsTplData as $elementIdx => $queueElementTplData) {
            $queueElementTPLBody = (
                $elementIdx === 0 ?
                $tplBodyCache['queue_element_first_body'] :
                $tplBodyCache['queue_element_next_body']
            );

            $queueElementsHTMLParts[] = parsetemplate($queueElementTPLBody, $queueElementTplData);
        }

        $componentTPLData['Data_QueueElements'] = implode('', $queueElementsHTMLParts);
    }

    $componentTPLBody = $tplBodyCache['queue_body'];
    $componentHTML = parsetemplate($componentTPLBody, $componentTPLData);

    return [
        'componentHTML' => $componentHTML
    ];
}

?>
