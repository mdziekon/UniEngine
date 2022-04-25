<?php

namespace UniEngine\Engine\Modules\FlightControl\Components\AvailableMissionsList;

//  Arguments
//      - $props (Object)
//          - availableMissions (Array<>)
//          - selectedMission (MissionType)
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_Lang;

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);
    $tplBodyCache = [
        'missionSelector' => $localTemplateLoader('missionSelector'),
    ];

    $availableMissions = $props['availableMissions'];
    $selectedMission = $props['selectedMission'];

    $missionSelectorsHTML = array_map_withkeys(
        $availableMissions,
        function ($missionType) use ($selectedMission, &$tplBodyCache, &$_Lang) {
            $missionSelectorTplData = [
                'MID' => $missionType,
                'ThisMissionName' => $_Lang['type_mission'][$missionType],
                'CheckThisMission' => (
                    $missionType == $selectedMission ?
                        ' checked' :
                        ''
                ),
            ];

            return parsetemplate($tplBodyCache['missionSelector'], $missionSelectorTplData);
        }
    );


    return [
        'componentHTML' => implode('', $missionSelectorsHTML),
    ];
}

?>
