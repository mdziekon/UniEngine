<?php

// TODO: Migrate to IIFE once PHP 5 support is removed
call_user_func(function () {
    global $_EnginePath;

    $includePath = $_EnginePath . 'modules/phalanx/';

    include($includePath . './utils/effects/updateMoonFuelOnUsage.effect.php');
    include($includePath . './utils/errors/tryScanPlanet.errors.php');
    include($includePath . './utils/helpers/canUserBypassChecks.helper.php');
    include($includePath . './utils/helpers/tryScanPlanet.helper.php');
    include($includePath . './utils/queries/getTargetDetails.query.php');
    include($includePath . './utils/queries/updatePhalanxMoon.query.php');

});

?>
