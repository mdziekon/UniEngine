<?php

namespace UniEngine\Engine\Includes\Helpers\World\Resources;

use UniEngine\Engine\Common\Exceptions;

function getKnownPlanetaryResourceKeys() {
    return [
        'metal',
        'crystal',
        'deuterium',
        'energy_max'
    ];
}

function getKnownUserResourceKeys() {
    return [
        'darkEnergy'
    ];
}

function isResource($resourceKey) {
    return (
        isPlanetaryResource($resourceKey) ||
        isUserResource($resourceKey)
    );
}

function isPlanetaryResource($resourceKey) {
    $knownResources = getKnownPlanetaryResourceKeys();

    return in_array($resourceKey, $knownResources);
}

function isUserResource($resourceKey) {
    $knownResources = getKnownUserResourceKeys();

    return in_array($resourceKey, $knownResources);
}

function getResourceState($resourceKey, &$user, &$planet) {
    if (isPlanetaryResource($resourceKey)) {
        return $planet[$resourceKey];
    }

    if (isUserResource($resourceKey)) {
        return $user[$resourceKey];
    }

    throw new Exceptions\UniEngineException("Invalid resource type");
}

?>
