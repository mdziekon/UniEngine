<?php

namespace UniEngine\Engine\Modules\Flights\Utils\Checks;

/**
 * @param array $params
 * @param object $params['coordinate']
 * @param int $params['coordinate']['galaxy']
 * @param int $params['coordinate']['system']
 * @param int $params['coordinate']['planet']
 * @param int? $params['coordinate']['type']
 * @param boolean? $params['areExpeditionsExcluded']
 */
function isValidCoordinate($params) {
    $originalCoordinate = $params['coordinate'];
    $areExpeditionsExcluded = (
        isset($params['areExpeditionsExcluded']) ?
            $params['areExpeditionsExcluded'] :
            false
    );

    $maxPlanetsInSystem = (
        !$areExpeditionsExcluded ?
            (MAX_PLANET_IN_SYSTEM + 1) :
            MAX_PLANET_IN_SYSTEM
    );
    $knownTargetTypes = [ 1, 2, 3 ];

    $inRangeCoordinates = [
        'galaxy' => keepInRange($originalCoordinate['galaxy'], 1, MAX_GALAXY_IN_WORLD),
        'system' => keepInRange($originalCoordinate['system'], 1, MAX_SYSTEM_IN_GALAXY),
        'planet' => keepInRange($originalCoordinate['planet'], 1, $maxPlanetsInSystem),
        'type' => (
            (
                isset($originalCoordinate['type']) &&
                in_array($originalCoordinate['type'], $knownTargetTypes)
            ) ?
                $originalCoordinate['type'] :
                -1
        ),
    ];

    foreach ($inRangeCoordinates as $paramName => $paramValue) {
        if (!isset($originalCoordinate[$paramName])) {
            continue;
        }
        if ($originalCoordinate[$paramName] == $paramValue) {
            continue;
        }

        return [
            'isValid' => false,
            'error' => [
                'code' => 'OUT_OF_BOUNDS',
                'param' => $paramName,
            ],
        ];
    }

    return [
        'isValid' => true,
    ];
}

?>
