<?php

// TODO: Migrate to IIFE once PHP 5 support is removed
call_user_func(function () {
    global $_EnginePath;

    $includePath = $_EnginePath . 'modules/messages/';

    include($includePath . './commands/batchDeleteUserMessages.commands.php');

});

?>
