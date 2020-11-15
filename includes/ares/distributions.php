<?php

namespace UniEngine\Engine\Includes\Ares\Distributions;

function getShipFullKey ($shipId, $ownerId) {
    return "{$shipId}|{$ownerId}";
};

function getAlreadyDestroyedShipsCount ($shipFullKey, $alreadyDestroyedTable) {
    return (
        isset($alreadyDestroyedTable[$shipFullKey]) ?
            $alreadyDestroyedTable[$shipFullKey] :
            0
    );
};

function getStillExistingShipsCount ($shipId, $ownerId, $initialShipsTable, $alreadyDestroyedTable) {
    $shipFullKey = getShipFullKey($shipId, $ownerId);

    return (
        $initialShipsTable[$shipId][$ownerId] -
        getAlreadyDestroyedShipsCount($shipFullKey, $alreadyDestroyedTable)
    );
};

function distributeShots ($params) {
    $targetShipId = $params['targetShipId'];
    $targetShipsOwners = $params['targetShipsOwners'];
    $targetInitialShipsTable = $params['targetInitialShipsTable'];
    $targetAlreadyDestroyedShipsTable = $params['targetAlreadyDestroyedShipsTable'];
    $shotsCountOriginal = $params['shotsCount'];

    $targetShipsTotalCount = 0;
    $targetShipsByFullKey = [];
    $shotsDistribution = [];

    foreach ($targetShipsOwners[$targetShipId] as $ownerId => $_unused1) {
        $targetFullShipKey = getShipFullKey($targetShipId, $ownerId);

        $existingTargetShipsCount = getStillExistingShipsCount(
            $targetShipId,
            $ownerId,
            $targetInitialShipsTable,
            $targetAlreadyDestroyedShipsTable
        );

        $targetShipsByFullKey[$targetFullShipKey] = $existingTargetShipsCount;
        $targetShipsTotalCount += $existingTargetShipsCount;
    }

    $shotsCountLeft = $shotsCountOriginal;

    foreach ($targetShipsByFullKey as $targetFullShipKey => $targetShipsCount) {
        $distributedShotsCount = floor(
            ($targetShipsCount / $targetShipsTotalCount) *
            $shotsCountOriginal
        );

        $shotsCountLeft -= $distributedShotsCount;
        $shotsDistribution[$targetFullShipKey] = $distributedShotsCount;
    }

    if ($shotsCountLeft > 0) {
        arsort($shotsDistribution);

        foreach ($shotsDistribution as $targetFullShipKey => &$shotsCount) {
            $shotsCount += $shotsCountLeft;

            // We want to assign this to only the top-most group
            break;
        }
    }

    return $shotsDistribution;
};

?>
