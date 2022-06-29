<?php

namespace UniEngine\Engine\Modules\Settings\Components\LoginHistory;

use UniEngine\Engine\Includes\Helpers\Common;
use UniEngine\Engine\Modules\Settings;

/**
 * @param object $props
 * @param array $props['loginHistoryEntries']
 * @param number $props['displayItemsCount']
 * @param string $props['currentUserLastIp']
 * @param number $props['currentTimestamp']
 *
 * @return object $result
 * @return string $result['componentHTML']
 */
function render($props) {
    global $_Lang;

    $loginHistoryEntries = $props['loginHistoryEntries'];
    $displayItemsCount = $props['displayItemsCount'];
    $currentUserLastIp = $props['currentUserLastIp'];
    $currentTimestamp = $props['currentTimestamp'];

    if (empty($loginHistoryEntries)) {
        $localTemplateLoader = createLocalTemplateLoader(__DIR__);
        $componentHTML = parsetemplate($localTemplateLoader('emptyList'), $_Lang);

        return [
            'componentHTML' => $componentHTML
        ];
    }

    $limitedLoginHistoryEntries = Common\Collections\firstN($loginHistoryEntries, $displayItemsCount);

    $listElements = array_map_withkeys(
        $limitedLoginHistoryEntries,
        function ($loginEntry) use ($currentUserLastIp, $currentTimestamp) {
            return Settings\Components\LoginHistoryEntry\render([
                'entryData' => $loginEntry,
                'userLastIp' => $currentUserLastIp,
                'currentTimestamp' => $currentTimestamp,
            ])['componentHTML'];
        }
    );

    $componentHTML = implode('', $listElements);

    return [
        'componentHTML' => $componentHTML
    ];
}

?>
