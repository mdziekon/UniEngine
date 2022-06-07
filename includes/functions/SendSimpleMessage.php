<?php

function SendSimpleMessage($Owner, $Sender, $Time, $Type, $From, $Subject, $Message) {
    if ($Sender == 0) {
        $Sender = '0';
    }

    SendSimpleMultipleMessages([
        [
            'id_owner' => $Owner,
            'id_sender' => $Sender,
            'time' => $Time,
            'type' => $Type,
            'from' => $From,
            'subject' => $Subject,
            'text' => $Message,
        ],
    ]);

    return getLastInsertId();
}

?>
