<?php

define('INSIDE', true);

$_EnginePath = './';
include($_EnginePath.'common.php');

loggedCheck();

includeLang('officers');
$_Lang['skinpath'] = $_SkinPath;
$_Lang['ParsedOfficers'] = '';
$RowTPL = gettemplate('officers_row');
$BuyTPL = gettemplate('officers_buy');
$Now = time();

foreach($_Lang['OfficersArr'] as $Officer => $Data)
{
    foreach($Data['bene'] as &$Value)
    {
        $Value = '&#149; '.$Value;
    }

    $ParseRow = array();
    $ParseRow['Name'] = $Data['name'];
    $ParseRow['skinpath'] = $_SkinPath;
    $ParseRow['img'] = $Data['img'];
    $ParseRow['Info'] = $Data['desc'];
    $ParseRow['Benefits'] = $_Lang['Benefits'];
    $ParseRow['ParseBenefits'] = implode('<br/>', $Data['bene']);
    if($Data['type'] == 1)
    {
        $ThisTimeKey = $Officer.'_time';

        $ParseRow['OfficerState']        = $_Lang['RentState'];
        if($_User[$ThisTimeKey] > $Now)
        {
            $ParseRow['ThisState']        = $_Lang['RentTill'];
            $ParseRow['OfficerVal']        = prettyDate('d m Y H:i:s', $_User[$ThisTimeKey], 1);
            $ParseRow['OfficerValColor'] = 'lime';
            $ParseRow['ButtonText']        = $_Lang['RenewRent'];
        }
        else if($_User[$ThisTimeKey] > 0)
        {
            $ParseRow['ThisState']        = $_Lang['RentEnded'];
            $ParseRow['OfficerVal']        = prettyDate('d m Y H:i:s', $_User[$ThisTimeKey], 1);
            $ParseRow['OfficerValColor'] = 'orange';
            $ParseRow['ButtonText']        = $_Lang['RentAgain'];
        }
        else
        {
            $ParseRow['ThisState']        = $_Lang['NeverRented'];
            $ParseRow['OfficerStateColor'] = 'red';
            $ParseRow['ButtonText']        = $_Lang['FirstRent'];
        }

        foreach($Data['price'] as $KeyID => $PriceVal)
        {
            $ThisTimeVar = '_days';
            if($Data['time'][$KeyID] == 1)
            {
                $ThisTimeVar = '_day';
            }
            $ParseRow['ButtonVars'][$KeyID] = array($Data['time'][$KeyID], $_Lang[$ThisTimeVar]);
            $ParseRow['ButtonItems'][$KeyID] = $Data['itemid'][$KeyID];
        }
    }
    else if($Data['type'] == 2)
    {
        $ParseRow['OfficerState'] = $_Lang['RentState'];
        $ParseRow['ThisState']        = $_Lang[$Data['thisState']];

        if($_User[$Data['field']] == 0)
        {
            $ParseRow['ButtonText'] = $_Lang['FirstRent'];
            $ParseRow['OfficerValColor'] = 'red';
        }
        else
        {
            $ParseRow['ButtonText'] = $_Lang['RentPlus'];
            $ParseRow['OfficerValColor'] = 'lime';
        }
        $ParseRow['OfficerVal'] = ($_User[$Data['field']] + 0);

        foreach($Data['price'] as $KeyID => $PriceVal)
        {
            $ParseRow['ButtonVars'][$KeyID] = array($Data['count'][$KeyID], $_Lang['RentForTransactions']);
            $ParseRow['ButtonItems'][$KeyID] = $Data['itemid'][$KeyID];
        }
    }

    foreach($Data['price'] as $KeyID => $PriceVal)
    {
        $BuyParse = array();
        $BuyParse['ButtonText'] = vsprintf($ParseRow['ButtonText'], $ParseRow['ButtonVars'][$KeyID]);
        $BuyParse['Cost'] = $_Lang['Cost'];
        $BuyParse['CostVal'] = prettyNumber($PriceVal);
        $BuyParse['CostUnits'] = $_Lang['DEUnits'];
        $BuyParse['ShopItemID'] = $ParseRow['ButtonItems'][$KeyID];
        $ParseRow['BuyButtons'][] = parsetemplate($BuyTPL, $BuyParse);
    }
    $ParseRow['BuyButtons'] = implode('<br/>', $ParseRow['BuyButtons']);

    $_Lang['ParsedOfficers'] .= parsetemplate($RowTPL, $ParseRow);
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

$page = parsetemplate(gettemplate('officers'), $_Lang);
display($page, $_Lang['officers'], false);

?>
