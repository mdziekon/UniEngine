<?php

define('INSIDE', true);
define('IN_ADMIN', true);

$_SetAccessLogPreFilename = 'admin/';
$_SetAccessLogPath = '../';
$_EnginePath = './../';

include($_EnginePath.'common.php');

if(CheckAuth('supportadmin'))
{
    includeLang('admin');
    includeLang('admin/declarations_list');

    $PageTPL = gettemplate('admin/declarelist_body');
    $RowsTPL = gettemplate('admin/declarelist_rows');

    if(!empty($_GET['action']))
    {
        $ID = isset($_GET['id']) ? floor(floatval($_GET['id'])) : 0;
        if($ID > 0)
        {
            $GetDeclaration = doquery("SELECT `users`, `status` FROM {{table}} WHERE `id` = {$ID} LIMIT 1;", 'declarations', true);
            if($GetDeclaration)
            {
                if($GetDeclaration['status'] != -2)
                {
                    $Status = $GetDeclaration['status'];
                    if(!(($Status == 1 AND $_GET['action'] == 'accept') OR ($Status == -1 AND $_GET['action'] == 'refuse')))
                    {
                        $Message = false;

                        $UserIDs = str_replace('|', '', $GetDeclaration['users']);
                        switch($_GET['action'])
                        {
                            case 'delete':
                                $Message['msg_id'] = '080';
                                doquery("UPDATE {{table}} SET `status` = -2 WHERE `id` = {$ID};", 'declarations');
                                doquery("UPDATE {{table}} SET `multi_validated` = 0 WHERE `id` IN ({$UserIDs});", 'users');
                                $MSG = $_Lang['declaration_deleted'];
                                break;
                            case 'accept':
                                $Message['msg_id'] = '077';
                                doquery("UPDATE {{table}} SET `status` = 1 WHERE `id` = {$ID};", 'declarations');
                                doquery("UPDATE {{table}} SET `multi_validated` = 1 WHERE `id` IN ({$UserIDs});", 'users');
                                $MSG = $_Lang['declaration_accepted'];
                                break;
                            case 'refuse':
                                $Message['msg_id'] = '078';
                                doquery("UPDATE {{table}} SET `status` = -1 WHERE `id` = {$ID};", 'declarations');
                                doquery("UPDATE {{table}} SET `multi_validated` = -1 WHERE `id` IN ({$UserIDs});", 'users');
                                $MSG = $_Lang['declaration_refused'];
                                break;
                        }
                        $ExplodeUsers = explode(',', $UserIDs);
                        foreach($ExplodeUsers as $UserID)
                        {
                            if($UserID > 0)
                            {
                                $SendArray[] = $UserID;
                            }
                        }

                        $Message['args'] = array();
                        $Message = json_encode($Message);
                        Cache_Message($SendArray, 0, time(), 70, '007', '020', $Message);
                    }
                    else
                    {
                        $MSG = $_Lang['declaration_noaffect'];
                    }
                }
                else
                {
                    $MSG = $_Lang['declaration_deletedalready'];
                }
            }
            else
            {
                $MSG = $_Lang['declaration_noexist'];
            }
        }
        else
        {
            $MSG = $_Lang['No_id_given'];
        }
    }

    $AddWhere = '';
    if(!isset($_GET['cmd']) || $_GET['cmd'] != 'showall')
    {
        $AddWhere = ' WHERE `status` != -2 ';
        $_Lang['AddShowAll'] = 'cmd=showall';
    }
    else
    {
        $_Lang['Declarations_Header_ShowAll'] = $_Lang['Declarations_Header_HideDel'];
    }

    $SQLResult_GetDeclarations = doquery(
        "SELECT * FROM {{table}} {$AddWhere} ORDER BY `time` DESC",
        'declarations'
    );

    $parse = $_Lang;
    $parse['adm_ul_table'] = '';
    if(!empty($MSG))
    {
        $parse['system_msg'] = '<tr><td class="c" colspan="8" style="padding: 5px;">'.$MSG.'</td></tr><tr style="visibility: hidden;"><td><br/></td></tr>';
    }
    $i = 0;

    if($SQLResult_GetDeclarations->num_rows > 0)
    {
        while($u = $SQLResult_GetDeclarations->fetch_assoc())
        {
            $UserTemp = explode(',', $u['users']);
            foreach($UserTemp as $Key)
            {
                $UserID = str_replace('|', '', $Key);
                if($UserID > 0)
                {
                    if(empty($UsersID) OR !in_array($UserID, $UsersID))
                    {
                        $UsersID[] = $UserID;
                    }
                    $u['userlist'][] = $UserID;
                }
            }
            if(!empty($u['all_present_users']))
            {
                $UserTemp = explode(',', $u['all_present_users']);
                if(!empty($UserTemp))
                {
                    foreach($UserTemp as $Key)
                    {
                        $UserID = str_replace('|', '', $Key);
                        if($UserID > 0)
                        {
                            if(empty($UsersID) OR !in_array($UserID, $UsersID))
                            {
                                $UsersID[] = $UserID;
                            }
                            $u['allusers'][] = $UserID;
                        }
                    }
                }
            }

            $Data[] = $u;
        }

        $SQLResult_GetUsers = doquery(
            "SELECT `id`, `username` FROM {{table}} WHERE `id` IN (".implode(',', $UsersID).")",
            'users'
        );

        while($Users = $SQLResult_GetUsers->fetch_assoc())
        {
            $UserNames[$Users['id']] = $Users['username'];
        }
        foreach($UsersID as $UserID)
        {
            if(empty($UserNames[$UserID]))
            {
                $UserNames[$UserID] = $_Lang['Declarations_EmptyUser'];
            }
        }

        foreach($Data as $Vars)
        {
            $Bloc = array();

            $Bloc['UserID'] = $Vars['userlist'][0];
            $Bloc['Username'] = $UserNames[$Vars['userlist'][0]];
            foreach($Vars['userlist'] as $Index => $Values)
            {
                if($Index == 0)
                {
                    continue;
                }
                $Bloc['CreateOtherPlayers'][] = "<a href=\"userlist.php?uid={$Values}\">{$UserNames[$Values]} (#{$Values})</a>";
            }
            if(!empty($Bloc['CreateOtherPlayers']))
            {
                $Bloc['CreateOtherPlayers'] = implode(', ', $Bloc['CreateOtherPlayers']);
            }
            else
            {
                $Bloc['CreateOtherPlayers'] = $_Lang['Declarations_NoUser'];
            }
            if(!empty($Vars['allusers']))
            {
                foreach($Vars['allusers'] as $UserID)
                {
                    if(!in_array($UserID, $Vars['userlist']))
                    {
                        $Bloc['NotPresentUsers'][] = "<a href=\"userlist.php?uid={$UserID}\">{$UserNames[$UserID]} (#{$UserID})</a>";
                    }
                }
            }
            if(!empty($Bloc['NotPresentUsers']))
            {
                $Bloc['CreateOtherPlayers'] .= '<br/><span class="orange">[</span> '.implode(', ', $Bloc['NotPresentUsers']).' <span class="orange">]</span>';
            }

            $Bloc['UsersIDList'] = implode('|', $Vars['userlist']);
            $Bloc['DeclarationDate'] = prettyDate('d m Y\<\b\r/>H:i:s', $Vars['time'], 1);
            $Bloc['DeclarationReason'] = $_Lang['DeclarationReasons_'][$Vars['reason']];
            $Bloc['DeclarationStatus'] = $_Lang['DeclarationStatuses_'][(string)($Vars['status'] + 0)];
            $Bloc['declaration_id'] = $Vars['id'];

            $Bloc['btn_search__tooltip']                    = $_Lang['DeclarationRow_Actions_BtnSearch_tooltip'];
            $Bloc['btn_search__alt']                        = $_Lang['DeclarationRow_Actions_BtnSearch_alt'];
            $Bloc['btn_soft_delete_declaration__tooltip']   = $_Lang['DeclarationRow_Actions_BtnSoftDeleteDeclaration_tooltip'];
            $Bloc['btn_soft_delete_declaration__alt']       = $_Lang['DeclarationRow_Actions_BtnSoftDeleteDeclaration_alt'];
            $Bloc['btn_reject_declaration__tooltip']        = $_Lang['DeclarationRow_Actions_BtnRejectDeclaration_tooltip'];
            $Bloc['btn_reject_declaration__alt']            = $_Lang['DeclarationRow_Actions_BtnRejectDeclaration_alt'];
            $Bloc['btn_accept_declaration__tooltip']        = $_Lang['DeclarationRow_Actions_BtnAcceptDeclaration_tooltip'];
            $Bloc['btn_accept_declaration__alt']            = $_Lang['DeclarationRow_Actions_BtnAcceptDeclaration_alt'];

            $parse['adm_ul_table'] .= parsetemplate($RowsTPL, $Bloc);
            $i += 1;
        }

        $parse['adm_ul_count'] = $i;
    }
    else
    {
        $parse['adm_ul_table'] = '<tr><th colspan="8" style="padding: 3px; color: red;">'.$_Lang['Declarations_NothingFound'].'</th></tr>';
    }

    $page = parsetemplate( $PageTPL, $parse );
    display($page, $_Lang['declaration_title'], false, true);
}
else
{
    message($_Lang['sys_noalloaw'], $_Lang['sys_noaccess']);
}

?>
