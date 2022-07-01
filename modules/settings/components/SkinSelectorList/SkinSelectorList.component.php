<?php

namespace UniEngine\Engine\Modules\Settings\Components\SkinSelectorList;

/**
 * @param object $props
 * @param number $props['currentUserSkinPath']
 * @param array $props['availableSkins']
 *
 * @return object $result
 * @return string $result['componentHTML']
 */
function render($props) {
    $currentUserSkinPath = $props['currentUserSkinPath'];
    $availableSkins = $props['availableSkins'];

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $tplBodyCache = [
        'optionBody' => $localTemplateLoader('optionBody'),
    ];

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
