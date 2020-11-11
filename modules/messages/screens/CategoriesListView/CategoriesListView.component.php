<?php

namespace UniEngine\Engine\Modules\Messages\Screens\CategoriesListView;

use UniEngine\Engine\Modules\Messages\Screens\CategoriesListView;
use UniEngine\Engine\Modules\Messages;

//  Arguments
//      - $props (Object)
//          - readerUser (&Object)
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_Lang;

    $readerUser = &$props['readerUser'];

    $isReaderUsingThreads = Messages\Utils\_isMessagesThreadViewEnabled([ 'user' => &$readerUser ]);

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $tplBodyCache = [
        'body' => $localTemplateLoader('body'),
        'categoryEntryRow' => $localTemplateLoader('categoryEntryRow'),
    ];

    $messageTypes = Messages\Utils\getMessageTypes();

    $counters = [
        'total' => [],
        'threaded' => [],
        'unread' => [],
    ];

    foreach ($messageTypes as $typeId) {
        $counters['total'][$typeId] = 0;
        $counters['threaded'][$typeId] = 0;
        $counters['unread'][$typeId] = 0;
    }

    $internalCaches = [
        'seenThreads' => [],
        'threadMainTypes' => [],
    ];

    $messageCountersResult = CategoriesListView\Utils\fetchMessagesCounters([
        'readerId' => $readerUser['id'],
    ]);

    while ($counterEntry = $messageCountersResult->fetch_assoc()) {
        $typeId = $counterEntry['type'];

        $thisCounterUpdates = [
            'total' => 0,
            'threaded' => 0,
            'unread' => 0,
        ];

        if (
            $isReaderUsingThreads &&
            $counterEntry['Thread_ID'] > 0
        ) {
            $threadId = $counterEntry['Thread_ID'];

            if ($counterEntry['Thread_IsLast'] == 1) {
                $internalCaches['threadMainTypes'][$threadId] = $typeId;
            }

            // For all messages in a thread, assume they are of the same type
            // as the last message
            $typeId = $internalCaches['threadMainTypes'][$threadId];

            if (!in_array($threadId, $internalCaches['seenThreads'])) {
                $thisCounterUpdates['threaded'] += 1;

                $internalCaches['seenThreads'][] = $threadId;
            }
        } else {
            $thisCounterUpdates['threaded'] += $counterEntry['Count'];
        }

        $thisCounterUpdates['total'] += $counterEntry['Count'];
        $thisCounterUpdates['unread'] += (
            $counterEntry['read'] == 0 ?
                $counterEntry['Count'] :
                0
        );

        foreach ($thisCounterUpdates as $updateKey => $updateIncrement) {
            $counters[$updateKey][$typeId] += $updateIncrement;

            // Don't count admin messages towards total counter
            if ($typeId == 80) {
                continue;
            }

            // Update total counter as well
            $counters[$updateKey][100] += $updateIncrement;
        }
    }

    $componentTPLData = [
        'Insert_Styles' => [],
        'Insert_CategoryList' => [],
        'Insert_Hide_ThreadEnabled' => (
            $isReaderUsingThreads ?
                'display: none;' :
                ''
        ),
        'Insert_Hide_ThreadDisabled' => (
            !$isReaderUsingThreads ?
                'display: none;' :
                ''
        ),
    ];

    foreach ($messageTypes as $typeId) {
        $typeCSSClass = 'c' . (string)($typeId + 0);
        $typeColor = Messages\Utils\getMessageTypeColor($typeId);

        $componentTPLData['Insert_Styles'][] = ".{$typeCSSClass} { color: {$typeColor}; } ";

        $categoryEntryRowTPLData = [
            'Insert_CatID' => $typeId,
            'Insert_CatClass' => $typeCSSClass,
            'Insert_CatName' => $_Lang['type'][$typeId],
            'Insert_CatUnread' => prettyNumber($counters['unread'][$typeId]),
            'Insert_CatTotal' => (
                (
                    $isReaderUsingThreads &&
                    Messages\Utils\_isMessageTypeThreadable($typeId) &&
                    $counters['threaded'][$typeId] < $counters['total'][$typeId]
                ) ?
                    prettyNumber($counters['threaded'][$typeId]) . '/' :
                    ''
            ) . prettyNumber($counters['total'][$typeId]),
        ];

        $componentTPLData['Insert_CategoryList'][] = parsetemplate(
            $tplBodyCache['categoryEntryRow'],
            $categoryEntryRowTPLData
        );
    }

    $componentTPLData['Insert_Styles'] = implode('', $componentTPLData['Insert_Styles']);
    $componentTPLData['Insert_CategoryList'] = implode('', $componentTPLData['Insert_CategoryList']);

    $componentTPLBody = $tplBodyCache['body'];
    $componentHTML = parsetemplate($componentTPLBody, array_merge($_Lang, $componentTPLData));

    return [
        'componentHTML' => $componentHTML,
    ];
}

?>
