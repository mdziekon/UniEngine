<?php

namespace UniEngine\Engine\Modules\Settings\Components\FleetMissionColorsForm;

/**
 * @param object $props
 * @param number $props['missionsColorSettings']
 *
 * @return object $result
 * @return string $result['componentHTML']
 */
function render($props) {
    global $_Lang, $_Vars_FleetMissions;

    $missionsColorSettings = (
        !empty($props['missionsColorSettings']) ?
            json_decode($props['missionsColorSettings'], true) :
            []
    );

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $tplBodyCache = [
        'missionRow' => $localTemplateLoader('missionRow'),
    ];

    $getMissionColor = function ($missionId, $flightType) use ($missionsColorSettings) {
        return (
            isset($missionsColorSettings[$flightType][$missionId]) ?
                $missionsColorSettings[$flightType][$missionId] :
                null
        );
    };

    $missionRows = array_map_withkeys(
        $_Vars_FleetMissions['all'],
        function ($missionId) use (&$tplBodyCache, &$_Lang, $getMissionColor) {
            $tplParams = [
                'MissionName'       => $_Lang['type_mission'][$missionId],
                'MissionID'         => $missionId,
                'Value_OwnFly'      => $getMissionColor($missionId, 'ownfly'),
                'Value_OwnComeback' => $getMissionColor($missionId, 'owncb'),
                'Value_NonOwn'      => $getMissionColor($missionId, 'nonown'),
            ];

            return parsetemplate($tplBodyCache['missionRow'], $tplParams);
        }
    );

    $componentHTML = implode('', $missionRows);

    return [
        'componentHTML' => $componentHTML
    ];
}

?>
