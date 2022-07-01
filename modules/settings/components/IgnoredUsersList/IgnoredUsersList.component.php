<?php

namespace UniEngine\Engine\Modules\Settings\Components\IgnoredUsersList;

/**
 * @param object $props
 * @param number $props['ignoredUsers']
 *
 * @return object $result
 * @return string $result['componentHTML']
 */
function render($props) {
    global $_Lang;

    $ignoredUsers = $props['ignoredUsers'];

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $tplBodyCache = [
        'emptyList' => $localTemplateLoader('emptyList'),
        'listElementBody' => $localTemplateLoader('listElementBody'),
    ];

    if (empty($ignoredUsers)) {
        $componentHTML = parsetemplate($tplBodyCache['emptyList'], $_Lang);

        return [
            'componentHTML' => $componentHTML
        ];
    }

    $listElements = array_map_withkeys(
        $ignoredUsers,
        function ($userName, $userId) use (&$tplBodyCache) {
            $tplParams = [
                'userId' => $userId,
                'userName' => $userName,
            ];

            return parsetemplate($tplBodyCache['listElementBody'], $tplParams);
        }
    );

    $componentHTML = implode('<br/>', $listElements);

    return [
        'componentHTML' => $componentHTML
    ];
}

?>
