<?php

define('INSIDE', true);

$_EnginePath = './';
include($_EnginePath.'common.php');

loggedCheck();

$Now = time();

includeLang('aboutpro');
$_Lang['skinpath'] = $_SkinPath;

foreach($_Lang['Benefits'] as &$Value)
{
    $Value = '&#149; '.$Value;
}
$_Lang['ParsedBenefits'] = implode('<br/>', $_Lang['Benefits']);

foreach($_Vars_ProAccountData as $Data)
{
    $_Lang['ProArray'][$Data['shopID']]['time'] = $Data['time'];
    if($Data['time'] == 1)
    {
        $_Lang['ProArray'][$Data['shopID']]['thistime'] = '_day';
    }
    else
    {
        $_Lang['ProArray'][$Data['shopID']]['thistime'] = '_days';
    }
    $_Lang['CostVal'.$Data['shopID']] = $Data['cost'];
    $_Lang['ShopItemID'.$Data['shopID']] = $Data['shopID'];
}

if($_User['pro_time'] > $Now)
{
    $_Lang['ProState'] = $_Lang['ProTill'];
    $_Lang['ProTime'] = prettyDate('d m Y H:i:s', $_User['pro_time'], 1);
    $_Lang['ProTimeColor'] = 'lime';
    foreach($_Lang['ProArray'] as $ShopID => $Data)
    {
        $_Lang['BuyButton'.$ShopID] = sprintf($_Lang['RenewPro'], $Data['time'], $_Lang[$Data['thistime']]);
    }
}
else if($_User['pro_time'] > 0)
{
    $_Lang['ProState'] = $_Lang['ProEnded'];
    $_Lang['ProTime'] = prettyDate('d m Y H:i:s', $_User['pro_time'], 1);
    $_Lang['ProTimeColor'] = 'orange';
    foreach($_Lang['ProArray'] as $ShopID => $Data)
    {
        $_Lang['BuyButton'.$ShopID] = sprintf($_Lang['BuyAgain'], $Data['time'], $_Lang[$Data['thistime']]);
    }
}
else
{
    $_Lang['ProState'] = $_Lang['NeverHadPro'];
    $_Lang['ProStateColor'] = 'red';
    foreach($_Lang['ProArray'] as $ShopID => $Data)
    {
        $_Lang['BuyButton'.$ShopID] = sprintf($_Lang['FirstBuy'], $Data['time'], $_Lang[$Data['thistime']]);
    }
}

$_Lang['DarkEnergy_Counter'] = $_User['darkEnergy'];
if($_User['darkEnergy'] >= 15)
{
    $_Lang['DarkEnergy_Color'] = 'lime';
}
else if($_User['darkEnergy'] > 0)
{
    $_Lang['DarkEnergy_Color'] = 'orange';
}
else
{
    $_Lang['DarkEnergy_Color'] = 'red';
}

$page = parsetemplate(gettemplate('about_pro'), $_Lang);
display($page, $_Lang['aboutpro'], false);

?>
