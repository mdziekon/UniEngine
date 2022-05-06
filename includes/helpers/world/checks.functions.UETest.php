<?php

use PHPUnit\Framework\TestCase;

$_EnginePath = './';

require_once $_EnginePath . 'common/_includes.php';
require_once $_EnginePath . 'includes/helpers/_includes.php';

use UniEngine\Engine\Includes\Helpers\World\Checks;


/**
 * @group UniEngineTest
 */
class ChecksIsTargetInRangeTestCase extends TestCase {
    /**
     * @test
     * @testWith    [ 1, 1, 0, true ]
     *              [ 1, 2, 0, false ]
     *              [ 2, 1, 0, false ]
     *              [ 2, 1, 1, true ]
     *              [ 1, 2, 1, true ]
     *              [ 1, 10, 7, false ]
     *              [ 10, 1, 7, false ]
     *              [ 1, 10, 100, true ]
     *              [ 10, 1, 100, true ]
     *              [ 5, 5, 1, true ]
     *              [ 5, 6, 1, true ]
     *              [ 5, 7, 1, false ]
     *              [ 5, 5, 3, true ]
     *              [ 5, 8, 3, true ]
     *              [ 5, 9, 3, false ]
     */
    public function itShouldValidateIsTargetInRange($originPosition, $targetPosition, $range, $expectedResult) {
        $params = [
            'originPosition' => $originPosition,
            'targetPosition' => $targetPosition,
            'range' => $range,
        ];

        $result = Checks\isTargetInRange($params);

        $this->assertEquals(
            $expectedResult,
            $result
        );
    }
}
