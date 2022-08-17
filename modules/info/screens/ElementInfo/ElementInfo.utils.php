<?php

namespace UniEngine\Engine\Modules\Info\Screens\ElementInfo\Utils;

use UniEngine\Engine\Includes\Helpers\World;

function getElementDescription($elementId) {
    global $_Lang;

    if (!empty($_Lang['WorldElements_Detailed'][$elementId]['description_alt'])) {
        return $_Lang['WorldElements_Detailed'][$elementId]['description_alt'];
    }

    $baseDescription = $_Lang['WorldElements_Detailed'][$elementId]['description_short'];
    $extraDescription = (
        !empty($_Lang['WorldElements_Detailed'][$elementId]['description_extra']) ?
            $_Lang['WorldElements_Detailed'][$elementId]['description_extra'] :
            null
    );

    if (!$extraDescription) {
        return $baseDescription;
    }

    return "{$baseDescription}<br/><br/>{$extraDescription}";
}

function getElementtypeLabel($elementId) {
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
