<?php

namespace UniEngine\Engine\Modules\Session\Screens\LoginView\Components\LoginForm;

//  Arguments
//      - $props (Object)
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_Lang, $_GameConfig;

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $tplBodyCache = [
        'pageBody' => $localTemplateLoader('form_body'),
    ];

    $renderLangSelectorOptions = function () use ($_Lang) {
        $options = [];

        foreach (UNIENGINE_LANGS_AVAILABLE as $langKey) {
            $langData = $_Lang['LanguagesAvailable'][$langKey];

            $options[] = (
                "<a href='?lang={$langKey}' title='{$langData['name']}'>" .
                "{$langData['flag_emoji']}" .
                "</a>"
            );
        }

        return implode('&nbsp;&nbsp;', $options);
    };

    $isGameDisabled = $_GameConfig['game_disable'];
    $gameDisabledReason = $_GameConfig['close_reason'];

    $additionalTplData = [
        'PHP_InsertUniCode' => LOGINPAGE_UNIVERSUMCODE,
        'PHP_Insert_LangSelectors' => $renderLangSelectorOptions(),

        'type' => (
            $isGameDisabled ?
                'button" onclick="alert(\'' . str_replace('<br/>', "\n", $gameDisabledReason) . '\')' :
                'submit'
        ),
        'LoginButton' => (
            $isGameDisabled ?
                $_Lang['Body_ServerOffline'] :
                $_Lang['Body_Submit']
        ),
    ];

    $componentHTML = parsetemplate(
        $tplBodyCache['pageBody'],
        array_merge(
            $_Lang,
            $additionalTplData
        )
    );

    return [
        'componentHTML' => $componentHTML
    ];
}

?>
