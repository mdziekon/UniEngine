<?php

namespace UniEngine\Engine\Modules\Development\Components\ModernQueue;

use UniEngine\Engine\Includes\Helpers\World\Elements;

//  Arguments
//      - $props (Object)
//          - planet (Object)
//          - queue (Array<QueueElement>)
//              QueueElement: Object
//                  - elementID (Number)
//                  - level (Number)
//                  - duration (Number)
//                  - endTimestamp (Number)
//                  - mode (String)
//          - queueMaxLength (Number)
//          - isQueueEmptyInfoHidden (Boolean | undefined)
//          - timestamp (Number)
//          - infoComponents (Array<String: componentHTML> | undefined)
//          - getQueueElementCancellationLinkHref (Function: (ExtendedQueueElement) => String)
//              ExtendedQueueElement: Object
//                  - queueElementIdx (Number)
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
    global $_Lang, $_SkinPath, $_EnginePath;

    includeLang('worldElements.detailed');

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $planet = &$props['planet'];
    $queue = $props['queue'];
    $queueMaxLength = $props['queueMaxLength'];
    $isQueueEmptyInfoHidden = (
        isset($props['isQueueEmptyInfoHidden']) ?
        $props['isQueueEmptyInfoHidden'] :
        false
    );
    $currentTimestamp = $props['timestamp'];
    $infoComponents = (
        isset($props['infoComponents']) ?
        $props['infoComponents'] :
        []
    );
    $getQueueElementCancellationLinkHref = $props['getQueueElementCancellationLinkHref'];

    $componentTPLData = [
        'queueElements' => [],
        'queueTopInfobox' => []
    ];
    $tplBodyCache = [
        'row_infobox_generic' => $localTemplateLoader('row_infobox_generic'),
        'row_element_firstel' => $localTemplateLoader('row_element_firstel'),
        'row_element_nextel' => $localTemplateLoader('row_element_nextel')
    ];

    $queueElementsTplData = [];
    $queueUnfinishedElementsCount = 0;

    foreach ($queue as $queueIdx => $queueElement) {
        if ($queueElement['endTimestamp'] < $currentTimestamp) {
            continue;
        }

        $listID = ($queueUnfinishedElementsCount + 1);
        $elementID = $queueElement['elementID'];
        $elementLevel = $queueElement['level'];
        $progressDuration = $queueElement['duration'];
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
        $elementModeLabelText = (
            $isUpgrading ?
            $_Lang['Queue_Mode_Build_1'] :
            $_Lang['Queue_Mode_Destroy_1']
        );
        $elementModeLabelColorClass = (
            $isUpgrading ?
            'lime' :
            'red'
        );
        $elementCancelButtonText = (
            $isFirstQueueElement ?
            (
                (!Elements\isCancellableOnceInProgress($elementID)) ?
                $_Lang['Queue_Cancel_CantCancel'] :
                (
                    $isUpgrading ?
                    $_Lang['Queue_Cancel_Build'] :
                    $_Lang['Queue_Cancel_Destroy']
                )
            ) :
            $_Lang['Queue_Cancel_Remove']
        );
        $elementBuildTimeLabelText = (
            $isUpgrading ?
            $_Lang['InfoBox_BuildTime'] :
            $_Lang['InfoBox_DestroyTime']
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
            'Data_SkinPath'                     => $_SkinPath,
            'Data_ListID'                       => $listID,
            'Data_ElementNo'                    => $listID,
            'Data_ElementID'                    => $elementID,
            'Data_Name'                         => $_Lang['tech'][$elementID],
            'Data_Level'                        => $elementLevel,
            'Data_PlanetID'                     => $planet['id'],
            'Data_BuildTime'                    => pretty_time($progressDuration),
            'Data_EndTimer'                     => pretty_time($progressTimeLeft, true, 'D'),
            'Data_EndTimeExpand'                => date('H:i:s', $progressEndTime),
            'Data_EndDate'                      => date('d/m | H:i:s', $progressEndTime),
            'Data_EndDateExpand'                => prettyDate('d m Y', $progressEndTime, 1),

            'Data_RemoveElementFromQueueLinkHref' => $getQueueElementCancellationLinkHref([
                'queueElementIdx'   => $queueIdx,
                'listID'            => $listID,
                'elementID'         => $queueElement['elementID'],
                'level'             => $queueElement['level'],
                'duration'          => $queueElement['duration'],
                'endTimestamp'      => $queueElement['endTimestamp'],
                'mode'              => $queueElement['mode']
            ]),

            'Data_ModeText'                     => $elementModeLabelText,
            'Data_CancelBtn_Text'               => $elementCancelButtonText,
            'Data_BuildTimeLabel'               => $elementBuildTimeLabelText,
            'Data_CancelLock_class'             => $elementCancellableClass,
            'Data_ModeColor'                    => $elementModeLabelColorClass,

            'PHPInject_ChronoAppletScriptCode'  => $elementChronoAppletScript,

            'Lang_LevelText'                    => $_Lang['level'],
            'Lang_EndText'                      => $_Lang['Queue_EndTime'],
            'Lang_EndTitleBeg'                  => $_Lang['Queue_EndTitleBeg'],
            'Lang_EndTitleHour'                 => $_Lang['Queue_EndTitleHour'],
        ];

        $queueElementsTplData[] = $queueElementTplData;

        $queueUnfinishedElementsCount += 1;
    }

    if (!empty($queueElementsTplData)) {
        foreach ($queueElementsTplData as $elementIdx => $queueElementTplData) {
            $queueElementTPLBody = (
                $elementIdx === 0 ?
                $tplBodyCache['row_element_firstel'] :
                $tplBodyCache['row_element_nextel']
            );

            $componentTPLData['queueElements'][] = parsetemplate($queueElementTPLBody, $queueElementTplData);
        }
    } else if (!$isQueueEmptyInfoHidden) {
        $componentTPLData['queueTopInfobox'][] = parsetemplate(
            $tplBodyCache['row_infobox_generic'],
            [
                'InfoText' => $_Lang['Queue_Empty']
            ]
        );
    }

    $isQueueFull = ($queueUnfinishedElementsCount >= $queueMaxLength);

    if ($isQueueFull) {
        $queueFullMsgHTML = parsetemplate(
            $tplBodyCache['row_infobox_generic'],
            [
                'InfoColor' => 'red',
                'InfoText' => $_Lang['Queue_Full']
            ]
        );

        $componentTPLData['queueTopInfobox'][] = $queueFullMsgHTML;
    }

    if (!empty($infoComponents)) {
        foreach ($infoComponents as $infoComponentHTML) {
            $componentTPLData['queueTopInfobox'][] = $infoComponentHTML;
        }
    }

    $componentHTML = (
        implode('', $componentTPLData['queueTopInfobox']) .
        implode('', $componentTPLData['queueElements'])
    );

    return [
        'componentHTML' => $componentHTML
    ];
}

?>
