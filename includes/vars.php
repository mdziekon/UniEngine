<?php

if(defined('INSIDE'))
{
    include('vars_resources.php');
    include('vars_requirements.php');
    include('vars_prices.php');
    include('vars_combatdata.php');
    include('vars_militaryupgrades.php');
    include('vars_resproduction.php');
    include('vars_elementcategories.php');
    include('vars_tasks.php');

    // Other stuff
    $_Vars_FleetMissions = array
    (
        'all'        => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10),
        'military'    => array(1, 2, 6, 9, 10),
        'civil'        => array(3, 4, 5, 7, 8),
    );
    $_Vars_AllyRankLabels = array
    (
        'name', 'like_admin', 'mlist', 'mlist_online', 'sendmsg', 'admingen', 'lookreq',
        'managereq', 'mlist_mod', 'ranks_mod', 'cankick', 'warnpact', 'caninvite', 'canusechat',
    );
    $_Vars_TechSpeedModifiers = array
    (
        115 => 0.1,
        117 => 0.2,
        118 => 0.3,
    );

    $_Vars_PremiumBuildings = array
    (
        50    => 1,
    );
    $_Vars_PremiumBuildingPrices= array
    (
        50    => 20,
    );

    $_Vars_IndestructibleBuildings = array
    (
        50    => 1,
        33    => 1,
        41    => 1,
    );

    $_Vars_MaxElementLevel = array
    (
        50    => 1,
    );

    $_Vars_BuildingsFixedBuildTime = array
    (
        50    => 3600000,
    );

    $_Vars_ProAccountData = array
    (
        array
        (
            'shopID' => 1,
            'time' => 7,
            'cost' => 15
        ),
        array
        (
            'shopID' => 2,
            'time' => 25,
            'cost' => 45
        ),
    );
}

?>
