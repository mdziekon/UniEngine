<?php

define('INSIDE', true);

$_DontShowMenus = true;
$_DontShowRulesBox = true;
$_DontCheckPolls = true;
$_BlockFleetHandler = true;

$_EnginePath = './';
include($_EnginePath.'common.php');
include($_EnginePath.'modules/flights/_includes.php');
include($_EnginePath.'modules/phalanx/_includes.php');
include($_EnginePath.'includes/functions/GetPhalanxRange.php');

use UniEngine\Engine\Includes\Helpers\World\Checks;
use UniEngine\Engine\Modules\Flights;
use UniEngine\Engine\Modules\Phalanx;

loggedCheck();

includeLang('overview');
includeLang('phalanx');
$PageTPL = gettemplate('phalanx_body');
$PageTitle = $_Lang['Page_Title'];

$ThisMoon = &$_Planet;
$Now = time();
$ScanCost = PHALANX_DEUTERIUMCOST;
if(CheckAuth('supportadmin'))
{
    $ScanCost = 0;
    $ThisMoon['sensor_phalanx'] = 50;
}

if ($ThisMoon['planet_type'] == 3) {
    if ($ThisMoon['sensor_phalanx'] > 0) {
        $thisMoonCoords = [
            'galaxy' => $ThisMoon['galaxy'],
            'system' => $ThisMoon['system'],
            'planet' => $ThisMoon['planet'],
        ];
        $thisPhalanxLevel = $ThisMoon['sensor_phalanx'];

        $targetCoords = [
            'galaxy' => (isset($_GET['galaxy']) ? intval($_GET['galaxy']) : 0),
            'system' => (isset($_GET['system']) ? intval($_GET['system']) : 0),
            'planet' => (isset($_GET['planet']) ? intval($_GET['planet']) : 0),
        ];

        $isInRange = Checks\isTargetInRange([
            'originPosition' => $thisMoonCoords['system'],
            'targetPosition' => $targetCoords['system'],
            'range' => GetPhalanxRange($thisPhalanxLevel),
        ]);

        $DenyScan = false;

        $isValidCoordinate = Flights\Utils\Checks\isValidCoordinate([
            'coordinate' => $targetCoords,
            'areExpeditionsExcluded' => true,
        ]);

        if (!$isValidCoordinate['isValid']) {
            $DenyScan = true;
            $WhyDoNotScan = $_Lang['PhalanxError_BadCoordinates'];
        }
        if($targetCoords['galaxy'] != $thisMoonCoords['galaxy'])
        {
            $DenyScan = true;
            $WhyDoNotScan = $_Lang['PhalanxError_GalaxyOutOfRange'];
        }
        if (!$isInRange) {
            $DenyScan = true;
            $WhyDoNotScan = $_Lang['PhalanxError_TargetOutOfRange'];
        }
        if(CheckAuth('supportadmin'))
        {
            $DenyScan = false;
        }

        if (!$DenyScan) {
            $targetDetails = Phalanx\Utils\Queries\getTargetDetails([
                'targetCoords' => $targetCoords,
            ]);

            if ($targetDetails['id'] > 0) {
                FlyingFleetHandler($ThisMoon, [ $targetDetails['id'] ]);

                if ($ThisMoon['id'] > 0) {
                    if ($ThisMoon['deuterium'] >= $ScanCost) {
                        if ($ScanCost > 0) {
                            Phalanx\Utils\Effects\updateMoonFuelOnUsage([
                                'scanCost' => $ScanCost,
                                'phalanxMoon' => &$ThisMoon,
                                'currentTimestamp' => $Now,
                            ]);
                        }

                        $parse = $_Lang;

                        if ($targetDetails['id_owner'] > 0) {
                            $parse['Insert_OwnerName'] = "({$targetDetails['username']})";
                            $parse['Insert_TargetName'] = $targetDetails['name'];
                        } else {
                            $parse['Table_Title2'] = '';
                            $parse['Insert_TargetName'] = "<b class=\"red\">{$_Lang['Abandoned_planet']}</b>";
                        }

                        $parse['skinpath'] = $_SkinPath;
                        $parse['Insert_Coord_Galaxy'] = $targetDetails['galaxy'];
                        $parse['Insert_Coord_System'] = $targetDetails['system'];
                        $parse['Insert_Coord_Planet'] = $targetDetails['planet'];
                        $parse['Insert_My_Galaxy'] = $thisMoonCoords['galaxy'];
                        $parse['Insert_My_System'] = $thisMoonCoords['system'];
                        $parse['Insert_My_Planet'] = $thisMoonCoords['planet'];
                        $parse['Insert_MyMoonName'] = $ThisMoon['name'];
                        $parse['Insert_DeuteriumAmount'] = prettyNumber($ThisMoon['deuterium']);
                        $parse['Insert_DeuteriumColor'] = (
                            ($ThisMoon['deuterium'] >= $ScanCost) ?
                                'lime' :
                                'red'
                        );

                        $Result_GetFleets = Flights\Fetchers\fetchCurrentFlights([
                            'targetId' => $targetDetails['id'],
                        ]);

                        $parse['phl_fleets_table'] = Flights\Components\FlightsList\render([
                            'viewMode' => Flights\Components\FlightsList\Utils\ViewMode::Phalanx,
                            'flights' => $Result_GetFleets,
                            'viewingUserId' => $_User['id'],
                            'targetOwnerId' => $targetDetails['id_owner'],
                            'currentTimestamp' => $Now,
                        ])['componentHTML'];

                        if (empty($parse['phl_fleets_table'])) {
                            $parse['phl_fleets_table'] = $_Lang['PhalanxInfo_NoMovements'];
                        }

                        $page = parsetemplate($PageTPL, $parse);
                    }
                    else
                    {
                        message(sprintf($_Lang['PhalanxError_NoEnoughFuel'], prettyNumber($ScanCost)), $PageTitle);
                    }
                }
                else
                {
                    message($_Lang['PhalanxError_MoonDestroyed'], $PageTitle);
                }
            }
            else
            {
                message($_Lang['PhalanxError_CoordsEmpty'], $PageTitle);
            }
        }
        else
        {
            message($WhyDoNotScan, $PageTitle);
        }
    }
    else
    {
        message($_Lang['PhalanxError_NoPhalanxHere'], $PageTitle);
    }
}
else
{
    message($_Lang['PhalanxError_ScanOnlyFromMoon'], $PageTitle);
}

display($page, $PageTitle, false);

?>
