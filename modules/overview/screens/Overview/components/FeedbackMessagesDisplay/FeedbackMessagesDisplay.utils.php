<?php

namespace UniEngine\Engine\Modules\Overview\Screens\Overview\Components\FeedbackMessagesDisplay\Utils;

/**
 * @param array $props
 * @param arrayRef $props['input']
 */
function getMessagesToDisplay($props) {
    global $_Lang;

    $input = &$props['input'];

    $messages = [];

    if (
        !empty($input['showmsg']) &&
        $input['showmsg'] == 'abandon'
    ) {
        $messages[] = [
            'messageContent' => $_Lang['Abandon_ColonyAbandoned'],
            'colorClass' => 'lime',
        ];
    }

    return $messages;
}

?>
