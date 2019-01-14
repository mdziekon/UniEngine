<?php

function SendSimpleMultipleMessages($MessagesArray)
{
    $Query_Insert = "INSERT INTO {{table}} (`id`, `id_owner`, `id_sender`, `time`, `type`, `from`, `subject`, `text`, `Thread_ID`, `Thread_IsLast`) VALUES ";
    foreach($MessagesArray as $Message)
    {
        if(empty($Message['time']))
        {
            $Message['time'] = 'UNIX_TIMESTAMP()';
        }
        if(empty($Message['Thread_ID']))
        {
            $Message['Thread_ID'] = '0';
        }
        if(empty($Message['Thread_IsLast']))
        {
            $Message['Thread_IsLast'] = '0';
        }
        $Message['subject'] = getDBLink()->escape_string($Message['subject']);
        $Message['text']    = getDBLink()->escape_string($Message['text']);
        $Array_Insert[] = "(NULL, '{$Message['id_owner']}', '{$Message['id_sender']}', '{$Message['time']}', '{$Message['type']}', '{$Message['from']}', '{$Message['subject']}', '{$Message['text']}', {$Message['Thread_ID']}, {$Message['Thread_IsLast']}) ";
    }
    $Query_Insert .= implode(', ', $Array_Insert).';';
    doquery($Query_Insert, 'messages');
}

?>
