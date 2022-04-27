<?php

namespace UniEngine\Engine\Modules\FlightControl\Screens\Shortcuts\Components\ListManagement;

use UniEngine\Engine\Modules\FlightControl;

/**
 * @param Object $props
 * @param String $props['userId']
 *
 * @return Object $result
 * @return String $result['componentHTML']
 */
function render($props) {
    global $_Lang;

    $userId = $props['userId'];

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);
    $tplBodyCache = [
        'body' => $localTemplateLoader('body'),
    ];

    $fetchShortcutsResult = FlightControl\Utils\Fetchers\fetchSavedShortcuts([
        'userId' => $userId,
    ]);

    $shortcutsList = mapQueryResults($fetchShortcutsResult, function ($shortcutEntry) {
        $shortcutId = $shortcutEntry['id'];
        $shortcutLabel = FlightControl\Components\TargetOptionLabel\render([
            'target' => $shortcutEntry,
        ])['componentHTML'];

        return "<option value=\"{$shortcutId}\">{$shortcutLabel}</option>";
    });

    $componentTPLData = [
        'shortcuts_list' => (
            !empty($shortcutsList) ?
                implode('', $shortcutsList) :
                '<option>'.$_Lang['no_shortcuts'].'</option>'
        )
    ];

    $componentHTML = parsetemplate($tplBodyCache['body'], array_merge($_Lang, $componentTPLData));

    return [
        'componentHTML' => $componentHTML,
    ];
}

?>
