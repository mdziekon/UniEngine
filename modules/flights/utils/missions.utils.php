<?php

namespace UniEngine\Engine\Modules\Flights\Utils\Missions;

use UniEngine\Engine\Includes\Helpers\Common\Collections;
use UniEngine\Engine\Includes\Helpers\World\Resources;

function calculateMaxPlanetPillage ($props) {
    $planet = $props['planet'];
    $maxPillagePercentage = $props['maxPillagePercentage'];

    $pillagableResourceKeys = Resources\getKnownPillagableResourceKeys();

    $maxPillagePerResource = [];

    foreach ($pillagableResourceKeys as $resourceKey) {
        $maxPillagePerResource[$resourceKey] = (
            $planet[$resourceKey] *
            $maxPillagePercentage
        );
    }

    return $maxPillagePerResource;
}

/**
 * Calculates resources pillage from a specified planet, trying to evenly fill
 * the entire available ships' storage capacity.
 *
 * @param object $props
 * @param object $props['maxPillagePerResource']
 * @param number $props['fleetTotalStorage']
 */
function calculateEvenResourcesPillage ($props) {
    $maxPillagePerResource = $props['maxPillagePerResource'];
    $fleetTotalStorage = $props['fleetTotalStorage'];

    $pillagableResourceKeys = Resources\getKnownPillagableResourceKeys();
    $pillagableResourceKeysCount = count($pillagableResourceKeys);

    $initialStoragePerOneResource = (
        $fleetTotalStorage /
        count($pillagableResourceKeys)
    );

    $pillagedResources = [];
    $storagePerResource = [];

    foreach ($pillagableResourceKeys as $resourceKey) {
        $pillagedResources[$resourceKey] = 0;
        $storagePerResource[$resourceKey] = $initialStoragePerOneResource;
    }

    $unusedStorage = $fleetTotalStorage;
    $stillPillagableResourceKeys = $pillagableResourceKeys;

    // Iterate as many times as needed to fill out the entire storage.
    // By definition, on each iteration as least one resource will no longer be
    // pillagable, so a max of $pillagableResourceKeysCount iterations are needed.
    for ($iter = 0; $iter < $pillagableResourceKeysCount; $iter++) {
        $resourceKeys = $stillPillagableResourceKeys;

        foreach ($resourceKeys as $resourceKey) {
            $pillagableAmount = (
                $maxPillagePerResource[$resourceKey] -
                $pillagedResources[$resourceKey]
            );

            if ($pillagableAmount < 0) {
                // We can no longer pillage this resource from the planet,
                // pillaging limit has been reached
                $stillPillagableResourceKeys = Collections\without(
                    $stillPillagableResourceKeys,
                    $resourceKey
                );

                continue;
            }

            $loadableAmount = (
                $storagePerResource[$resourceKey] -
                $pillagedResources[$resourceKey]
            );

            if ($loadableAmount > $pillagableAmount) {
                $loadableAmount = $pillagableAmount;
            }

            if ($loadableAmount === $pillagableAmount) {
                // In this iteration, we'll load the maximal pillagable amount
                // of this resource.
                $stillPillagableResourceKeys = Collections\without(
                    $stillPillagableResourceKeys,
                    $resourceKey
                );
            }

            $pillagedResources[$resourceKey] += $loadableAmount;
            $unusedStorage -= $loadableAmount;
        }

        // Redistribute unused storage into still pillagable resources
        $stillPillagableResourceKeysCount = count($stillPillagableResourceKeys);

        foreach ($stillPillagableResourceKeys as $resourceKey) {
            $storagePerResource[$resourceKey] += (
                $unusedStorage /
                $stillPillagableResourceKeysCount
            );
        }
    }

    return array_map(
        function ($value) { return floor($value); },
        $pillagedResources
    );
}

/**
 * Calculates resources pillage from a specified planet, trying to fill the entire
 * available ships' storage capacity by prioritizing most valuable resources first
 * (assuming that getKnownPillagableResourceKeys() returns them in order
 * from least to most valuable resource).
 *
 * @param object $props
 * @param number $props['maxPillagePerResource']
 * @param number $props['fleetTotalStorage']
 */
function calculateValuePrioritizedResourcesPillage ($props) {
    $maxPillagePerResource = $props['maxPillagePerResource'];
    $fleetTotalStorage = $props['fleetTotalStorage'];

    $pillagableResourceKeys = Resources\getKnownPillagableResourceKeys();
    $pillagableResourceKeysCount = count($pillagableResourceKeys);

    $pillagedResources = [];

    foreach ($pillagableResourceKeys as $resourceKey) {
        $pillagedResources[$resourceKey] = 0;
    }

    $unusedStorage = $fleetTotalStorage;
    $stillPillagableResourceKeys = $pillagableResourceKeys;

    // Iterate as many times as needed to fill out the entire storage.
    // By definition, on each iteration as least one resource will no longer be
    // pillagable, so a max of $pillagableResourceKeysCount iterations are needed.
    for ($iter = 0; $iter < $pillagableResourceKeysCount; $iter++) {
        $resourceKeys = $stillPillagableResourceKeys;
        $spreadResourceKeys = count($resourceKeys);

        foreach ($resourceKeys as $resourceKey) {
            $pillagableAmount = (
                $maxPillagePerResource[$resourceKey] -
                $pillagedResources[$resourceKey]
            );

            $loadableAmount = (
                $unusedStorage /
                $spreadResourceKeys
            );

            if ($loadableAmount > $pillagableAmount) {
                $loadableAmount = $pillagableAmount;
            }

            $pillagedResources[$resourceKey] += $loadableAmount;
            $unusedStorage -= $loadableAmount;

            $spreadResourceKeys -= 1;
        }

        $stillPillagableResourceKeys = Collections\without(
            $stillPillagableResourceKeys,
            end($resourceKeys)
        );
    }

    return array_map(
        function ($value) { return floor($value); },
        $pillagedResources
    );
}

?>
