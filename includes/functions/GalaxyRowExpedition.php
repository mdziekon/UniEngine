<?php

function GalaxyRowExpedition($Galaxy, $System) {
    global $_Lang;
    static $TPL = false;

    if ($TPL === false) {
        $TPL = gettemplate('galaxy_row_expedition');
    }

    $Parse = [
        'data_galaxy' => $Galaxy,
        'data_system' => $System,
        'data_planetpos' => (MAX_PLANET_IN_SYSTEM + 1),
        'data_missionid' => 15,
        'lang_label' => $_Lang['Footer_Expedition'],
    ];

    return parsetemplate($TPL, $Parse);
}

?>
