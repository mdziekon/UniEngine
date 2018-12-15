<?php

define('INSIDE', true);
define('IN_ADMIN', true);

$_SetAccessLogPreFilename = 'admin/';
$_SetAccessLogPath = '../';
$_EnginePath = './../';

include($_EnginePath.'common.php');

$MerchantRatio = array('metal' => 1, 'crystal' => 2, 'deuterium' => 4);

if(CheckAuth('programmer'))
{
    includeLang('admin/ship_calculations');
    $TPL = gettemplate('admin/ship_calculations_body');

    $ShipID = 0;
    $TargetID = 0;

    if(isset($_GET['ship']) && $_GET['ship'] > 0)
    {
        $ShipID = intval($_GET['ship']);
        $TargetID = isset($_GET['ship2']) ? intval($_GET['ship2']) : 0;
        if(!in_array($TargetID, $_Vars_ElementCategories['fleet']))
        {
            $TargetID = 0;
        }

        if(in_array($ShipID, $_Vars_ElementCategories['fleet']))
        {
            // Do calculations in here!
            $_Lang['Input_Table_Hide'] = 'hide';
            $TPL_Headers = gettemplate('admin/ship_calculations_headers');
            $TPL_Rows = gettemplate('admin/ship_calculations_rows');

            $Ship['Price'] = (($_Vars_Prices[$ShipID]['metal'] * $MerchantRatio['metal']) +
                            ($_Vars_Prices[$ShipID]['crystal'] * $MerchantRatio['crystal']) +
                            ($_Vars_Prices[$ShipID]['deuterium'] * $MerchantRatio['deuterium']));
            $Ship['Hull'] = ($_Vars_Prices[$ShipID]['metal'] + $_Vars_Prices[$ShipID]['crystal']) / 10;
            $Ship['Life'] = $Ship['Hull'] + $_Vars_CombatData[$ShipID]['shield'];

            if($TargetID > 0)
            {
                if($_Vars_CombatData[$ShipID]['attack'] > 0)
                {
                    $_Lang['Input_EnableProfitabilitySort'] = 'true';
                    $Ship2['Hull'] = ($_Vars_Prices[$TargetID]['metal'] + $_Vars_Prices[$TargetID]['crystal']) / 10;
                    $Ship2['Life'] = $Ship2['Hull'] + $_Vars_CombatData[$TargetID]['shield'];

                    $Ship['Profitability'] = $_Vars_CombatData[$ShipID]['attack'] / $Ship2['Life'];
                    if($Ship['Profitability'] > 1)
                    {
                        $Ship['Profitability'] = 1;
                    }
                    if($_Vars_CombatData[$ShipID]['sd'][$TargetID] > 1)
                    {
                        $Ship['Profitability'] *= $_Vars_CombatData[$ShipID]['sd'][$TargetID];
                    }
                    $Ship['Profitability'] = $Ship['Price'] / $Ship['Profitability'];
                }
                else
                {
                    $TargetID = 0;
                }
            }

            $_Lang['Input_InsertRows'] = '';
            foreach($_Vars_ElementCategories['fleet'] as $ID)
            {
                $Row = array();
                if($ID == $ShipID OR $ID == $TargetID)
                {
                    if($ID == $ShipID)
                    {
                        $Row['Input_CurrentColor'] = 'lime';
                    }
                    else
                    {
                        $Row['Input_CurrentColor'] = 'orange';
                    }
                    $Row['Input_IsCurrentShip'] = 'current';
                }
                $Row['Input_ID'] = $ID;
                $Row['Input_Name'] = $_Lang['tech'][$ID];
                if($_Vars_Prices[$ID]['metal'] > 0 AND $_Vars_Prices[$ShipID]['metal'] > 0)
                {
                    $Row['Input_MetalRatio']= round($_Vars_Prices[$ID]['metal'] / $_Vars_Prices[$ShipID]['metal'], 4);
                }
                else
                {
                    $Row['Input_MetalRatio']= '-';
                }
                if($_Vars_Prices[$ID]['crystal'] > 0 AND $_Vars_Prices[$ShipID]['crystal'] > 0)
                {
                    $Row['Input_CrystalRatio']= round($_Vars_Prices[$ID]['crystal'] / $_Vars_Prices[$ShipID]['crystal'], 4);
                }
                else
                {
                    $Row['Input_CrystalRatio']= '-';
                }
                if($_Vars_Prices[$ID]['deuterium'] > 0 AND $_Vars_Prices[$ShipID]['deuterium'] > 0)
                {
                    $Row['Input_DeuteriumRatio']= round($_Vars_Prices[$ID]['deuterium'] / $_Vars_Prices[$ShipID]['deuterium'], 4);
                }
                else
                {
                    $Row['Input_DeuteriumRatio']= '-';
                }
                $Row['Price'] =
                    ($_Vars_Prices[$ID]['metal'] * $MerchantRatio['metal']) +
                    ($_Vars_Prices[$ID]['crystal'] * $MerchantRatio['crystal']) +
                    ($_Vars_Prices[$ID]['deuterium'] * $MerchantRatio['deuterium']);

                $Row['Input_TotalPriceRatio'] = round($Row['Price']/ $Ship['Price'], 4);

                if($_Vars_CombatData[$ID]['attack'] > 0 AND $_Vars_CombatData[$ShipID]['attack'] > 0)
                {
                    $Row['Input_ForceRatio'] = round($_Vars_CombatData[$ID]['attack'] / $_Vars_CombatData[$ShipID]['attack'], 4);
                }
                else
                {
                    $Row['Input_ForceRatio'] = '-';
                }
                if($_Vars_CombatData[$ID]['shield'] > 0 AND $_Vars_CombatData[$ShipID]['shield'] > 0)
                {
                    $Row['Input_ShieldRatio'] = round($_Vars_CombatData[$ID]['shield'] / $_Vars_CombatData[$ShipID]['shield'], 4);
                }
                else
                {
                    $Row['Input_ShieldRatio'] = '-';
                }
                $Row['Hull'] = ($_Vars_Prices[$ID]['metal'] + $_Vars_Prices[$ID]['crystal']) / 10;
                $Row['Life'] = $Row['Hull'] + $_Vars_CombatData[$ID]['shield'];

                $Row['Input_HullRatio'] = round($Row['Hull'] / $Ship['Hull'], 4);
                if($_Vars_CombatData[$ShipID]['attack'] > 0)
                {
                    $Row['NeedToDestroyOne'] = $_Vars_CombatData[$ShipID]['attack'] / $Row['Life'];
                    if($Row['NeedToDestroyOne'] > 1)
                    {
                        $Row['NeedToDestroyOne'] = 1;
                    }
                    if($_Vars_CombatData[$ShipID]['sd'][$ID] > 1)
                    {
                        $Row['DestroyInOneTurn'] = $Row['NeedToDestroyOne'] * $_Vars_CombatData[$ShipID]['sd'][$ID];
                    }
                    else
                    {
                        $Row['DestroyInOneTurn'] = $Row['NeedToDestroyOne'];
                    }
                    $Row['Input_DestroyInOneTurn'] = floor($Row['DestroyInOneTurn']);
                    if($Row['Input_DestroyInOneTurn'] < 1)
                    {
                        $Row['Input_DestroyInOneTurn'] = '('.ceil(1 / $Row['DestroyInOneTurn']).')';
                    }
                }
                else
                {
                    $Row['Input_DestroyInOneTurn']= '-';
                }
                $Row['Input_RapidFireValue'] = $_Vars_CombatData[$ShipID]['sd'][$ID];

                if($TargetID == 0)
                {
                    $Row['Input_CompareProfitability'] = '0';
                }
                else
                {
                    if($_Vars_CombatData[$ID]['attack'] > 0)
                    {
                        $Row['Profitability'] = $_Vars_CombatData[$ID]['attack'] / $Ship2['Life'];
                        if($Row['Profitability'] > 1)
                        {
                            $Row['Profitability'] = 1;
                        }
                        if($_Vars_CombatData[$ID]['sd'][$TargetID] > 1)
                        {
                            $Row['Profitability'] *= $_Vars_CombatData[$ID]['sd'][$TargetID];
                        }
                        $Row['Profitability'] = $Row['Price'] / $Row['Profitability'];

                        $Row['Input_CompareProfitability'] = $Ship['Profitability'] / $Row['Profitability'];
                    }
                    else
                    {
                        $Row['Input_CompareProfitability'] = '0';
                    }
                }

                $_Lang['Input_InsertRows'] .= parsetemplate($TPL_Rows, $Row);
            }

            $_Lang['Input_InsertHeaders'] = parsetemplate($TPL_Headers, $_Lang);
        }
        else
        {
            $_Lang['Table_SelectShip'] = $_Lang['Table_BadShip'];
        }
    }

    $_Lang['Input_Selector_ShipList'] = '';
    $_Lang['Input_Selector_ShipList2'] = '';
    foreach($_Vars_ElementCategories['fleet'] as $ID)
    {
        $_Lang['Input_Selector_ShipList'] .= "<option value=\"{$ID}\" ".($ID == $ShipID ? 'selected' : '').">{$_Lang['tech'][$ID]}</option>";
        $_Lang['Input_Selector_ShipList2'] .= "<option value=\"{$ID}\" ".($ID == $TargetID ? 'selected' : '').">{$_Lang['tech'][$ID]}</option>";
    }

    display(parsetemplate($TPL, $_Lang), $_Lang['Page_Title'], false, true);
}
else
{
    message($_Lang['sys_noalloaw'], $_Lang['sys_noaccess']);
}

?>
