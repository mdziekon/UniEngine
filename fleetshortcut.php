<?php

define('INSIDE', true);

$_EnginePath = './';
include($_EnginePath . 'common.php');
include($_EnginePath . 'modules/flightControl/_includes.php');

use UniEngine\Engine\Modules\FlightControl;

loggedCheck();

includeLang('fleetshortcut');

// TODO: Move all modes into Screens\Shortcuts
function renderPage($mode) {
    global $_Lang, $_User;

    if (empty($mode)) {
        $page = FlightControl\Screens\Shortcuts\render([
            'userId' => $_User['id'],
        ])['componentHTML'];

        return $page;
    }

    if ($mode === 'add') {
        if (
            !isset($_POST['action']) ||
            $_POST['action'] != 'add'
        ) {
            $page = FlightControl\Screens\Shortcuts\Components\ShortcutManagementForm\render([
                'shortcutId' => null,
                'userId' => $_User['id'],
            ])['componentHTML'];

            return $page;
        }

        $upsertShortcutResult = FlightControl\Screens\Shortcuts\Commands\upsertShortcut([
            'userId' => $_User['id'],
            'input' => $_POST,
        ]);

        if ($upsertShortcutResult['isSuccess']) {
            message($_Lang['Shortcut_hasbeen_added'], $_Lang['Adding_shortcut'],'fleetshortcut.php', 2);

            return;
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

        return;
    }
    if ($mode === 'edit') {
        if (
            !isset($_POST['action']) ||
            $_POST['action'] != 'edit'
        ) {
            $shortcutId = (isset($_GET['id']) ? intval($_GET['id']) : 0);

            if ($shortcutId <= 0) {
                message($_Lang['Bad_ID_given'], $_Lang['Deleting_shortcut'], 'fleetshortcut.php', 2);
            }

            $SelectLink = doquery("SELECT * FROM {{table}} WHERE `id` = {$shortcutId} LIMIT 1;", 'fleet_shortcuts', true);
            if (
                !$SelectLink ||
                $SelectLink['id_owner'] != $_User['id']
            ) {
                message($_Lang['Bad_ID_given'], $_Lang['Editing_shortcut'], 'fleetshortcut.php', 2);
            }

            $page = FlightControl\Screens\Shortcuts\Components\ShortcutManagementForm\render([
                'shortcut' => $SelectLink,
            ])['componentHTML'];

            return $page;
        }

        $upsertShortcutResult = FlightControl\Screens\Shortcuts\Commands\upsertShortcut([
            'userId' => $_User['id'],
            'input' => $_POST,
            'shortcutId' => $_GET['id'],
        ]);

        if ($upsertShortcutResult['isSuccess']) {
            message($_Lang['Shortcut_hasbeen_saved'], $_Lang['Editing_shortcut'], 'fleetshortcut.php', 2);

            return;
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

        return;
    }
    if ($mode === 'delete') {
        $deleteShortcutResult = FlightControl\Screens\Shortcuts\Commands\deleteShortcut([
            'userId' => $_User['id'],
            'input' => $_GET,
        ]);

        if ($deleteShortcutResult['isSuccess']) {
            message($_Lang['Link_hasbeen_deleted'], $_Lang['Deleting_shortcut'], 'fleetshortcut.php', 2);

            return;
        }

        switch ($deleteShortcutResult['error']['code']) {
            case 'INVALID_ID':
            case 'USER_NOT_OWNER':
                message($_Lang['Bad_ID_given'], $_Lang['Deleting_shortcut'], 'fleetshortcut.php', 2);
                break;
        }

        return;
    }
}

$mode = (isset($_GET['mode']) ? $_GET['mode'] : null);
$page = renderPage($mode);

display($page, $_Lang['Title']);

?>
