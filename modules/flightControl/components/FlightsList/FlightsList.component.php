<?php

namespace UniEngine\Engine\Modules\FlightControl\Components\FlightsList;

use UniEngine\Engine\Modules\FlightControl\Components\FlightsList\Utils;

//  Arguments
//      - $props (Object)
//          - userId (String)
//          - currentTimestamp (Number)
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    $userId = $props['userId'];
    $currentTimestamp = $props['currentTimestamp'];

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

    $acsMainFleets = [];
    $listElements = [];
    $nextElementNo = 1;

    foreach ($relatedAcsUnionsResult as $relatedAcsUnion) {
        $acsId = $relatedAcsUnion['id'];
        $acsMainFleetId = $relatedAcsUnion['main_fleet_id'];

        if ($relatedAcsUnion['owner_id'] == $userId) {
            $acsMainFleets[$acsMainFleetId] = [
                'acsId' => $acsId,
                'hasJoinedFleets' => !empty($relatedAcsUnion['fleets_id']),
            ];

            continue;
        }

        $listElement = Utils\buildFriendlyAcsListElement([
            'elementNo' => $nextElementNo,
            'acsUnion' => $relatedAcsUnion,
            'currentTimestamp' => $currentTimestamp,
            'acsUnionsExtraSquads' => $relatedAcsUnionsExtraSquads,
            'isJoiningThisUnion' => (
                // TODO: Remove direct $_GET & $_POST access
                (
                    isset($_GET['joinacs']) &&
                    $_GET['joinacs'] == $acsId
                ) ||
                (
                    isset($_POST['getacsdata']) &&
                    $_POST['getacsdata'] == $acsId
                )
            ),
        ]);

        $listElements[] = $listElement;

        $nextElementNo += 1;
    }


    $ownFleets = Utils\fetchUserFleets([ 'userId' => $userId ]);
    $ownFleetsResult = mapQueryResults($ownFleets, function ($fleetEntry) {
        return $fleetEntry;
    });

    foreach ($ownFleetsResult as $fleetEntry) {
        $fleetId = $fleetEntry['fleet_id'];
        $acsId = (
            !empty($acsMainFleets[$fleetId]) ?
            $acsMainFleets[$fleetId]['acsId'] :
            null
        );

        $listElement = Utils\buildOwnListElement([
            'elementNo' => $nextElementNo,
            'fleetEntry' => $fleetEntry,
            'acsMainFleets' => $acsMainFleets,
            'currentTimestamp' => $currentTimestamp,
            'acsUnionsExtraSquads' => $relatedAcsUnionsExtraSquads,
            'relatedAcsFleets' => $relatedAcsFleetBaseDetails,
            'isJoiningThisUnion' => (
                // TODO: Remove direct $_GET & $_POST access
                (
                    isset($_POST['getacsdata']) &&
                    $acsId !== null &&
                    $_POST['getacsdata'] == $acsId
                )
            ),
        ]);

        $listElements[] = $listElement;

        $nextElementNo += 1;
    }

    $componentHTML = parsetemplate($tplBodyCache['body'], $componentTPLData);

    return [
        'componentHTML' => $componentHTML
    ];
}

?>
