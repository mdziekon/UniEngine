<?php

namespace UniEngine\Engine\Modules\FlightControl\Components\SmartFleetBlockadeInfoBox;

use UniEngine\Engine\Modules\FlightControl\Components\SmartFleetBlockadeInfoBox;

//  Returns: Object
//      - componentHTML (String)
//
function render () {
    global $_EnginePath;

    $mostRecentBlockadeEntry = SmartFleetBlockadeInfoBox\Utils\fetchMostRecentBlockadeEntry();

    if (!$mostRecentBlockadeEntry) {
        return [
            'componentHTML' => '',
        ];
    }

    include_once("{$_EnginePath}/includes/functions/CreateSFBInfobox.php");

    $componentHTML = CreateSFBInfobox(
        $mostRecentBlockadeEntry,
        [
            'Width' => 750,
            'MarginBottom' => 10
        ]
    );

    return [
        'componentHTML' => $componentHTML
    ];
}

?>
