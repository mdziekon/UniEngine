<?php

namespace UniEngine\Engine\Includes\Helpers\Planets\Elements;

use UniEngine\Engine\Includes\Helpers\World;

function hasResearchLab($planet) {
    $elementID = 31;
    $user = [];

    $elementState = World\Elements\getElementState($elementID, $planet, $user);

    return ($elementState['level'] > 0);
}

?>
