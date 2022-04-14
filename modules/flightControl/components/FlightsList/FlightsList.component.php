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

    $relatedAcsUnions = Utils\fetchRelatedAcsUnions([ 'userId' => $userId ]);
    $relatedAcsUnionsResult = mapQueryResults($relatedAcsUnions, function ($result) {
        return $result;
    });
    $relatedAcsFleetBaseDetails = Utils\extractRelatedFleetsFromAcsUnions($relatedAcsUnionsResult);
    $relatedAcsFleetIds = array_map_withkeys($relatedAcsFleetBaseDetails, function ($fleetBaseDetails) {
        return $fleetBaseDetails['fleetId'];
    });
    $relatedAcsFleets = (
        !empty($relatedAcsFleetIds) ?
        mapQueryResults(
            Utils\fetchRelatedAcsFleetsSquadDetails([ 'fleetIds' => $relatedAcsFleetIds ]),
            function ($result) {
                return $result;
            }
        ) :
        []
    );
    $relatedAcsUnionsExtraSquads = Utils\extractAcsUnionsExtraSquads([
        'relatedAcsFleets' => $relatedAcsFleets,
        'fleetsBaseDetails' => $relatedAcsFleetBaseDetails,
    ]);


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
