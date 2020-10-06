<?php

namespace UniEngine\Engine\Modules\Messages\Utils;

function _buildBattleSimulationDetails($simulationDataString) {
    global $_Lang;

    $SimTechs = [
        109,
        110,
        111,
        120,
        121,
        122,
        125,
        126,
        199,
    ];
    $SimTechsRep = [
        109 => 1,
        110 => 2,
        111 => 3,
        120 => 4,
        121 => 5,
        122 => 6,
        125 => 7,
        126 => 8,
        199 => 9,
    ];

    $battleSimulationData = [
        'tech' => [],
        'ships' => [],
    ];

    // TODO: For now, this uid generator is good enough (result doesn't have to be "truly unique"),
    // but consider using something more standard in the future.
    $simulationFormUid = uniqid();
    $simulationElements = explode(';', $simulationDataString);

    foreach ($simulationElements as $simulationElementPacked) {
        $simulationElement = explode(',', $simulationElementPacked);

        if (empty($simulationElement[0])) {
            continue;
        }

        $elementId = $simulationElement[0];
        $elementData = $simulationElement[1];

        if (in_array($elementId, $SimTechs)) {
            $battleSimulationData['tech'][$SimTechsRep[$elementId]] = $elementData;
        } else {
            $battleSimulationData['ships'][$elementId] = $elementData;
        }
    }
    $simulationForm = sprintf(
        $_Lang['msg_const']['sim']['form'],
        $simulationFormUid,
        json_encode($battleSimulationData)
    );
    $simulationCTAButton = sprintf(
        $_Lang['msg_const']['sim']['button'],
        'sim_' . $simulationFormUid
    );

    return [
        'simulationForm' => $simulationForm,
        'simulationCTAButton' => $simulationCTAButton,
    ];
}

?>
