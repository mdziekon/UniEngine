<?php

// TODO: Migrate to IIFE once PHP 5 support is removed
call_user_func(function () {
    global $_EnginePath;

    $includePath = $_EnginePath . 'modules/session/';

    include($includePath . './input/cookieLogin.inputHandler.php');
    include($includePath . './input/language.inputHandler.php');
    include($includePath . './input/localIdentityLogin.inputHandler.php');

    include($includePath . './screens/LoginView/LoginView.component.php');
    include($includePath . './screens/LoginView/components/LoginForm/LoginForm.component.php');

    include($includePath . './utils/cookie.utils.php');
    include($includePath . './utils/rateLimiter.utils.php');
    include($includePath . './utils/redirects.utils.php');

});

?>
