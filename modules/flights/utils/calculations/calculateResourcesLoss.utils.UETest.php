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
class CalculateResourcesLossTestCase extends TestCase {
    /**
     * @test
     */
    public function itShouldHandleEmptyLossesArray() {
        $params = [
            'unitsLost' => [],
            'debrisRecoveryPercentages' => [
                'ships' => 0.5,
                'defenses' => 0.5,
            ],
        ];

        $result = Calculations\calculateResourcesLoss($params);

        $this->assertEquals(
            [
                'realLoss' => [
                    'metal' => 0,
                    'crystal' => 0,
                    'deuterium' => 0,
                    'darkEnergy' => 0,
                ],
                'recoverableLoss' => [
                    'metal' => 0,
                    'crystal' => 0,
                ],
            ],
            $result
        );
    }

    /**
     * @test
     */
    public function itShouldProperlyCalculateShipsLosses() {
        $params = [
            'unitsLost' => [
                202 => 10,
                203 => 1,
                206 => 1,
            ],
            'debrisRecoveryPercentages' => [
                'ships' => 0.1,
                'defenses' => 0.1,
            ],
        ];

        $result = Calculations\calculateResourcesLoss($params);

        $this->assertEquals(
            [
                'realLoss' => [
                    'metal' => 46000,
                    'crystal' => 33000,
                    'deuterium' => 2000,
                    'darkEnergy' => 0,
                ],
                'recoverableLoss' => [
                    'metal' => 46000 * 0.1,
                    'crystal' => 33000 * 0.1,
                ],
            ],
            $result
        );
    }

    /**
     * @test
     */
    public function itShouldProperlyCalculateDefensesLosses() {
        $params = [
            'unitsLost' => [
                401 => 10,
                402 => 1,
                404 => 1,
            ],
            'debrisRecoveryPercentages' => [
                'ships' => 0.1,
                'defenses' => 0.1,
            ],
        ];

        $result = Calculations\calculateResourcesLoss($params);

        $this->assertEquals(
            [
                'realLoss' => [
                    'metal' => 41500,
                    'crystal' => 15500,
                    'deuterium' => 2000,
                    'darkEnergy' => 0,
                ],
                'recoverableLoss' => [
                    'metal' => 41500 * 0.1,
                    'crystal' => 15500 * 0.1,
                ],
            ],
            $result
        );
    }

    /**
     * @test
     */
    public function itShouldProperlyCalculateMixedLosses() {
        $params = [
            'unitsLost' => [
                202 => 10,
                203 => 1,
                206 => 1,
                401 => 10,
                402 => 1,
                404 => 1,
            ],
            'debrisRecoveryPercentages' => [
                'ships' => 0.1,
                'defenses' => 0.2,
            ],
        ];

        $result = Calculations\calculateResourcesLoss($params);

        $this->assertEquals(
            [
                'realLoss' => [
                    'metal' => 46000 + 41500,
                    'crystal' => 33000 + 15500,
                    'deuterium' => 2000 + 2000,
                    'darkEnergy' => 0,
                ],
                'recoverableLoss' => [
                    'metal' => (46000 * 0.1) + (41500 * 0.2),
                    'crystal' => (33000 * 0.1) + (15500 * 0.2),
                ],
            ],
            $result
        );
    }
}
