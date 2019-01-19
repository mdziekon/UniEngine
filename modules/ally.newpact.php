<?php

if(IN_ALLYPAGE !== true)
{
    header('Location: index.php');
    die();
}

if($_ThisUserRank['warnpact'] !== true)
{
    message($_Lang['Ally_AccessDenied'], $MsgTitle, 'alliance.php', 3);
}

if(isset($_POST['sent']))
{
    $NewPact_Msg['color'] = 'red';
    if(!empty($_POST['allyname']))
    {
        $NewPact_CheckAlly = $_POST['allyname'];
        if(preg_match(REGEXP_ALLYNAME_ABSOLUTE, $NewPact_CheckAlly))
        {
            if($NewPact_CheckAlly != $Ally['ally_name'])
            {
                if(in_array($_POST['type'], array(1, 2, 3, 4)))
                {
                    $NewPact_Type = $_POST['type'];

                    $Query_NewPact_CheckAlly = '';
                    $Query_NewPact_CheckAlly .= "SELECT `id`, `ally_ranks` FROM {{table}} WHERE `ally_name` = '{$NewPact_CheckAlly}' LIMIT 1;";
                    $Query_NewPact_CheckAlly .= " -- alliance.php|NewPact|CheckAlly";
                    $Result_NewPact_CheckAlly = doquery($Query_NewPact_CheckAlly, 'alliance', true);
                    if($Result_NewPact_CheckAlly['id'] > 0)
                    {
                        $NewPact_AllyID = $Result_NewPact_CheckAlly['id'];
                        $NewPact_AllyRanks = json_decode($Result_NewPact_CheckAlly['ally_ranks'], true);

                        $Query_NewPact_CheckPacts = '';
                        $Query_NewPact_CheckPacts .= "(";
                        $Query_NewPact_CheckPacts .= "SELECT COUNT(*) AS `Count`, 1 AS `Type` FROM `{{prefix}}ally_pacts` WHERE ";
                        $Query_NewPact_CheckPacts .= "(`AllyID_Sender` = {$Ally['id']} AND `AllyID_Owner` = {$NewPact_AllyID}) OR ";
                        $Query_NewPact_CheckPacts .= "(`AllyID_Owner` = {$Ally['id']} AND `AllyID_Sender` = {$NewPact_AllyID})";
                        $Query_NewPact_CheckPacts .= ")";
                        //$Query_NewPact_CheckPacts .= " UNION ";
                        // Finish this, when AllyWars will be ready
                        $Query_NewPact_CheckPacts .= "; -- alliance.php|NewPact|CheckPacts";
                        $Result_NewPact_CheckPacts = doquery($Query_NewPact_CheckPacts, '');
                        while($FetchData = $Result_NewPact_CheckPacts->fetch_assoc())
                        {
                            if($FetchData['Count'] > 0)
                            {
                                $CantGoFurther = true;
                                if($FetchData['Type'] == 1)
                                {
                                    $NewPact_Msg['text'] = $_Lang['Ally_PactNew_Error_PactAlreadySent'];
                                }
                                else
                                {
                                    $NewPact_Msg['text'] = $_Lang['Ally_PactNew_Error_AllyInWar'];
                                }
                            }
                        }

                        if(!isset($CantGoFurther))
                        {
                            $Query_NewPact_CreatePact = '';
                            $Query_NewPact_CreatePact .= "INSERT INTO {{table}} SET ";
                            $Query_NewPact_CreatePact .= "`AllyID_Sender` = {$Ally['id']}, ";
                            $Query_NewPact_CreatePact .= "`AllyID_Owner` = {$NewPact_AllyID}, ";
                            $Query_NewPact_CreatePact .= "`Date` = {$Time}, ";
                            $Query_NewPact_CreatePact .= "`Type` = {$NewPact_Type} ";
                            $Query_NewPact_CreatePact .= "; -- alliance.php|NewPact|CreatePact";
                            doquery($Query_NewPact_CreatePact, 'ally_pacts');

                            foreach($NewPact_AllyRanks as $RankID => $RankData)
                            {
                                foreach($RankData as $DataID => $DataVal)
                                {
                                    $NewPact_AllyRanks_Parsed[$RankID][$_Vars_AllyRankLabels[$DataID]] = $DataVal;
                                }
                                if($NewPact_AllyRanks_Parsed[$RankID]['warnpact'] === true)
                                {
                                    $NewPact_RankIDs[] = $RankID;
                                }
                            }
                            if(!empty($NewPact_RankIDs))
                            {
                                $NewPact_RankIDs = implode(',', $NewPact_RankIDs);
                                $Query_NewPact_GetUsers = '';
                                $Query_NewPact_GetUsers .= "SELECT `id` FROM {{table}} WHERE ";
                                $Query_NewPact_GetUsers .= "`ally_id` = {$NewPact_AllyID} AND `ally_rank_id` IN ({$NewPact_RankIDs})";
                                $Query_NewPact_GetUsers .= "; -- alliance.php|NewPact|GetUsers";
                                $Result_NewPact_GetUsers = doquery($Query_NewPact_GetUsers, 'users');
                                while($FetchData = $Result_NewPact_GetUsers->fetch_assoc())
                                {
                                    $NewPact_UserIDs[] = $FetchData['id'];
                                }

                                if(!empty($NewPact_UserIDs))
                                {
                                    $Message = array();
                                    $Message['msg_id'] = '096';
                                    $Message['args'] = array($Ally['id'], $Ally['ally_name']);
                                    $Message = json_encode($Message);
                                    Cache_Message($NewPact_UserIDs, 0, $Time, 2, '005', '024', $Message);
                                }
                            }

                            $NewPact_Msg['color'] = 'lime';
                            $NewPact_Msg['text'] = sprintf($_Lang['Ally_PactNew_Success'], $NewPact_AllyID, $NewPact_CheckAlly);
                        }
                    }
                    else
                    {
                        $NewPact_Msg['text'] = $_Lang['Ally_PactNew_Error_AllyDoesntExist'];
                    }
                }
                else
                {
                    $NewPact_Msg['text'] = $_Lang['Ally_PactNew_Error_BadType'];
                }
            }
            else
            {
                $NewPact_Msg['text'] = $_Lang['Ally_PactNew_Error_AllySame'];
            }
        }
        else
        {
            $NewPact_Msg['text'] = $_Lang['Ally_PactNew_Error_BadName'];
        }
    }
    else
    {
        $NewPact_Msg['text'] = $_Lang['Ally_PactNew_Error_EmptyName'];
    }
}

$_Lang['Insert_MsgBox'] = '';
if(!empty($NewPact_Msg))
{
    $_Lang['Insert_MsgBox'] .= parsetemplate(gettemplate('_singleRow'), array('Colspan' => 2, 'Classes' => 'pad5 '.$NewPact_Msg['color'], 'Text' => $NewPact_Msg['text']));
    $_Lang['Insert_MsgBox'] .= parsetemplate(gettemplate('_singleRow'), array('Colspan' => 2, 'Classes' => 'inv', 'Text' => ''));
}

$Page = parsetemplate(gettemplate('alliance_newpact'), $_Lang);
display($Page, $_Lang['Ally_PactNew_Title']);

?>
