<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Helpers;

function getAvailableExpeditionTimes() {
    $availableOptions = range(1, 12, 1);

    return $availableOptions;
}

?>
