<?php

// TODO: Migrate to IIFE once PHP 5 support is removed
call_user_func(function () {
    global $_EnginePath;

    $includePath = $_EnginePath . 'modules/phalanx/';

    include($includePath . './utils/queries/getTargetDetails.query.php');
    include($includePath . './utils/queries/updatePhalanxMoon.query.php');

});

?>
