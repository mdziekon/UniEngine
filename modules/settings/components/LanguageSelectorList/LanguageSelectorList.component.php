<?php

namespace UniEngine\Engine\Modules\Settings\Components\LanguageSelectorList;

/**
 * @param object $props
 * @param number $props['currentUserLanguage']
 *
 * @return object $result
 * @return string $result['componentHTML']
 */
function render($props) {
    global $_Lang;

    $currentUserLanguage = $props['currentUserLanguage'];

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $tplBodyCache = [
        'optionBody' => $localTemplateLoader('optionBody'),
    ];
    $availableLanguages = UNIENGINE_LANGS_AVAILABLE;

    $options = array_map_withkeys(
        $availableLanguages,
        function ($langKey) use ($currentUserLanguage, &$_Lang, &$tplBodyCache) {
            $langData = $_Lang['LanguagesAvailable'][$langKey];
            $isSelectedHTMLAttr = ($langKey == $currentUserLanguage ? "selected" : "");

            $tplParams = [
                'langKey' => $langKey,
                'isSelectedHTMLAttr' => $isSelectedHTMLAttr,
                'langFlagEmoji' => $langData["flag_emoji"],
                'langName' => $langData["name"],
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
