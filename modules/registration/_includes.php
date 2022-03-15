<?php

// TODO: Migrate to IIFE once PHP 5 support is removed
call_user_func(function () {
    global $_EnginePath;

    $includePath = $_EnginePath . 'modules/registration/';

    include($includePath . './components/RegistrationConfirmationMail/RegistrationConfirmationMail.component.php');

    include($includePath . './utils/cookies.utils.php');
    include($includePath . './utils/queries.utils.php');

});

?>
