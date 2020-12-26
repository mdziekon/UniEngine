<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Helpers;

use UniEngine\Engine\Includes\Helpers\Users;

/**
 * @param array $props
 * @param ref $props['user']
 */
function getUserExpeditionSlotsCount ($props) {
    $user = &$props['user'];

    $expeditionTechLevel = Users\getUsersTechLevel(124, $user);

    return (
        1 +
        floor($expeditionTechLevel / 3)
    );
}

?>
