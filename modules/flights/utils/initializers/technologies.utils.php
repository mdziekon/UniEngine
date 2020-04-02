<?php

namespace UniEngine\Engine\Modules\Flights\Utils\Initializers;

use UniEngine\Engine\Includes\Helpers\World\Elements;

/**
 * @param array $params
 * @param array $params['user']
 */
function initCombatTechnologiesMap($params) {
    global $_Vars_ElementCategories;

    $user = $params['user'];
    $planet = [];

    $technologiesMap = [];

    foreach ($_Vars_ElementCategories['techPurpose']['combat'] as $elementID) {
        $technologiesMap[$elementID] = Elements\getElementState($elementID, $planet, $user)['level'];
    }

    return $technologiesMap;
}

?>
