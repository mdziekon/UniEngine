<?php

if(IN_ALLYPAGE !== true)
{
    header('Location: index.php');
    die();
}

if($_ThisUserRank['warnpact'] === true)
{
    $CanManage = true;
}
else
{
    $CanManage = false;
}

function SendNotification($AllyID, $AllyRanks, $Message)
{
    global $_Vars_AllyRankLabels;

    $AllyRanks = json_decode($AllyRanks, true);
    foreach($AllyRanks as $RankID => $RankData)
    {
        foreach($RankData as $DataID => $DataVal)
        {
            $ParsedRanks[$RankID][$_Vars_AllyRankLabels[$DataID]] = $DataVal;
        }
        if($ParsedRanks[$RankID]['warnpact'] === true)
        {
            $RanksID[] = $RankID;
        }
    }
    if(!empty($RanksID))
    {
        $RanksID = implode(',', $RanksID);
        $Query_GetUsers = '';
        $Query_GetUsers .= "SELECT `id` FROM {{table}} WHERE ";
        $Query_GetUsers .= "`ally_id` = {$AllyID} AND `ally_rank_id` IN ({$RanksID})";
        $Query_GetUsers .= "; -- ally.pactslist.php|SendNotification|GetUsers";
        $Result_GetUsers = doquery($Query_GetUsers, 'users');
        while($FetchData = $Result_GetUsers->fetch_assoc())
        {
            $UserIDs[] = $FetchData['id'];
        }

        if(!empty($UserIDs))
        {
            Cache_Message($UserIDs, 0, null, 2, $Message['from'], $Message['subject'], $Message['content']);
        }
    }
}

if(isset($_GET['do']))
{
    $_MsgBox['color'] = 'red';
    if($CanManage)
    {
        $Manage_CMD = (isset($_GET['cmd']) ? intval($_GET['cmd']) : null);
        $Manage_AID = (isset($_GET['aid']) ? intval($_GET['aid']) : null);
        if($Manage_AID > 0)
        {
            if(in_array($Manage_CMD, array(1, 2, 3, 4, 5)))
            {
                $Query_CheckPact = '';
                $Query_CheckPact .= "SELECT `pact`.*, `ally`.`ally_ranks` FROM {{table}} AS `pact` ";
                $Query_CheckPact .= "LEFT JOIN `{{prefix}}alliance` AS `ally` ON ";
                $Query_CheckPact .= "`ally`.`id` = IF(`pact`.`AllyID_Sender` = {$Ally['id']}, `pact`.`AllyID_Owner`, `pact`.`AllyID_Sender`) ";
                $Query_CheckPact .= "WHERE ";
                $Query_CheckPact .= "(`pact`.`AllyID_Sender` = {$Ally['id']} AND `pact`.`AllyID_Owner` = {$Manage_AID}) OR ";
                $Query_CheckPact .= "(`pact`.`AllyID_Owner` = {$Ally['id']} AND `pact`.`AllyID_Sender` = {$Manage_AID}) ";
                $Query_CheckPact .= "LIMIT 1; -- ally.pactslist.php|CheckPact";
                $Result_CheckPact = doquery($Query_CheckPact, 'ally_pacts', true);
                if($Result_CheckPact['AllyID_Sender'] > 0)
                {
                    $Manage_IsSender = ($Result_CheckPact['AllyID_Sender'] == $Ally['id'] ? true : false);

                    if($Manage_CMD == 1)
                    {
                        // User wants to Remove this Pact
                        if($Result_CheckPact['Active'] == 1)
                        {
                            $Query_RemovePact = '';
                            $Query_RemovePact .= "DELETE FROM {{table}} WHERE ";
                            $Query_RemovePact .= "`AllyID_Sender` = {$Result_CheckPact['AllyID_Sender']} AND ";
                            $Query_RemovePact .= "`AllyID_Owner` = {$Result_CheckPact['AllyID_Owner']} ";
                            $Query_RemovePact .= "LIMIT 1; -- ally.pactslist.php|RemovePact";
                            doquery($Query_RemovePact, 'ally_pacts');

                            $Message = array();
                            $Message['msg_id'] = '102';
                            $Message['args'] = array($Ally['id'], $Ally['ally_name']);
                            $Message = json_encode($Message);
                            $Message = array('from' => '005', 'subject' => '024', 'content' => $Message);
                            SendNotification($Manage_AID, $Result_CheckPact['ally_ranks'], $Message);

                            $_MsgBox['color'] = 'lime';
                            $_MsgBox['text'] = $_Lang['AllyPacts_Msg_OK_Remove'];
                        }
                        else
                        {
                            $_MsgBox['text'] = $_Lang['AllyPacts_Msg_PactRemove_NonActive'];
                        }
                    }
                    else if($Manage_CMD == 2)
                    {
                        // User wants to RollBack this Pact
                        if($Manage_IsSender)
                        {
                            if($Result_CheckPact['Active'] == 0)
                            {
                                $Query_RollbackPact = '';
                                $Query_RollbackPact .= "DELETE FROM {{table}} WHERE ";
                                $Query_RollbackPact .= "`AllyID_Sender` = {$Result_CheckPact['AllyID_Sender']} AND ";
                                $Query_RollbackPact .= "`AllyID_Owner` = {$Result_CheckPact['AllyID_Owner']} ";
                                $Query_RollbackPact .= "LIMIT 1; -- ally.pactslist.php|RollbackPact";
                                doquery($Query_RollbackPact, 'ally_pacts');

                                $Message = array();
                                $Message['msg_id'] = '097';
                                $Message['args'] = array($Ally['id'], $Ally['ally_name']);
                                $Message = json_encode($Message);
                                $Message = array('from' => '005', 'subject' => '024', 'content' => $Message);
                                SendNotification($Manage_AID, $Result_CheckPact['ally_ranks'], $Message);

                                $_MsgBox['color'] = 'lime';
                                $_MsgBox['text'] = $_Lang['AllyPacts_Msg_OK_Rollback'];
                            }
                            else
                            {
                                $_MsgBox['text'] = $_Lang['AllyPacts_Msg_PactRollback_Active'];
                            }
                        }
                        else
                        {
                            $_MsgBox['text'] = $_Lang['AllyPacts_Msg_PactRollback_NotSender'];
                        }
                    }
                    elseif($Manage_CMD == 3)
                    {
                        // User wants to Remove your (sent by your ally) change type proposal
                        if($Manage_IsSender)
                        {
                            $Manage_CheckField = 'Change_Sender';
                        }
                        else
                        {
                            $Manage_CheckField = 'Change_Owner';
                        }
                        if($Result_CheckPact[$Manage_CheckField] > 0)
                        {
                            $Query_UpdatePactType = '';
                            $Query_UpdatePactType .= "UPDATE {{table}} SET ";
                            $Query_UpdatePactType .= "`{$Manage_CheckField}` = 0 ";
                            $Query_UpdatePactType .= "WHERE ";
                            $Query_UpdatePactType .= "`AllyID_Sender` = {$Result_CheckPact['AllyID_Sender']} AND ";
                            $Query_UpdatePactType .= "`AllyID_Owner` = {$Result_CheckPact['AllyID_Owner']} ";
                            $Query_UpdatePactType .= "LIMIT 1; -- ally.pactslist.php|UpdatePactType|StopChange";
                            doquery($Query_UpdatePactType, 'ally_pacts');

                            $Message = array();
                            $Message['msg_id'] = '103';
                            $Message['args'] = array($Ally['id'], $Ally['ally_name']);
                            $Message = json_encode($Message);
                            $Message = array('from' => '005', 'subject' => '024', 'content' => $Message);
                            SendNotification($Manage_AID, $Result_CheckPact['ally_ranks'], $Message);

                            $_MsgBox['color'] = 'lime';
                            $_MsgBox['text'] = $_Lang['AllyPacts_Msg_OK_StopChange'];
                        }
                        else
                        {
                            $_MsgBox['text'] = $_Lang['AllyPacts_Msg_PactChange_NothingToStop'];
                        }
                    }
                    else if($Manage_CMD == 4)
                    {
                        // User wants to Accept new Pact proposal or change type proposal
                        if($Result_CheckPact['Active'] == 0)
                        {
                            if(!$Manage_IsSender)
                            {
                                // User wants to Accept new Pact proposal
                                $Query_AcceptNewPact = '';
                                $Query_AcceptNewPact .= "UPDATE {{table}} SET ";
                                $Query_AcceptNewPact .= "`Active` = 1, `Date` = {$Time} ";
                                $Query_AcceptNewPact .= "WHERE ";
                                $Query_AcceptNewPact .= "`AllyID_Sender` = {$Result_CheckPact['AllyID_Sender']} AND ";
                                $Query_AcceptNewPact .= "`AllyID_Owner` = {$Result_CheckPact['AllyID_Owner']} ";
                                $Query_AcceptNewPact .= "LIMIT 1; -- ally.pactslist.php|AcceptNewPact";
                                doquery($Query_AcceptNewPact, 'ally_pacts');

                                $Message = array();
                                $Message['msg_id'] = '098';
                                $Message['args'] = array($Ally['id'], $Ally['ally_name']);
                                $Message = json_encode($Message);
                                $Message = array('from' => '005', 'subject' => '024', 'content' => $Message);
                                SendNotification($Manage_AID, $Result_CheckPact['ally_ranks'], $Message);

                                $_MsgBox['color'] = 'lime';
                                $_MsgBox['text'] = $_Lang['AllyPacts_Msg_OK_Accept_NewPact'];
                            }
                            else
                            {
                                $_MsgBox['text'] = $_Lang['AllyPacts_Msg_PactAccept_Sender'];
                            }
                        }
                        else
                        {
                            // User wants to Accept change type proposal
                            if($Manage_IsSender)
                            {
                                $Manage_NewType = $Result_CheckPact['Change_Owner'];
                            }
                            else
                            {
                                $Manage_NewType = $Result_CheckPact['Change_Sender'];
                            }
                            if($Manage_NewType > 0)
                            {
                                $Query_UpdatePactType = '';
                                $Query_UpdatePactType .= "UPDATE {{table}} SET ";
                                $Query_UpdatePactType .= "`Type` = {$Manage_NewType}, ";
                                $Query_UpdatePactType .= "`Change_Owner` = 0, ";
                                $Query_UpdatePactType .= "`Change_Sender` = 0 ";
                                $Query_UpdatePactType .= "WHERE ";
                                $Query_UpdatePactType .= "`AllyID_Sender` = {$Result_CheckPact['AllyID_Sender']} AND ";
                                $Query_UpdatePactType .= "`AllyID_Owner` = {$Result_CheckPact['AllyID_Owner']} ";
                                $Query_UpdatePactType .= "LIMIT 1; -- ally.pactslist.php|UpdatePactType|Accept";
                                doquery($Query_UpdatePactType, 'ally_pacts');

                                $Message = array();
                                $Message['msg_id'] = '100';
                                $Message['args'] = array($Ally['id'], $Ally['ally_name']);
                                $Message = json_encode($Message);
                                $Message = array('from' => '005', 'subject' => '024', 'content' => $Message);
                                SendNotification($Manage_AID, $Result_CheckPact['ally_ranks'], $Message);

                                $_MsgBox['color'] = 'lime';
                                $_MsgBox['text'] = $_Lang['AllyPacts_Msg_OK_Accept_NewType'];
                            }
                            else
                            {
                                $_MsgBox['text'] = $_Lang['AllyPacts_Msg_PactAccept_NothingToAccept'];
                            }
                        }
                    }
                    else if($Manage_CMD == 5)
                    {
                        // User wants to Refuse new Pact proposal or change type proposal
                        if($Result_CheckPact['Active'] == 0)
                        {
                            if(!$Manage_IsSender)
                            {
                                // User wants to Refuse new Pact proposal
                                $Query_RefuseNewPact = '';
                                $Query_RefuseNewPact .= "DELETE FROM {{table}} WHERE ";
                                $Query_RefuseNewPact .= "`AllyID_Sender` = {$Result_CheckPact['AllyID_Sender']} AND ";
                                $Query_RefuseNewPact .= "`AllyID_Owner` = {$Result_CheckPact['AllyID_Owner']} ";
                                $Query_RefuseNewPact .= "LIMIT 1; -- ally.pactslist.php|RefuseNewPact";
                                doquery($Query_RefuseNewPact, 'ally_pacts');

                                $Message = array();
                                $Message['msg_id'] = '099';
                                $Message['args'] = array($Ally['id'], $Ally['ally_name']);
                                $Message = json_encode($Message);
                                $Message = array('from' => '005', 'subject' => '024', 'content' => $Message);
                                SendNotification($Manage_AID, $Result_CheckPact['ally_ranks'], $Message);

                                $_MsgBox['color'] = 'lime';
                                $_MsgBox['text'] = $_Lang['AllyPacts_Msg_OK_Refuse_NewPact'];
                            }
                            else
                            {
                                $_MsgBox['text'] = $_Lang['AllyPacts_Msg_PactRefuse_Sender'];
                            }
                        }
                        else
                        {
                            // User wants to Refuse change type proposal
                            if($Manage_IsSender)
                            {
                                $Manage_TypeField = 'Change_Owner';
                                $Manage_NewType = $Result_CheckPact['Change_Owner'];
                            }
                            else
                            {
                                $Manage_TypeField = 'Change_Sender';
                                $Manage_NewType = $Result_CheckPact['Change_Sender'];
                            }
                            if($Manage_NewType > 0)
                            {
                                $Query_UpdatePactType = '';
                                $Query_UpdatePactType .= "UPDATE {{table}} SET ";
                                $Query_UpdatePactType .= "`$Manage_TypeField` = 0 ";
                                $Query_UpdatePactType .= "WHERE ";
                                $Query_UpdatePactType .= "`AllyID_Sender` = {$Result_CheckPact['AllyID_Sender']} AND ";
                                $Query_UpdatePactType .= "`AllyID_Owner` = {$Result_CheckPact['AllyID_Owner']} ";
                                $Query_UpdatePactType .= "LIMIT 1; -- ally.pactslist.php|UpdatePactType|Refuse";
                                doquery($Query_UpdatePactType, 'ally_pacts');

                                $Message = array();
                                $Message['msg_id'] = '101';
                                $Message['args'] = array($Ally['id'], $Ally['ally_name']);
                                $Message = json_encode($Message);
                                $Message = array('from' => '005', 'subject' => '024', 'content' => $Message);
                                SendNotification($Manage_AID, $Result_CheckPact['ally_ranks'], $Message);

                                $_MsgBox['color'] = 'lime';
                                $_MsgBox['text'] = $_Lang['AllyPacts_Msg_OK_Refuse_NewType'];
                            }
                            else
                            {
                                $_MsgBox['text'] = $_Lang['AllyPacts_Msg_PactRefuse_NothingToAccept'];
                            }
                        }
                    }
                }
                else
                {
                    $_MsgBox['text'] = $_Lang['AllyPacts_Msg_PactDoesntExist'];
                }
            }
            else
            {
                $_MsgBox['text'] = $_Lang['AllyPacts_Msg_BadCMD'];
            }
        }
        else
        {
            $_MsgBox['text'] = $_Lang['AllyPacts_Msg_BadAID'];
        }
    }
    else
    {
        $_MsgBox['text'] = $_Lang['AllyPacts_Msg_CantManage'];
    }
}

if($CanManage)
{
    $ThisRow = gettemplate('alliance_pactslist_manage_row');
    $BodyTPL = gettemplate('alliance_pactslist_manage_body');
    $ThisColspan = 5;
}
else
{
    $ThisRow = gettemplate('alliance_pactslist_row');
    $BodyTPL = gettemplate('alliance_pactslist_body');
    $ThisColspan = 4;
}

$_Lang['Insert_MsgBox'] = '';
if(!empty($_MsgBox))
{
    $_Lang['Insert_MsgBox'] .= parsetemplate(gettemplate('_singleRow'), array('Colspan' => $ThisColspan, 'Classes' => 'pad5 '.$_MsgBox['color'], 'Text' => $_MsgBox['text']));
    $_Lang['Insert_MsgBox'] .= parsetemplate(gettemplate('_singleRow'), array('Colspan' => $ThisColspan, 'Classes' => 'inv', 'Text' => ''));
}

$Query_GetPacts = '';
$Query_GetPacts .= "SELECT `pacts`.*, `ally`.`ally_name` ";
$Query_GetPacts .= "FROM {{table}} AS `pacts` ";
$Query_GetPacts .= "LEFT JOIN `{{prefix}}alliance` AS `ally` ";
$Query_GetPacts .= "ON `ally`.`id` = IF(`pacts`.`AllyID_Sender` = {$Ally['id']}, `pacts`.`AllyID_Owner`, `pacts`.`AllyID_Sender`) ";
$Query_GetPacts .= "WHERE (`AllyID_Sender` = {$Ally['id']} OR `AllyID_Owner` = {$Ally['id']}) ";
$Query_GetPacts .= "; -- ally.pactslist.php|GetPacts";
$SQLResult_GetPacts = doquery($Query_GetPacts, 'ally_pacts');

if($SQLResult_GetPacts->num_rows > 0)
{
    while($FetchData = $SQLResult_GetPacts->fetch_assoc())
    {
        $FetchData['IsSender'] = ($FetchData['AllyID_Sender'] == $Ally['id'] ? true : false);
        $FetchData['SecondAllyID'] = ($FetchData['IsSender'] === true ? $FetchData['AllyID_Owner'] : $FetchData['AllyID_Sender']);
        if($FetchData['Active'] == 1)
        {
            if($FetchData['Change_Owner'] > 0)
            {
                if($FetchData['IsSender'] === false)
                {
                    $FetchData['KeyState'][] = '3';
                    $FetchData['Status'][0] = $_Lang['AWNP_States']['active'];
                    $FetchData['Status'][] = sprintf($_Lang['AWNP_States']['my_2'], $_Lang['AWNP_Types'][$FetchData['Change_Owner']]);
                }
                else
                {
                    $FetchData['KeyState'][] = '4';
                    $FetchData['Status'][0] = $_Lang['AWNP_States']['active'];
                    $FetchData['Status'][] = sprintf($_Lang['AWNP_States']['their_2'], $_Lang['AWNP_Types'][$FetchData['Change_Owner']]);
                }
            }
            if($FetchData['Change_Sender'] > 0)
            {
                if($FetchData['IsSender'] === true)
                {
                    $FetchData['KeyState'][] = '3';
                    $FetchData['Status'][0] = $_Lang['AWNP_States']['active'];
                    $FetchData['Status'][] = sprintf($_Lang['AWNP_States']['my_2'], $_Lang['AWNP_Types'][$FetchData['Change_Sender']]);
                }
                else
                {
                    $FetchData['KeyState'][] = '4';
                    $FetchData['Status'][0] = $_Lang['AWNP_States']['active'];
                    $FetchData['Status'][] = sprintf($_Lang['AWNP_States']['their_2'], $_Lang['AWNP_Types'][$FetchData['Change_Sender']]);
                }
            }
            if(empty($FetchData['KeyState']))
            {
                $FetchData['KeyState'][] = '1';
                $FetchData['Status'][] = $_Lang['AWNP_States']['active'];
            }
        }
        else
        {
            if($FetchData['IsSender'] === true)
            {
                $FetchData['KeyState'][] = '2';
                $FetchData['Status'][] = $_Lang['AWNP_States']['my_0'];
            }
            else
            {
                $FetchData['KeyState'][] = '5';
                $FetchData['Status'][] = $_Lang['AWNP_States']['their_0'];
            }
        }

        if($CanManage)
        {
            if(in_array(1, $FetchData['KeyState']))
            {
                $FetchData['Actions']['remove'] = array('Class' => 'remove', 'Link' => '?mode=pactslist&amp;do=1&amp;cmd=1&amp;aid='.$FetchData['SecondAllyID']);
                $FetchData['Actions']['change'] = array('Class' => 'change', 'Link' => '?mode=changepact&amp;aid='.$FetchData['SecondAllyID']);
            }
            if(in_array(2, $FetchData['KeyState']))
            {
                $FetchData['Actions']['rollback'] = array('Class' => 'rollback', 'Link' => '?mode=pactslist&amp;do=1&amp;cmd=2&amp;aid='.$FetchData['SecondAllyID']);
            }
            if(in_array(3, $FetchData['KeyState']))
            {
                $FetchData['Actions']['remove'] = array('Class' => 'remove', 'Link' => '?mode=pactslist&amp;do=1&amp;cmd=1&amp;aid='.$FetchData['SecondAllyID']);
                $FetchData['Actions']['stopchange'] = array('Class' => 'stopchange', 'Link' => '?mode=pactslist&amp;do=1&amp;cmd=3&amp;aid='.$FetchData['SecondAllyID']);
            }
            if(in_array(4, $FetchData['KeyState']))
            {
                $FetchData['Actions']['remove'] = array('Class' => 'remove', 'Link' => '?mode=pactslist&amp;do=1&amp;cmd=1&amp;aid='.$FetchData['SecondAllyID']);
                if(!in_array(3, $FetchData['KeyState']))
                {
                    $FetchData['Actions']['change'] = array('Class' => 'change', 'Link' => '?mode=changepact&amp;aid='.$FetchData['SecondAllyID']);
                }
                $FetchData['Actions']['accept'] = array('Class' => 'accept', 'Link' => '?mode=pactslist&amp;do=1&amp;cmd=4&amp;aid='.$FetchData['SecondAllyID']);
                $FetchData['Actions']['refuse'] = array('Class' => 'refuse', 'Link' => '?mode=pactslist&amp;do=1&amp;cmd=5&amp;aid='.$FetchData['SecondAllyID']);
            }
            if(in_array(5, $FetchData['KeyState']))
            {
                $FetchData['Actions']['accept'] = array('Class' => 'accept', 'Link' => '?mode=pactslist&amp;do=1&amp;cmd=4&amp;aid='.$FetchData['SecondAllyID']);
                $FetchData['Actions']['refuse'] = array('Class' => 'refuse', 'Link' => '?mode=pactslist&amp;do=1&amp;cmd=5&amp;aid='.$FetchData['SecondAllyID']);
            }

            $TPL_PactsList_Actions = gettemplate('alliance_pactslist_manage_actions');
            foreach($FetchData['Actions'] as &$ThisVal)
            {
                $ThisVal = parsetemplate($TPL_PactsList_Actions, $ThisVal);
            }
            $FetchData['Actions'] = implode('&nbsp;', $FetchData['Actions']);
        }
        array_walk($FetchData['Status'], function(&$Value){ $Value = '&bull; '.$Value; });
        $FetchData['Status'] = implode('<br/>', $FetchData['Status']);
        $FetchData['Key'] = max($FetchData['KeyState']).$FetchData['Date'];
        $FetchData['AllyID'] = $FetchData['SecondAllyID'];
        $FetchData['AllyName'] = $FetchData['ally_name'];
        $FetchData['PactDate'] = prettyDate('d m Y, H:i:s', $FetchData['Date'], 1);
        $FetchData['PactType'] = $_Lang['AWNP_Types'][$FetchData['Type']];

        $PactsList[$FetchData['Key']] = $FetchData;
    }
    krsort($PactsList, SORT_STRING);
    foreach($PactsList as $PactData)
    {
        $_Lang['ShowPactsList'][] = parsetemplate($ThisRow, $PactData);
    }

    $_Lang['ShowPactsList'] = implode('', $_Lang['ShowPactsList']);
}
else
{
    $_Lang['ShowPactsList'] = parsetemplate(gettemplate('_singleRow'), array
    (
        'Colspan' => $ThisColspan,
        'Classes' => 'red pad5',
        'Text' => $_Lang['AWNP_NoPacts']
    ));
}

if($_ThisUserRank['warnpact'] !== true)
{
    $_Lang['Insert_HideNewPact'] = 'class="hide"';
}

$Page = parsetemplate($BodyTPL, $_Lang);
display($Page, $_Lang['Ally_Pacts_WinTitle']);

?>
