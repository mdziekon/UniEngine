<?php

// TODO: Migrate to IIFE once PHP 5 support is removed
call_user_func(function () {
    global $_EnginePath;

    $includePath = $_EnginePath . 'modules/registration/';

    include($includePath . './components/RegistrationConfirmationMail/RegistrationConfirmationMail.component.php');

    include($includePath . './input/normalization.input.php');

    include($includePath . './utils/cookies.utils.php');
    include($includePath . './utils/galaxy.utils.php');
    include($includePath . './utils/general.utils.php');
    include($includePath . './utils/queries.utils.php');

    include($includePath . './validators/validateInputs.validators.php');
    include($includePath . './validators/validateTakenParams.validators.php');
    include($includePath . './validators/validateReCaptcha.validators.php');

});

?>
