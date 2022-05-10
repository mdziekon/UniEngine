<?php

define('INSIDE', true);

$_EnginePath = './';

include("{$_EnginePath}/common.php");
include("{$_EnginePath}/modules/flightControl/_includes.php");
include("{$_EnginePath}/includes/functions/InsertJavaScriptChronoApplet.php");

use UniEngine\Engine\Modules\FlightControl;

loggedCheck();

includeLang('fleet');

if (!$_Planet) {
    message($_Lang['fl_noplanetrow'], $_Lang['fl_error']);
}

$pageElement = FlightControl\Screens\SendWizardStepOne\render([
    'inputs' => [
        'queryParams' => $_GET,
        'formData' => $_POST,
    ],
    'user' => &$_User,
    'planet' => &$_Planet,
    'currentTimestamp' => time(),
]);

display($pageElement['componentHTML'], $_Lang['fl_title']);

?>
