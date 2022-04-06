<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Factories;

/**
 * @param array $props
 * @param ref $props['fleetOwner']
 * @param array $props['targetOwner']
 * @param array $props['ipsIntersectionsCheckResult']
 */
function createAlertFiltersSearchParams ($props) {
    $fleetOwner = &$props['fleetOwner'];
    $targetOwner = $props['targetOwner'];
    $targetOwnerId = $targetOwner['id'];
    $ipsIntersectionsCheckResult = (
        isset($props['ipsIntersectionsCheckResult']) ?
        $props['ipsIntersectionsCheckResult'] :
        [
            'Intersect' => [],
            'IPLogData' => [],
        ]
    );

    $intersectingIps = $ipsIntersectionsCheckResult['Intersect'];
    $ipsLogData = $ipsIntersectionsCheckResult['IPLogData'];

    $searchParams = [
        'place' => 1,
        'alertsender' => 1,
        'users' => [
            $fleetOwner['id'],
            $targetOwnerId,
        ],
        'sender' => $fleetOwner['id'],
        'target' => $targetOwnerId,
        'ips' => $intersectingIps,
        'logcount' => [],
    ];

    foreach ($intersectingIps as $intersectingIp) {
        $searchParams['logcount'][$intersectingIp] = [
            $fleetOwner['id'] => $ipsLogData[$fleetOwner['id']][$intersectingIp]['Count'],
            $targetOwnerId => $ipsLogData[$targetOwnerId][$intersectingIp]['Count'],
        ];
    }

    return $searchParams;
}

?>
