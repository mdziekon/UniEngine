<?php

define('INSIDE', true);

$_DontCheckPolls = true;
$_DontForceRulesAcceptance = true;
$_UseMinimalCommon = true;
$_AllowInVacationMode = true;

$_SetAccessLogPreFilename = 'ajax/';
$_SetAccessLogPath = '../';
$_EnginePath = '../';

include($_EnginePath.'common.php');

function requestHandler() {
    if (!isLogged()) {
        return '5';
    }

    if (!CheckAuth('supportadmin')) {
        return '4';
    }

    if (!isset($_GET['id'])) {
        return '2';
    }

    $msgID = round($_GET['id']);

    if (!($msgID > 0)) {
        return '2';
    }

    $query_DeleteMessage = (
        "DELETE FROM {{table}} " .
        "WHERE " .
        "  `ID` = {$msgID} " .
        "LIMIT 1;"
    );
    $result_DeleteMessage = doquery($query_DeleteMessage, 'chat_messages');

    if ($result_DeleteMessage === false) {
        return '3';
    }

    if (getDBLink()->affected_rows != 1) {
        return '2';
    }

    return '1';
}

$response = requestHandler();

echo $response;

safeDie();

?>
