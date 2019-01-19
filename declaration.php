<?php

define('INSIDE', true );

$_EnginePath = './';
include($_EnginePath.'common.php');

loggedCheck();

includeLang('declaration');

$PageTpl = gettemplate('declaration_body');
$parse = $_Lang;

$HasExistingDeclarationEntry = false;
$DeclarationDetails = [];

if($_User['multiIP_DeclarationID'] > 0)
{
    $SQLResult_GetCurrentDeclaration = doquery(
        "SELECT `users`, `status` FROM {{table}} WHERE `id` = '{$_User['multiIP_DeclarationID']}' AND `status` != -2 LIMIT 1;",
        'declarations'
    );

    if($SQLResult_GetCurrentDeclaration->num_rows != 0)
    {
        $DeclarationDetails = $SQLResult_GetCurrentDeclaration->fetch_assoc();
        $HasExistingDeclarationEntry = true;

        $ExplodeUsers = explode(',', $DeclarationDetails['users']);

        $DeclarationOwner = false;
        foreach($ExplodeUsers as $UserID)
        {
            $UserID = str_replace('|', '', $UserID);
            if($UserID > 0)
            {
                $DeclarationUsers[] = $UserID;
                if($DeclarationOwner === false)
                {
                    $DeclarationOwner = $UserID;
                }
            }
        }
        if($DeclarationOwner == $_User['id'])
        {
            $IsDeclarationOwner = true;
        }
        else
        {
            $IsDeclarationOwner = false;
            $GetUsername = doquery("SELECT `username` FROM {{table}} WHERE `id` = '{$DeclarationOwner}' LIMIT 1;", 'users', true);
            $GetUsername = $GetUsername['username'];
        }
    }
}

$parse['ShowError'] = 'display: none;';

if(isset($_GET['cmd']) && $_GET['cmd'] == 'rmv')
{
    if (
        $HasExistingDeclarationEntry === true AND
        (
            $DeclarationDetails['status'] == 0 OR
            $DeclarationDetails['status'] == 1
        )
    )
    {
        if($IsDeclarationOwner === true)
        {
            doquery("UPDATE {{table}} SET `multi_validated` = 0 WHERE `id` IN (".implode(', ', $DeclarationUsers).");", 'users');
            doquery("UPDATE {{table}} SET `status` = -2 WHERE `id` = {$_User['multiIP_DeclarationID']};", 'declarations');
            $parse['ShowError'] = '';
            $parse['ErrorText'] = $_Lang['Msg_DeclarationRemoved'];
            $parse['ErrorColor'] = 'lime';

            $SendDeleteMessage = true;
            foreach($DeclarationUsers as $UserID)
            {
                if($UserID != $_User['id'])
                {
                    $SendDeleteMessageUsers[] = $UserID;
                }
            }
        }
        else
        {
            $NewArray = [];
            foreach($DeclarationUsers as $UserID)
            {
                if($UserID != $_User['id'])
                {
                    $NewArray[] = "|{$UserID}|";
                }
            }
            if(count($NewArray) <= 1)
            {
                doquery("UPDATE {{table}} SET `multi_validated` = 0 WHERE `id` IN (".implode(', ', $DeclarationUsers).");", 'users');
                doquery("UPDATE {{table}} SET `status` = -2 WHERE `id` = {$_User['multiIP_DeclarationID']};", 'declarations');

                $SendDeleteMessage = true;
                foreach($DeclarationUsers as $UserID)
                {
                    if($UserID != $_User['id'])
                    {
                        $SendDeleteMessageUsers[] = $UserID;
                    }
                }
            }
            else
            {
                doquery("UPDATE {{table}} SET `users` = '".implode(',', $NewArray)."' WHERE `id` = {$_User['multiIP_DeclarationID']};", 'declarations');
                doquery("UPDATE {{table}} SET `multi_validated` = 0, `multiIP_DeclarationID` = 0 WHERE `id` = {$_User['id']};", 'users');
            }
            $parse['ShowError'] = '';
            $parse['ErrorText'] = $_Lang['Msg_DeclarationLeft'];
            $parse['ErrorColor'] = 'lime';
        }

        $HasExistingDeclarationEntry = false;
        $DeclarationDetails = [];

        if($SendDeleteMessage === true)
        {
            $Message = false;
            $Message['msg_id'] = '079';
            $Message['args'] = array();
            $Message = json_encode($Message);
            Cache_Message($SendDeleteMessageUsers, 0, time(), 70, '007', '020', $Message);
        }
    }
}

if(isset($_POST['mode']) && $_POST['mode'] == 'addit')
{
    if($_User['multi_validated'] !== 1)
    {
        if (
            $HasExistingDeclarationEntry === false ||
            (
                isset($DeclarationDetails['status']) &&
                $DeclarationDetails['status'] == -1
            )
        )
        {
            if(empty($_POST['userslist']))
            {
                message($_Lang['Error_Empty_Userlist'], $_Lang['Title'], 'declaration.php', 3);
            }
            if(!isset($_POST['declaration_type']) || $_POST['declaration_type'] <= 0)
            {
                message($_Lang['Error_Type_NotSelected'], $_Lang['Title'], 'declaration.php', 3);
            }
            $DeclarationType = intval($_POST['declaration_type']);
            if($DeclarationType > 5)
            {
                message($_Lang['Error_Bad_DeclarationType'], $_Lang['Title'], 'declaration.php', 3);
            }

            $UserList = explode(',', $_POST['userslist']);
            if((array)$UserList !== $UserList)
            {
                message($_Lang['Error_BadUserlist_Sent'], $_Lang['Title'], 'declaration.php', 3);
            }

            $BadUsernames = array();
            $SearchUsers = array();
            foreach($UserList as $ThisUser)
            {
                $ThisUser = strtolower(trim($ThisUser));
                if($ThisUser == strtolower($_User['username']))
                {
                    $FoundOwn = true;
                    continue;
                }
                if(!preg_match(REGEXP_USERNAME_ABSOLUTE, $ThisUser))
                {
                    if(!in_array($ThisUser, $BadUsernames))
                    {
                        $BadUsernames[] = $ThisUser;
                    }
                }
                else
                {
                    if(!in_array($ThisUser, $SearchUsers))
                    {
                        $BadUsernames[] = $ThisUser;
                        $SearchUsers[] = "'{$ThisUser}'";
                    }
                }
            }

            if(!empty($SearchUsers))
            {
                $SQLResult_SearchUsers = doquery(
                    "SELECT `user`.`id`, `user`.`username` FROM {{table}} AS `user` LEFT JOIN {{prefix}}declarations AS `decl` ON `decl`.`id` = `user`.`multiIP_DeclarationID` WHERE `user`.`username` IN (".implode(', ', $SearchUsers).") AND (`user`.`multiIP_DeclarationID` = 0 OR `decl`.`status` = -2 OR `decl`.`status` = -1);",
                    'users'
                );

                while($Result = $SQLResult_SearchUsers->fetch_assoc())
                {
                    $FoundUsers[] = "|{$Result['id']}|";
                    $SearchNDestroy = array_search(strtolower($Result['username']), $BadUsernames);
                    if((int)$SearchNDestroy === $SearchNDestroy)
                    {
                        unset($BadUsernames[$SearchNDestroy]);
                    }
                    else
                    {
                        foreach($SearchNDestroy as $Value)
                        {
                            unset($BadUsernames[$Value]);
                        }
                    }
                }
            }

            if(!empty($BadUsernames))
            {
                $parse['ShowBadUsers'] = implode(', ', $BadUsernames);
                $parse['ShowError'] = '';
                $parse['ErrorText'] = sprintf($_Lang['Error_BadUsernames'], $parse['ShowBadUsers']);
                $parse['ErrorColor'] = 'red';
            }
            else
            {
                if(!empty($FoundUsers))
                {
                    $QryCreateDeclaration = "INSERT INTO {{table}} SET `users` = '|{$_User['id']}|,".implode(',', $FoundUsers)."', `all_present_users` = `users`, `time` = UNIX_TIMESTAMP(), `reason` = '{$DeclarationType}';";
                    doquery($QryCreateDeclaration, 'declarations');
                    foreach($FoundUsers as &$UserID)
                    {
                        $UserID =str_replace('|', '', $UserID);
                    }
                    doquery("UPDATE {{table}} SET `multi_validated` = 0, `multiIP_DeclarationID` = LAST_INSERT_ID() WHERE `id` IN ({$_User['id']}, ".implode(',', $FoundUsers).");", 'users');
                    message($_Lang['Declaration_added'], $_Lang['Title']);
                }
                else
                {
                    if($FoundOwn)
                    {
                        message($_Lang['Error_OnlyUFound'], $_Lang['Title'], 'declaration.php', 3);
                    }
                    else
                    {
                        message($_Lang['Error_UsersNotFound'], $_Lang['Title'], 'declaration.php', 3);
                    }
                }
            }
        }
        else
        {
            if($IsDeclarationOwner === false)
            {
                $Message = $_Lang['Cannot_add_second_declare_other'];
            }
            else
            {
                $Message = $_Lang['Cannot_add_second_declare'];
            }
            message($Message, $_Lang['Title']);
        }
    }
    else
    {
        message($_Lang['Multi_validated'], $_Lang['Title'], 'declaration.php', 3);
    }
}

if (
    $HasExistingDeclarationEntry === false ||
    $DeclarationDetails['status'] == -1
)
{
    if (
        isset($DeclarationDetails['status']) &&
        $DeclarationDetails['status'] == -1 &&
        empty($BadUsernames)
    )
    {
        $parse['ShowError'] = '';
        if($IsDeclarationOwner === false)
        {
            $parse['ErrorText'] = $_Lang['Error_DeclarationRejected_other'];
        }
        else
        {
            $parse['ErrorText'] = $_Lang['Error_DeclarationRejected'];
        }
        $parse['ErrorColor'] = 'orange';
    }

    $parse['InsertUserslist'] = (isset($_POST['userslist']) ? $_POST['userslist'] : null);
    if(isset($_POST['declaration_type']))
    {
        $parse['InsertType_'.$_POST['declaration_type']] = 'checked';
    }

    $Page = parsetemplate($PageTpl, $parse);
}
else
{
    if ($DeclarationDetails['status'] == 1)
    {
        // Validation Done
        if($IsDeclarationOwner === true)
        {
            $Message = $_Lang['Multi_validated'];
        }
        else
        {
            $Message = sprintf($_Lang['Multi_validated_other'], $GetUsername);
        }
        message($Message, $_Lang['Title']);
    }
    else if ($DeclarationDetails['status'] == 0)
    {
        // Validation Awaiting
        if($IsDeclarationOwner === true)
        {
            $Message = $_Lang['Declaration_awaiting'];
        }
        else
        {
            $Message = sprintf($_Lang['Declaration_awaiting_other'], $GetUsername);
        }
        message($Message, $_Lang['Title']);
    }
}

display($Page, $_Lang['Title'], false);

?>
