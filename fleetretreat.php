<?php

define('INSIDE', true);

$_EnginePath = './';
include($_EnginePath.'common.php');
include($_EnginePath . 'modules/flightControl/_includes.php');

use UniEngine\Engine\Modules\FlightControl\Enums\RetreatResultType;

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
            $SetMsg = RetreatResultType::ErrorMissileStrikeRetreat;
        }
        else if($Result['Errors'][$FleetID] == 2)
        {
            $SetMsg = RetreatResultType::ErrorCantRetreatAnymore;
        }
        else
        {
            if($Result['Types'][$FleetID] == 1)
            {
                $SetMsg = RetreatResultType::SuccessTurnedBack;
                $SetColor = 2;
            }
            else if($Result['Types'][$FleetID] == 2)
            {
                $SetMsg = RetreatResultType::SuccessRetreated;
                $SetColor = 2;
            }
        }
    }
}

header("Location: fleet.php?ret=1&m={$SetMsg}&c={$SetColor}");
safeDie();

?>
