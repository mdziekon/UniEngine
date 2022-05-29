<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Helpers;

/**
 * @param array $params
 * @param number $params['unionId']
 * @param number $params['currentTimestamp']
 */
function tryJoinUnion($params) {
    $executor = function ($input, $resultHelpers) {
        $unionId = $input['unionId'];
        $currentTimestamp = $input['currentTimestamp'];

        $fetchUnionDataQuery = (
            "SELECT " .
            "`id`, `name`, `start_time`, " .
            "`end_galaxy`, `end_system`, `end_planet`, `end_type` " .
            "FROM {{table}} " .
            "WHERE " .
            "`id` = {$unionId} " .
            "LIMIT 1 " .
            ";"
        );
        $fetchUnionDataResult = doquery($fetchUnionDataQuery, 'acs', true);

        if (!$fetchUnionDataResult) {
            return $resultHelpers['createFailure']([
                'code' => 'INVALID_UNION_ID',
            ]);
        }

        if ($fetchUnionDataResult['start_time'] <= $currentTimestamp) {
            return $resultHelpers['createFailure']([
                'code' => 'UNION_JOIN_TIME_EXPIRED',
            ]);
        }

        // TODO: To prevent union data leakage,
        // check whether this union is accessible to this user

        return $resultHelpers['createSuccess']([
            'unionData' => $fetchUnionDataResult,
        ]);
    };

    return createFuncWithResultHelpers($executor)($params);
}

?>
