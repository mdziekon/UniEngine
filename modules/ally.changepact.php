<?php

if(IN_ALLYPAGE !== true)
{
    header('Location: index.php');
    die();
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
        $Query_GetUsers .= "; -- ally.changepact.php|SendNotification|GetUsers";
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

if($_ThisUserRank['warnpact'] !== true)
{
    message($_Lang['Ally_AccessDenied'], $MsgTitle, 'alliance.php', 3);
}

$Manage_AID = (isset($_GET['aid']) ? intval($_GET['aid']) : 0);
if($Manage_AID <= 0 OR $Manage_AID == $Ally['id'])
{
    message($_Lang['Ally_PactChange_BadAID'], $_Lang['Ally_PactChange_Title'], 'alliance.php?mode=pactslist', 3);
}

$Query_GetPactData = '';
$Query_GetPactData .= "SELECT `pact`.*, `ally`.`ally_name`, `ally`.`ally_ranks` FROM {{table}} AS `pact` ";
$Query_GetPactData .= "LEFT JOIN `{{prefix}}alliance` AS `ally` ON `ally`.`id` = IF(`pact`.`AllyID_Sender` = {$Ally['id']}, `pact`.`AllyID_Owner`, `pact`.`AllyID_Sender`) ";
$Query_GetPactData .= "WHERE ";
$Query_GetPactData .= "(`pact`.`AllyID_Sender` = {$Ally['id']} AND `pact`.`AllyID_Owner` = {$Manage_AID}) OR ";
$Query_GetPactData .= "(`pact`.`AllyID_Owner` = {$Ally['id']} AND `pact`.`AllyID_Sender` = {$Manage_AID}) ";
$Query_GetPactData .= "LIMIT 1; -- ally.changepact.php|GetPactData";
$Result_GetPactData = doquery($Query_GetPactData, 'ally_pacts', true);
if(empty($Result_GetPactData['AllyID_Sender']))
{
    message($_Lang['Ally_PactChange_PactDoesntExist'], $_Lang['Ally_PactChange_Title'], 'alliance.php?mode=pactslist', 3);
}

$Manage_IsSender = ($Result_GetPactData['AllyID_Sender'] == $Ally['id'] ? true : false);
if(($Manage_IsSender && $Result_GetPactData['Change_Sender'] > 0) || (!$Manage_IsSender && $Result_GetPactData['Change_Owner'] > 0))
{
    message($_Lang['Ally_PactChange_PactChangeAwaiting'], $_Lang['Ally_PactChange_Title'], 'alliance.php?mode=pactslist', 3);
}

$_Lang['Insert_AID'] = $Manage_AID;
$_Lang['Insert_AllyName'] = $Result_GetPactData['ally_name'];
$_Lang['Insert_CurrentOption_'.$Result_GetPactData['Type']] = 'style="color: orange" selected';
$_Lang['Insert_CurrentType'] = $_Lang['Ally_PactNew_Type_'.$Result_GetPactData['Type']];

if(isset($_POST['sent']) && $_POST['sent'] == 1)
{
    $NewPact_Msg['color'] = 'red';
    $Manage_NewType = intval($_POST['type']);
    if(in_array($Manage_NewType, array(1, 2, 3, 4)))
    {
        if($Manage_NewType != $Result_GetPactData['Type'])
        {
            if($Manage_NewType > $Result_GetPactData['Type'])
            {
                if($Manage_IsSender)
                {
                    $UpdateField = 'Change_Sender';
                    $CheckOtherField = 'Change_Owner';
                }
                else
                {
                    $UpdateField = 'Change_Owner';
                    $CheckOtherField = 'Change_Sender';
                }
                if($Result_GetPactData[$CheckOtherField] != $Manage_NewType)
                {
                    $Query_UpdatePactType = '';
                    $Query_UpdatePactType .= "UPDATE {{table}} SET ";
                    $Query_UpdatePactType .= "`{$UpdateField}` = {$Manage_NewType} ";
                    $Query_UpdatePactType .= "WHERE ";
                    $Query_UpdatePactType .= "`AllyID_Sender` = {$Result_GetPactData['AllyID_Sender']} AND ";
                    $Query_UpdatePactType .= "`AllyID_Owner` = {$Result_GetPactData['AllyID_Owner']} ";
                    $Query_UpdatePactType .= "LIMIT 1; -- ally.changepact.php|UpdatePactType|Higher";
                    doquery($Query_UpdatePactType, 'ally_pacts');

                    $Message = array();
                    $Message['msg_id'] = '104';
                    $Message['args'] = array($Ally['id'], $Ally['ally_name']);
                    $Message = json_encode($Message);
                    $Message = array('from' => '005', 'subject' => '024', 'content' => $Message);
                    SendNotification($Manage_AID, $Result_GetPactData['ally_ranks'], $Message);

                    message($_Lang['Ally_PactChange_Msg_Ok_Higher'], $_Lang['Ally_PactChange_Title'], 'alliance.php?mode=pactslist', 3);
                }
                else
                {
                    $NewPact_Msg['text'] = $_Lang['Ally_PactChange_Msg_TypeAlreadyProposed'];
                }
            }
            else
            {
                $Query_UpdatePactType = '';
                $Query_UpdatePactType .= "UPDATE {{table}} SET ";
                $Query_UpdatePactType .= "`Type` = {$Manage_NewType} ";
                $Query_UpdatePactType .= "WHERE ";
                $Query_UpdatePactType .= "`AllyID_Sender` = {$Result_GetPactData['AllyID_Sender']} AND ";
                $Query_UpdatePactType .= "`AllyID_Owner` = {$Result_GetPactData['AllyID_Owner']} ";
                $Query_UpdatePactType .= "LIMIT 1; -- ally.changepact.php|UpdatePactType|Lower";
                doquery($Query_UpdatePactType, 'ally_pacts');

                $Message = array();
                $Message['msg_id'] = '105';
                $Message['args'] = array($Ally['id'], $Ally['ally_name'], $_Lang['Ally_PactNew_Type_'.$Manage_NewType]);
                $Message = json_encode($Message);
                $Message = array('from' => '005', 'subject' => '024', 'content' => $Message);
                SendNotification($Manage_AID, $Result_GetPactData['ally_ranks'], $Message);

                message($_Lang['Ally_PactChange_Msg_Ok_Lower'], $_Lang['Ally_PactChange_Title'], 'alliance.php?mode=pactslist', 3);
            }
        }
        else
        {
            $NewPact_Msg['text'] = $_Lang['Ally_PactChange_Msg_TypeSame'];
        }
    }
    else
    {
        $NewPact_Msg['text'] = $_Lang['Ally_PactChange_Msg_TypeBad'];
    }
}

$_Lang['Insert_MsgBox'] = '';
if(!empty($NewPact_Msg))
{
    $_Lang['Insert_MsgBox'] .= parsetemplate(gettemplate('_singleRow'), array('Colspan' => 2, 'Classes' => 'pad5 '.$NewPact_Msg['color'], 'Text' => $NewPact_Msg['text']));
    $_Lang['Insert_MsgBox'] .= parsetemplate(gettemplate('_singleRow'), array('Colspan' => 2, 'Classes' => 'inv', 'Text' => ''));
}

$Page = parsetemplate(gettemplate('alliance_changepact'), $_Lang);
display($Page, $_Lang['Ally_PactChange_Title']);

?>
