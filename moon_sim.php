<?php

define('INSIDE', true);

$_EnginePath = './';
include($_EnginePath.'common.php');

loggedCheck();

includeLang('moon_sim');

$_Lang['HideResult'] = 'style="display: none;"';
$_Lang['HideError'] = 'style="display: none;"';

if(isset($_POST['simulate']) && $_POST['simulate'] == 'yes')
{
    $_Lang['Input_RawDiameter'] = $_POST['diameter'];
    $_Lang['Input_RawShipCount'] = $_POST['ship_count'];

    $Data['diameter'] = floor(str_replace(array(',', '.'), '', $_POST['diameter']));
    $Data['shipcount'] = floor(str_replace(array(',', '.'), '', $_POST['ship_count']));

    if($Data['diameter'] > 0 AND $Data['shipcount'] > 0)
    {
        if($Data['diameter'] < 10000)
        {
            $_Lang['HideResult'] = '';

            $MoonChance = (100 - sqrt($Data['diameter'])) * sqrt($Data['shipcount']);
            $FleetChance = sqrt($Data['diameter']) / 2;

            if($MoonChance > 100)
            {
                $MoonChance = 100;
            }
            else if($MoonChance <= 0)
            {
                $MoonChance = 0;
            }
            else
            {
                $MoonChance = round($MoonChance, 2);
            }

            if($FleetChance > 100)
            {
                $FleetChance = 100;
            }
            else if($FleetChance <= 0)
            {
                $FleetChance = 0;
            }
            else
            {
                $FleetChance = round($FleetChance, 2);
            }

            $_Lang['Input_Diamter'] = prettyNumber($Data['diameter']);
            $_Lang['Input_ShipCount'] = prettyNumber($Data['shipcount']);
            $_Lang['Input_MoonChance'] = $MoonChance;
            $_Lang['Input_FleetChance'] = $FleetChance;
        }
        else
        {
            $_Lang['HideError'] = '';
            $_Lang['Input_Error'] = $_Lang['Error_DiameterTooBig'];
        }
    }
    else
    {
        $_Lang['HideError'] = '';
        $_Lang['Input_Error'] = $_Lang['Error_BadDataGiven'];
    }
}

//Display page
$page = parsetemplate(gettemplate('moon_sim_body'), $_Lang);

display($page, $_Lang['Title'], false);

?>
