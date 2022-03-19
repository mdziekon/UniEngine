<?php

namespace UniEngine\Engine\Modules\Registration\Utils\Queries;

//  Arguments
//      - $params (Object)
//          - userId (string | null)
//
function checkIfUserExists ($params) {
    $selectUserQuery = (
        "SELECT " .
        "`id` " .
        "FROM {{table}}  " .
        "WHERE " .
        "`id` = {$params['userId']} " .
        "LIMIT 1 " .
        ";"
    );

    $selectUserResult = doquery($selectUserQuery, 'users');
    $doesUserExist = $selectUserResult->num_rows == 1;

    return $doesUserExist;
}

//  Arguments
//      - $params (Object)
//          - ips (string[])
//
function findEnterLogIPsWithMatchingIPValue ($params) {
    $ips = array_map(function ($ip) {
        return "'{$ip}'";
    }, $params['ips']);

    $selectIPsIdsQuery = (
        "SELECT `ID` " .
        "FROM {{table}} " .
        "WHERE " .
        "`Type` = 'ip' AND " .
        "`Value` IN (".implode(',', $ips).") " .
        ";"
    );

    $selectIPsIdsResult = doquery($selectIPsIdsQuery, 'used_ip_and_ua');

    $ipIds = mapQueryResults($selectIPsIdsResult, function ($ipRow) {
        return $ipRow['ID'];
    });

    $selectEnterLogMatchesQuery = (
        "SELECT `ID` " .
        "FROM {{table}} " .
        "WHERE " .
        "`IP_ID` IN (".implode(',', $ipIds).") " .
        ";"
    );

    $selectEnterLogMatchesResult = doquery($selectEnterLogMatchesQuery, 'user_enterlog');

    $enterLogIds = mapQueryResults($selectEnterLogMatchesResult, function ($enterLogRow) {
        return $enterLogRow['ID'];
    });

    return $enterLogIds;
}

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
//          - username (String)
//          - passwordHash (String)
//          - langCode (String)
//          - email (String)
//          - registrationIP (String)
//          - currentTimestamp (String)
//
function insertNewUser ($params) {
    $insertUserQuery = (
        "INSERT INTO {{table}} " .
        "SET " .
        "`username` = '{$params['username']}', " .
        "`password` = '{$params['passwordHash']}', " .
        "`lang` = '{$params['langCode']}', " .
        "`email` = '{$params['email']}', " .
        "`email_2` = '{$params['email']}', " .
        "`ip_at_reg` = '{$params['registrationIP']}', " .
        "`id_planet` = 0, " .
        "`register_time` = {$params['currentTimestamp']}, " .
        "`onlinetime` = {$params['currentTimestamp']} - (24*60*60), " .
        "`rules_accept_stamp` = {$params['currentTimestamp']} " .
        ";"
    );

    doquery($insertUserQuery, 'users');

    return [
        'userId' => getLastInsertId()
    ];
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

function incrementUsersCounterInGameConfig () {
    global $_GameConfig, $_MemCache;

    $_GameConfig['users_amount'] += 1;

    $updateUserConfigQuery = (
        "UPDATE {{table}} " .
        "SET " .
        "`config_value` = {$_GameConfig['users_amount']} " .
        "WHERE " .
        "`config_name` = 'users_amount' " .
        ";"
    );

    doquery($updateUserConfigQuery, 'config');

    $_MemCache->GameConfig = $_GameConfig;
}

//  Arguments
//      - $params (Object)
//          - email (String)
//
function updateAllMailChanges ($params) {
    // This confirmation type means that the process has been interrupted externally
    $confirmType = "4";

    $updateMailChangesQuery = (
        "UPDATE {{table}} " .
        "SET " .
        "`ConfirmType` = {$confirmType} " .
        "WHERE " .
        "`NewMail` = '{$params['email']}' AND " .
        "`ConfirmType` = 0 " .
        ";"
    );

    doquery($updateMailChangesQuery, 'mailchange');
}

?>
