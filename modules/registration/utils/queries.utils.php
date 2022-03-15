<?php

namespace UniEngine\Engine\Modules\Registration\Utils\Queries;

//  Arguments
//      - $params (Object)
//          - referrerUserId (string)
//          - referredUserId (string)
//          - timestamp (number)
//          - registrationIPs (Record<ipType: string, ipValue: string>)
//          - existingMatchingEnterLogIds (string[])
//
function insertReferralsTableEntry ($params) {
    $registrationIPs = array_map_withkeys($params['registrationIPs'], function ($value, $key) {
        return "{$key},{$value}";
    });
    $registrationIPs = implode(';', $registrationIPs);

    $existingMatchingEnterLogIds = "null";

    if (!empty($params['existingMatchingEnterLogIds'])) {
        $existingMatchingEnterLogIds = implode(',', $params['existingMatchingEnterLogIds']);
        $existingMatchingEnterLogIds = "'{$existingMatchingEnterLogIds}'";
    }

    $insertEntryQuery = (
        "INSERT INTO {{table}} " .
        "SET " .
        "`referrer_id` = {$params['referrerUserId']}, " .
        "`newuser_id` = {$params['referredUserId']}, " .
        "`time` = {$params['timestamp']}, " .
        "`reg_ip` = '{$registrationIPs}', " .
        "`matches_found` = {$existingMatchingEnterLogIds} " .
        ";"
    );

    doquery($insertEntryQuery, 'referring_table');
}

//  Arguments
//      - $params (Object)
//          - userId (String)
//          - motherPlanetId (String)
//          - motherPlanetGalaxy (String)
//          - motherPlanetSystem (String)
//          - motherPlanetPlanetPos (String)
//          - referrerId (String) (Optional)
//          - activationCode (String | undefined)
//
function updateUserFinalDetails ($params) {
    $columnsToUpdate = [];

    if (isset($params['referrerId'])) {
        $columnsToUpdate[] = "`referred` = {$params['referrerId']}";
    }

    $columnsToUpdate[] = "`id_planet` = {$params['motherPlanetId']}";
    $columnsToUpdate[] = "`settings_mainPlanetID` = {$params['motherPlanetId']}";
    $columnsToUpdate[] = "`current_planet` = {$params['motherPlanetId']}";
    $columnsToUpdate[] = "`galaxy` = {$params['motherPlanetGalaxy']}";
    $columnsToUpdate[] = "`system` = {$params['motherPlanetSystem']}";
    $columnsToUpdate[] = "`planet` = {$params['motherPlanetPlanetPos']}";

    if (isset($params['activationCode'])) {
        $columnsToUpdate[] = "`activation_code` = '{$params['activationCode']}'";
    } else {
        $columnsToUpdate[] = "`activation_code` = ''";
    }

    $columnsToUpdateQueryPart = implode(', ', $columnsToUpdate);

    $updateUserQuery = (
        "UPDATE {{table}} " .
        "SET {$columnsToUpdateQueryPart}" .
        "WHERE `id` = {$params['userId']} " .
        "LIMIT 1" .
        ";"
    );

    doquery($updateUserQuery, 'users');
}

?>
