<?php

define('INSIDE', true);

$_EnginePath = './';

include($_EnginePath.'common.php');

loggedCheck();

includeLang('galacticshop');
$_Lang['skinpath'] = $_SkinPath;

$_Lang['Articles'] = '';

$DontGetDarkEnergy = false;

$AdditionalPlanets = $_User['additional_planets'];
$_Lang['shop_items'][9]['price'] = $_Lang['shop_items'][9]['price_array'][$AdditionalPlanets];

if(isset($_GET['darkenergy_bought']) && $_GET['darkenergy_bought'] == 'true')
{
    $_Lang['showMsg'] = sprintf($_Lang['AddSuccess'], $_Lang['_Added'.(isset($_GET['count']) ? intval($_GET['count']) : 0).'DEUnits']);
}

if(isset($_GET['show']))
{
    if($_GET['show'] == 'deform')
    {
        $_Lang['SetActiveMarker'] = '01';
    }
    else if($_GET['show'] == 'shop')
    {
        $_Lang['SetActiveMarker'] = '02';
    }
    else if($_GET['show'] == 'free')
    {
        $_Lang['SetActiveMarker'] = '03';
    }
}

$SQLResult_GetFreeItems = doquery(
    "SELECT * FROM {{table}} WHERE `UserID` = {$_User['id']} AND `Used` = false;",
    'premium_free'
);

if($SQLResult_GetFreeItems->num_rows > 0)
{
    $GetUsernames = array();
    while($Free = $SQLResult_GetFreeItems->fetch_assoc())
    {
        if(!isset($_Lang['FreeItemsList'][$Free['ID']]))
        {
            $_Lang['FreeItemsList'][$Free['ID']] = '';
        }
        $_Lang['FreeItemsList'][$Free['ID']] .= '<tr><td class="b pad ta_left lime" colspan="2"><b>'.$_Lang['shop_items'][$Free['ItemID']]['name'].''.($_Lang['shop_items'][$Free['ItemID']]['duration'] !== false ? '<br/>'.$_Lang['FreeDuration'].': '.$_Lang['shop_items'][$Free['ItemID']]['duration'] : '').'</b></td>';
        $_Lang['FreeItemsList'][$Free['ID']] .= '<td class="b pad ta_left" colspan="2"><b>{USERID_'.$Free['GivenBy'].'}<br/>'.prettyDate('d m Y - H:i:s', $Free['GiveDate'], 1).'</b></td>';
        $_Lang['FreeItemsList'][$Free['ID']] .= '<td class="b pad ta_cent" colspan="2"><input type="button" class="pad3 freeItemUse" id="freeid_'.$Free['ID'].'" value="'.$_Lang['FreeItemUse'].'"/></td></tr>';
        if(!in_array($Free['GivenBy'], $GetUsernames))
        {
            $GetUsernames[$Free['GivenBy']] = $Free['GivenBy'];
        }
        $FreeItems[$Free['ID']] = $Free;
    }
    if(!empty($GetUsernames))
    {
        $SQLResult_GetUsernames = doquery(
            "SELECT `id`, `username` FROM {{table}} WHERE `id` IN (".implode(', ', $GetUsernames).");",
            'users'
        );

        if($SQLResult_GetUsernames->num_rows > 0)
        {
            while($Username = $SQLResult_GetUsernames->fetch_assoc())
            {
                foreach($_Lang['FreeItemsList'] as $Key => $Value)
                {
                    $_Lang['FreeItemsList'][$Key] = str_replace('{USERID_'.$Username['id'].'}', $Username['username'], $Value);
                }
                unset($GetUsernames[$Username['id']]);
            }
        }
        if(!empty($GetUsernames))
        {
            foreach($GetUsernames as $DeleteID)
            {
                foreach($_Lang['FreeItemsList'] as $Key => $Value)
                {
                    if($DeleteID > 0)
                    {
                        $_Lang['FreeItemsList'][$Key] = str_replace('{USERID_'.$Username['id'].'}', '<b class="red">'.$_Lang['User_Deleted'].'</b>', $Value);
                    }
                    else
                    {
                        $_Lang['FreeItemsList'][$Key] = str_replace('{USERID_0}', '<b class="orange">'.$_Lang['Free_GivenBySys'].'</b>', $Value);
                    }
                }
            }
        }
    }
}

if(!empty($_GET['use_freeid']))
{
    $_POST = false;
    $Use_FreeID = intval(str_replace('freeid_', '', $_GET['use_freeid']));
    if($Use_FreeID > 0)
    {
        if(!empty($FreeItems[$Use_FreeID]))
        {
            $_POST['mode'] = 'buyitem';
            $_POST['buyitem_'.$FreeItems[$Use_FreeID]['ItemID']] = $_Lang['BuyIT'];
            $DontGetDarkEnergy = true;
        }
        else
        {
            $_Lang['showError'] = $_Lang['Free_BadItemGiven'];
            $_Lang['SetActiveMarker'] = '03';
        }
    }
    else
    {
        $_Lang['showError'] = $_Lang['Free_BadIDGiven'];
        $_Lang['SetActiveMarker'] = '03';
    }
}

if(!empty($_POST['mode']))
{
    switch($_POST['mode'])
    {
        // Buy Dark Energy
        case 'buyde':
        {
            $_Lang['SetActiveMarker'] = '01';

            // Currently it breaks here just to ensure, that this section won't be executed without proper code
            break;

            // -------------------------------------
            // PLACE YOUR PAYMENT SYSTEM'S CODE HERE
            // -------------------------------------
            //
            // Variables to set
            // $addDarkEnergy   - How much Dark Energy user should get
            // $userInputCode   - User's input, most likely the SMS code they have received
            //                  - Required to save it to the DB. If you don't need that functionality,
            //                    delete code marked with "SAVECODETODB" comment
            //
            // Remember that you also have to implement how this section is presented to the user.
            // Place your implementation in galacticshop.tpl (template file) in place of this text:
            // "THIS OPTION IS NOT IMPLEMENTED YET"
            //

            $addDarkEnergy = 0;
            $userInputCode = "";

            if($_User['referred'] > 0)
            {
                if(empty($GlobalParsedTasks[$_User['referred']]['tasks_done_parsed']))
                {
                    $GetUserTasksDone = doquery("SELECT `id`, `tasks_done` FROM {{table}} WHERE `id` = {$_User['referred']} LIMIT 1;", 'users', true);
                    if($GetUserTasksDone['id'] == $_User['referred'])
                    {
                        unset($GetUserTasksDone['id']);
                        Tasks_CheckUservar($GetUserTasksDone);
                        $GlobalParsedTasks[$_User['referred']] = $GetUserTasksDone;
                        $ThisTaskUser        = $GlobalParsedTasks[$_User['referred']];
                        $ThisTaskUser['id'] = $_User['referred'];
                    }
                }
                else
                {
                    $ThisTaskUser        = $GlobalParsedTasks[$_User['referred']];
                    $ThisTaskUser['id'] = $_User['referred'];
                }

                if(!empty($ThisTaskUser))
                {
                    $ThisUserID = $_User['id'];

                    Tasks_TriggerTask($ThisTaskUser, 'INVITEDUSER_BOUGHT_DE', array
                    (
                        'mainCheck' => function($JobArray) use ($addDarkEnergy)
                        {
                            if($addDarkEnergy < $JobArray['count'])
                            {
                                return true;
                            }
                        }
                    ));
                    Tasks_TriggerTask($ThisTaskUser, 'INVITEDUSERS_BOUGHT_DE_LIMIT', array
                    (
                        'mainCheck' => function($JobArray, $ThisCat, $TaskID, $JobID) use ($ThisTaskUser, $addDarkEnergy)
                        {
                            return Tasks_TriggerTask_MainCheck_Progressive($JobArray, $ThisCat, $TaskID, $JobID, $ThisTaskUser, $addDarkEnergy);
                        }
                    ));
                    Tasks_TriggerTask($ThisTaskUser, 'INVITEDUSERS_BOUGHT_DE_USERCOUNT', array
                    (
                        'mainCheck' => function($JobArray, $ThisCat, $TaskID, $JobID) use (&$ThisTaskUser, $ThisUserID)
                        {
                            global $UserTasksUpdate;

                            if(!empty($UserTasksUpdate[$ThisTaskUser['id']]['jobdata'][$ThisCat][$TaskID][$JobID]))
                            {
                                $ThisTaskUser['tasks_done_parsed']['jobdata'][$ThisCat][$TaskID][$JobID] = $UserTasksUpdate[$ThisTaskUser['id']]['jobdata'][$ThisCat][$TaskID][$JobID];
                            }
                            $ThisJobData = $ThisTaskUser['tasks_done_parsed']['jobdata'][$ThisCat][$TaskID][$JobID];
                            if(!empty($ThisJobData['users']) AND in_array($ThisUserID, $ThisJobData['users']))
                            {
                                return true;
                            }

                            $ThisTaskUser['tasks_done_parsed']['jobdata'][$ThisCat][$TaskID][$JobID]['users'][] = $ThisUserID;
                            $UserTasksUpdate[$ThisTaskUser['id']]['jobdata'][$ThisCat][$TaskID][$JobID] = $ThisTaskUser['tasks_done_parsed']['jobdata'][$ThisCat][$TaskID][$JobID];
                            if(!empty($UserTasksUpdate[$ThisTaskUser['id']]['status'][$ThisCat][$TaskID][$JobID]))
                            {
                                $ThisTaskUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] = $UserTasksUpdate[$ThisTaskUser['id']]['status'][$ThisCat][$TaskID][$JobID];
                            }
                            $ThisTaskUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] += 1;
                            if($ThisTaskUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] < $JobArray[$JobArray['statusField']])
                            {
                                $UserTasksUpdate[$ThisTaskUser['id']]['status'][$ThisCat][$TaskID][$JobID] = $ThisTaskUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID];
                                return true;
                            }
                        }
                    ));
                }
            }

            // SAVECODETODB
            // Send this Action to PremiumCodes Log
            $SecurePassedCode = preg_replace('#[^0-9a-zA-Z]{1,}#si', '', $userInputCode);
            doquery("INSERT INTO {{table}} VALUES (NULL, {$_User['id']}, UNIX_TIMESTAMP(), ".intval($_POST['option']).", '{$SecurePassedCode}');", 'premiumcodes');
            // END OF SAVECODETODB

            $result = doquery("UPDATE {{table}} SET `darkEnergy` = `darkEnergy` + {$addDarkEnergy} WHERE `id` = {$_User['id']};", 'users');
            if($result == true)
            {
                $_User['darkEnergy'] += $addDarkEnergy;
                if($_User['referred'] > 0)
                {
                    $Provision = floor($addDarkEnergy * REFERING_PROVISION);
                    doquery("UPDATE {{table}} SET `darkEnergy` = `darkEnergy` + {$Provision} WHERE `id` = {$_User['referred']};", 'users');
                    doquery("UPDATE {{table}} SET `provisions_granted` = `provisions_granted` + {$Provision} WHERE `referrer_id` = {$_User['referred']} AND `newuser_id` = {$_User['id']};", 'referring_table');
                }
                header('Location: galacticshop.php?darkenergy_bought=true&count='.$addDarkEnergy.'&show=deform');
                safeDie();
            }
            else
            {
                $_Lang['showError'] = $_Lang['ErrorSQL'];
            }
            break;
        }
        // Buy item
        case 'buyitem':
        {
            if($DontGetDarkEnergy !== true)
            {
                $_Lang['SetActiveMarker'] = '02';
            }
            else
            {
                $_Lang['SetActiveMarker'] = '03';
            }
            if((array)$_Lang['shop_items'] === $_Lang['shop_items'])
            {
                $Keys = array_keys($_POST);
                $KeysCount = count($Keys);
                if($KeysCount == 2)
                {
                    sort($Keys);
                    $ItemID = intval(substr($Keys[0], 8));
                    if($ItemID >= 1)
                    {
                        if((array)$_Lang['shop_items'][$ItemID] === $_Lang['shop_items'][$ItemID])
                        {
                            if((!isset($_Lang['shop_items'][$ItemID]['buyable']) || $_Lang['shop_items'][$ItemID]['buyable'] !== false) || $DontGetDarkEnergy === true)
                            {
                                if($_User['darkEnergy'] >= $_Lang['shop_items'][$ItemID]['price'] || $DontGetDarkEnergy === true)
                                {
                                    // ProAccounts
                                    if($ItemID == 1 OR $ItemID == 2 OR $ItemID == 11 OR $ItemID == 13)
                                    {
                                        $Duration = $_Lang['shop_items'][$ItemID]['duration_days'] * TIME_DAY;
                                        if($Duration > 0)
                                        {
                                            $QueryTable = 'users';
                                            if($_User['pro_time'] > time())
                                            {
                                                $Query = "UPDATE {{table}} SET `pro_time` = `pro_time` + {$Duration} WHERE `id` = {$_User['id']};";
                                                $_User['pro_time'] += $Duration;
                                                $Msg = $_Lang['ProAccountExtended'];
                                            }
                                            else
                                            {
                                                $Query = "UPDATE {{table}} SET `pro_time` = UNIX_TIMESTAMP() + {$Duration} WHERE `id` = {$_User['id']};";
                                                $_User['pro_time'] = time() + $Duration;
                                                $Msg = $_Lang['ProAccountAdded'];
                                            }
                                        }
                                        else
                                        {
                                            $_Lang['showError'] = $_Lang['ErrorConfigError'];
                                        }
                                        //End of ProAccounts
                                        // ---------------------
                                        // Add fields to Planet
                                    }
                                    elseif($ItemID == 3)
                                    {
                                        if($_Planet['id'] > 0)
                                        {
                                            if($_Planet['planet_type'] == 1)
                                            {
                                                $AddFields = $_Lang['shop_items'][$ItemID]['addFields'];
                                                if($AddFields > 0)
                                                {
                                                    $QueryTable = 'planets';
                                                    $Query = "UPDATE {{table}} SET `field_max` = `field_max` + {$AddFields} WHERE `id` = {$_Planet['id']};";
                                                    $Msg = $_Lang['PlanetFieldsAdded'];
                                                }
                                                else
                                                {
                                                    $_Lang['showError'] = $_Lang['ErrorConfigError'];
                                                }
                                            }
                                            else
                                            {
                                                $_Lang['showError'] = $_Lang['ErrorAddFieldsNoMoon'];
                                            }
                                        }
                                        else
                                        {
                                            $_Lang['showError'] = $_Lang['ErrorNoPlanetID'];
                                        }
                                        //End of Add fields to Planet
                                        // ----------------------------
                                        // Add Officers or Spy Jammer
                                    }
                                    elseif(($ItemID >= 4 AND $ItemID <= 8) OR $ItemID == 12)
                                    {
                                        $Duration = $_Lang['shop_items'][$ItemID]['duration_days'] * TIME_DAY;
                                        if($Duration > 0)
                                        {
                                            $QueryTable = 'users';
                                            switch($ItemID)
                                            {
                                                case 4:
                                                    $Field = 'spy_jam_time';
                                                    $MsgAdd = $_Lang['SpyJamAdd'];
                                                    $MsgExt = $_Lang['SpyJamExt'];
                                                    break;
                                                case 5:
                                                    $Field = 'geologist_time';
                                                    $MsgAdd = $_Lang['GeologistAdd'];
                                                    $MsgExt = $_Lang['GeologistExt'];
                                                    break;
                                                case 6:
                                                    $Field = 'engineer_time';
                                                    $MsgAdd = $_Lang['EngineerAdd'];
                                                    $MsgExt = $_Lang['EngineerExt'];
                                                    break;
                                                case 7:
                                                    $Field = 'admiral_time';
                                                    $MsgAdd = $_Lang['AdmiralAdd'];
                                                    $MsgExt = $_Lang['AdmiralExt'];
                                                    break;
                                                case 8:
                                                    $Field = 'technocrat_time';
                                                    $MsgAdd = $_Lang['TechnocratAdd'];
                                                    $MsgExt = $_Lang['TechnocratExt'];
                                                    break;
                                                case 12:
                                                    $Field = 'geologist_time';
                                                    $MsgAdd = $_Lang['GeologistAdd'];
                                                    $MsgExt = $_Lang['GeologistExt'];
                                                    break;
                                            }
                                            if($_User[$Field] > time())
                                            {
                                                $Query = "UPDATE {{table}} SET `{$Field}` = `{$Field}` + {$Duration} WHERE `id` = {$_User['id']};";
                                                $_User[$Field] += $Duration;
                                                $Msg = $MsgExt;
                                            }
                                            else
                                            {
                                                $Query = "UPDATE {{table}} SET `{$Field}` = UNIX_TIMESTAMP() + {$Duration} WHERE `id` = {$_User['id']};";
                                                $_User[$Field] = time() + $Duration;
                                                $Msg = $MsgAdd;
                                            }
                                        }
                                        else
                                        {
                                            $_Lang['showError'] = $_Lang['ErrorConfigError'];
                                        }
                                        //End of Add Officers or Spy Jammer
                                        // ----------------------------
                                        // Add Additional Planet Slots
                                    }
                                    elseif($ItemID == 9)
                                    {
                                        if($AdditionalPlanets < 3)
                                        {
                                            $QueryTable = 'users';
                                            $Query = "UPDATE {{table}} SET `additional_planets` = `additional_planets` + 1 WHERE `id` = {$_User['id']};";
                                            if($AdditionalPlanets < 2)
                                            {
                                                $TempSprintf = $_Lang['PlanetSlots_left'][$AdditionalPlanets];
                                                $Msg = sprintf($_Lang['PlanetBought'], $TempSprintf);
                                            }
                                            else
                                            {
                                                $Msg = sprintf($_Lang['PlanetBoughtLast']);
                                            }
                                            $AdditionalPlanets++;
                                            $_User['additional_planets']++;
                                        }
                                        else
                                        {
                                            $_Lang['showError'] = $_Lang['ErrorCanBuyOnly3Planets'];
                                        }
                                        //End of Add Additional Planet Slots
                                        // ----------------------------
                                        // Add Trader Uses (Transactions)
                                    }
                                    elseif(in_array($ItemID, array(14, 15, 16)))
                                    {
                                        $QueryTable = 'users';
                                        $Query = "UPDATE {{table}} SET `trader_usesCount` = `trader_usesCount` + {$_Lang['shop_items'][$ItemID]['amount']} WHERE `id` = {$_User['id']};";
                                        $Msg = sprintf($_Lang['TraderUsesBought'], prettyNumber($_Lang['shop_items'][$ItemID]['amount']));
                                        $_User['trader_usesCount'] += $_Lang['shop_items'][$ItemID]['amount'];
                                    }
                                    //End of Add Trader Uses (Transactions)

                                    // Send Query here!
                                    if(!empty($Query) AND !empty($QueryTable))
                                    {
                                        if($DontGetDarkEnergy !== true)
                                        {
                                            $Result = doquery("UPDATE {{table}} SET `darkEnergy` = `darkEnergy` - {$_Lang['shop_items'][$ItemID]['price']} WHERE `id` = {$_User['id']};", 'users');
                                        }
                                        else
                                        {
                                            $Result = TRUE;
                                        }
                                        if($Result == TRUE)
                                        {
                                            if($DontGetDarkEnergy !== true)
                                            {
                                                $_User['darkEnergy'] -= $_Lang['shop_items'][$ItemID]['price'];
                                            }
                                            else
                                            {
                                                doquery("UPDATE {{table}} SET `Used` = true, `UseDate` = UNIX_TIMESTAMP() WHERE `ID` = {$Use_FreeID};", 'premium_free');
                                                unset($_Lang['FreeItemsList'][$Use_FreeID]);
                                            }

                                            // Send PremiumPayments to Log
                                            doquery("INSERT INTO {{table}} VALUES (NULL, {$_User['id']}, UNIX_TIMESTAMP(), {$ItemID}, ".($DontGetDarkEnergy === true ? 'true' : 'false').");", 'premiumpayments');
                                            // ---

                                            $Result = doquery($Query, $QueryTable);
                                            if($Result == TRUE)
                                            {
                                                $_Lang['showMsg'] = $Msg;
                                            }
                                            else
                                            {
                                                $_Lang['showError'] = $_Lang['ErrorSQL'];
                                            }
                                        }
                                        else
                                        {
                                            $_Lang['showError'] = $_Lang['ErrorSQL'];
                                        }
                                    }
                                    else
                                    {
                                        if(empty($_Lang['showError']))
                                        {
                                            $_Lang['showError'] = $_Lang['ErrorNoQuery'];
                                        }
                                    }
                                }
                                else
                                {
                                    $_Lang['showError'] = $_Lang['ErrorNoEnoughDE'];
                                }
                            }
                            else
                            {
                                $_Lang['showError'] = $_Lang['ErrorNotBuyable'];
                            }
                        }
                        else
                        {
                            $_Lang['showError'] = $_Lang['ErrorBadIDGiven'];
                        }
                    }
                    else
                    {
                        $_Lang['showError'] = $_Lang['ErrorBadIDGiven'];
                    }
                }
                else
                {
                    if($KeysCount > 2)
                    {
                        $_Lang['showError'] = $_Lang['ErrorTooMuchItemIDGiven'];
                    }
                    else
                    {
                        $_Lang['showError'] = $_Lang['ErrorNoItemIDGiven'];
                    }
                }
            }
            else
            {
                $_Lang['showError'] = $_Lang['ErrorCurrentlyNothingToSell'];
            }
            break;
        }
    }
}

if(empty($_Lang['FreeItemsList']))
{
    $_Lang['FreeItemsList'] = '<tr><th colspan="6" class="pad orange">'.$_Lang['Free_NoFreeItems'].'</th></tr>';
}
else
{
    $_Lang['FreeItemsList'] = implode("\n", $_Lang['FreeItemsList']);
}

$BreakLine = '<tr><td class="bl"></td></tr>';

if(!empty($_Lang['showError']))
{
    $_Lang['showError'] = '<tr><th colspan="6" class="red pad">'.$_Lang['showError'].'</th></tr>'.$BreakLine;
}
if(!empty($_Lang['showMsg']))
{
    $_Lang['showMsg'] = '<tr><th colspan="6" class="lime pad">'.$_Lang['showMsg'].'</th></tr>'.$BreakLine;
}
$_Lang['shop_items'][9]['price'] = $_Lang['shop_items'][9]['price_array'][$AdditionalPlanets];

if((array)$_Lang['shop_items'] === $_Lang['shop_items'])
{
    foreach($_Lang['shop_items'] as $key => $val)
    {
        if(!isset($val['buyable']) || $val['buyable'] !== false)
        {
            $_Lang['Articles'] .= '<tr><td class="b" style="text-align: left;" colspan="1"><b style="color: lime;">'.$val['name'].'</b></td>';
            $_Lang['Articles'] .= '<td colspan="4" class="b" style="text-align: left;">'.$val['desc'].'</td>';
            if(empty($val['action']))
            {
                if($val['price'] > 0 || $val['price'] === 0)
                {
                    $_Lang['Articles'] .= '<td colspan="1" class="b" style="text-align: center;"><b>'.$val['price'].'</b> '.$_Lang['DEprice'].'<br/><input type="submit" class="pad3 marg_top" name="buyitem_'.$key.'" value="'.$_Lang['BuyIT'].'" onclick="return confirm(\''.$_Lang['AreYouSure'].'\');"/></td></tr>';
                }
                else
                {
                    $_Lang['Articles'] .= '<td colspan="1" class="b" style="text-align: center;"><b style="color: red;">'.$_Lang['CannotBuyItAnymore'].'</b></td></tr>';
                }
            }
            else
            {
                switch($val['action'])
                {
                    case 'redirect':
                        $_Lang['Articles'] .= '<td colspan="1" class="b" style="text-align: center;"><b>'.$val['price'].'</b> '.$_Lang['DEprice'].'<br/><input type="button" class="pad3 marg_top" value="'.$_Lang['GoToBuyPlace'].'" onclick="document.location = \''.$val['data']['url'].'\';"/></td></tr>';
                        break;
                }
            }
        }
    }
}
if(empty($_Lang['Articles']))
{
    $_Lang['Articles'] = '<tr><td class="b" colspan="6" style="text-align: center;"><b>'.$_Lang['NoItemsOnStock'].'</b></td></tr>';
}

if(isset($_GET['darkenergy_bought']) && $_GET['darkenergy_bought'] == 'true')
{
    if(!isset($_GET['count']))
    {
        $_GET['count'] = 5;
    }
    $CheckDEFormTabs = array(5 => '01', 15 => '02', 25 => '03', 45 => '04');
    $_Lang['SetActiveFormTab'] = $CheckDEFormTabs[$_GET['count']];
}

$_Lang['DarkEnergy_Counter'] = $_User['darkEnergy'];
if($_User['darkEnergy'] >= 15)
{
    $_Lang['DarkEnergy_Color'] = 'lime';
}
elseif($_User['darkEnergy'] > 0)
{
    $_Lang['DarkEnergy_Color'] = 'orange';
}
else
{
    $_Lang['DarkEnergy_Color'] = 'red';
}

$page = parsetemplate(gettemplate('galacticshop'), $_Lang);
display($page, $_Lang['Title'], false);

?>
