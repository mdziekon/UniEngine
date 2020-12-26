<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Fetchers;

/**
 * @param array $props
 * @param ref $props['user']
 * @param array $props['targetCoordinates']
 * @param boolean $props['isExtendedUserDetailsEnabled']
 */
function fetchPlanetOwnerDetails ($props) {
    $user = &$props['user'];
    $targetCoordinates = $props['targetCoordinates'];
    $isExtendedUserDetailsEnabled = $props['isExtendedUserDetailsEnabled'];

    $userId = $user['id'];
    $userAllianceId = $user['ally_id'];
    $isUserInAlliance = ($userAllianceId > 0);

    $query = (
        "SELECT " .
        "`planet`.`id` AS `id`, " .
        "`planet`.`id_owner` AS `owner`, " .
        "`planet`.`name` AS `name`, " .
        "`planet`.`quantumgate`, " .
        "`planetOwnerUser`.`ally_id`, " .
        "`planetOwnerUser`.`username` AS `username`, " .
        (
            $isExtendedUserDetailsEnabled ?
            (
                "`planetOwnerUser`.`onlinetime`, " .
                "`planetOwnerUser`.`user_lastip` as `lastip`, " .
                "`planetOwnerUser`.`is_onvacation`, " .
                "`planetOwnerUser`.`is_banned`, " .
                "`planetOwnerUser`.`authlevel`, " .
                "`planetOwnerUser`.`first_login`, " .
                "`planetOwnerUser`.`NoobProtection_EndTime`, " .
                "`planetOwnerUser`.`multiIP_DeclarationID`, " .
                "`planetOwnerStats`.`total_rank`, " .
                "`planetOwnerStats`.`total_points`, "
            ) :
            ""
        ) .
        "`buddy1`.`active` AS `active1`, " .
        "`buddy2`.`active` AS `active2` " .
        (
            $isUserInAlliance ?
            (
                ", " .
                "`apact1`.`Type` AS `AllyPact1`, " .
                "`apact2`.`Type` AS `AllyPact2` "
            ) :
            ""
        ) .
        "FROM {{table}} AS `planet` " .
        "LEFT JOIN `{{prefix}}buddy` AS `buddy1` " .
        "ON (`planet`.`id_owner` = `buddy1`.`sender` AND `buddy1`.`owner` = {$userId}) " .
        "LEFT JOIN `{{prefix}}buddy` AS `buddy2` " .
        "ON (`planet`.`id_owner` = `buddy2`.`owner` AND `buddy2`.`sender` = {$userId}) " .
        "LEFT JOIN `{{prefix}}users` AS `planetOwnerUser` " .
        "ON `planet`.`id_owner` = `planetOwnerUser`.`id` " .
        (
            $isUserInAlliance ?
            (
                "LEFT JOIN `{{prefix}}ally_pacts` AS `apact1` " .
                "ON (`apact1`.`AllyID_Sender` = {$userAllianceId} AND `apact1`.`AllyID_Owner` = `planetOwnerUser`.`ally_id` AND `apact1`.`Active` = 1) " .
                "LEFT JOIN `{{prefix}}ally_pacts` AS `apact2` " .
                "ON (`apact2`.`AllyID_Sender` = `planetOwnerUser`.`ally_id` AND `apact2`.`AllyID_Owner` = {$userAllianceId} AND `apact2`.`Active` = 1) "
            ) :
            ""
        ) .
        (
            $isExtendedUserDetailsEnabled ?
            (
                "LEFT JOIN `{{prefix}}statpoints` AS `planetOwnerStats` " .
                "ON `planet`.`id_owner` = `planetOwnerStats`.`id_owner` AND `stat_type` = '1' "
            ) :
            ""
        ) .
        "WHERE " .
        "`planet`.`galaxy` = {$targetCoordinates['galaxy']} AND " .
        "`planet`.`system` = {$targetCoordinates['system']} AND " .
        "`planet`.`planet` = {$targetCoordinates['planet']} AND " .
        "`planet`.`planet_type` = {$targetCoordinates['type']} " .
        "LIMIT 1 " .
        ";"
    );

    $result = doquery($query, 'planets', true);

    return $result;
}

?>
