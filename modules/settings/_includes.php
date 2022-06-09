<?php

// TODO: Migrate to IIFE once PHP 5 support is removed
call_user_func(function () {
    global $_EnginePath;

    $includePath = $_EnginePath . 'modules/settings/';

    include($includePath . './utils/errorMappers/validatePasswordChange.errorMapper.php');

    include($includePath . './utils/validators/validatePasswordChange.validator.php');

});

?>
