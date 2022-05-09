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

use UniEngine\Engine\Modules\FlightControl\Components\FlightsList\Utils;

/**
 * @group UniEngineTest
 */
class BuildOwnListElementTestCase extends TestCase {
    use ArraySubsetAsserts;

    public $hidingClassString = ' class="hide"';

    public $mockFleetEntries = [
        'commonAttack' => [
            "fleet_id" => "1269",
            "fleet_mess" => "0",
            "fleet_mission" => "1",
            "fleet_array" => "202,100;203,10",
            "fleet_amount" => "110",
            "fleet_start_time" => "1591971305",
            "fleet_end_time" => "1591975805",
            "fleet_end_stay" => "0",
            "fleet_send_time" => "1591966805",
            "fleet_start_galaxy" => "1",
            "fleet_start_system" => "1",
            "fleet_start_planet" => "1",
            "fleet_start_type" => "1",
            "fleet_end_galaxy" => "2",
            "fleet_end_system" => "5",
            "fleet_end_planet" => "10",
            "fleet_end_type" => "3",
            "fleet_resource_metal" => "5",
            "fleet_resource_crystal" => "10",
            "fleet_resource_deuterium" => "200",
        ],
    ];

    public $expectedResults = [
        'commonAttack' => [
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
                'FleetEndTime'              => '12.06.2020<br/>15:30:05',
                'data' => [
                    'resources' => [
                        'metal' => '5',
                        'crystal' => '10',
                        'deuterium' => '200',
                    ],
                ],
            ],
        ],
    ];

    /**
     * @test
     */
    public function itShouldCreateCorrectElementStructure() {
        $params = [
            "elementNo" => 1,
            "fleetEntry" => $this->mockFleetEntries['commonAttack'],
            "acsMainFleets" => [],
            "currentTimestamp" => 1591968605,
            "acsUnionsExtraSquads" => [],
            "relatedAcsFleets" => [],
            "isJoiningThisUnion" => false,
        ];

        $result = Utils\buildOwnListElement($params);

        $expectedParams = [
            'FleetNo'                   => 1,
            'FleetMissionColor'         => '',
            'FleetMission'              => "Attack",
            'FleetBehaviour'            => 'In flight to the destination point',
            'FleetBehaviourTxt'         => '(In flight)',
            'FleetCount'                => '110',
            'FleetHideTargetTime'       => '',
            'FleetHideTargetorBackTime' => '',
            'FleetHideComeBackTime'     => '',
            'FleetHideStayTime'         => $this->hidingClassString,
            'FleetHideRetreatTime'      => '',
        ];
        $expectedParamsData = [
            'ships' => [
                '202' => '100',
                '203' => '10',
            ],
            'extraShipsInUnion' => [],
            'orders' => [
                0 => [
                    'orderType' => 'retreat',
                    'params' => [
                        'FleetID' => "1269",
                        'ButtonText' => "Turn back",
                    ],
                ],
                1 => [
                    'orderType' => 'createUnion',
                    'params' => [
                        'FleetID' => "1269",
                        'ButtonText' => "Join fleets",
                    ],
                ],
            ],
        ];

        $this->assertArraySubset($this->expectedResults['commonAttack']['positionsAndTime'], $result, true);
        $this->assertArraySubset($expectedParams, $result, true);
        $this->assertArraySubset($expectedParamsData, $result['data'], true);
    }

    /**
     * @test
     */
    public function itShouldCreateCorrectElementWithAcsStructure() {
        $params = [
            "elementNo" => 1,
            "fleetEntry" => $this->mockFleetEntries['commonAttack'],
            "acsMainFleets" => [
                "1269" => [
                    "acsId" => "113",
                    "hasJoinedFleets" => true,
                ],
            ],
            "currentTimestamp" => 1591968605,
            "acsUnionsExtraSquads" => [
                "1269" => [
                    [
                        "array" => [
                            "202" => "1",
                        ],
                        "count" => "1",
                    ],
                ],
            ],
            "relatedAcsFleets" => [
                [
                    "fleetId" => "1270",
                    "mainFleetId" => "1269",
                ],
            ],
            "isJoiningThisUnion" => false,
        ];

        $result = Utils\buildOwnListElement($params);

        $expectedParams = [
            'FleetNo'                   => 1,
            'FleetMissionColor'         => 'orange',
            'FleetMission'              => "United Attack #1",
            'FleetBehaviour'            => 'In flight to the destination point',
            'FleetBehaviourTxt'         => '(In flight)',
            'FleetCount'                => '111',
            'FleetHideTargetTime'       => '',
            'FleetHideTargetorBackTime' => '',
            'FleetHideComeBackTime'     => '',
            'FleetHideStayTime'         => $this->hidingClassString,
            'FleetHideRetreatTime'      => '',
        ];
        $expectedParamsData = [
            'ships' => [
                '202' => '100',
                '203' => '10',
            ],
            'extraShipsInUnion' => [
                "202" => 1,
            ],
            'orders' => [
                0 => [
                    'orderType' => 'retreat',
                    'params' => [
                        'FleetID' => "1269",
                        'ButtonText' => "Turn back",
                    ],
                ],
                1 => [
                    'orderType' => 'createUnion',
                    'params' => [
                        'FleetID' => "1269",
                        'ButtonText' => "Join fleets",
                    ],
                ],
                2 => [
                    'orderType' => 'joinUnion',
                    'params' => [
                        'ACS_ID' => '113',
                        'checked' => false,
                        'Text' => 'Join fleet',
                    ],
                ],
            ],
        ];

        $this->assertArraySubset($this->expectedResults['commonAttack']['positionsAndTime'], $result, true);
        $this->assertArraySubset($expectedParams, $result, true);
        $this->assertArraySubset($expectedParamsData, $result['data'], true);
    }

    /**
     * @test
     */
    public function itShouldCreateCorrectElementWithMultiAcsStructure() {
        $params = [
            "elementNo" => 1,
            "fleetEntry" => $this->mockFleetEntries['commonAttack'],
            "acsMainFleets" => [
                "1269" => [
                    "acsId" => "113",
                    "hasJoinedFleets" => true,
                ],
            ],
            "currentTimestamp" => 1591968605,
            "acsUnionsExtraSquads" => [
                "1269" => [
                    [
                        "array" => [
                            "202" => "2",
                        ],
                        "count" => "2",
                    ],
                    [
                        "array" => [
                            "204" => "5",
                        ],
                        "count" => "5",
                    ],
                    [
                        "array" => [
                            "202" => "1",
                            "203" => "1",
                        ],
                        "count" => "2",
                    ],
                ],
            ],
            "relatedAcsFleets" => [
                [
                    "fleetId" => "1270",
                    "mainFleetId" => "1269",
                ],
                [
                    "fleetId" => "1271",
                    "mainFleetId" => "1269",
                ],
                [
                    "fleetId" => "1272",
                    "mainFleetId" => "1269",
                ],
            ],
            "isJoiningThisUnion" => false,
        ];

        $result = Utils\buildOwnListElement($params);

        $expectedParams = [
            'FleetNo'                   => 1,
            'FleetMissionColor'         => 'orange',
            'FleetMission'              => "United Attack #1",
            'FleetBehaviour'            => 'In flight to the destination point',
            'FleetBehaviourTxt'         => '(In flight)',
            'FleetCount'                => '119',
            'FleetHideTargetTime'       => '',
            'FleetHideTargetorBackTime' => '',
            'FleetHideComeBackTime'     => '',
            'FleetHideStayTime'         => $this->hidingClassString,
            'FleetHideRetreatTime'      => '',
        ];
        $expectedParamsData = [
            'ships' => [
                '202' => '100',
                '203' => '10',
            ],
            'extraShipsInUnion' => [
                "202" => 3,
                "203" => 1,
                "204" => 5,
            ],
            'orders' => [
                0 => [
                    'orderType' => 'retreat',
                    'params' => [
                        'FleetID' => "1269",
                        'ButtonText' => "Turn back",
                    ],
                ],
                1 => [
                    'orderType' => 'createUnion',
                    'params' => [
                        'FleetID' => "1269",
                        'ButtonText' => "Join fleets",
                    ],
                ],
                2 => [
                    'orderType' => 'joinUnion',
                    'params' => [
                        'ACS_ID' => '113',
                        'checked' => false,
                        'Text' => 'Join fleet',
                    ],
                ],
            ],
        ];

        $this->assertArraySubset($this->expectedResults['commonAttack']['positionsAndTime'], $result, true);
        $this->assertArraySubset($expectedParams, $result, true);
        $this->assertArraySubset($expectedParamsData, $result['data'], true);
    }
}
