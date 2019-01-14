<?php

define('INSIDE', true);

$_DontCheckPolls = TRUE;
$_AllowInVacationMode = true;

$_EnginePath = './';
include($_EnginePath.'common.php');

loggedCheck();

includeLang('polls');

$_BackLink = 'polls.php';

$PollID = (isset($_GET['pid']) ? intval($_GET['pid']) : 0);
if($PollID > 0)
{
    $Query_PollData = '';
    $Query_PollData .= "SELECT `poll`.*, `votes`.`id` AS `vote_id`, `votes`.`answer` ";
    $Query_PollData .= "FROM {{table}} AS `poll` ";
    $Query_PollData .= "LEFT JOIN `{{prefix}}poll_votes` AS `votes` ON `votes`.`poll_id` = `poll`.`id` AND `votes`.`user_id` = {$_User['id']} ";
    $Query_PollData .= "WHERE `poll`.`id` = {$PollID} LIMIT 1; -- Polls|PollData";
    $SelectPoll = doquery($Query_PollData, 'polls', true);

    if($SelectPoll['id'] != $PollID)
    {
        message($_Lang['Poll_doesnt_exist'], $_Lang['Title'], $_BackLink, 3);
    }
    if($SelectPoll['open'] != 1 AND $SelectPoll['show_results'] != 1)
    {
        message($_Lang['Poll_is_closed_for_view'], $_Lang['Title'], $_BackLink, 3);
    }

    $Answers = explode(';', $SelectPoll['answers']);
    foreach($Answers as $Key => $Value)
    {
        if(!empty($Value))
        {
            $AvailableAnswers[] = $Key;
            $AnswersArray[$Key] = $Value;
        }
    }
    if(empty($AvailableAnswers))
    {
        message($_Lang['Error_NoAnswers'], $_Lang['Title'], $_BackLink, 3);
    }

    if(isset($_POST['send']) && $_POST['send'] == 1)
    {
        $_BackLink = 'polls.php?pid='.$PollID;
        if($SelectPoll['open'] != 1)
        {
            message($_Lang['Poll_is_closed'], $_Lang['Title'], $_BackLink, 3);
        }
        if(isset($_POST['vote']) && $_POST['vote'] != null)
        {
            if($SelectPoll['Opt_Multivote'] == 0)
            {
                $Vote = intval($_POST['vote']);
                if(!in_array($Vote, $AvailableAnswers))
                {
                    message($_Lang['No_selected_answer'], $_Lang['Title'], $_BackLink, 3);
                }
            }
            else
            {
                if(!empty($_POST['vote']))
                {
                    foreach($_POST['vote'] as $Key => $Value)
                    {
                        if($Value == 'on' AND in_array($Key, $AvailableAnswers))
                        {
                            $Votes[] = $Key;
                        }
                    }
                }
                if(empty($Votes))
                {
                    message($_Lang['Error_BadSelection_Multi'], $_Lang['Title'], $_BackLink, 3);
                }
                $Vote = implode(',', $Votes);
            }

            if($SelectPoll['vote_id'] == null)
            {
                doquery("INSERT INTO {{table}} VALUES (NULL, {$PollID}, {$_User['id']}, '{$Vote}', UNIX_TIMESTAMP());", 'poll_votes');
                message($_Lang['Thx_for_voting'], $_Lang['Title'], $_BackLink, 3);
            }
            else
            {
                doquery("UPDATE {{table}} SET `answer` = '{$Vote}', `time` = UNIX_TIMESTAMP() WHERE `id` = {$SelectPoll['vote_id']};", 'poll_votes');
                message($_Lang['Thx_for_chaning_vote'], $_Lang['Title'], $_BackLink, 3);
            }
        }
        else
        {
            message($_Lang['Error_NoOptionSelected'], $_Lang['Title'], $_BackLink, 3);
        }
    }
    else
    {
        $IsOpen = ($SelectPoll['open'] == 1 ? true : false);
        $AllVotes = 0;

        if($SelectPoll['show_results'] == 1 OR CheckAuth('supportadmin'))
        {
            $ShowResult = true;

            if(CheckAuth('supportadmin'))
            {
                $TPL_Voting_Username = gettemplate('polls_voting_username');
                $Query_GetVotes = '';
                $Query_GetVotes .= "SELECT `votes`.`answer`, `votes`.`user_id` AS `uid`, `users`.`username` ";
                $Query_GetVotes .= "FROM {{table}} AS `votes` ";
                $Query_GetVotes .= "LEFT JOIN `{{prefix}}users` AS `users` ON `votes`.`user_id` = `users`.`id` ";
                $Query_GetVotes .= "WHERE `votes`.`poll_id` = {$PollID}; -- Polls|GetVotes|Admin";
                $SelectVotes = doquery($Query_GetVotes, 'poll_votes');
                while($Votes = $SelectVotes->fetch_assoc())
                {
                    $Votes['answer'] = explode(',', $Votes['answer']);
                    foreach($Votes['answer'] as $ThisAnswer)
                    {
                        if(!isset($Results[$ThisAnswer]))
                        {
                            $Results[$ThisAnswer] = 0;
                        }
                        $Results[$ThisAnswer] += 1;
                        $AllVotes += 1;
                        if(empty($Votes['username']))
                        {
                            $Votes['username'] = "<b class=\"red\">{$_Lang['User_Deleted']}</b>";
                        }
                        $UserVotes[$ThisAnswer][] = parsetemplate($TPL_Voting_Username, array('UID' => $Votes['uid'], 'Username' => $Votes['username']));
                    }
                }
            }
            else
            {
                $Query_GetVotes = '';
                $Query_GetVotes .= "SELECT `answer`, COUNT(*) AS `Count` ";
                $Query_GetVotes .= "FROM {{table}} ";
                $Query_GetVotes .= "WHERE `poll_id` = {$PollID} ";
                $Query_GetVotes .= "GROUP BY `answer`; ";
                $Query_GetVotes .= "-- Polls|GetVotes|User";
                $SelectVotes = doquery($Query_GetVotes, 'poll_votes');

                if($SelectPoll['Opt_Multivote'] == 0)
                {
                    while($Votes = $SelectVotes->fetch_assoc())
                    {
                        if(!isset($Results[$Votes['answer']]))
                        {
                            $Results[$Votes['answer']] = 0;
                        }
                        $Results[$Votes['answer']] += $Votes['Count'];
                        $AllVotes += $Votes['Count'];
                    }
                }
                else
                {
                    while($Votes = $SelectVotes->fetch_assoc())
                    {
                        $Votes['answer'] = explode(',', $Votes['answer']);
                        foreach($Votes['answer'] as $ThisAnswer)
                        {
                            if(!isset($Results[$ThisAnswer]))
                            {
                                $Results[$ThisAnswer] = 0;
                            }
                            $Results[$ThisAnswer] += $Votes['Count'];
                            $AllVotes += $Votes['Count'];
                        }
                    }
                }
            }
        }
        else
        {
            $_Lang['PollResultsInfo'] = parsetemplate(gettemplate('_singleRow'), array('Classes' => 'orange pad5', 'Colspan' => '3', 'Text' => $_Lang['ResultsAreHidden']));
        }

        $_Lang['PollID'] = $PollID;
        $_Lang['PollName'] = $SelectPoll['name'];
        if(!empty($SelectPoll['desc']))
        {
            $_Lang['PollDesc'] = $SelectPoll['desc'];
        }
        else
        {
            $_Lang['PollDescHide'] = ' style="display: none;"';
        }
        if($SelectPoll['open'] == 1)
        {
            if($SelectPoll['vote_id'] == null)
            {
                $_Lang['Vote_or_change'] = $_Lang['Vote_now'];
                $_Lang['Insert_SubmitColor'] = 'lime';
            }
            else
            {
                $_Lang['Vote_or_change'] = $_Lang['Change_vote'];
                $_Lang['Insert_SubmitColor'] = 'orange';
            }
        }
        else
        {
            $_Lang['Vote_or_change'] = $_Lang['Submit_PollLocked'];
            $_Lang['Insert_SubmitColor'] = 'red';
            $_Lang['Insert_SubmitDisable'] = 'disabled';
        }

        $TPL_Voting_Row_Template = ($SelectPoll['Opt_Multivote'] == 1 ? 'polls_voting_row_multi' : 'polls_voting_row_single');
        $TPL_Voting_Row = gettemplate($TPL_Voting_Row_Template);
        $SelectPoll['userAnswers'] = explode(',', $SelectPoll['answer']);
        foreach($AnswersArray as $Key => $Value)
        {
            $Key = (string)($Key + 0);
            $ThisValue = array();

            $VotingResult = '';
            if($ShowResult === true)
            {
                $ThisValue['voteResult_Count'] = (isset($Results[$Key]) && $Results[$Key] > 0 ? $Results[$Key] : '0');
                $ThisValue['voteResult_Percent'] = ((isset($Results[$Key]) && $AllVotes > 0) ? round(($Results[$Key] / $AllVotes) * 100, 1) : '0');
                if($SelectPoll['vote_id'] > 0 AND in_array($Key, $SelectPoll['userAnswers']))
                {
                    $ThisValue['voteResult_Color'] = 'orange';
                }
            }
            else
            {
                $VotingResult = '-';
            }
            $ThisValue['voteNo'] = $Key;
            if(!$IsOpen)
            {
                $ThisValue['voteDisable'] = 'disabled';
            }
            if($SelectPoll['vote_id'] > 0 AND in_array($Key, $SelectPoll['userAnswers']))
            {
                $ThisValue['voteCheck'] = 'checked';
            }
            $ThisValue['voteAnswerName'] = $Value;
            if(!empty($UserVotes[$Key]))
            {
                $ThisValue['voteResult_Usernames'] = implode('<br/>', $UserVotes[$Key]);
            }

            $_Lang['Insert_PollAnswers'][] = parsetemplate($TPL_Voting_Row, $ThisValue);
        }
        $_Lang['Insert_PollAnswers'] = implode('', $_Lang['Insert_PollAnswers']);
    }

    if(CheckAuth('supportadmin'))
    {
        $_Lang['ShowUsersLink'] = ' [<a id="toggleUsers">'.$_Lang['ShowUsers'].'</a>]';
    }

    $page = parsetemplate(gettemplate('polls_voting'), $_Lang);
}
else
{
    $Query_GetPolls = '';
    $Query_GetPolls .= "SELECT `poll`.`id`, `poll`.`name`, `poll`.`time`, `poll`.`open`, `poll`.`show_results`, `poll`.`obligatory`, `votes`.`id` AS `vote_id` ";
    $Query_GetPolls .= "FROM `{{table}}` AS `poll` ";
    $Query_GetPolls .= "LEFT JOIN `{{prefix}}poll_votes` AS `votes` ON `votes`.`poll_id` = `poll`.`id` AND `votes`.`user_id` = {$_User['id']} ";
    $Query_GetPolls .= "WHERE `open` = 1 OR `show_results` = 1 ";
    $Query_GetPolls .= "; -- Polls|GetPolls";
    $Result_GetPolls = doquery($Query_GetPolls, 'polls');

    if($Result_GetPolls->num_rows > 0)
    {
        $TPL_List_Row = gettemplate('polls_list_row');
        while($FetchData = $Result_GetPolls->fetch_assoc())
        {
            $CreateKey = (($FetchData['vote_id'] > 0 OR $FetchData['open'] == 0) ? '1' : '2').($FetchData['obligatory'] == 0 ? '1' : '2').($FetchData['open'] == 0 ? '1' : '2').$FetchData['time'].str_pad($FetchData['id'], 10, '0', STR_PAD_LEFT);
            $FetchData['Insert_PollID'] = $FetchData['id'];
            $FetchData['Insert_PollName'] = $FetchData['name'];

            if($FetchData['open'] == 0)
            {
                $FetchData['Insert_StateClass'][] = 'locked';
                $FetchData['Insert_PollState'][] = $_Lang['State_IsClosed'];
            }
            if($FetchData['vote_id'] > 0)
            {
                $FetchData['Insert_StateClass'][] = 'voted';
                $FetchData['Insert_PollState'][] = $_Lang['State_IsVoted'];
            }
            if($FetchData['obligatory'] == 1 AND $FetchData['open'] == 1 AND $FetchData['vote_id'] == null)
            {
                $FetchData['Insert_StateClass'][] = 'obligatory orange';
                $FetchData['Insert_PollState'][] = $_Lang['State_IsObligatory'];
            }
            if(!empty($FetchData['Insert_StateClass']))
            {
                $FetchData['Insert_StateClass'] = implode(' ', $FetchData['Insert_StateClass']);
            }
            if(!empty($FetchData['Insert_PollState']))
            {
                $FetchData['Insert_PollState'] = implode(' ', $FetchData['Insert_PollState']);
            }

            $_Lang['Insert_PollRows'][$CreateKey] = parsetemplate($TPL_List_Row, $FetchData);
        }
        krsort($_Lang['Insert_PollRows'], SORT_STRING);
        $_Lang['Insert_PollRows'] = implode('', $_Lang['Insert_PollRows']);
    }
    else
    {
        $_Lang['Insert_PollRows'] = parsetemplate(gettemplate('_singleRow'), array('Classes' => 'pad5 orange info', 'Text' => $_Lang['no_polls']));
    }

    $page = parsetemplate(gettemplate('polls_list_body'), $_Lang);
}

display($page,$_Lang['Title'], false);

?>
