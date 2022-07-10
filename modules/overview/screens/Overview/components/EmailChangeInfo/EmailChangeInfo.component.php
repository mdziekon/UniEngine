<?php

namespace UniEngine\Engine\Modules\Overview\Screens\Overview\Components\EmailChangeInfo;

/**
 * @param array $props
 * @param arrayRef $props['user']
 * @param number $props['currentTimestamp']
 */
function render($props) {
    global $_Lang;

    $user = &$props['user'];
    $currentTimestamp = $props['currentTimestamp'];

    if ($user['email'] == $user['email_2']) {
        return [
            'componentHTML' => '',
        ];
    }

    $getEmailChangeDataQuery = (
        "SELECT " .
        "* " .
        "FROM {{table}} " .
        "WHERE " .
        "`UserID` = {$user['id']} AND " .
        "`ConfirmType` = 0 " .
        "LIMIT 1 " .
        ";"
    );
    $emailChangeData = doquery($getEmailChangeDataQuery, 'mailchange', true);

    if (!$emailChangeData) {
        return [
            'componentHTML' => '',
        ];
    }

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $changeEndTimestamp = $emailChangeData['Date'] + (TIME_DAY * 7);
    $hasConfirmedNewAddress = ($emailChangeData['ConfirmHashNew'] == '');
    $hasChangeTimePassed = ($changeEndTimestamp < $currentTimestamp);

    $tplBodyParams = [
        'ChangeProcess_HideFormStyle' => (
            ($hasConfirmedNewAddress && $hasChangeTimePassed) ?
                '' :
                'display: none;'
        ),
        'ChangeProcess_Status' => (
            !$hasConfirmedNewAddress ?
                $_Lang['MailChange_Inf1'] :
                (
                    !$hasChangeTimePassed ?
                        sprintf(
                            $_Lang['MailChange_Inf2'],
                            date('d.m.Y H:i:s', $changeEndTimestamp)
                        ) :
                        ''
                )
        ),
    ];
    $tplBodyParams = array_merge($_Lang, $tplBodyParams);

    $componentHTML = parsetemplate(
        $localTemplateLoader('body'),
        $tplBodyParams
    );

    return [
        'componentHTML' => $componentHTML,
    ];
}

?>
