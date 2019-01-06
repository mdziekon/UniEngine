<?php

function Cache_Message($Owners, $Sender, $Time, $Type, $From, $Subject, $Message, $Thread_ID = 0, $Thread_IsLast = 0)
{
    global $_Cache;
    if(empty($Time))
    {
        $Time = time();
    }

    if((array)$Owners === $Owners)
    {
        foreach($Owners as $OwnerID)
        {
            $_Cache['Messages'][] = array
            (
                'id_owner' => $OwnerID,
                'id_sender' => $Sender,
                'type' => $Type,
                'time' => $Time,
                'from' => $From,
                'subject' => $Subject,
                'text' => $Message,
                'Thread_ID' => $Thread_ID,
                'Thread_IsLast' => $Thread_IsLast
            );
        }
    }
    else
    {
        $_Cache['Messages'][] = array
        (
            'id_owner' => $Owners,
            'id_sender' => $Sender,
            'type' => $Type,
            'time' => $Time,
            'from' => $From,
            'subject' => $Subject,
            'text' => $Message,
            'Thread_ID' => $Thread_ID,
            'Thread_IsLast' => $Thread_IsLast
        );
    }
}

?>
