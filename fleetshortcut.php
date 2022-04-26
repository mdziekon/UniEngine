<?php

define('INSIDE', true);

$_EnginePath = './';
include($_EnginePath.'common.php');

include($_EnginePath . 'modules/flightControl/_includes.php');

use UniEngine\Engine\Modules\FlightControl;

loggedCheck();

includeLang('fleetshortcut');
$_Lang['shortcuts_list'] = '';

$Mode = (isset($_GET['mode']) ? $_GET['mode'] : null);
$ID = (isset($_GET['id']) ? intval($_GET['id']) : 0);

if (empty($Mode)) {
    $fetchShortcutsResult = FlightControl\Utils\Fetchers\fetchSavedShortcuts([
        'userId' => $_User['id'],
    ]);

    $shortcutsList = mapQueryResults($fetchShortcutsResult, function ($shortcutEntry) use (&$_Lang) {
        return '<option value="'.$shortcutEntry['id'].'">'.((!empty($shortcutEntry['own_name']) ? $shortcutEntry['own_name'].' - ' : '')).$shortcutEntry['name'].(($shortcutEntry['planet_type'] == 3) ? ' ('.$_Lang['moon_sign'].')' : (($shortcutEntry['planet_type'] == 2) ? ' ('.$_Lang['debris_sign'].')' : ' ('.$_Lang['planet_sign'].')')).' ['.$shortcutEntry['galaxy'].':'.$shortcutEntry['system'].':'.$shortcutEntry['planet'].']</option>';
    });

    $_Lang['shortcuts_list'] = (
        !empty($shortcutsList) ?
            implode('', $shortcutsList) :
            '<option>'.$_Lang['no_shortcuts'].'</option>'
    );

    $page = parsetemplate(gettemplate('fleetshortcut_overview'), $_Lang);
} else {
    switch($Mode)
    {
        case 'add':
        {
            if(isset($_POST['action']) && $_POST['action'] == 'add')
            {
                $Name = trim($_POST['name']);
                $Galaxy = intval($_POST['galaxy']);
                $System = intval($_POST['system']);
                $Planet = intval($_POST['planet']);
                $Type = intval($_POST['type']);
                if(in_array($Type, array(1,2,3)) && $Galaxy > 0 && $System > 0 && $Planet > 0 && $Galaxy <= MAX_GALAXY_IN_WORLD && $System <= MAX_SYSTEM_IN_GALAXY && $Planet <= (MAX_PLANET_IN_SYSTEM + 1))
                {
                    $TargetID = '0';
                    if($Type == 1 || $Type == 3)
                    {
                        $Query_GetTarget = '';
                        $Query_GetTarget .= "SELECT `id` FROM {{table}} WHERE ";
                        $Query_GetTarget .= "`galaxy` = {$Galaxy} AND `system` = {$System} AND `planet` = {$Planet} AND `planet_type` = {$Type} ";
                        $Query_GetTarget .= "LIMIT 1;";

                        $SQLResult_SelectTarget = doquery($Query_GetTarget, 'planets');

                        if($SQLResult_SelectTarget->num_rows == 1)
                        {
                            $TargetID = ($SQLResult_SelectTarget->fetch_assoc())['id'];
                        }
                    }

                    if(!empty($Name))
                    {
                        if(!preg_match('/^[a-zA-Z0-9\_\-\ ]{1,}$/D', $Name))
                        {
                            message($_Lang['Forbidden_signs_in_name'], $_Lang['Adding_shortcut'], 'fleetshortcut.php?mode=add', 2);
                        }
                    }

                    $Query_CheckShortcut = '';
                    $Query_CheckShortcut .= "SELECT `id` FROM {{table}} WHERE ";
                    $Query_CheckShortcut .= "`galaxy` = {$Galaxy} AND `system` = {$System} AND `planet` = {$Planet} AND `type` = {$Type} ";
                    $Query_CheckShortcut .= "AND `id_owner` = {$_User['id']} ";
                    $Query_CheckShortcut .= "LIMIT 1;";

                    $Result_CheckShortcut = doquery($Query_CheckShortcut, 'fleet_shortcuts');

                    if($Result_CheckShortcut->num_rows == 1)
                    {
                        message($_Lang['That_target_already_exists'], $_Lang['Adding_shortcut'], 'fleetshortcut.php?mode=add', 2);
                    }

                    doquery("INSERT INTO {{table}} VALUES (NULL, {$_User['id']}, {$TargetID}, {$Galaxy}, {$System}, {$Planet}, {$Type}, '{$Name}');", 'fleet_shortcuts');
                    message($_Lang['Shortcut_hasbeen_added'], $_Lang['Adding_shortcut'],'fleetshortcut.php', 2);
                }
                else
                {
                    message($_Lang['Bad_coordinates'], $_Lang['Adding_shortcut'], 'fleetshortcut.php?mode=add', 2);
                }
            }
            else
            {
                $_Lang['Action_shortcut'] = $_Lang['Adding_shortcut'];
                $_Lang['Action']= $_Lang['Add'];
                $_Lang['post_action'] = 'add';

                $page = parsetemplate(gettemplate('fleetshortcut_add_edit'), $_Lang);
            }
            break;
        }
        case 'delete':
        {
            $ID = (isset($_GET['id']) ? intval($_GET['id']) : 0);

            if($ID <= 0)
            {
                message($_Lang['Bad_ID_given'], $_Lang['Deleting_shortcut'], 'fleetshortcut.php', 2);
            }

            $SelectLink = doquery("SELECT `id_owner` FROM {{table}} WHERE `id` = {$ID} LIMIT 1;", 'fleet_shortcuts', true);
            if($SelectLink['id_owner'] > 0)
            {
                if($SelectLink['id_owner'] == $_User['id'])
                {
                    doquery("DELETE FROM {{table}} WHERE `id` = {$ID};", 'fleet_shortcuts');
                    message($_Lang['Link_hasbeen_deleted'], $_Lang['Deleting_shortcut'], 'fleetshortcut.php', 2);
                }
                else
                {
                    message($_Lang['This_shortcut_is_not_yours'], $_Lang['Deleting_shortcut'], 'fleetshortcut.php', 2);
                }
            }
            else
            {
                message($_Lang['Bad_ID_given'], $_Lang['Deleting_shortcut'], 'fleetshortcut.php', 2);
            }
            break;
        }
        case 'edit':
        {
            $ID = (isset($_GET['id']) ? intval($_GET['id']) : 0);

            if($ID <= 0)
            {
                message($_Lang['Bad_ID_given'], $_Lang['Deleting_shortcut'], 'fleetshortcut.php', 2);
            }

            $SelectLink = doquery("SELECT * FROM {{table}} WHERE `id` = {$ID} LIMIT 1;", 'fleet_shortcuts', true);
            if($SelectLink['id_owner'] > 0)
            {
                if($SelectLink['id_owner'] == $_User['id'])
                {
                    if(isset($_POST['action']) && $_POST['action'] == 'edit')
                    {
                        $Name = trim($_POST['name']);
                        $Galaxy = intval($_POST['galaxy']);
                        $System = intval($_POST['system']);
                        $Planet = intval($_POST['planet']);
                        $Type = intval($_POST['type']);
                        if(in_array($Type, array(1,2,3)) && $Galaxy > 0 && $System > 0 && $Planet > 0 && $Galaxy <= MAX_GALAXY_IN_WORLD && $System <= MAX_SYSTEM_IN_GALAXY && $Planet <= (MAX_PLANET_IN_SYSTEM + 1))
                        {
                            $TargetID = '0';
                            if($Type == 1 || $Type == 3)
                            {
                                $SelectTarget = doquery("SELECT `id` FROM {{table}} WHERE `galaxy` = {$Galaxy} AND `system` = {$System} AND `planet` = {$Planet} AND `planet_type` = {$Type} LIMIT 1;", 'planets', true);
                                if($SelectTarget['id'] > 0)
                                {
                                    $TargetID = $SelectTarget['id'];
                                }
                            }

                            if(!empty($Name))
                            {
                                if(!preg_match('/^[a-zA-Z0-9\_\-\ ]{1,}$/D', $Name))
                                {
                                    message($_Lang['Forbidden_signs_in_name'], $_Lang['Editing_shortcut'], 'fleetshortcut.php?mode=edit&id='.$ID, 2);
                                }
                            }

                            $SQLResult_SelectTarget = doquery(
                                "SELECT `id` FROM {{table}} WHERE `galaxy` = {$Galaxy} AND `system` = {$System} AND `planet` = {$Planet} AND `type` = {$Type} AND `id_owner` = {$_User['id']} LIMIT 1;",
                                'fleet_shortcuts'
                            );

                            if($SQLResult_SelectTarget->num_rows == 1)
                            {
                                $SelectTarget = $SQLResult_SelectTarget->fetch_assoc();
                                if($SelectTarget['id'] > 0 AND $SelectTarget['id'] != $ID)
                                {
                                    message($_Lang['That_target_already_exists'], $_Lang['Editing_shortcut'], 'fleetshortcut.php?mode=edit&id='.$ID, 2);
                                }
                                else if($SelectTarget['id'] == $ID)
                                {
                                    message($_Lang['NothingChanged'], $_Lang['Editing_shortcut'], 'fleetshortcut.php?mode=edit&id='.$ID, 2);
                                }
                            }

                            doquery("UPDATE {{table}} SET `id_planet` = {$TargetID}, `galaxy` = {$Galaxy}, `system` = {$System}, `planet` = {$Planet}, `type` = {$Type}, `own_name` = '{$Name}' WHERE `id` = {$ID};", 'fleet_shortcuts');
                            message($_Lang['Shortcut_hasbeen_saved'], $_Lang['Editing_shortcut'], 'fleetshortcut.php', 2);
                        }
                        else
                        {
                            message($_Lang['Bad_coordinates'], $_Lang['Editing_shortcut'], 'fleetshortcut.php?mode=edit&id='.$ID, 2);
                        }
                    }
                    else
                    {
                        $_Lang['Action_shortcut'] = $_Lang['Editing_shortcut'];
                        $_Lang['Action'] = $_Lang['Edit'];
                        $_Lang['post_action'] = 'edit';
                        $_Lang['edit_id'] = $ID;
                        $_Lang['set_name'] = $SelectLink['own_name'];
                        $_Lang['set_galaxy'] = $SelectLink['galaxy'];
                        $_Lang['set_system'] = $SelectLink['system'];
                        $_Lang['set_planet'] = $SelectLink['planet'];
                        switch($SelectLink['type'])
                        {
                            case 1:
                                $_Lang['planet_selected'] = 'selected';
                                break;
                            case 2:
                                $_Lang['debris_selected'] = 'selected';
                                break;
                            case 3:
                                $_Lang['moon_selected'] = 'selected';
                                break;
                        }

                        $page = parsetemplate(gettemplate('fleetshortcut_add_edit'), $_Lang);
                    }
                }
                else
                {
                    message($_Lang['This_shortcut_is_not_yours'], $_Lang['Editing_shortcut'],'fleetshortcut.php', 2);
                }
            }
            else
            {
                message($_Lang['Bad_ID_given'], $_Lang['Editing_shortcut'],'fleetshortcut.php', 2);
            }
            break;
        }
    }
}

display($page, $_Lang['Title']);

?>
