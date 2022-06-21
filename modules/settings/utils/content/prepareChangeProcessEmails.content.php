<?php

namespace UniEngine\Engine\Modules\Settings\Utils\Content;

/**
 * @param array $params
 * @param arrayRef $params['user']
 * @param string $params['newEmailAddress']
 * @param string $params['changeTokenOldAddress']
 * @param string $params['changeTokenNewAddress']
 * @param number $params['currentTimestamp']
 */
function prepareChangeProcessEmails($params) {
    global $_Lang, $_GameConfig;

    $user = &$params['user'];
    $newEmailAddress = $params['newEmailAddress'];
    $changeTokenOldAddress = $params['changeTokenOldAddress'];
    $changeTokenNewAddress = $params['changeTokenNewAddress'];
    $currentTimestamp = $params['currentTimestamp'];

    $mailContentCommonProps = [
        'EP_GameName'       => $_GameConfig['game_name'],
        'EP_User'           => $user['username'],
        'EP_GameLink'       => GAMEURL_STRICT,
        'EP_OldMail'        => $user['email'],
        'EP_NewMail'        => $newEmailAddress,
        'EP_Date'           => date('d.m.Y - H:i:s', $currentTimestamp),
        'EP_IP'             => $user['user_lastip'],
        'EP_ContactLink'    => GAMEURL_STRICT . '/contact.php',
    ];

    $mailContentOldAddressProps = [
        'EP_Link'           => GAMEURL . "email_change.php?hash=old&amp;key={$changeTokenOldAddress}",
        'EP_Text'           => $_Lang['Email_MailOld'],
        'EP_Text2'          => $_Lang['Email_WarnOld']
    ];
    $mailContentNewAddressProps = [
        'EP_Link'           => GAMEURL . "email_change.php?hash=new&amp;key={$changeTokenNewAddress}",
        'EP_Text'           => $_Lang['Email_MailNew'],
        'EP_Text2'          => $_Lang['Email_WarnNew']
    ];

    $mailTitle = parsetemplate(
        $_Lang['Email_Title'],
        [
            'gameName' => $_GameConfig['game_name']
        ]
    );

    return [
        'oldAddress' => [
            'title' => $mailTitle,
            'content' => parsetemplate(
                $_Lang['Email_Body'],
                array_merge($mailContentCommonProps, $mailContentOldAddressProps)
            ),
        ],
        'newAddress' => [
            'title' => $mailTitle,
            'content' => parsetemplate(
                $_Lang['Email_Body'],
                array_merge($mailContentCommonProps, $mailContentNewAddressProps)
            ),
        ],
    ];
}

?>
