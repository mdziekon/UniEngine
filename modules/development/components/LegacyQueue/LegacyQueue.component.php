<?php

namespace UniEngine\Engine\Modules\Development\Components\LegacyQueue;

use UniEngine\Engine\Includes\Helpers\World\Elements;

//  Arguments
//      - $props (Object)
//          - queue (Array<QueueElement>)
//              QueueElement: Object
//                  - elementID (Number)
//                  - level (Number)
//                  - duration (Number)
//                  - endTimestamp (Number)
//                  - mode (String)
//          - currentTimestamp (Number)
//          - getQueueElementCancellationLinkHref (Function: (ExtendedQueueElement) => String)
//              ExtendedQueueElement: Object
//                  - listID (Number)
//                  - elementID (Number)
//                  - level (Number)
//                  - duration (Number)
//                  - endTimestamp (Number)
//                  - mode (String)
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_Lang, $_EnginePath;

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $tplBodyCache = [
        'body' => $localTemplateLoader('queue_body'),
        'row_element_firstel' => $localTemplateLoader('queue_element_firstel'),
        'row_element_nextel' => $localTemplateLoader('queue_element_nextel')
    ];

    $queue = $props['queue'];
    $currentTimestamp = $props['currentTimestamp'];
    $getQueueElementCancellationLinkHref = $props['getQueueElementCancellationLinkHref'];

    $queueElementsTplData = [];
    $queueUnfinishedElementsCount = 0;

    foreach ($queue as $queueIdx => $queueElement) {
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
            'premblock' :
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
            'Data_BuildTimeEndFormatted'            => pretty_time($progressTimeLeft, true, 'D'),
            'Data_ElementProgressEndTimeDatepoint'  => date('d/m | H:i:s', $progressEndTime),

            'Data_RemoveElementFromQueueLinkHref'   => $getQueueElementCancellationLinkHref([
                'queueElementIdx'   => $queueIdx,
                'listID'            => $listID,
                'elementID'         => $queueElement['elementID'],
                'level'             => $queueElement['level'],
                'duration'          => $queueElement['duration'],
                'endTimestamp'      => $queueElement['endTimestamp'],
                'mode'              => $queueElement['mode']
            ]),

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

        'Queue_CantCancel_Premium' => $_Lang['Queue_CantCancel_Premium'],
        'Queue_ConfirmCancel' => $_Lang['Queue_ConfirmCancel'],
    ];

    if (!empty($queueElementsTplData)) {
        $queueElementsHTMLParts = [];

        foreach ($queueElementsTplData as $elementIdx => $queueElementTplData) {
            $queueElementTPLBody = (
                $elementIdx === 0 ?
                $tplBodyCache['row_element_firstel'] :
                $tplBodyCache['row_element_nextel']
            );

            $queueElementsHTMLParts[] = parsetemplate($queueElementTPLBody, $queueElementTplData);
        }

        $componentTPLData['Data_QueueElements'] = implode('', $queueElementsHTMLParts);
    }

    $componentTPLBody = $tplBodyCache['body'];
    $componentHTML = parsetemplate($componentTPLBody, $componentTPLData);

    return [
        'componentHTML' => $componentHTML
    ];
}

?>
