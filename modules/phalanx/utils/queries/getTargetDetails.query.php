<?php

namespace UniEngine\Engine\Modules\Phalanx\Utils\Queries;

/**
 * @param array $params
 * @param array $params['targetCoords']
 * @param number $params['galaxy']
 * @param number $params['system']
 * @param number $params['planet']
 */
function getTargetDetails($params) {
    $targetCoords = $params['targetCoords'];

    $query = (
        "SELECT " .
        "`planet`.`id`, `planet`.`id_owner`, `planet`.`name`, " .
        "`planet`.`galaxy`, `planet`.`system`, `planet`.`planet`, " .
        "`user`.`username` " .
        "FROM {{table}} as `planet` " .
        "LEFT JOIN {{prefix}}users AS `user` " .
        "ON `user`.`id` = `planet`.`id_owner` " .
        "WHERE " .
        "`planet`.`galaxy` = {$targetCoords['galaxy']} AND " .
        "`planet`.`system` = {$targetCoords['system']} AND " .
        "`planet`.`planet` = {$targetCoords['planet']} AND " .
        "`planet`.`planet_type` = 1 " .
        "LIMIT 1 " .
        "; -- Phalanx\Utils\Queries\getTargetDetails()"
    );

    $result = doquery($query, 'planets', true);

    return $result;
}

?>
