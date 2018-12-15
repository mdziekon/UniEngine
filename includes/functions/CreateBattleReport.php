<?php

function CreateBattleReport($ReportData, $UsersID, $Disallow_Attacker, $Simulator = false)
{
    $CreateArray = addslashes(json_encode($ReportData));

    if(is_array($UsersID['atk']))
    {
        $AttackerID = implode(',', $UsersID['atk']);
    }
    else
    {
        $AttackerID = $UsersID['atk'];
    }
    if(is_array($UsersID['def']))
    {
        $DefenderID = implode(',', $UsersID['def']);
    }
    else
    {
        $DefenderID = $UsersID['def'];
    }

    if($Disallow_Attacker == 1)
    {
        $Disallow_Attacker = "'1'";
    }
    else
    {
        $Disallow_Attacker = "'0'";
    }

    if($Simulator === false)
    {
        $Result = doquery("INSERT INTO {{table}} SET `time` = UNIX_TIMESTAMP(), `id_owner1` = '{$AttackerID}', `id_owner2` = '{$DefenderID}', `report` = '{$CreateArray}', `disallow_attacker` = {$Disallow_Attacker}, `Hash` = MD5(RAND());", 'battle_reports');

        if($Result == true)
        {
            $Result = doquery("SELECT LAST_INSERT_ID() as `ID`;", '', true);
            if($Result['ID'] > 0)
            {
                $GetHash = doquery("SELECT `Hash` FROM {{table}} WHERE `ID` = {$Result['ID']};", 'battle_reports', true);
                return array('ID' => $Result['ID'], 'Hash' => $GetHash['Hash']);
            }
        }
    }
    else
    {
        $Result = doquery("INSERT INTO {{table}} SET `time` = UNIX_TIMESTAMP() + (10*60), `report` = '{$CreateArray}', `owner` = {$AttackerID};", 'sim_battle_reports');

        if($Result == true)
        {
            $Result = doquery("SELECT LAST_INSERT_ID() as `ID`;", '', true);
            if($Result['ID'] > 0)
            {
                return $Result['ID'];
            }
        }
    }

    return false;
}

?>
