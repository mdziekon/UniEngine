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
    global $_Lang;

    $userId = $props['userId'];
    $currentTimestamp = $props['currentTimestamp'];

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);
    $tplBodyCache = [
        'listElement' => $localTemplateLoader('listElement'),
        'listElementShipRow' => $localTemplateLoader('listElementShipRow'),
    ];

    $tplBodyCache['listElement'] = str_replace(
        [
            'fl_fleetinfo_ships',
            'fl_flytargettime',
            'fl_flygobacktime',
            'fl_flystaytime',
            'fl_flyretreattime',
        ],
        [
            $_Lang['fl_fleetinfo_ships'],
            $_Lang['fl_flytargettime'],
            $_Lang['fl_flygobacktime'],
            $_Lang['fl_flystaytime'],
            $_Lang['fl_flyretreattime'],
        ],
        $tplBodyCache['listElement']
    );

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

        $acsMainFleets[$acsMainFleetId] = [
            'acsId' => $acsId,
            'hasJoinedFleets' => !empty($relatedAcsUnion['fleets_id']),
        ];
    }

    foreach ($relatedAcsUnionsResult as $relatedAcsUnion) {
        // Own unions are being displayed later on, as "own fleets"
        if ($relatedAcsUnion['owner_id'] == $userId) {
            continue;
        }

        $acsId = $relatedAcsUnion['id'];
        $acsMainFleetId = $relatedAcsUnion['main_fleet_id'];

        $listElement = Utils\buildFriendlyAcsListElement([
            'elementNo' => $nextElementNo,
            'acsUnion' => $relatedAcsUnion,
            'acsMainFleets' => $acsMainFleets,
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
        $listElement = Utils\prerenderFriendlyAcsListElement($listElement, [ 'tplBodyCache' => &$tplBodyCache ]);

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
        $listElement = Utils\prerenderOwnListElement($listElement, [ 'tplBodyCache' => &$tplBodyCache ]);

        $listElements[] = $listElement;

        $nextElementNo += 1;
    }

    $elementsListHTML = implode(
        '',
        array_map_withkeys($listElements, function ($listElement) use (&$tplBodyCache) {
            return parsetemplate($tplBodyCache['listElement'], $listElement);
        })
    );
    $chronoAppletsHTML = implode(
        '',
        array_map_withkeys($listElements, function ($listElement) {
            return join('', $listElement['addons']['chronoApplets']);
        })
    );

    return [
        'componentHTML' => [
            'elementsList' => $elementsListHTML,
            'chronoApplets' => $chronoAppletsHTML,
        ],
    ];
}

?>
