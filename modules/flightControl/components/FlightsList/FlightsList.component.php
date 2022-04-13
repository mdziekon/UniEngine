<?php

namespace UniEngine\Engine\Modules\FlightControl\Components\FlightsList;

use UniEngine\Engine\Modules\FlightControl\Components\FlightsList\Utils;

//  Arguments
//      - $props (Object)
//          - userId (String)
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    $userId = $props['userId'];

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);
    $tplBodyCache = [
        'body' => $localTemplateLoader('body'),
    ];

    $componentTPLData = [
        'someVar' => null,
    ];

    $ownFleets = Utils\fetchUserFleets([ 'userId' => $userId ]);
    $ownFleetsResult = mapQueryResults($ownFleets, function ($fleetEntry) {
        return [];
    });

    $componentHTML = parsetemplate($tplBodyCache['body'], $componentTPLData);

    return [
        'componentHTML' => $componentHTML
    ];
}

?>
