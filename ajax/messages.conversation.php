<?php

define('INSIDE', true);

$_DontCheckPolls = TRUE;
$_DontForceRulesAcceptance = true;
$_UseMinimalCommon = true;

$_SetAccessLogPreFilename = 'ajax/';
$_SetAccessLogPath = '../';
$_EnginePath = '../';

include($_EnginePath . 'common.php');
include($_EnginePath . 'modules/messages/_includes.php');

use UniEngine\Engine\Modules\Messages;

function ajaxReturn($Array)
{
    safeDie(json_encode($Array));
}

$ExcludeIDs = array();

if(!isLogged())
{
    ajaxReturn(array('Err' => '001'));
}
$ThreadID = (isset($_GET['tid']) ? round($_GET['tid']) : 0);
if($ThreadID <= 0)
{
    ajaxReturn(array('Err' => '002'));
}
if(isset($_GET['exc']) && !empty($_GET['exc']))
{
    $Temp = explode(',', $_GET['exc']);
    foreach($Temp as $Value)
    {
        $Value = round($Value);
        if($Value > 0)
        {
            $ExcludeIDs[] = $Value;
        }
    }
}
$MaxMessageID = 0;
if(isset($_GET['mid']) && !empty($_GET['mid']))
{
    $MaxMessageID = round($_GET['mid']);
}
$_ThisCategory = 0;
if(isset($_GET['nc']) && $_GET['nc'] == '1')
{
    $_ThisCategory = 100;
}

$Query_GetMessages  = "SELECT `m`.*, `u`.`username`, `u`.`authlevel` FROM {{table}} AS `m` ";
$Query_GetMessages .= "LEFT JOIN `{{prefix}}users` AS `u` ON `u`.`id` = `m`.`id_sender` ";
$Query_GetMessages .= "WHERE (`m`.`deleted` = false OR `m`.`id_sender` = {$_User['id']}) AND (`m`.`id_owner` = {$_User['id']} OR `m`.`id_sender` = {$_User['id']}) AND `m`.`Thread_ID` = {$ThreadID} ";
$Query_GetMessages .= " AND (`m`.`Thread_IsLast` = 0 OR `m`.`id_owner` != {$_User['id']}) ";
$Query_GetMessages .= (!empty($ExcludeIDs) ? " AND `m`.`id` NOT IN (".implode(', ', $ExcludeIDs).") " : '');
$Query_GetMessages .= ($MaxMessageID > 0 ? " AND `m`.`id` < {$MaxMessageID} " : '');
$Query_GetMessages .= "ORDER BY `m`.`time` DESC, `m`.`id` DESC;";

$SQLResult_GetMessages = doquery($Query_GetMessages, 'messages');

if($SQLResult_GetMessages->num_rows <= 0)
{
    ajaxReturn(array('Err' => '003'));
}
else
{
    includeLang('messages');
    includeLang('messageSystem');
    includeLang('spyReport');
    includeLang('FleetMission_MissileAttack');

    $Messages = array();
    while ($CurMess = $SQLResult_GetMessages->fetch_assoc()) {
        $MsgCache[] = $CurMess;
    }

    $messagesCopyIds = Messages\Utils\getMessagesCopyIds($MsgCache);
    $copyOriginalMessages = Messages\Utils\fetchOriginalMessagesForRefSystem([
        'originalMessageIds' => $messagesCopyIds,
    ]);

    foreach ($MsgCache as $CurMess) {
        $parseMSG = Messages\Utils\_buildBasicMessageDetails(
            $CurMess,
            [
                'isReadingThread' => true,
                'displayedCategoryId' => $_ThisCategory,
                'readerUserData' => &$_User,
            ]
        );

        // The assumption here is that we'll never encounter "non user created messages"
        $messageDetails = Messages\Utils\_buildTypedUserMessageDetails(
            $CurMess,
            [
                'copyOriginalMessagesStorage' => &$copyOriginalMessages,
            ]
        );

        $parseMSG['CurrMSG_subject'] = $messageDetails['subject'];
        $parseMSG['CurrMSG_from'] = $messageDetails['from'];
        $parseMSG['CurrMSG_text'] = $messageDetails['text'];

        $Messages[$CurMess['id']] = $parseMSG;
    }

    $MsgTPL = gettemplate('message_mailbox_body');
    foreach ($Messages as $MessageData) {
        $AllMessages[] = parsetemplate($MsgTPL, $MessageData);
    }

    ajaxReturn(array('Code' => implode('', $AllMessages)));
}

?>
