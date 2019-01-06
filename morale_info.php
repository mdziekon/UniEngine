<?php

define('INSIDE', true);

$_EnginePath = './';
include($_EnginePath.'common.php');

loggedCheck();

includeLang('morale_info');

if(MORALE_ENABLED)
{
    Morale_ReCalculate($_User);
}
else
{
    $_User['morale_level'] = 0;
}

$_Lang['Insert_MoraleColor'] = '';
if($_User['morale_level'] > 0)
{
    $_Lang['Insert_MoraleColor'] = 'lime';
}
else if($_User['morale_level'] <= -50)
{
    $_Lang['Insert_MoraleColor'] = 'red';
}
else if($_User['morale_level'] < 0)
{
    $_Lang['Insert_MoraleColor'] = 'orange';
}

$PowerUpsDowns = array
(
    'Table_Bonus_FleetSpeedUp1',
    'Table_Bonus_FleetSpeedUp2',
    'Table_Bonus_FleetPowerUp1',
    'Table_Bonus_FleetShieldPowerUp1',
    'Table_Bonus_IdleResStealRaise1',
    'Table_Bonus_FleetRFAddition1',
    'Table_Penalty_FleetSlowDown1',
    'Table_Penalty_FleetShieldAttenuation1',
    'Table_Penalty_FleetShieldAttenuation2',
    'Table_Penalty_FleetPowerAttenuation1',
    'Table_Penalty_FleetPowerAttenuation2',
    'Table_Penalty_FleetRFRemoval1',
    'Table_Penalty_IdleResStealDrop1',
    'Table_Penalty_AllResStealDrop1',
    'Table_Penalty_SpyReportRevolt',
    'Table_Penalty_OwnResLoseRaise1',
);

foreach($PowerUpsDowns as $ThisElement)
{
    $ThisElementLevel = $_Lang[$ThisElement.'_Level'];
    $ThisIsActive = false;
    if($ThisElementLevel > 0)
    {
        $ThisType = 'Bonus';
        if($_User['morale_level'] >= $ThisElementLevel)
        {
            $ThisIsActive = true;
        }
    }
    else if($ThisElementLevel < 0)
    {
        $ThisType = 'Penalty';
        if($_User['morale_level'] <= $ThisElementLevel)
        {
            $ThisIsActive = true;
        }
    }

    $_Lang[$ThisElement.'_State'] = sprintf(($ThisIsActive ? $_Lang['Table_State_Active_'.$ThisType] : $_Lang['Table_State_InActive']), $ThisElementLevel);
}

$_Lang['Table_MoraleStatus'] = sprintf($_Lang['Table_MoraleStatus'], $_Lang['Insert_MoraleColor'], $_User['morale_level']);

$page = parsetemplate(gettemplate('morale_info_body'), $_Lang);
display($page, $_Lang['PageTitle'], false);

?>
