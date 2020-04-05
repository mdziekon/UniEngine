<?php

// TODO: Migrate to IIFE once PHP 5 support is removed
call_user_func(function () {
    global $_EnginePath;

    $includePath = $_EnginePath . 'modules/flights/';

    include($includePath . './utils/factories/createFleetUpdateEntry.utils.php');
    include($includePath . './utils/calculations/calculatePillageFactor.utils.php');
    include($includePath . './utils/calculations/calculatePillageStorage.utils.php');
    include($includePath . './utils/calculations/calculateResourcesLoss.utils.php');
    include($includePath . './utils/fleetCache/updateGalaxyDebris.utils.php');
    include($includePath . './utils/fleetCache/updateUserStats.utils.php');
    include($includePath . './utils/initializers/technologies.utils.php');
    include($includePath . './utils/initializers/userStats.utils.php');
    include($includePath . './utils/modifiers/calculateMoraleModifiers.utils.php');
    include($includePath . './utils/missions.utils.php');

});

?>
