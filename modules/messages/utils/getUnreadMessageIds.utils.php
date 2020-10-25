<?php

namespace UniEngine\Engine\Modules\Messages\Utils;

use UniEngine\Engine\Includes\Helpers\Common\Collections;

function getUnreadMessageIds($messages) {
    $messageIds = array_map(
        function ($messageDetails) {
            if ($messageDetails['read']) {
                return null;
            }

            return $messageDetails['id'];
        },
        $messages
    );
    $messageIds = Collections\compact($messageIds);

    return $messageIds;
}

?>
