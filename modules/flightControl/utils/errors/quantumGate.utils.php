<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Errors;

/**
 * @param object $error As returned by FlightControl\Utils\Validators\validateQuantumGate
 */
function mapQuantumGateValidationErrorToReadableMessage($error) {
    global $_Lang;

    $errorCode = $error['code'];
    $errorParams = $error['params'];

    $knownErrorsByCode = [
        'PLANET_NO_QUANTUM_GATE' => $_Lang['fl3_NoQuantumGate'],
        'FLEET_MISSION_DISALLOWED' => $_Lang['fl3_QuantumDisallowAttack'],
        'TARGET_PLANET_NOT_SAME_GALAXY' => $_Lang['fl3_SpaceTimeJumpGalaxy'],
        'ORIGIN_PLANET_QUANTUMGATE_NOT_READY' => function ($params) use (&$_Lang) {
            return sprintf(
                $_Lang['CannotUseQuantumGateTill'],
                prettyDate(
                    // TODO: Polish translation embedded in date format
                    'd m Y \o H:i:s',
                    $params['nextUseAt'],
                    1
                )
            );
        },
    ];

    if (is_callable($knownErrorsByCode[$errorCode])) {
        return $knownErrorsByCode[$errorCode]($errorParams);
    }

    return $knownErrorsByCode[$errorCode];
}

?>
