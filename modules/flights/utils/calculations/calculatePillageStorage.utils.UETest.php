<?php

use PHPUnit\Framework\TestCase;

$_EnginePath = './';

define('INSIDE', true);
require_once $_EnginePath . 'common/_includes.php';
require_once $_EnginePath . 'includes/vars.php';
require_once $_EnginePath . 'includes/helpers/_includes.php';
require_once $_EnginePath . 'modules/flights/_includes.php';

use UniEngine\Engine\Modules\Flights\Utils\Calculations;


/**
 * @group UniEngineTest
 */
class CalculatePillageStorageTestCase extends TestCase {
    /**
     * @test
     */
    public function itShouldHandleEmptyShipsArray() {
        $params = [
            'fleetRow' => [
                'fleet_id' => '42',
                'fleet_resource_metal' => '0',
                'fleet_resource_crystal' => '0',
                'fleet_resource_deuterium' => '0',
                'fleet_array' => '',
            ],
            'ships' => [],
        ];

        $result = Calculations\calculatePillageStorage($params);

        $this->assertEquals(
            0,
            $result
        );
    }

    /**
     * @test
     */
    public function itShouldHandleRegularShipsArray() {
        $params = [
            'fleetRow' => [
                'fleet_id' => '42',
                'fleet_resource_metal' => '0',
                'fleet_resource_crystal' => '0',
                'fleet_resource_deuterium' => '0',
                'fleet_array' => '202,100;204,100',
            ],
            'ships' => [
                '202' => 100,
                '204' => 120,
            ],
        ];

        $result = Calculations\calculatePillageStorage($params);

        $this->assertEquals(
            (
                (5000 * 100) +
                (50 * 120)
            ),
            $result
        );
    }

    /**
     * @test
     * @backupGlobals enabled
     */
    public function itShouldHandleShipsWithForbiddenPillageArray() {
        global $_Vars_Prices;

        $_Vars_Prices[204]['cantPillage'] = true;

        $params = [
            'fleetRow' => [
                'fleet_id' => '42',
                'fleet_resource_metal' => '0',
                'fleet_resource_crystal' => '0',
                'fleet_resource_deuterium' => '0',
                'fleet_array' => '202,100;204,100',
            ],
            'ships' => [
                '202' => 100,
                '204' => 120,
            ],
        ];

        $result = Calculations\calculatePillageStorage($params);

        $this->assertEquals(
            (
                (5000 * 100) +
                (0 * 120)
            ),
            $result
        );
    }

    /**
     * @test
     */
    public function itShouldHandleFleetsWithOccupiedSpace() {
        $params = [
            'fleetRow' => [
                'fleet_id' => '42',
                'fleet_resource_metal' => '1000',
                'fleet_resource_crystal' => '70',
                'fleet_resource_deuterium' => '0',
                'fleet_array' => '202,100;204,100',
            ],
            'ships' => [
                '202' => 100,
                '204' => 120,
            ],
        ];

        $result = Calculations\calculatePillageStorage($params);

        $this->assertEquals(
            (
                (5000 * 100) +
                (50 * 120) -
                (1000 + 70 + 0)
            ),
            $result
        );
    }

    /**
     * @test
     */
    public function itShouldNotBlowUpWhenFleetRowDoesNotHaveAllResourceKeys() {
        $params = [
            'fleetRow' => [
                'fleet_id' => '42',
                'fleet_resource_metal' => '1000',
                'fleet_resource_deuterium' => '5',
                'fleet_array' => '202,100;204,100',
            ],
            'ships' => [
                '202' => 100,
                '204' => 120,
            ],
        ];

        $result = Calculations\calculatePillageStorage($params);

        $this->assertEquals(
            (
                (5000 * 100) +
                (50 * 120) -
                (1000 + 0 + 5)
            ),
            $result
        );
    }

    /**
     * @test
     */
    public function itShouldNotBlowUpWhenFleetRowHasUnknownResource() {
        $params = [
            'fleetRow' => [
                'fleet_id' => '42',
                'fleet_resource_metal' => '1000',
                'fleet_resource_unknown' => '5',
                'fleet_array' => '202,100;204,100',
            ],
            'ships' => [
                '202' => 100,
                '204' => 120,
            ],
        ];

        $result = Calculations\calculatePillageStorage($params);

        $this->assertEquals(
            (
                (5000 * 100) +
                (50 * 120) -
                (1000 + 0 + 0)
            ),
            $result
        );
    }

    /**
     * @test
     */
    public function itShouldNotBlowUpWhenUsingMixedDataTypes() {
        $params = [
            'fleetRow' => [
                'fleet_id' => '42',
                'fleet_resource_metal' => '1000',
                'fleet_resource_crystal' => 70,
                'fleet_resource_deuterium' => '5',
                'fleet_array' => '202,100;204,100',
            ],
            'ships' => [
                '202' => '100',
                204 => 120,
            ],
        ];

        $result = Calculations\calculatePillageStorage($params);

        $this->assertEquals(
            (
                (5000 * 100) +
                (50 * 120) -
                (1000 + 70 + 5)
            ),
            $result
        );
    }
}
