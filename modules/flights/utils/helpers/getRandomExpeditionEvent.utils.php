<?php

namespace UniEngine\Engine\Modules\Flights\Utils\Helpers;

abstract class ExpeditionEvent {
    const NothingHappened = 0;
    const PlanetaryResourcesFound = 1;
}

/**
 * @param array $params
 */
function getRandomExpeditionEvent($params) {
    // TODO: Add more events
    $rollValue = mt_rand(0, 99);

    if ($rollValue >= 0 && $rollValue < 50) {
        return ExpeditionEvent::NothingHappened;
    }
    if ($rollValue >= 50 && $rollValue < 100) {
        return ExpeditionEvent::PlanetaryResourcesFound;
    }
}

/**
 * @param array $params
 * @param ExpeditionEvent $params['event']
 */
function getExpeditionEventOutcome($params) {
    $event = $params['event'];

    if ($event == ExpeditionEvent::PlanetaryResourcesFound) {
        return getExpeditionEventPlanetaryResourcesFoundOutcome($params);
    }

    return [];
}

/**
 * @param array $params
 */
function getExpeditionEventPlanetaryResourcesFoundOutcome($params) {
    // TODO: Create real resources randomization
    return [
        'gains' => [
            'planetaryResources' => [
                'metal' => 10000,
                'crystal' => 10000,
                'deuterium' => 10000
            ]
        ]
    ];
}

?>
