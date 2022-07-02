<?php

// TODO: Migrate to IIFE once PHP 5 support is removed
call_user_func(function () {
    global $_EnginePath;

    $includePath = $_EnginePath . 'modules/overview/';

    include($includePath . './screens/FirstLogin/FirstLogin.screen.php');
    include($includePath . './screens/FirstLogin/utils/effects/handleProxyDetection.effect.php');
    include($includePath . './screens/FirstLogin/utils/effects/handleReferralMultiAccountDetection.effect.php');

});

?>
