<?php

use UniEngine\Engine\Modules\Development;

function StructuresBuildingPage(&$CurrentPlanet, $CurrentUser) {
    global $_Lang, $_GET;

    $pageView = Development\Screens\StructuresView\render([
        'pageType' => Development\Screens\StructuresView\StructuresViewType::Grid,
        'input' => $_GET,
        'planet' => &$CurrentPlanet,
        'user' => $CurrentUser,
        'timestamp' => time(),
    ]);

    display(
        $pageView['componentHTML'],
        $_Lang['Builds']
    );
}

?>
