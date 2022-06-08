<?php

function Cache_Message($Owners, $Sender, $Time, $Type, $From, $Subject, $Message, $Thread_ID = 0, $Thread_IsLast = 0) {
    global $_Cache;

    if (empty($Time)) {
        $Time = time();
    }

    $recipients = (
        is_array($Owners) ?
            $Owners :
            [ $Owners ]
    );

    foreach ($recipients as $recipientId) {
        $_Cache['Messages'][] = [
            'id_owner' => $recipientId,
            'id_sender' => $Sender,
            'type' => $Type,
            'time' => $Time,
            'from' => $From,
            'subject' => $Subject,
            'text' => $Message,
            'Thread_ID' => $Thread_ID,
            'Thread_IsLast' => $Thread_IsLast
        ];
    }
}

?>
