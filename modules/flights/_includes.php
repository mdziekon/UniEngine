<?php

// TODO: Migrate to IIFE once PHP 5 support is removed
call_user_func(function () {
    global $_EnginePath;

    $includePath = $_EnginePath . 'modules/flights/';

    include($includePath . './utils/calculations/calculateResourcesLoss.utils.php');
    include($includePath . './utils/fleetCache/updateGalaxyDebris.utils.php');
    include($includePath . './utils/missions.utils.php');

});

?>
