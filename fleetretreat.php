<?php

define('INSIDE', true);

$_EnginePath = './';
include($_EnginePath.'common.php');

loggedCheck();

includeLang('fleet');

$SetColor = 1;
$SetMsg = 1;
$FleetID = (isset($_POST['fleetid']) ? floor(floatval($_POST['fleetid'])) : 0);

if($FleetID > 0)
{
    include($_EnginePath.'includes/functions/FleetControl_Retreat.php');
    $Result = FleetControl_Retreat("`fleet_id` = {$FleetID} AND `fleet_owner` = {$_User['id']}");
    if($Result['Rows'] != 0)
    {
        if($Result['Errors'][$FleetID] == 1)
        {
            $SetMsg = 4;
        }
        else if($Result['Errors'][$FleetID] == 2)
        {
            $SetMsg = 1;
        }
        else
        {
            if($Result['Types'][$FleetID] == 1)
            {
                $SetMsg = 2;
                $SetColor = 2;
            }
            else if($Result['Types'][$FleetID] == 2)
            {
                $SetMsg = 3;
                $SetColor = 2;
            }
        }
    }
}

header("Location: fleet.php?ret=1&m={$SetMsg}&c={$SetColor}");
safeDie();

?>
