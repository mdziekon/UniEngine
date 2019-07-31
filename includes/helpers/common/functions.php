<?php

//  Arguments:
//      - $period (Object)
//          - start (Number)
//          - end (Number)
//      - $subperiods (Array<Object>)
//          - start (Number)
//          - end (Number)
//          - data (Object)
//
//  Returns: Array<Object>
//      - start (Number)
//      - end (Number)
//      - isSubperiod (Boolean)
//          Whether the range was created as a representation of one of the subperiods.
//      - data (Object)
//          Any data passed along with applicable subperiod.
//
function createTimeline($period, $subperiods) {
    $ranges = [];

    $currentStart = $period['start'];

    foreach ($subperiods as $subperiod) {
        if ($subperiod['start'] > $currentStart) {
            $ranges[] = [
                'start' => $currentStart,
                'end' => $subperiod['start'],
                'isSubperiod' => false,
                'data' => []
            ];

            $currentStart = $subperiod['start'];
        }

        if ($subperiod['end'] <= $currentStart) {
            continue;
        }

        $ranges[] = [
            'start' => $currentStart,
            'end' => $subperiod['end'],
            'isSubperiod' => true,
            'data' => $subperiod['data']
        ];

        $currentStart = $subperiod['end'];
    }

    if ($period['end'] > $currentStart) {
        $ranges[] = [
            'start' => $currentStart,
            'end' => $period['end'],
            'isSubperiod' => false,
            'data' => []
        ];
    }

    return $ranges;
}

function mergeTimelines($timelines) {
    $timelinePeriodEdges = [
        'start' => array_map(
            function ($periods) {
                return $periods[0]['start'];
            },
            $timelines
        ),
        'end' => array_map(
            function ($periods) {
                return $periods[count($periods) - 1]['end'];
            },
            $timelines
        ),
    ];
    $timelineMarkers = array_map(
        function () { return 0; },
        $timelines
    );

    $newTimelinePeriod = [
        'start' => min($timelinePeriodEdges['start']),
        'end' => max($timelinePeriodEdges['end']),
    ];

    $ranges = [];
    $currentStart = $newTimelinePeriod['start'];

    while ($currentStart < $newTimelinePeriod['end']) {
        $rangeMergedData = [];
        $currentEnd = $newTimelinePeriod['end'];

        foreach ($timelines as $timelineIdx => $timeline) {
            if (count($timeline) <= $timelineMarkers[$timelineIdx]) {
                continue;
            }

            $timelineSubrange = $timeline[$timelineMarkers[$timelineIdx]];

            if ($timelineSubrange['start'] > $currentStart) {
                continue;
            }

            $rangeMergedData = array_merge($rangeMergedData, $timelineSubrange['data']);

            if ($timelineSubrange['end'] < $currentEnd) {
                $currentEnd = $timelineSubrange['end'];
            }
        }

        $ranges[] = [
            'start' => $currentStart,
            'end' => $currentEnd,
            'isSubperiod' => !empty($rangeMergedData),
            'data' => $rangeMergedData
        ];

        foreach ($timelines as $timelineIdx => $timeline) {
            if (count($timeline) <= $timelineMarkers[$timelineIdx]) {
                continue;
            }

            $timelineSubrange = $timeline[$timelineMarkers[$timelineIdx]];

            if ($timelineSubrange['end'] <= $currentEnd) {
                $timelineMarkers[$timelineIdx] += 1;
            }
        }

        $currentStart = $currentEnd;
    }

    return $ranges;
}

?>
