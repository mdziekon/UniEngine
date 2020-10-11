<?php

namespace UniEngine\Engine\Modules\Messages\Utils;

function formatUserMessageContent($dbMessageData) {
    global $_EnginePath, $_GameConfig;

    include_once($_EnginePath . 'includes/functions/BBcodeFunction.php');

    $messageParsedContent = $dbMessageData['text'];

    if ($_GameConfig['enable_bbcode'] == 1) {
        $messageParsedContent = bbcode(image($messageParsedContent));
    }

    $messageParsedContent = nl2br($messageParsedContent);

    return $messageParsedContent;
}

?>
