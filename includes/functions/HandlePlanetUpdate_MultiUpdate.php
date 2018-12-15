<?php

function HandlePlanetUpdate_MultiUpdate($Results, $TheUser, $ClearVars = false, $UpdateUserVar = false)
{
    global $HPQ_PlanetUpdatedFields, $HPQ_UserUpdatedFields, $HFUU_UsersToUpdate;

    if(!empty($Results['planets']))
    {
        $HPQ_PlanetUpdatedFields = array_unique($HPQ_PlanetUpdatedFields);
        foreach($Results['planets'] as $PlanetData)
        {
            $Query_Update_Arr2 = array();
            foreach($HPQ_PlanetUpdatedFields as $Field)
            {
                $Query_Update_Arr2[] = "'{$PlanetData[$Field]}'";
            }
            $Query_Update_Arr[] = "({$PlanetData['id']}, ".implode(', ', $Query_Update_Arr2).")";
        }
        $UpdateFields = array();
        foreach($HPQ_PlanetUpdatedFields as $Value)
        {
            $UpdateFields[] = "`{$Value}`";
        }
        $Query_Update = "INSERT INTO {{table}} (`id`, ".implode(', ', $UpdateFields).") VALUES ";
        $Query_Update .= implode(', ', $Query_Update_Arr);
        $Query_Update .= " ON DUPLICATE KEY UPDATE ";
        $Query_Update_Arr = array();
        foreach($HPQ_PlanetUpdatedFields as $Field)
        {
            $Query_Update_Arr[] = "`{$Field}` = VALUES(`{$Field}`)";
        }
        $Query_Update .= implode(', ', $Query_Update_Arr);

        doquery($Query_Update, 'planets');
        $HPQ_PlanetUpdatedFields = array();
    }
    if(!empty($Results['users']))
    {
        if($UpdateUserVar === true)
        {
            global $_User;
        }
        $HPQ_UserUpdatedFields = array_unique($HPQ_UserUpdatedFields);
        $Query_Update_Arr = array();
        foreach($Results['users'] as $UserData)
        {
            if($HFUU_UsersToUpdate[$UserData['id']] !== true)
            {
                continue;
            }
            $UpdateThisUserVar = false;
            if($UpdateUserVar === true AND $UserData['id'] == $_User['id'])
            {
                $UpdateThisUserVar = true;
                $UpdateUserVar = false;
            }
            $Query_Update_Arr2 = array();
            foreach($HPQ_UserUpdatedFields as $Field)
            {
                $Query_Update_Arr2[] = "'{$UserData[$Field]}'";
                if($UpdateThisUserVar === true)
                {
                    $_User[$Field] = $UserData[$Field];
                }
            }
            $Query_Update_Arr[] = "({$UserData['id']}, ".implode(', ', $Query_Update_Arr2).")";
        }
        if(!empty($Query_Update_Arr))
        {
            $UpdateFields = array();
            foreach($HPQ_UserUpdatedFields as $Value)
            {
                $UpdateFields[] = "`{$Value}`";
            }
            $Query_Update = "INSERT INTO {{table}} (`id`, ".implode(', ', $UpdateFields).") VALUES ";
            $Query_Update .= implode(', ', $Query_Update_Arr);
            $Query_Update .= " ON DUPLICATE KEY UPDATE ";
            $Query_Update_Arr = array();
            foreach($HPQ_UserUpdatedFields as $Field)
            {
                $Query_Update_Arr[] = "`{$Field}` = VALUES(`{$Field}`)";
            }
            $Query_Update .= implode(', ', $Query_Update_Arr);

            doquery($Query_Update, 'users');
            $HPQ_UserUpdatedFields = array();
        }
    }
    else
    {
        if(!empty($HPQ_UserUpdatedFields))
        {
            if($HFUU_UsersToUpdate[$TheUser['id']] === true)
            {
                $HPQ_UserUpdatedFields = array_unique($HPQ_UserUpdatedFields);
                $UpdateFields = array();
                foreach($HPQ_UserUpdatedFields as $Value)
                {
                    $UpdateFields[] = "`{$Value}` = '{$TheUser[$Value]}'";
                }
                $Query_Update = "UPDATE {{table}} SET ".implode(', ', $UpdateFields)." WHERE `id` = {$TheUser['id']};";
                doquery($Query_Update, 'users');
                $HPQ_UserUpdatedFields = array();
            }
        }
    }

    if($ClearVars === true)
    {
        $HPQ_PlanetUpdatedFields = $HPQ_UserUpdatedFields = $HFUU_UsersToUpdate = array();
    }
}

?>
