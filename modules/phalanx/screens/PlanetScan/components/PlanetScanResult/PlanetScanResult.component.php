<?php

namespace UniEngine\Engine\Modules\Phalanx\Screens\PlanetScan\Components\PlanetScanResult;

use UniEngine\Engine\Modules\Flights;

/**
 * @param Object $props
 * @param array $props['targetDetails']
 * @param arrayRef $props['phalanxMoon']
 * @param number $props['viewingUserId']
 * @param number $props['currentTimestamp']
 * @param number $props['scanCost']
 * @param array $props['foundFlights']
 *
 * @return Object $result
 * @return String $result['componentHTML']
 */
function render($props) {
    global $_Lang, $_SkinPath;

    $targetDetails = $props['targetDetails'];
    $phalanxMoon = &$props['phalanxMoon'];
    $viewingUserId = $props['viewingUserId'];
    $currentTimestamp = $props['currentTimestamp'];
    $scanCost = $props['scanCost'];
    $foundFlights = $props['foundFlights'];

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);
    $tplBodyCache = [
        'body' => $localTemplateLoader('body'),
    ];

    $flightsListComponentHTML = Flights\Components\FlightsList\render([
        'viewMode'          => Flights\Components\FlightsList\Utils\ViewMode::Phalanx,
        'flights'           => $foundFlights,
        'viewingUserId'     => $viewingUserId,
        'targetOwnerId'     => $targetDetails['id_owner'],
        'currentTimestamp'  => $currentTimestamp,
    ])['componentHTML'];

    $isTargetAbandoned = ($targetDetails['id_owner'] <= 0);
    $hasFuelForNextScan = ($phalanxMoon['deuterium'] >= $scanCost);

    $componentTplData = [
        'skinpath'                  => $_SkinPath,
        'Insert_Coord_Galaxy'       => $targetDetails['galaxy'],
        'Insert_Coord_System'       => $targetDetails['system'],
        'Insert_Coord_Planet'       => $targetDetails['planet'],
        'Insert_My_Galaxy'          => $phalanxMoon['galaxy'],
        'Insert_My_System'          => $phalanxMoon['system'],
        'Insert_My_Planet'          => $phalanxMoon['planet'],
        'Insert_MyMoonName'         => $phalanxMoon['name'],
        'Insert_DeuteriumAmount'    => prettyNumber($phalanxMoon['deuterium']),
        'Insert_DeuteriumColor'     => (
            $hasFuelForNextScan ?
                'lime' :
                'red'
        ),
        'Insert_OwnerName'          => (
            $isTargetAbandoned ?
                null :
                "({$targetDetails['username']})"
        ),
        'Insert_TargetNamePrefix'   => (
            $isTargetAbandoned ?
                null :
                $_Lang['Table_Title2']
        ),
        'Insert_TargetName'         => (
            $isTargetAbandoned ?
                "<b class=\"red\">{$_Lang['Abandoned_planet']}</b>" :
                $targetDetails['name']
        ),

        'Insert_FlightsList'        => (
            empty($flightsListComponentHTML) ?
                $_Lang['PhalanxInfo_NoMovements'] :
                $flightsListComponentHTML
        ),
    ];

    $componentHTML = parsetemplate(
        $tplBodyCache['body'],
        array_merge($_Lang, $componentTplData)
    );

    return [
        'componentHTML' => $componentHTML,
    ];
}

?>
