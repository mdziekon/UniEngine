<?php

namespace UniEngine\Engine\Modules\FlightControl\Screens\Shortcuts\Components\ShortcutManagementForm;

/**
 * @param Object $props
 * @param Object? $props['shortcut']
 *
 * @return Object $result
 * @return String $result['componentHTML']
 */
function render($props) {
    global $_Lang;

    $shortcut = $props['shortcut'];

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);
    $tplBodyCache = [
        'body' => $localTemplateLoader('body'),
    ];

    $managedShortcut = [
        'own_name' => null,
        'galaxy' => null,
        'system' => null,
        'planet' => null,
        'type' => null,
    ];

    if (!empty($shortcut)) {
        $managedShortcut = $shortcut;
    }

    $componentTPLData = [
        'Action_shortcut' => (
            $shortcut !== null ?
                $_Lang['Editing_shortcut'] :
                $_Lang['Adding_shortcut']
        ),
        'Action' => (
            $shortcut !== null ?
                $_Lang['Edit'] :
                $_Lang['Add']
        ),
        'post_action' => (
            $shortcut !== null ?
                'edit' :
                'add'
        ),
        'edit_id' => (
            $shortcut !== null ?
                $shortcut['id'] :
                null
        ),
        'set_name' => $managedShortcut['own_name'],
        'set_galaxy' => $managedShortcut['galaxy'],
        'set_system' => $managedShortcut['system'],
        'set_planet' => $managedShortcut['planet'],
        'planet_selected' => (
            $managedShortcut['type'] == 1 ?
                'selected' :
                null
        ),
        'debris_selected' => (
            $managedShortcut['type'] == 2 ?
                'selected' :
                null
        ),
        'moon_selected' => (
            $managedShortcut['type'] == 3 ?
                'selected' :
                null
        ),
    ];

    $componentHTML = parsetemplate($tplBodyCache['body'], array_merge($_Lang, $componentTPLData));

    return [
        'componentHTML' => $componentHTML,
    ];
}

?>
