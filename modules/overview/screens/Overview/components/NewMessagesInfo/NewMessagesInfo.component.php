<?php

namespace UniEngine\Engine\Modules\Overview\Screens\Overview\Components\NewMessagesInfo;

/**
 * @param array $props
 * @param number $props['userId']
 */
function render($props) {
    global $_Lang;

    $userId = $props['userId'];

    $getMessagesDataQuery = (
        "SELECT " .
        "COUNT(`id`) as `count` " .
        "FROM {{table}} " .
        "WHERE " .
        "`deleted` = false AND " .
        "`read` = false AND " .
        "`id_owner` = {$userId} " .
        ";"
    );
    $messagesData = doquery($getMessagesDataQuery, 'messages', true);

    if (
        !$messagesData ||
        $messagesData['count'] == 0
    ) {
        return [
            'componentHTML' => '',
        ];
    }

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    /**
     * TODO: This counting logic is strictly related to Polish rules for countable things.
     * In the future, use a better translation system to remove that and bring better support for other languages.
     */
    if ($messagesData['count'] == 1) {
        $msgBoxNew_suffix = $_Lang['MsgBox_New_1'];
        $msgBoxUnread_suffix = $_Lang['MsgBox_Unreaden_1'];
        $msgBoxCounter_variant = $_Lang['MsgBox_Msg'];
    } else if (
        $messagesData['count'] > 1 &&
        $messagesData['count'] < 5
    ) {
        $msgBoxNew_suffix = $_Lang['MsgBox_New_2_4'];
        $msgBoxUnread_suffix = $_Lang['MsgBox_Unreaden_2_4'];
        $msgBoxCounter_variant = $_Lang['MsgBox_Msgs'];
    } else {
        $msgBoxNew_suffix = $_Lang['MsgBox_New_5'];
        $msgBoxUnread_suffix = $_Lang['MsgBox_Unreaden_5'];
        $msgBoxCounter_variant = $_Lang['MsgBox_Msgs'];
    }

    $newMessagesCount = prettyNumber($messagesData['count']);

    $content = "{$_Lang['MsgBox_YouHave']} {$newMessagesCount} {$_Lang['MsgBox_New']}{$msgBoxNew_suffix}, {$_Lang['MsgBox_Unreaden']}{$msgBoxUnread_suffix} {$msgBoxCounter_variant}!";

    $tplBodyParams = [
        'content' => $content,
    ];

    $componentHTML = parsetemplate(
        $localTemplateLoader('body'),
        $tplBodyParams
    );

    return [
        'componentHTML' => $componentHTML,
    ];
}

?>
