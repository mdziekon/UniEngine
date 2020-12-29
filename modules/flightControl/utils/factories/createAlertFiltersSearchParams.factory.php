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
            $targetOwner['owner'],
        ],
        'sender' => $fleetOwner['id'],
        'target' => $targetOwner['owner'],
        'ips' => $intersectingIps,
        'logcount' => [],
    ];

    foreach ($intersectingIps as $intersectingIp) {
        $searchParams['logcount'][$intersectingIp] = [
            $fleetOwner['id'] => $ipsLogData[$fleetOwner['id']][$intersectingIp]['Count'],
            $targetOwner['owner'] => $ipsLogData[$targetOwner['owner']][$intersectingIp]['Count'],
        ];
    }

    return $searchParams;
}

?>
