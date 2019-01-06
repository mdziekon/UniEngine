<?php

function FilterMessages($Message, $Mode = 1, $Replace = '***')
{
    $BadWords = array
    (

    );

    if($Mode == 1)
    {
        foreach($BadWords as $String)
        {
            if(strstr($Message, $String) !== false)
            {
                return true;
            }
        }
        return false;
    }
    else if($Mode == 2)
    {
        return str_replace($BadWords, $Replace, $Message);
    }
    return null;
}

?>
