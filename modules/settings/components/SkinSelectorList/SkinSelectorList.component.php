<?php

namespace UniEngine\Engine\Modules\Settings\Components\SkinSelectorList;

use UniEngine\Engine\Modules\Settings;

/**
 * @param object $props
 * @param number $props['currentUserSkinPath']
 *
 * @return object $result
 * @return string $result['componentHTML']
 */
function render($props) {
    $currentUserSkinPath = $props['currentUserSkinPath'];

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $tplBodyCache = [
        'optionBody' => $localTemplateLoader('optionBody'),
    ];
    $availableSkins = Settings\Utils\Helpers\getAvailableSkins();

    $options = array_map_withkeys(
        $availableSkins,
        function ($skinDetails) use ($currentUserSkinPath, &$tplBodyCache) {
            $isSelectedHTMLAttr = ($skinDetails['path'] == $currentUserSkinPath ? "selected" : "");

            $tplParams = [
                'skinPath' => $skinDetails['path'],
                'isSelectedHTMLAttr' => $isSelectedHTMLAttr,
                'skinName' => $skinDetails['name'],
            ];

            return parsetemplate($tplBodyCache['optionBody'], $tplParams);
        }
    );

    $componentHTML = implode('', $options);

    return [
        'componentHTML' => $componentHTML
    ];
}

?>
