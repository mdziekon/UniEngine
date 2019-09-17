<?php

namespace UniEngine\Engine\Modules\Structures\Screens\StructuresListPage\Queue;

use UniEngine\Engine\Includes\Helpers\World\Elements;
use UniEngine\Engine\Includes\Helpers\Planets;
use UniEngine\Engine\Includes\Helpers\Users;

//  Arguments
//      - $props (Object)
//          - user (Object)
//          - planet (Object)
//          - currentTimestamp (Number)
//
function render ($props) {
    global $_Lang, $_SkinPath, $_EnginePath;

    includeLang('worldElements.detailed');

    $planet = &$props['planet'];
    $user = &$props['user'];
    $currentTimestamp = $props['currentTimestamp'];

    $componentTPLData = [
        'queueElements' => [],
        'queueTopInfobox' => ''
    ];
    $tplBodyCache = [
        'queue_topinfo' => gettemplate('buildings_compact_queue_topinfo'),
        'queue_elements_first' => gettemplate('buildings_compact_queue_firstel'),
        'queue_elements_next' => gettemplate('buildings_compact_queue_nextel')
    ];

    $queueUnfinishedLenght = 0;
    $queuedResourcesToUse = [
        'metal' => 0,
        'crystal' => 0,
        'deuterium' => 0
    ];
    $queuedElementLevelModifiers = [];
    $fieldsModifierByQueuedDowngrades = 0;

    $buildingsQueue = Planets\Queues\parseStructuresQueueString($planet['buildQueue']);

    if (!empty($buildingsQueue)) {
        $queueElementsTplData = [];
        $queueDisplayIdx = 0;

        foreach ($buildingsQueue as $queueIdx => $queueElement) {
            if ($queueElement['endTimestamp'] < $currentTimestamp) {
                continue;
            }

            $listID = $queueDisplayIdx + 1;
            $elementID = $queueElement['elementID'];
            $elementLevel = $queueElement['level'];
            $progressDuration = $queueElement['duration'];
            $progressEndTime = $queueElement['endTimestamp'];
            $progressTimeLeft = $progressEndTime - $currentTimestamp;
            $isUpgrading = (
                $queueElement['mode'] == 'build' ?
                true :
                false
            );
            $isFirstQueueElement = ($queueIdx === 0);

            if ($queueElement['mode'] != 'build') {
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
                'EndTimer'              => pretty_time($progressTimeLeft, true),
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

            $queueDisplayIdx += 1;
        }

        $queueUnfinishedLenght = $queueDisplayIdx;

        if (!empty($queueElementsTplData)) {
            foreach ($queueElementsTplData as $elementIdx => $queueElementTplData) {
                $queueElementTPLBody = (
                    $elementIdx === 0 ?
                    $tplBodyCache['queue_elements_first'] :
                    $tplBodyCache['queue_elements_next']
                );

                $componentTPLData['queueElements'][] = parsetemplate($queueElementTPLBody, $queueElementTplData);
            }
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
        $queueUnfinishedLenght >=
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
        'componentHTML' => $componentHTML,
        'planetModifiers' => [
            'queuedResourcesToUse' => $queuedResourcesToUse,
            'queuedElementLevelModifiers' => $queuedElementLevelModifiers,
            'fields' => $fieldsModifierByQueuedDowngrades,
            'unfinishedLength' => $queueUnfinishedLenght
        ]
    ];
}

?>
