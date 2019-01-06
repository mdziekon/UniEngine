<?php

function GetMissileRange($TheUser = null, $Level = null)
{
    global $_Vars_GameElements;

    if($TheUser === null)
    {
        global $_User;
        $TheUser = &$_User;
    }

    if($Level === null)
    {
        $Level = $TheUser[$_Vars_GameElements[117]];
    }

    if($Level > 0)
    {
        $MissileRange = ($Level * 5) - 1;
    }
    else
    {
        $MissileRange = 0;
    }

    return $MissileRange;
}

?>
