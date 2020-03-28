<?php

use PHPUnit\Framework\TestCase;

$_EnginePath = './';

require_once $_EnginePath . 'common/_includes.php';
require_once $_EnginePath . 'includes/helpers/_includes.php';
require_once $_EnginePath . 'modules/flights/_includes.php';

use UniEngine\Engine\Modules\Flights\Utils\Missions;


/**
 * @group UniEngineTest
 */
class CalculateEvenResourcesPillageTestCase extends TestCase {
    /**
     * @test
     * @testWith    [ 0.5, 500000 ]
     *              [ 0.7, 700000 ]
     */
    public function itShouldPillageLimitedAmountOfResources($maxPillagePercentage, $maxPillagePerResource) {
        $params = [
            'maxPillagePerResource' => Missions\calculateMaxPlanetPillage([
                'planet' => [
                    'metal' => 1000000,
                    'crystal' => 1000000,
                    'deuterium' => 1000000,
                ],
                'maxPillagePercentage' => $maxPillagePercentage,
            ]),
            'fleetTotalStorage' => 100000000,
        ];

        $result = Missions\calculateEvenResourcesPillage($params);

        $this->assertEquals(
            [
                'metal' => $maxPillagePerResource,
                'crystal' => $maxPillagePerResource,
                'deuterium' => $maxPillagePerResource,
            ],
            $result
        );
    }

    /**
     * @test
     */
    public function itShouldEvenlyDistributePillageResourcesWhenStorageIsLimited() {
        $params = [
            'maxPillagePerResource' => Missions\calculateMaxPlanetPillage([
                'planet' => [
                    'metal' => 1000000,
                    'crystal' => 1000000,
                    'deuterium' => 1000000,
                ],
                'maxPillagePercentage' => 0.5,
            ]),
            'fleetTotalStorage' => 1000000,
        ];

        $result = Missions\calculateEvenResourcesPillage($params);

        $this->assertEquals(
            [
                'metal' => 333333,
                'crystal' => 333333,
                'deuterium' => 333333,
            ],
            $result
        );
    }

    /**
     * @test
     * @testWith    ["metal"]
     *              ["crystal"]
     *              ["deuterium"]
     */
    public function itShouldUseEntireStorageWhenOnlyOneResourceCanBePillaged($resourceKey) {
        $planet = [
            'metal' => 0,
            'crystal' => 0,
            'deuterium' => 0,
        ];
        $planet[$resourceKey] = 1000000;

        $params = [
            'maxPillagePerResource' => Missions\calculateMaxPlanetPillage([
                'planet' => $planet,
                'maxPillagePercentage' => 0.5,
            ]),
            'fleetTotalStorage' => 500000,
        ];

        $result = Missions\calculateEvenResourcesPillage($params);

        $expectedPillage = [
            'metal' => 0,
            'crystal' => 0,
            'deuterium' => 0,
        ];
        $expectedPillage[$resourceKey] = 500000;

        $this->assertEquals(
            $expectedPillage,
            $result
        );
    }

    /**
     * @test
     * @testWith    ["metal"]
     *              ["crystal"]
     *              ["deuterium"]
     */
    public function itShouldEvenlyUseAvailableStorageWhenOneOfResourcesCannotBePillaged($resourceKey) {
        $planet = [
            'metal' => 1000000,
            'crystal' => 1000000,
            'deuterium' => 1000000,
        ];
        $planet[$resourceKey] = 0;

        $params = [
            'maxPillagePerResource' => Missions\calculateMaxPlanetPillage([
                'planet' => $planet,
                'maxPillagePercentage' => 0.5,
            ]),
            'fleetTotalStorage' => 1000000,
        ];

        $result = Missions\calculateEvenResourcesPillage($params);

        $expectedPillage = [
            'metal' => 500000,
            'crystal' => 500000,
            'deuterium' => 500000,
        ];
        $expectedPillage[$resourceKey] = 0;

        $this->assertEquals(
            $expectedPillage,
            $result
        );
    }

    /**
     * @test
     */
    public function itShouldProperlyPillageMixedResourceAmountsWhenTheresEnoughStorage() {
        $planet = [
            'metal' => 1500000,
            'crystal' => 2000000,
            'deuterium' => 700000,
        ];

        $params = [
            'maxPillagePerResource' => Missions\calculateMaxPlanetPillage([
                'planet' => $planet,
                'maxPillagePercentage' => 0.5,
            ]),
            'fleetTotalStorage' => 10000000,
        ];

        $result = Missions\calculateEvenResourcesPillage($params);

        $expectedPillage = [
            'metal' => 750000,
            'crystal' => 1000000,
            'deuterium' => 350000,
        ];

        $this->assertEquals(
            $expectedPillage,
            $result
        );
    }

    /**
     * @test
     * @testWith    [ [ "metal", "crystal", "deuterium" ], [ 999999, 700000, 50000 ] ]
     *              [ [ "metal", "deuterium", "crystal" ], [ 999999, 700000, 50000 ] ]
     *              [ [ "crystal", "metal", "deuterium" ], [ 999999, 700000, 50000 ] ]
     *              [ [ "crystal", "deuterium", "metal" ], [ 999999, 700000, 50000 ] ]
     *              [ [ "deuterium", "metal", "crystal" ], [ 999999, 700000, 50000 ] ]
     *              [ [ "deuterium", "crystal", "metal" ], [ 999999, 700000, 50000 ] ]
     */
    public function itShouldProperlyPillageMixedResourceAmountsWhenTheresExactlyEnoughStorage(
        $orderedResourceKeys,
        $expectedPillagePerKey
    ) {
        $planet = [
            'metal' => 0,
            'crystal' => 0,
            'deuterium' => 0,
        ];
        $planet[$orderedResourceKeys[0]] = 2000000;
        $planet[$orderedResourceKeys[1]] = 1400000;
        $planet[$orderedResourceKeys[2]] = 100000;

        $params = [
            'maxPillagePerResource' => Missions\calculateMaxPlanetPillage([
                'planet' => $planet,
                'maxPillagePercentage' => 0.5,
            ]),
            'fleetTotalStorage' => 1750000,
        ];

        $result = Missions\calculateEvenResourcesPillage($params);

        $expectedPillage = [
            'metal' => 0,
            'crystal' => 0,
            'deuterium' => 0,
        ];
        $expectedPillage[$orderedResourceKeys[0]] = $expectedPillagePerKey[0];
        $expectedPillage[$orderedResourceKeys[1]] = $expectedPillagePerKey[1];
        $expectedPillage[$orderedResourceKeys[2]] = $expectedPillagePerKey[2];

        $this->assertEquals(
            $expectedPillage,
            $result
        );
    }

    /**
     * @test
     * @testWith    [ [ "metal", "crystal", "deuterium" ] ]
     *              [ [ "metal", "deuterium", "crystal" ] ]
     *              [ [ "crystal", "metal", "deuterium" ] ]
     *              [ [ "crystal", "deuterium", "metal" ] ]
     *              [ [ "deuterium", "metal", "crystal" ] ]
     *              [ [ "deuterium", "crystal", "metal" ] ]
     */
    public function itShouldProperlyPillageMixedResourceAmountsWhenTheresNotEnoughStorage($orderedResourceKeys) {
        $planet = [
            'metal' => 0,
            'crystal' => 0,
            'deuterium' => 0,
        ];
        $planet[$orderedResourceKeys[0]] = 2000000;
        $planet[$orderedResourceKeys[1]] = 1500000;
        $planet[$orderedResourceKeys[2]] = 70000;

        $params = [
            'maxPillagePerResource' => Missions\calculateMaxPlanetPillage([
                'planet' => $planet,
                'maxPillagePercentage' => 0.5,
            ]),
            'fleetTotalStorage' => 1000000,
        ];

        $result = Missions\calculateEvenResourcesPillage($params);

        $expectedPillage = [
            'metal' => 0,
            'crystal' => 0,
            'deuterium' => 0,
        ];
        $expectedPillage[$orderedResourceKeys[0]] = 482500;
        $expectedPillage[$orderedResourceKeys[1]] = 482500;
        $expectedPillage[$orderedResourceKeys[2]] = 35000;

        $this->assertEquals(
            $expectedPillage,
            $result
        );
    }
}



/**
 * @group UniEngineTest
 */
class CalculateValuePrioritizedResourcesPillageTestCase extends TestCase {
    /**
     * @test
     * @testWith    [ 0.5, 500000 ]
     *              [ 0.7, 700000 ]
     */
    public function itShouldPillageLimitedAmountOfResources($maxPillagePercentage, $maxPillagePerResource) {
        $params = [
            'maxPillagePerResource' => Missions\calculateMaxPlanetPillage([
                'planet' => [
                    'metal' => 1000000,
                    'crystal' => 1000000,
                    'deuterium' => 1000000,
                ],
                'maxPillagePercentage' => $maxPillagePercentage,
            ]),
            'fleetTotalStorage' => 100000000,
        ];

        $result = Missions\calculateValuePrioritizedResourcesPillage($params);

        $this->assertEquals(
            [
                'metal' => $maxPillagePerResource,
                'crystal' => $maxPillagePerResource,
                'deuterium' => $maxPillagePerResource,
            ],
            $result
        );
    }

    /**
     * @test
     */
    public function itShouldEvenlyDistributePillageResourcesWhenStorageIsLimited() {
        $params = [
            'maxPillagePerResource' => Missions\calculateMaxPlanetPillage([
                'planet' => [
                    'metal' => 1000000,
                    'crystal' => 1000000,
                    'deuterium' => 1000000,
                ],
                'maxPillagePercentage' => 0.5,
            ]),
            'fleetTotalStorage' => 1000000,
        ];

        $result = Missions\calculateValuePrioritizedResourcesPillage($params);

        $this->assertEquals(
            [
                'metal' => 333333,
                'crystal' => 333333,
                'deuterium' => 333333,
            ],
            $result
        );
    }

    /**
     * @test
     * @testWith    ["metal"]
     *              ["crystal"]
     *              ["deuterium"]
     */
    public function itShouldUseEntireStorageWhenOnlyOneResourceCanBePillaged($resourceKey) {
        $planet = [
            'metal' => 0,
            'crystal' => 0,
            'deuterium' => 0,
        ];
        $planet[$resourceKey] = 1000000;

        $params = [
            'maxPillagePerResource' => Missions\calculateMaxPlanetPillage([
                'planet' => $planet,
                'maxPillagePercentage' => 0.5,
            ]),
            'fleetTotalStorage' => 500000,
        ];

        $result = Missions\calculateValuePrioritizedResourcesPillage($params);

        $expectedPillage = [
            'metal' => 0,
            'crystal' => 0,
            'deuterium' => 0,
        ];
        $expectedPillage[$resourceKey] = 500000;

        $this->assertEquals(
            $expectedPillage,
            $result
        );
    }

    /**
     * @test
     * @testWith    ["metal"]
     *              ["crystal"]
     *              ["deuterium"]
     */
    public function itShouldEvenlyUseAvailableStorageWhenOneOfResourcesCannotBePillaged($resourceKey) {
        $planet = [
            'metal' => 1000000,
            'crystal' => 1000000,
            'deuterium' => 1000000,
        ];
        $planet[$resourceKey] = 0;

        $params = [
            'maxPillagePerResource' => Missions\calculateMaxPlanetPillage([
                'planet' => $planet,
                'maxPillagePercentage' => 0.5,
            ]),
            'fleetTotalStorage' => 1000000,
        ];

        $result = Missions\calculateValuePrioritizedResourcesPillage($params);

        $expectedPillage = [
            'metal' => 500000,
            'crystal' => 500000,
            'deuterium' => 500000,
        ];
        $expectedPillage[$resourceKey] = 0;

        $this->assertEquals(
            $expectedPillage,
            $result
        );
    }

    /**
     * @test
     */
    public function itShouldProperlyPillageMixedResourceAmountsWhenTheresEnoughStorage() {
        $planet = [
            'metal' => 1500000,
            'crystal' => 2000000,
            'deuterium' => 700000,
        ];

        $params = [
            'maxPillagePerResource' => Missions\calculateMaxPlanetPillage([
                'planet' => $planet,
                'maxPillagePercentage' => 0.5,
            ]),
            'fleetTotalStorage' => 10000000,
        ];

        $result = Missions\calculateValuePrioritizedResourcesPillage($params);

        $expectedPillage = [
            'metal' => 750000,
            'crystal' => 1000000,
            'deuterium' => 350000,
        ];

        $this->assertEquals(
            $expectedPillage,
            $result
        );
    }

    /**
     * @test
     * @testWith    [ [ "metal", "crystal", "deuterium" ], [ 999999, 700000, 50000 ] ]
     *              [ [ "metal", "deuterium", "crystal" ], [ 999999, 700000, 50000 ] ]
     *              [ [ "crystal", "metal", "deuterium" ], [ 999999, 700000, 50000 ] ]
     *              [ [ "crystal", "deuterium", "metal" ], [ 1000000, 700000, 50000 ] ]
     *              [ [ "deuterium", "metal", "crystal" ], [ 1000000, 699999, 50000 ] ]
     *              [ [ "deuterium", "crystal", "metal" ], [ 1000000, 700000, 50000 ] ]
     */
    public function itShouldProperlyPillageMixedResourceAmountsWhenTheresExactlyEnoughStorage(
        $orderedResourceKeys,
        $expectedPillagePerKey
    ) {
        $planet = [
            'metal' => 0,
            'crystal' => 0,
            'deuterium' => 0,
        ];
        $planet[$orderedResourceKeys[0]] = 2000000;
        $planet[$orderedResourceKeys[1]] = 1400000;
        $planet[$orderedResourceKeys[2]] = 100000;

        $params = [
            'maxPillagePerResource' => Missions\calculateMaxPlanetPillage([
                'planet' => $planet,
                'maxPillagePercentage' => 0.5,
            ]),
            'fleetTotalStorage' => 1750000,
        ];

        $result = Missions\calculateValuePrioritizedResourcesPillage($params);

        $expectedPillage = [
            'metal' => 0,
            'crystal' => 0,
            'deuterium' => 0,
        ];
        $expectedPillage[$orderedResourceKeys[0]] = $expectedPillagePerKey[0];
        $expectedPillage[$orderedResourceKeys[1]] = $expectedPillagePerKey[1];
        $expectedPillage[$orderedResourceKeys[2]] = $expectedPillagePerKey[2];

        $this->assertEquals(
            $expectedPillage,
            $result
        );
    }

    /**
     * @test
     * @testWith    [ [ "metal", "crystal", "deuterium" ], [ 482500, 482500, 35000 ] ]
     *              [ [ "metal", "deuterium", "crystal" ], [ 333333, 631666, 35000 ] ]
     *              [ [ "crystal", "metal", "deuterium" ], [ 482500, 482500, 35000 ] ]
     *              [ [ "crystal", "deuterium", "metal" ], [ 482500, 482500, 35000 ] ]
     *              [ [ "deuterium", "metal", "crystal" ], [ 631666, 333333, 35000 ] ]
     *              [ [ "deuterium", "crystal", "metal" ], [ 482500, 482500, 35000 ] ]
     */
    public function itShouldProperlyPillageMixedResourceAmountsWhenTheresNotEnoughStorage(
        $orderedResourceKeys,
        $expectedPillagePerKey
    ) {
        $planet = [
            'metal' => 0,
            'crystal' => 0,
            'deuterium' => 0,
        ];
        $planet[$orderedResourceKeys[0]] = 2000000;
        $planet[$orderedResourceKeys[1]] = 1500000;
        $planet[$orderedResourceKeys[2]] = 70000;

        $params = [
            'maxPillagePerResource' => Missions\calculateMaxPlanetPillage([
                'planet' => $planet,
                'maxPillagePercentage' => 0.5,
            ]),
            'fleetTotalStorage' => 1000000,
        ];

        $result = Missions\calculateValuePrioritizedResourcesPillage($params);

        $expectedPillage = [
            'metal' => 0,
            'crystal' => 0,
            'deuterium' => 0,
        ];
        $expectedPillage[$orderedResourceKeys[0]] = $expectedPillagePerKey[0];
        $expectedPillage[$orderedResourceKeys[1]] = $expectedPillagePerKey[1];
        $expectedPillage[$orderedResourceKeys[2]] = $expectedPillagePerKey[2];

        $this->assertEquals(
            $expectedPillage,
            $result
        );
    }
}
