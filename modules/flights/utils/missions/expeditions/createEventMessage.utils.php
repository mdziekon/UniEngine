<?php

namespace UniEngine\Engine\Modules\Flights\Utils\Missions\Expeditions;

use UniEngine\Engine\Modules\Flights\Utils\Helpers\ExpeditionEvent;

/**
 * @param array $params
 * @param ExpeditionEvent $params['eventType']
 * @param ExpeditionEvent $params['eventFinalOutcome']
 */
function createEventMessage($params) {
    $eventType = $params['eventType'];

    switch ($eventType) {
        case ExpeditionEvent::NothingHappened:
            return _createNothingHappenedEventMessage($params);
        case ExpeditionEvent::PlanetaryResourcesFound:
            return _createPlanetaryResourcesFoundEventMessage($params);
    }
}

function _createNothingHappenedEventMessage($params) {
    $rollValue = mt_rand(0, 2);

    $messageID = strval(110 + $rollValue);

    return [
        'msg_id' => $messageID,
        'args' => [],
    ];
}

function _createPlanetaryResourcesFoundEventMessage($params) {
    $gainedResources = $params['eventFinalOutcome']['gains']['planetaryResources'];

    return [
        'msg_id' => '120',
        'args' => [
            $gainedResources['metal'],
            $gainedResources['crystal'],
            $gainedResources['deuterium'],
        ],
    ];
}

?>
