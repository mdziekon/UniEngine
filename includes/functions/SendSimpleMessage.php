<?php

function SendSimpleMessage($Owner, $Sender, $Time, $Type, $From, $Subject, $Message, $GetMsgID = false)
{
    if(empty($Time))
    {
        $Time = 'UNIX_TIMESTAMP()';
    }
    if($Sender == 0)
    {
        $Sender = '0';
    }

    $QryInsertMessage  = "INSERT INTO {{table}} SET ";
    $QryInsertMessage .= "`id_owner` = {$Owner}, ";
    $QryInsertMessage .= "`id_sender` = {$Sender}, ";
    $QryInsertMessage .= "`time` = {$Time}, ";
    $QryInsertMessage .= "`type` = {$Type}, ";
    $QryInsertMessage .= "`from` = '$From', ";
    $QryInsertMessage .= "`subject` = '" . (getDBLink()->escape_string($Subject)) . "', ";
    $QryInsertMessage .= "`text` = '" . (getDBLink()->escape_string($Message)) . "';";
    doquery($QryInsertMessage, 'messages');

    if($GetMsgID === true)
    {
        $LastID = doquery('SELECT LAST_INSERT_ID() AS `id`;', '', true);
        return $LastID['id'];
    }
}

?>
