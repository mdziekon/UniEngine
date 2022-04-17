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

    /**
     * @test
     */
    public function itShouldCreateCorrectElementStructure() {
        $params = [
            "elementNo" => 1,
            "fleetEntry" => [
                "fleet_id" => "1269",
                "fleet_mess" => "0",
                "fleet_mission" => "1",
                "fleet_array" => "202,100;203,10",
                "fleet_amount" => "100",
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
                "fleet_resource_metal" => "0",
                "fleet_resource_crystal" => "0",
                "fleet_resource_deuterium" => "0",
            ],
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
            'FleetCount'                => '100',
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
        ];

        $this->assertArraySubset($expectedParams, $result, true);
        $this->assertArraySubset($expectedParamsData, $result['data'], true);
    }
}
