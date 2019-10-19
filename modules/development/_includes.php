<?php

// TODO: Migrate to IIFE once PHP 5 support is removed
call_user_func(function () {
    global $_EnginePath;

    $includePath = $_EnginePath . 'modules/development/';

    include($includePath . './components/ModernQueue/ModernQueue.component.php');
    include($includePath . './components/LegacyQueue/LegacyQueue.component.php');
    include($includePath . './input/research.userCommands.php');
});

?>
