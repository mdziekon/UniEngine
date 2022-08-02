<?php

// TODO: Migrate to IIFE once PHP 5 support is removed
call_user_func(function () {
    global $_EnginePath;

    $includePath = $_EnginePath . 'modules/info/';

    include($includePath . './components/RapidFireCommonRow/RapidFireCommonRow.component.php');
    include($includePath . './components/RapidFireAgainstList/RapidFireAgainstList.component.php');
    include($includePath . './components/RapidFireFromList/RapidFireFromList.component.php');
    include($includePath . './components/ResourceProductionTable/ResourceProductionTable.component.php');

});

?>
