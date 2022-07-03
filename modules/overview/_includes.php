<?php

// TODO: Migrate to IIFE once PHP 5 support is removed
call_user_func(function () {
    global $_EnginePath;

    $includePath = $_EnginePath . 'modules/overview/';

    include($includePath . './screens/FirstLogin/FirstLogin.screen.php');
    include($includePath . './screens/FirstLogin/utils/effects/createUserDevLogDump.effect.php');
    include($includePath . './screens/FirstLogin/utils/effects/handleProxyDetection.effect.php');
    include($includePath . './screens/FirstLogin/utils/effects/handleReferralMultiAccountDetection.effect.php');
    include($includePath . './screens/FirstLogin/utils/effects/triggerUserReferralTask.effect.php');
    include($includePath . './screens/FirstLogin/utils/helpers/getReferrerTasksData.helper.php');

});

?>
