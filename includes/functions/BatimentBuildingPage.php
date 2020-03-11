<?php

use UniEngine\Engine\Modules\Development;

function BatimentBuildingPage(&$CurrentPlanet, $CurrentUser) {
    global $_Lang, $_GET;

    $pageView = Development\Screens\StructuresView\render([
        'pageType' => Development\Screens\StructuresView\StructuresViewType::List,
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
