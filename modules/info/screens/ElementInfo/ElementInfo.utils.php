<?php

namespace UniEngine\Engine\Modules\Info\Screens\ElementInfo\Utils;

use UniEngine\Engine\Includes\Helpers\World;

function getElementtypeLabel ($elementId) {
    global $_Lang;

    if (World\Elements\isStructure($elementId)) {
        return $_Lang['tech'][0];
    }
    if (World\Elements\isTechnology($elementId)) {
        return $_Lang['tech'][100];
    }
    if (World\Elements\isShip($elementId)) {
        return $_Lang['tech'][200];
    }
    if (
        World\Elements\isDefenseSystem($elementId) ||
        World\Elements\isMissile($elementId)
    ) {
        return $_Lang['tech'][400];
    }

    return "";
};

?>
