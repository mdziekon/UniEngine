<?php

// TODO: Migrate to IIFE once PHP 5 support is removed
call_user_func(function () {
    global $_EnginePath;

    $includePath = $_EnginePath . 'modules/overview/';

    include($includePath . './screens/AbandonPlanet/AbandonPlanet.screen.php');
    include($includePath . './screens/AbandonPlanet/AbandonPlanet.utils.php');
    include($includePath . './screens/AbandonPlanet/utils/effects/triggerUserTasksUpdates.effect.php');
    include($includePath . './screens/AbandonPlanet/utils/effects/tryAbandonPlanet.effect.php');
    include($includePath . './screens/AbandonPlanet/utils/effects/updateUserDevLog.effect.php');
    include($includePath . './screens/AbandonPlanet/utils/errorMappers/tryAbandonPlanet.errorMapper.php');
    include($includePath . './screens/AbandonPlanet/utils/validators/validateAbandonPlanet.validator.php');

    include($includePath . './screens/FirstLogin/FirstLogin.screen.php');
    include($includePath . './screens/FirstLogin/FirstLogin.utils.php');
    include($includePath . './screens/FirstLogin/utils/effects/createUserDevLogDump.effect.php');
    include($includePath . './screens/FirstLogin/utils/effects/giveUserPremium.effect.php');
    include($includePath . './screens/FirstLogin/utils/effects/handleProxyDetection.effect.php');
    include($includePath . './screens/FirstLogin/utils/effects/handleReferralMultiAccountDetection.effect.php');
    include($includePath . './screens/FirstLogin/utils/effects/triggerUserReferralTask.effect.php');
    include($includePath . './screens/FirstLogin/utils/effects/updateUserOnFirstLogin.effect.php');
    include($includePath . './screens/FirstLogin/utils/helpers/getReferrerTasksData.helper.php');

    include($includePath . './screens/Overview/components/AdminAlerts/AdminAlerts.component.php');
    include($includePath . './screens/Overview/components/PlanetsListElement/PlanetsListElement.component.php');
    include($includePath . './screens/Overview/components/ResourcesTransport/ResourcesTransport.component.php');
    include($includePath . './screens/Overview/components/StatsList/StatsList.component.php');

    include($includePath . './screens/PlanetNameChange/PlanetNameChange.screen.php');
    include($includePath . './screens/PlanetNameChange/PlanetNameChange.utils.php');
    include($includePath . './screens/PlanetNameChange/utils/errorMappers/validateNewName.errorMapper.php');
    include($includePath . './screens/PlanetNameChange/utils/validators/validateNewName.validator.php');

});

?>
