<?php

use PHPUnit\Framework\TestCase;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;

$_EnginePath = './';

if (!defined('INSIDE')) {
    define('INSIDE', true);
}

require_once $_EnginePath . 'common.minimal.php';
require_once $_EnginePath . 'includes/constants.php';
require_once $_EnginePath . 'common/_includes.php';
require_once $_EnginePath . 'includes/functions.php';
require_once $_EnginePath . 'includes/unlocalised.php';
require_once $_EnginePath . 'includes/ingamefunctions.php';
require_once $_EnginePath . 'class/UniEngine_Cache.class.php';
require_once $_EnginePath . 'includes/helpers/_includes.php';
require_once $_EnginePath . 'includes/vars.php';
require_once $_EnginePath . 'includes/strings.php';
require_once $_EnginePath . 'includes/per_module/common/_includes.php';
require_once $_EnginePath . 'includes/functions/InsertJavaScriptChronoApplet.php';
require_once $_EnginePath . 'modules/flightControl/_includes.php';

includeLang('tech');
includeLang('system');
includeLang('fleet');

use UniEngine\Engine\Modules\FlightControl\Screens\SendWizardStepOne\Components\FlightsList\Utils;

/**
 * @group UniEngineTest
 */
class BuildFriendlyAcsListElementTestCase extends TestCase {
    use ArraySubsetAsserts;

    public $hidingClassString = ' class="hide"';

    public $mockAcsUnions = [
        'baseUnion' => [
            "id" => "119",
            "main_fleet_id" => "1283",
            "owner_id" => "2",
            "fleets_id" => "",
            "start_time" => "1591971305",
            "end_galaxy" => "2",
            "end_system" => "5",
            "end_planet" => "10",
            "end_type" => "3",
            "username" => "testUser_friend_1",
            "fleet_amount" => "20",
            "fleet_array" => "202,100;204,10",
            "fleet_start_galaxy" => "1",
            "fleet_start_system" => "1",
            "fleet_start_planet" => "1",
            "fleet_start_type" => "1",
            "fleet_start_time" => "1591971305",
            "fleet_send_time" => "1591966805",
        ],
        'joinedUnion' => [
            "fleets_id" => "|1284|",
        ],
    ];

    public $expectedResults = [
        'baseUnion' => [
            'positionsAndTime' => [
                'FleetOriGalaxy'            => '1',
                'FleetOriSystem'            => '1',
                'FleetOriPlanet'            => '1',
                'FleetOriType'              => 'planet',
                'FleetOriStart'             => '12.06.2020<br/>13:00:05',
                'FleetDesGalaxy'            => '2',
                'FleetDesSystem'            => '5',
                'FleetDesPlanet'            => '10',
                'FleetDesType'              => 'moon',
                'FleetDesArrive'            => '12.06.2020<br/>14:15:05',
                'FleetEndTime'              => '-',
            ],
        ],
    ];

    /**
     * @test
     */
    public function itShouldCreateCorrectElementStructure() {
        $params = [
            'elementNo' => 1,
            'acsUnion' => $this->mockAcsUnions['baseUnion'],
            'acsMainFleets' => [],
            'currentTimestamp' => 1591968605,
            'acsUnionsExtraSquads' => [],
            "isJoiningThisUnion" => false,
        ];

        $result = Utils\buildFriendlyAcsListElement($params);

        $expectedParams = [
            'FleetNo'                   => 1,
            'FleetMissionColor'         => 'orange',
            'FleetMission'              => "United Attack #1",
            'FleetBehaviour'            => 'In flight to the destination point',
            'FleetBehaviourTxt'         => '(In flight)',
            'FleetCount'                => '110',
            'FleetHideTargetorBackTime' => $this->hidingClassString,
            'FleetHideComeBackTime'     => $this->hidingClassString,
            'FleetHideStayTime'         => $this->hidingClassString,
            'FleetHideRetreatTime'      => $this->hidingClassString,
        ];
        $expectedParamsData = [
            'ships' => [
                '202' => 100,
                '204' => 10,
            ],
            'orders' => [
                0 => [
                    'orderType' => 'joinUnion',
                    'params' => [
                        'acsId' => "119",
                        'isJoiningThisUnion' => false,
                    ],
                ],
            ],
        ];

        $this->assertArraySubset($this->expectedResults['baseUnion']['positionsAndTime'], $result, true);
        $this->assertArraySubset($expectedParams, $result, true);
        $this->assertArraySubset($expectedParamsData, $result['data'], true);
    }

    /**
     * @test
     */
    public function itShouldCreateCorrectElementWithJoinedFleetsStructure() {
        $params = [
            'elementNo' => 1,
            'acsUnion' => array_merge(
                $this->mockAcsUnions['baseUnion'],
                $this->mockAcsUnions['joinedUnion']
            ),
            "acsMainFleets" => [
                "1283" => [
                    "acsId" => "119",
                    "hasJoinedFleets" => true,
                ],
            ],
            "currentTimestamp" => 1591968605,
            "acsUnionsExtraSquads" => [
                "1283" => [
                    [
                        "array" => [
                            "202" => "1",
                            "203" => "10",
                        ],
                        "count" => "11",
                    ],
                ],
            ],
            "isJoiningThisUnion" => false,
        ];

        $result = Utils\buildFriendlyAcsListElement($params);

        $expectedParams = [
            'FleetNo'                   => 1,
            'FleetMissionColor'         => 'orange',
            'FleetMission'              => "United Attack #1",
            'FleetBehaviour'            => 'In flight to the destination point',
            'FleetBehaviourTxt'         => '(In flight)',
            'FleetCount'                => '121',
            'FleetHideTargetorBackTime' => $this->hidingClassString,
            'FleetHideComeBackTime'     => $this->hidingClassString,
            'FleetHideStayTime'         => $this->hidingClassString,
            'FleetHideRetreatTime'      => $this->hidingClassString,
        ];
        $expectedParamsData = [
            'ships' => [
                '202' => 101,
                '203' => 10,
                '204' => 10,
            ],
            'orders' => [
                0 => [
                    'orderType' => 'joinUnion',
                    'params' => [
                        'acsId' => "119",
                        'isJoiningThisUnion' => false,
                    ],
                ],
            ],
        ];

        $this->assertArraySubset($this->expectedResults['baseUnion']['positionsAndTime'], $result, true);
        $this->assertArraySubset($expectedParams, $result, true);
        $this->assertArraySubset($expectedParamsData, $result['data'], true);
    }

    /**
     * @test
     */
    public function itShouldDisplayCorrectUnionOrdinalNumber() {
        $params = [
            'elementNo' => 1,
            'acsUnion' => array_merge(
                $this->mockAcsUnions['baseUnion'],
                $this->mockAcsUnions['joinedUnion']
            ),
            "acsMainFleets" => [
                "1200" => [
                    "acsId" => "110",
                    "hasJoinedFleets" => true,
                ],
                "1283" => [
                    "acsId" => "119",
                    "hasJoinedFleets" => true,
                ],
                "1300" => [
                    "acsId" => "120",
                    "hasJoinedFleets" => true,
                ],
            ],
            "currentTimestamp" => 1591968605,
            "acsUnionsExtraSquads" => [
                "1283" => [
                    [
                        "array" => [
                            "202" => "1",
                            "203" => "10",
                        ],
                        "count" => "11",
                    ],
                ],
            ],
            "isJoiningThisUnion" => false,
        ];

        $result = Utils\buildFriendlyAcsListElement($params);

        $expectedParams = [
            'FleetMission'              => "United Attack #2",
        ];

        $this->assertArraySubset($expectedParams, $result, true);
    }
}
