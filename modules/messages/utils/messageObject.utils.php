<?php

namespace UniEngine\Engine\Modules\Messages\Utils;

function isSystemSentMessage($messageDetails) {
    return ($messageDetails['id_sender'] == 0);
}

function isUserSentMessage($messageDetails) {
    return !isSystemSentMessage($messageDetails);
}

?>
