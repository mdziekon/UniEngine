<?php

namespace UniEngine\Engine\Modules\Registration\Components\RegistrationConfirmationMail;

//  Arguments
//      - $props (Object)
//          - userId (String)
//          - login (String)
//          - password (String)
//          - gameName (String)
//          - universe (String)
//          - activationCode (String)
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_Lang;

    includeLang('reg_ajax');

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $tplBodyCache = [
        'body' => $localTemplateLoader('body'),
    ];

    $gameLink = buildLinkHTML([
        'text' => $props['gameName'],
        'href' => GAMEURL_STRICT
    ]);

    $activationLinkUrl = GAMEURL . 'activate.php?code=' . $props['activationCode'];
    $activationLink = buildLinkHTML([
        'text' => $activationLinkUrl,
        'href' => $activationLinkUrl
    ]);

    $referralLinkUrl = GAMEURL . 'index.php?r=' . $props['userId'];
    $referralLink = buildLinkHTML([
        'text' => $referralLinkUrl,
        'href' => $referralLinkUrl
    ]);

    $mailContent = parsetemplate(
        $_Lang['mail_text'],
        [
            'login' => $props['login'],
            'password' => $props['password'],
            'gameName' => $props['gameName'],
            'gameLink' => $gameLink,
            'Universe' => $props['universe'],
            'activationlink' => $activationLink,
            'UserRefLink' => $referralLink,
        ]
    );

    $componentHTML = parsetemplate(
        $tplBodyCache['body'],
        [
            'mailContent' => $mailContent,
        ]
    );

    return [
        'componentHTML' => $componentHTML
    ];
}

?>
