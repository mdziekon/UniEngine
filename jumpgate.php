<?php

define('INSIDE', true);

$_EnginePath = './';
include($_EnginePath.'common.php');

loggedCheck();

includeLang('jumpgate');

function ShowMessage($Text)
{
    global $_Lang;
    message($Text, $_Lang['tech'][43], 'infos.php?gid=43', 4);
}

if(isset($_POST['dojump']) && $_POST['dojump'] != 'yes')
{
    ShowMessage($_Lang['GateJump_NotDoing']);
}
if($_Planet['planet_type'] != 3)
{
    ShowMessage($_Lang['GateJump_NotOnMoon']);
}
if($_Planet[$_Vars_GameElements[43]] <= 0)
{
    ShowMessage($_Lang['GateJump_NoTeleportOnMoon1']);
}

$This_JumpState = GetNextJumpWaitTime($_Planet);
$This_NextJumpTime = $This_JumpState['value'];
if($This_NextJumpTime != 0)
{
    ShowMessage("{$_Lang['GateJump_Moon1NotReach']} {$This_JumpState['string']}");
}
$Target_ID = (isset($_POST['jumpto']) ? round($_POST['jumpto']) : 0);
if($Target_ID <= 0)
{
    ShowMessage($_Lang['GateJump_BadIDGiven']);
}
$Query_CheckTarget = "SELECT `id`, `jumpgate`, `last_jump_time` FROM {{table}} WHERE `id` = {$Target_ID} AND `id_owner` = {$_User['id']} LIMIT 1;";
$Target_Data = doquery($Query_CheckTarget, 'planets', true);
if($Target_Data['id'] != $Target_ID)
{
    ShowMessage($_Lang['GateJump_Moon2NotYours']);
}
if($Target_Data[$_Vars_GameElements[43]] <= 0)
{
    ShowMessage($_Lang['GateJump_NoTeleportOnMoon2']);
}
$Target_JumpState = GetNextJumpWaitTime($Target_Data);
$Target_NextJumpTime = $Target_JumpState['value'];
if($Target_NextJumpTime != 0)
{
    ShowMessage("{$_Lang['GateJump_Moon2NotReach']} {$Target_JumpState['string']}");
}
foreach($_Vars_ElementCategories['fleet'] as $ShipID)
{
    $ThisKey = 'ship_'.$ShipID;
    if(!empty($_POST[$ThisKey]))
    {
        $ThisCount = round(str_replace('.', '', $_POST[$ThisKey]));
        if($ThisCount > 0)
        {
            if($_Planet[$_Vars_GameElements[$ShipID]] > 0)
            {
                if($ThisCount > $_Planet[$_Vars_GameElements[$ShipID]])
                {
                    $ThisCount = $_Planet[$_Vars_GameElements[$ShipID]];
                }
                $JumpData_Fleet[$ShipID] = $ThisCount;
            }
        }
    }
}

if(empty($JumpData_Fleet))
{
    ShowMessage($_Lang['GateJump_BadPOSTFound']);
}

$JumpData_Time = time();
foreach($JumpData_Fleet as $ShipID => $ShipCount)
{
    $_Planet[$_Vars_GameElements[$ShipID]] -= $ShipCount;
    $Query_UpdateThis_Fields[] = "`{$_Vars_GameElements[$ShipID]}` = `{$_Vars_GameElements[$ShipID]}` - {$ShipCount}";
    $Query_UpdateTarget_Fields[] = "`{$_Vars_GameElements[$ShipID]}` = `{$_Vars_GameElements[$ShipID]}` + {$ShipCount}";
}
$Query_UpdateThis_Fields[] = "`last_jump_time` = {$JumpData_Time}";
$Query_UpdateTarget_Fields[] = "`last_jump_time` = {$JumpData_Time}";

$Query_UpdateThis = '';
$Query_UpdateThis .= "UPDATE {{table}} SET ";
$Query_UpdateThis .= implode(', ', $Query_UpdateThis_Fields);
$Query_UpdateThis .= " WHERE `id` = {$_Planet['id']} LIMIT 1;";

$Query_UpdateTarget = '';
$Query_UpdateTarget .= "UPDATE {{table}} SET ";
$Query_UpdateTarget .= implode(', ', $Query_UpdateTarget_Fields);
$Query_UpdateTarget .= " WHERE `id` = {$Target_ID} LIMIT 1;";

$Query_UpdateUser = '';
$Query_UpdateUser .= "UPDATE {{table}} SET `current_planet` = {$Target_ID} ";
$Query_UpdateUser .= "WHERE `id` = {$_User['id']} LIMIT 1;";

$_Planet['last_jump_time'] = $JumpData_Time;

doquery($Query_UpdateThis, 'planets');
doquery($Query_UpdateTarget, 'planets');
if(isset($_POST['changemoon']) && $_POST['changemoon'] == 'on')
{
    doquery($Query_UpdateUser, 'users');
}

$UserDev_Log[] = array('PlanetID' => $_Planet['id'], 'Date' => $JumpData_Time, 'Place' => 27, 'Code' => 0, 'ElementID' => $Target_ID, 'AdditionalData' => Array2String($JumpData_Fleet));

$This_NewJumpState = GetNextJumpWaitTime($_Planet);
ShowMessage("{$_Lang['GateJump_Done']} {$This_NewJumpState['string']}");

?>
