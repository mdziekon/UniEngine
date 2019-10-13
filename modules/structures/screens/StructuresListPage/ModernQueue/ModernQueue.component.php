<?php

namespace UniEngine\Engine\Modules\Structures\Screens\StructuresListPage\ModernQueue;

use UniEngine\Engine\Includes\Helpers\World\Elements;
use UniEngine\Engine\Includes\Helpers\Planets;
use UniEngine\Engine\Includes\Helpers\Users;

//  Arguments
//      - $props (Object)
//          - user (Object)
//          - planet (Object)
//          - timestamp (Number)
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_Lang, $_SkinPath, $_EnginePath;

    includeLang('worldElements.detailed');

    $planet = &$props['planet'];
    $user = &$props['user'];
    $currentTimestamp = $props['timestamp'];

    $componentTPLData = [
        'queueElements' => [],
        'queueTopInfobox' => ''
    ];
    $tplBodyCache = [
        'queue_topinfo' => gettemplate('buildings_compact_queue_topinfo'),
        'queue_elements_first' => gettemplate('buildings_compact_queue_firstel'),
        'queue_elements_next' => gettemplate('buildings_compact_queue_nextel')
    ];

    $buildingsQueue = Planets\Queues\parseStructuresQueueString($planet['buildQueue']);

    $queueElementsTplData = [];
    $queueUnfinishedElementsCount = 0;

    foreach ($buildingsQueue as $queueIdx => $queueElement) {
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

        $queueElementTplData = [
            'ListID'                => $listID,
            'ElementNo'             => $listID,
            'ElementID'             => $elementID,
            'Name'                  => $_Lang['tech'][$elementID],
            'Level'                 => $elementLevel,
            'PlanetID'              => $planet['id'],
            'BuildTime'             => pretty_time($progressDuration),
            'EndTimer'              => pretty_time($progressTimeLeft, true, 'D'),
            'EndTimeExpand'         => date('H:i:s', $progressEndTime),
            'EndDate'               => date('d/m | H:i:s', $progressEndTime),
            'EndDateExpand'         => prettyDate('d m Y', $progressEndTime, 1),

            'ChronoAppletScript'    => '',
            'Data_CancelLock_class' => (
                Elements\isCancellableOnceInProgress($elementID) ?
                '' :
                'premblock'
            ),
            'ModeText'              => (
                $isUpgrading ?
                $_Lang['Queue_Mode_Build_1'] :
                $_Lang['Queue_Mode_Destroy_1']
            ),
            'ModeColor'             => (
                $isUpgrading ?
                'lime' :
                'red'
            ),
            'Lang_CancelBtn_Text'   => (
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
            ),
            'InfoBox_BuildTime'     => (
                $isUpgrading ?
                $_Lang['InfoBox_BuildTime'] :
                $_Lang['InfoBox_DestroyTime']
            ),

            'SkinPath'              => $_SkinPath,
            'LevelText'             => $_Lang['level'],
            'EndText'               => $_Lang['Queue_EndTime'],
            'EndTitleBeg'           => $_Lang['Queue_EndTitleBeg'],
            'EndTitleHour'          => $_Lang['Queue_EndTitleHour'],
        ];

        if ($isFirstQueueElement) {
            include_once($_EnginePath . '/includes/functions/InsertJavaScriptChronoApplet.php');

            $queueElementTplData['ChronoAppletScript'] = InsertJavaScriptChronoApplet(
                'QueueFirstTimer',
                '',
                $progressEndTime,
                true,
                false,
                'function() { onQueuesFirstElementFinished(); }'
            );
        }

        $queueElementsTplData[] = $queueElementTplData;

        $queueUnfinishedElementsCount += 1;
    }

    if (!empty($queueElementsTplData)) {
        foreach ($queueElementsTplData as $elementIdx => $queueElementTplData) {
            $queueElementTPLBody = (
                $elementIdx === 0 ?
                $tplBodyCache['queue_elements_first'] :
                $tplBodyCache['queue_elements_next']
            );

            $componentTPLData['queueElements'][] = parsetemplate($queueElementTPLBody, $queueElementTplData);
        }
    } else {
        $componentTPLData['queueElements'][] = parsetemplate(
            $tplBodyCache['queue_topinfo'],
            [
                'InfoText' => $_Lang['Queue_Empty']
            ]
        );
    }

    $isQueueFull = (
        $queueUnfinishedElementsCount >=
        Users\getMaxStructuresQueueLength($user)
    );

    if ($isQueueFull) {
        $queueFullMsgHTML = parsetemplate(
            $tplBodyCache['queue_topinfo'],
            [
                'InfoColor' => 'red',
                'InfoText' => $_Lang['Queue_Full']
            ]
        );

        $componentTPLData['queueTopInfobox'] = $queueFullMsgHTML;
    }

    $componentHTML = (
        $componentTPLData['queueTopInfobox'] .
        implode('', $componentTPLData['queueElements'])
    );

    return [
        'componentHTML' => $componentHTML
    ];
}

?>
