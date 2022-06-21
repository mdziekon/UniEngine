<?php

namespace UniEngine\Engine\Modules\Settings\Utils\Helpers;

/**
 * @param array $params
 * @param array $params['historyEntries']
 * @param array $params['historyEntriesLimit']
 */
function parseLoginHistoryEntries($params) {
    $historyEntries = $params['historyEntries'];
    $historyEntriesLimit = $params['historyEntriesLimit'];

    $loginEntries = [];
    $loginEntriesTimestamps = [];

    foreach ($historyEntries as $historyEntry) {
        $loginsTimePoints = array_reverse(explode(',', $historyEntry['Times']));

        // TODO: This should most likely be global for the entire loop
        $limitCounter = $historyEntriesLimit;
        foreach ($loginsTimePoints as $loginTimePoint) {
            if ($limitCounter <= 0) {
                break;
            }

            $loginTimePointSplit = explode('|', $loginTimePoint);
            $loginTimestamp = SERVER_MAINOPEN_TSTAMP + $loginTimePointSplit[0];
            $loginPointStateValue = isset($loginTimePointSplit[1]) ? $loginTimePointSplit[1] : null;
            $loginPointState = $loginPointStateValue !== 'F';

            $loginEntries[] = [
                'Time' => $loginTimestamp,
                'IP' => $historyEntry['Value'],
                'State' => $loginPointState,
            ];
            $loginEntriesTimestamps[] = $loginTimestamp;

            $limitCounter -= 1;
        }
    }

    array_multisort($loginEntries, SORT_DESC, $loginEntriesTimestamps);

    return $loginEntries;
}

?>
