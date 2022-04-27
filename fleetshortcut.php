<?php

define('INSIDE', true);

$_EnginePath = './';
include($_EnginePath.'common.php');

include($_EnginePath . 'modules/flightControl/_includes.php');

use UniEngine\Engine\Modules\FlightControl;

loggedCheck();

includeLang('fleetshortcut');

$Mode = (isset($_GET['mode']) ? $_GET['mode'] : null);
$ID = (isset($_GET['id']) ? intval($_GET['id']) : 0);

if (empty($Mode)) {
    $page = FlightControl\Screens\Shortcuts\render([
        'userId' => $_User['id'],
    ])['componentHTML'];
} else {
    switch ($Mode) {
        case 'add': {
            if (
                isset($_POST['action']) &&
                $_POST['action'] == 'add'
            ) {
                $upsertShortcutResult = FlightControl\Screens\Shortcuts\Commands\upsertShortcut([
                    'userId' => $_User['id'],
                    'input' => $_POST,
                ]);

                if ($upsertShortcutResult['isSuccess']) {
                    message($_Lang['Shortcut_hasbeen_added'], $_Lang['Adding_shortcut'],'fleetshortcut.php', 2);
                }

                switch ($upsertShortcutResult['error']['code']) {
                    case 'INVALID_COORDINATES':
                        message($_Lang['Bad_coordinates'], $_Lang['Adding_shortcut'], 'fleetshortcut.php?mode=add', 2);
                        break;
                    case 'INVALID_NAME':
                        message($_Lang['Forbidden_signs_in_name'], $_Lang['Adding_shortcut'], 'fleetshortcut.php?mode=add', 2);
                        break;
                    case 'ALREADY_EXISTS':
                        message($_Lang['That_target_already_exists'], $_Lang['Adding_shortcut'], 'fleetshortcut.php?mode=add', 2);
                        break;
                }
            } else {
                $_Lang['Action_shortcut'] = $_Lang['Adding_shortcut'];
                $_Lang['Action'] = $_Lang['Add'];
                $_Lang['post_action'] = 'add';

                $page = parsetemplate(gettemplate('fleetshortcut_add_edit'), $_Lang);
            }

            break;
        }
        case 'delete': {
            $deleteShortcutResult = FlightControl\Screens\Shortcuts\Commands\deleteShortcut([
                'userId' => $_User['id'],
                'input' => $_GET,
            ]);

            if ($deleteShortcutResult['isSuccess']) {
                message($_Lang['Link_hasbeen_deleted'], $_Lang['Deleting_shortcut'], 'fleetshortcut.php', 2);
            }

            switch ($deleteShortcutResult['error']['code']) {
                case 'INVALID_ID':
                case 'USER_NOT_OWNER':
                    message($_Lang['Bad_ID_given'], $_Lang['Deleting_shortcut'], 'fleetshortcut.php', 2);
                    break;
            }

            break;
        }
        case 'edit': {
            if (
                isset($_POST['action']) &&
                $_POST['action'] == 'edit'
            ) {
                $upsertShortcutResult = FlightControl\Screens\Shortcuts\Commands\upsertShortcut([
                    'userId' => $_User['id'],
                    'input' => $_POST,
                    'shortcutId' => $_GET['id'],
                ]);

                if ($upsertShortcutResult['isSuccess']) {
                    message($_Lang['Shortcut_hasbeen_saved'], $_Lang['Editing_shortcut'], 'fleetshortcut.php', 2);
                }

                switch ($upsertShortcutResult['error']['code']) {
                    case 'INVALID_COORDINATES':
                        message($_Lang['Bad_coordinates'], $_Lang['Editing_shortcut'], 'fleetshortcut.php', 2);
                        break;
                    case 'INVALID_NAME':
                        message($_Lang['Forbidden_signs_in_name'], $_Lang['Editing_shortcut'], 'fleetshortcut.php', 2);
                        break;
                    case 'ALREADY_EXISTS':
                        message($_Lang['That_target_already_exists'], $_Lang['Editing_shortcut'], 'fleetshortcut.php', 2);
                        break;
                    case 'INVALID_ID':
                    case 'USER_NOT_OWNER':
                        message($_Lang['Bad_ID_given'], $_Lang['Editing_shortcut'], 'fleetshortcut.php', 2);
                        break;
                }
            }

            $ID = (isset($_GET['id']) ? intval($_GET['id']) : 0);

            if ($ID <= 0) {
                message($_Lang['Bad_ID_given'], $_Lang['Deleting_shortcut'], 'fleetshortcut.php', 2);
            }

            $SelectLink = doquery("SELECT * FROM {{table}} WHERE `id` = {$ID} LIMIT 1;", 'fleet_shortcuts', true);
            if (
                $SelectLink['id_owner'] > 0 &&
                $SelectLink['id_owner'] == $_User['id']
            ) {
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
            } else {
                message($_Lang['Bad_ID_given'], $_Lang['Editing_shortcut'],'fleetshortcut.php', 2);
            }

            break;
        }
    }
}

display($page, $_Lang['Title']);

?>
