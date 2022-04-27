<?php

namespace UniEngine\Engine\Modules\FlightControl\Screens\Shortcuts;

use UniEngine\Engine\Modules\FlightControl\Screens\Shortcuts;

/**
 * @param Object $props
 * @param String $props['userId']
 *
 * @return Object $result
 * @return String $result['componentHTML']
 */
function render($props) {
    $screenVariant = Shortcuts\Components\ListManagement\render($props);

    return [
        'componentHTML' => $screenVariant['componentHTML'],
    ];
}

?>
