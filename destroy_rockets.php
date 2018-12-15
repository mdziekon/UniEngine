<?php

define('INSIDE', true);

$_EnginePath = './';
include($_EnginePath.'common.php');

loggedCheck();

includeLang('destroy_rockets');

if($_Planet['missile_silo'] <= 0)
{
    message($_Lang['NoSilo'], $_Lang['Title'], 'infos.php?gid=44', 3);
}

$destroy[502] = (isset($_POST['destroy']['502']) ? round($_POST['destroy']['502']) : 0);
$destroy[503] = (isset($_POST['destroy']['503']) ? round($_POST['destroy']['503']) : 0);

if($destroy[502] < 0 OR $destroy[503] < 0)
{
    message($_Lang['BadCountGiven'], $_Lang['Title'], 'infos.php?gid=44', 3);
}

if($destroy[502] > $_Planet['antiballistic_missile'])
{
    $destroy[502] = $_Planet['antiballistic_missile'];
}
if($destroy[503] > $_Planet['interplanetary_missile'])
{
    $destroy[503] = $_Planet['interplanetary_missile'];
}

if($destroy[502] == 0 && $destroy[503] == 0)
{
    message($_Lang['NothingDestroyed'], $_Lang['Title'], 'infos.php?gid=44', 3);
}

$_Planet[$_Vars_GameElements[502]] -= $destroy[502];
$_Planet[$_Vars_GameElements[503]] -= $destroy[503];
if($destroy[502] > 0)
{
    $CreateLogArray[] = '502,'.$destroy[502];
}
if($destroy[503] > 0)
{
    $CreateLogArray[] = '503,'.$destroy[503];
}

$UserDev_Log[] = array('PlanetID' => $_User['current_planet'], 'Date' => time(), 'Place' => 28, 'Code' => '0', 'ElementID' => '0', 'AdditionalData' => implode(';', $CreateLogArray));

$Query_DeleteMissiles = '';
$Query_DeleteMissiles .= "UPDATE {{table}} SET ";
$Query_DeleteMissiles .= "`antiballistic_missile` = `antiballistic_missile` - {$destroy[502]}, ";
$Query_DeleteMissiles .= "`interplanetary_missile` = `interplanetary_missile` - {$destroy[503]} ";
$Query_DeleteMissiles .= "WHERE `id` = {$_User['current_planet']} LIMIT 1;";
doquery($Query_DeleteMissiles, 'planets');

message(
    sprintf
    (
        $_Lang['RocketsDestroyed'],
        prettyNumber($destroy[502]), prettyNumber($destroy[503]), $_Planet['name'],
        $_Planet['galaxy'], $_Planet['system'], $_Planet['planet']
    ),
    $_Lang['Title'], 'infos.php?gid=44', 3
);

?>
