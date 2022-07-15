<?php

namespace UniEngine\Engine\Modules\Overview\Screens\Overview\Components\FeedbackMessagesDisplay;

use UniEngine\Engine\Modules\Overview\Screens\Overview\Components\FeedbackMessagesDisplay;

/**
 * @param array $props
 * @param arrayRef $props['input']
 */
function render($props) {
    $messages = FeedbackMessagesDisplay\Utils\getMessagesToDisplay($props);

    if (empty($messages)) {
        return [
            'componentHTML' => '',
        ];
    }

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);
    $tplBodyCache = [
        'messageRow' => $localTemplateLoader('messageRow'),
    ];

    $parsedMessages = array_map_withkeys($messages, function ($message) use (&$tplBodyCache) {
        return parsetemplate(
            $tplBodyCache['messageRow'],
            $message
        );
    });

    $componentHTML = implode('', $parsedMessages);

    return [
        'componentHTML' => $componentHTML,
    ];
}

?>
