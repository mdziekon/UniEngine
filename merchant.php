<?php

define('INSIDE', true);

$_EnginePath = './';
include($_EnginePath.'common.php');

loggedCheck();

$CurrentPlanet = &$_Planet;
$CurrentUser = &$_User;

includeLang('merchant');

$parse = $_Lang;
$parse['SkinPath'] = $_SkinPath;
$parse['TraderMsg_Hide'] = 'inv';

PlanetResourceUpdate($CurrentUser, $CurrentPlanet, time());

$parse['InsertMaxMetal']        = explode('.', sprintf('%f', floor($CurrentPlanet['metal'])));
$parse['InsertMaxMetal']        = (string)$parse['InsertMaxMetal'][0];
$parse['InsertMaxCrystal']        = explode('.', sprintf('%f', floor($CurrentPlanet['crystal'])));
$parse['InsertMaxCrystal']        = (string)$parse['InsertMaxCrystal'][0];
$parse['InsertMaxDeuterium']    = explode('.', sprintf('%f', floor($CurrentPlanet['deuterium'])));
$parse['InsertMaxDeuterium']    = (string)$parse['InsertMaxDeuterium'][0];

if(isset($_GET['step']) && $_GET['step'] == 2 && isset($_POST['exchange']) && $_POST['exchange'] == 'yes')
{
    if(!isPro($CurrentUser) AND $CurrentUser['trader_usesCount'] <= 0)
    {
        message($_Lang['Trader_CantUseTrader'], $_Lang['Trader_Title']);
    }

    if(!isset($_POST['res']))
    {
        $_POST['res'] = 0;
    }
    if(!isset($_POST['mode']))
    {
        $_POST['mode'] = 0;
    }
    $_POST['res'] = intval($_POST['res']);

    if(!in_array($_POST['res'], array(1, 2, 3)))
    {
        $parse['TraderMsg_Hide'] = '';
        $parse['TraderMsg_Color'] = 'red';
        $parse['TraderMsg_Text'] = $_Lang['Trader_BadResType'];
    }
    if(!in_array($_POST['mode'], array(1, 2)))
    {
        $parse['TraderMsg_Hide'] = '';
        $parse['TraderMsg_Color'] = 'red';
        $parse['TraderMsg_Text'] = $_Lang['Trader_BadResMode'];
    }

    if(empty($parse['TraderMsg_Text']))
    {
        $TradeMet = (isset($_POST['met']) ? floor(str_replace('.', '', $_POST['met'])) : 0);
        $TradeCry = (isset($_POST['cry']) ? floor(str_replace('.', '', $_POST['cry'])) : 0);
        $TradeDeut = (isset($_POST['deu']) ? floor(str_replace('.', '', $_POST['deu'])) : 0);

        if($TradeMet < 0 || $TradeCry < 0 || $TradeDeut < 0)
        {
            $parse['TraderMsg_Hide'] = '';
            $parse['TraderMsg_Color'] = 'red';
            $parse['TraderMsg_Text'] = $_Lang['Trader_ResMinus'];
        }
        if($TradeMet == 0 && $TradeCry == 0 && $TradeDeut == 0)
        {
            $parse['TraderMsg_Hide'] = '';
            $parse['TraderMsg_Color'] = 'red';
            $parse['TraderMsg_Text'] = $_Lang['Trader_ResZero'];
        }

        if(empty($parse['TraderMsg_Text']))
        {
            if($_POST['res'] == 1)
            {
                // Metal Trade
                $TradeMet = 0;
                if($_POST['mode'] == 1)
                {
                    // Selling Metal
                    $Need2Trade = ceil(($TradeCry * 2) + ($TradeDeut * 4));
                    if($CurrentPlanet['metal'] < $Need2Trade)
                    {
                        $parse['TraderMsg_Hide'] = '';
                        $parse['TraderMsg_Color'] = 'red';
                        $parse['TraderMsg_Text'] = $_Lang['Trader_NoEnoughMetal'];
                    }
                    if($TradeCry + $TradeDeut <= 0)
                    {
                        $parse['TraderMsg_Hide'] = '';
                        $parse['TraderMsg_Color'] = 'orange';
                        $parse['TraderMsg_Text'] = $_Lang['Trader_ResZero'];
                    }
                    if(empty($parse['TraderMsg_Text']))
                    {
                        $CurrentPlanet['metal'] -= $Need2Trade;
                    }
                }
                else
                {
                    // Buying Metal
                    $WillReceive = floor(($TradeCry * 2) + ($TradeDeut * 4));
                    if($WillReceive <= 0)
                    {
                        $parse['TraderMsg_Hide'] = '';
                        $parse['TraderMsg_Color'] = 'orange';
                        $parse['TraderMsg_Text'] = $_Lang['Trader_ResZero2'];
                    }
                    if($CurrentPlanet['crystal'] < $TradeCry)
                    {
                        $parse['TraderMsg_Hide'] = '';
                        $parse['TraderMsg_Color'] = 'red';
                        $parse['TraderMsg_Text'] = $_Lang['Trader_NoEnoughCrystal'];
                    }
                    if($CurrentPlanet['deuterium'] < $TradeDeut)
                    {
                        $parse['TraderMsg_Hide'] = '';
                        $parse['TraderMsg_Color'] = 'red';
                        $parse['TraderMsg_Text'] = $_Lang['Trader_NoEnoughDeuterium'];
                    }
                    if(empty($parse['TraderMsg_Text']))
                    {
                        $CurrentPlanet['metal'] += $WillReceive;
                        $CurrentPlanet['crystal'] -= $TradeCry;
                        $CurrentPlanet['deuterium'] -= $TradeDeut;
                    }
                }
            }
            else if($_POST['res'] == 2)
            {
                // Crystal Trade
                $TradeCry = 0;
                if($_POST['mode'] == 1)
                {
                    // Selling Crystal
                    $Need2Trade = ceil(($TradeMet * 0.5) + ($TradeDeut * 2));
                    if($CurrentPlanet['crystal'] < $Need2Trade)
                    {
                        $parse['TraderMsg_Hide'] = '';
                        $parse['TraderMsg_Color'] = 'red';
                        $parse['TraderMsg_Text'] = $_Lang['Trader_NoEnoughCrystal'];
                    }
                    if($TradeMet + $TradeDeut <= 0)
                    {
                        $parse['TraderMsg_Hide'] = '';
                        $parse['TraderMsg_Color'] = 'orange';
                        $parse['TraderMsg_Text'] = $_Lang['Trader_ResZero'];
                    }
                    if(empty($parse['TraderMsg_Text']))
                    {
                        $CurrentPlanet['crystal'] -= $Need2Trade;
                    }
                }
                else
                {
                    // Buying Crystal
                    $WillReceive = floor(($TradeMet * 0.5) + ($TradeDeut * 2));
                    if($WillReceive <= 0)
                    {
                        $parse['TraderMsg_Hide'] = '';
                        $parse['TraderMsg_Color'] = 'orange';
                        $parse['TraderMsg_Text'] = $_Lang['Trader_ResZero2'];
                    }
                    if($CurrentPlanet['metal'] < $TradeMet)
                    {
                        $parse['TraderMsg_Hide'] = '';
                        $parse['TraderMsg_Color'] = 'red';
                        $parse['TraderMsg_Text'] = $_Lang['Trader_NoEnoughMetal'];
                    }
                    if($CurrentPlanet['deuterium'] < $TradeDeut)
                    {
                        $parse['TraderMsg_Hide'] = '';
                        $parse['TraderMsg_Color'] = 'red';
                        $parse['TraderMsg_Text'] = $_Lang['Trader_NoEnoughDeuterium'];
                    }
                    if(empty($parse['TraderMsg_Text']))
                    {
                        $CurrentPlanet['metal'] -= $TradeMet;
                        $CurrentPlanet['crystal'] += $WillReceive;
                        $CurrentPlanet['deuterium'] -= $TradeDeut;
                    }
                }
            }
            else if($_POST['res'] == 3)
            {
                // Deuterium Trade
                $TradeDeut = 0;
                if($_POST['mode'] == 1)
                {
                    // Selling Deuterium
                    $Need2Trade = ceil(($TradeMet * 0.25) + ($TradeCry * 0.5));
                    if($CurrentPlanet['deuterium'] < $Need2Trade)
                    {
                        $parse['TraderMsg_Hide'] = '';
                        $parse['TraderMsg_Color'] = 'red';
                        $parse['TraderMsg_Text'] = $_Lang['Trader_NoEnoughDeuterium'];
                    }
                    if($TradeMet + $TradeCry <= 0)
                    {
                        $parse['TraderMsg_Hide'] = '';
                        $parse['TraderMsg_Color'] = 'orange';
                        $parse['TraderMsg_Text'] = $_Lang['Trader_ResZero'];
                    }
                    if(empty($parse['TraderMsg_Text']))
                    {
                        $CurrentPlanet['deuterium'] -= $Need2Trade;
                    }
                }
                else
                {
                    // Buying Deuterium
                    $WillReceive = floor(($TradeMet * 0.25) + ($TradeCry * 0.5));
                    if($WillReceive <= 0)
                    {
                        $parse['TraderMsg_Hide'] = '';
                        $parse['TraderMsg_Color'] = 'orange';
                        $parse['TraderMsg_Text'] = $_Lang['Trader_ResZero2'];
                    }
                    if($CurrentPlanet['metal'] < $TradeMet)
                    {
                        $parse['TraderMsg_Hide'] = '';
                        $parse['TraderMsg_Color'] = 'red';
                        $parse['TraderMsg_Text'] = $_Lang['Trader_NoEnoughMetal'];
                    }
                    if($CurrentPlanet['crystal'] < $TradeCry)
                    {
                        $parse['TraderMsg_Hide'] = '';
                        $parse['TraderMsg_Color'] = 'red';
                        $parse['TraderMsg_Text'] = $_Lang['Trader_NoEnoughCrystal'];
                    }
                    if(empty($parse['TraderMsg_Text']))
                    {
                        $CurrentPlanet['metal'] -= $TradeMet;
                        $CurrentPlanet['crystal'] -= $TradeCry;
                        $CurrentPlanet['deuterium'] += $WillReceive;
                    }
                }
            }

            if(empty($parse['TraderMsg_Text']))
            {
                if($_POST['mode'] == 1)
                {
                    // Parse this only if Selling (Buying is Hardcoded in each case)
                    if($_POST['res'] != 1)
                    {
                        $CurrentPlanet['metal'] += $TradeMet;
                    }
                    if($_POST['res'] != 2)
                    {
                        $CurrentPlanet['crystal'] += $TradeCry;
                    }
                    if($_POST['res'] != 3)
                    {
                        $CurrentPlanet['deuterium'] += $TradeDeut;
                    }
                }

                $Query_UpdatePlanet = '';
                $Query_UpdatePlanet = "UPDATE {{table}} SET ";
                $Query_UpdatePlanet .= "`metal` = '{$CurrentPlanet['metal']}', ";
                $Query_UpdatePlanet .= "`crystal` = '{$CurrentPlanet['crystal']}', ";
                $Query_UpdatePlanet .= "`deuterium` = '{$CurrentPlanet['deuterium']}' ";
                $Query_UpdatePlanet .= "WHERE ";
                $Query_UpdatePlanet .= "`id` = '{$CurrentPlanet['id']}';";
                doquery($Query_UpdatePlanet , 'planets');

                if($_POST['mode'] == 1)
                {
                    $AdditionalData = "R,{$Need2Trade};M,{$TradeMet};C,{$TradeCry};D,{$TradeDeut}";
                    $SetElementID = 1;
                }
                else
                {
                    $AdditionalData = "R,{$WillReceive};M,{$TradeMet};C,{$TradeCry};D,{$TradeDeut}";
                    $SetElementID = 2;
                }

                $UserDev_Log[] = array('PlanetID' => $CurrentPlanet['id'], 'Date' => time(), 'Place' => 23, 'Code' => $_POST['res'], 'ElementID' => $SetElementID, 'AdditionalData' => $AdditionalData);

                if(!isPro())
                {
                    $CurrentUser['trader_usesCount'] -= 1;
                    doquery("UPDATE {{table}} SET `trader_usesCount` = `trader_usesCount` - 1 WHERE `id` = {$CurrentUser['id']};", 'users');
                    if($CurrentUser['trader_usesCount'] <= 0)
                    {
                        header('Location: ?show=ok');
                        safeDie();
                    }
                }

                $parse['TraderMsg_Hide'] = '';
                $parse['TraderMsg_Color'] = 'lime';
                $parse['TraderMsg_Text'] = $_Lang['Trader_Done'];
            }
        }
    }

    $_GET['step'] = 2;
    $_GET['mode'] = $_POST['mode'];
    $_GET['res'] = $_POST['res'];
}

if(isset($_GET['step']) && $_GET['step'] == 2)
{
    if(!isPro($CurrentUser) AND $CurrentUser['trader_usesCount'] <= 0)
    {
        message($_Lang['Trader_CantUseTrader'], $_Lang['Trader_Title']);
    }

    if(!isset($_GET['mode']) || ($_GET['mode'] != 1 && $_GET['mode'] != 2))
    {
        $_GET['mode'] = 1;
    }
    if(!isset($_GET['res']) || !in_array($_GET['res'], array(1, 2, 3)))
    {
        $_GET['res'] = 1;
    }

    $parse['InsertMainResource'] = $_Lang['MainResource_Mode'.$_GET['mode']];
    $parse['InsertOtherResources'] = $_Lang['OtherResources_Mode'.$_GET['mode']];

    switch($_GET['res'])
    {
        case '1':
        {
            // Metal
            $PageTPL = gettemplate('merchant_metal');
            $parse['mod_ma_res_a'] = '2';
            $parse['mod_ma_res_b'] = '4';
            if($_GET['mode'] == 1)
            {
                $parse['Trader_ModeNRes'] = $_Lang['Trader_SellMet'];
                $parse['Trader_NeedOrReceive'] = $_Lang['Trader_Needen'];
                $parse['InsertTraderMode'] = '1';
            }
            else
            {
                $parse['Trader_ModeNRes'] = $_Lang['Trader_BuyMet'];
                $parse['Trader_NeedOrReceive'] = $_Lang['Trader_Receiving'];
                $parse['InsertTraderMode'] = '2';
            }
            $parse['Insert_ResM'] = 'met';
            $parse['Insert_ResA'] = 'cry';
            $parse['Insert_ResB'] = 'deu';
            break;
        }
        case '2':
        {
            // Crystal
            $PageTPL = gettemplate('merchant_crystal');
            $parse['mod_ma_res_a'] = '0.5';
            $parse['mod_ma_res_b'] = '2';
            if($_GET['mode'] == 1)
            {
                $parse['Trader_ModeNRes'] = $_Lang['Trader_SellCry'];
                $parse['Trader_NeedOrReceive'] = $_Lang['Trader_Needen'];
                $parse['InsertTraderMode'] = '1';
            }
            else
            {
                $parse['Trader_ModeNRes'] = $_Lang['Trader_BuyCry'];
                $parse['Trader_NeedOrReceive'] = $_Lang['Trader_Receiving'];
                $parse['InsertTraderMode'] = '2';
            }
            $parse['Insert_ResM'] = 'cry';
            $parse['Insert_ResA'] = 'met';
            $parse['Insert_ResB'] = 'deu';
            break;
        }
        case '3':
        {
            // Deuterium
            $PageTPL = gettemplate('merchant_deuterium');
            $parse['mod_ma_res_a'] = '0.25';
            $parse['mod_ma_res_b'] = '0.5';
            if($_GET['mode'] == 1)
            {
                $parse['Trader_ModeNRes'] = $_Lang['Trader_SellDeu'];
                $parse['Trader_NeedOrReceive'] = $_Lang['Trader_Needen'];
                $parse['InsertTraderMode'] = '1';
            }
            else
            {
                $parse['Trader_ModeNRes'] = $_Lang['Trader_BuyDeu'];
                $parse['Trader_NeedOrReceive'] = $_Lang['Trader_Receiving'];
                $parse['InsertTraderMode'] = '2';
            }
            $parse['Insert_ResM'] = 'deu';
            $parse['Insert_ResA'] = 'met';
            $parse['Insert_ResB'] = 'cry';
            break;
        }
    }
}
else
{
    if(isset($_GET['show']) && $_GET['show'] == 'ok')
    {
        $parse['TraderMsg_Hide'] = '';
        $parse['TraderMsg_Color'] = 'lime';
        $parse['TraderMsg_Text'] = $_Lang['Trader_Done'];
    }
    else
    {
        $parse['TraderMsg_Hide'] = 'inv';
    }

    $PageTPL = gettemplate('merchant_main');
}

if(!isPro($CurrentUser))
{
    $parse['Insert_TraderRight'] = "<a href=\"officers.php\" class=\"orange\">{$_Lang['Trader_Uses_BuyMore']}</a>";
    if($CurrentUser['trader_usesCount'] <= 0)
    {
        $parse['Insert_TraderUsesColor'] = $parse['Insert_AddRed'] = 'red';
        $parse['Insert_TraderRight'] = "<a href=\"officers.php\" class=\"orange\">{$_Lang['Trader_Uses_BlockedBuy']}</a>";
    }
    else if($CurrentUser['trader_usesCount'] < 5)
    {
        $parse['Insert_TraderUsesColor'] = 'orange';
    }
    $parse['Insert_TraderUses'] = prettyNumber($CurrentUser['trader_usesCount']);
}
else
{
    $parse['Insert_TraderUsesColor'] = 'lime';
    $parse['Insert_TraderUses'] = $_Lang['Trader_Uses_Infinite'];
    $parse['Insert_TraderRight'] = "({$_Lang['Trader_Uses_InStandBy']}: ".prettyNumber($CurrentUser['trader_usesCount']).")";
}

$Page = parsetemplate($PageTPL, $parse);

display($Page, $_Lang['Trader_Title']);

?>
