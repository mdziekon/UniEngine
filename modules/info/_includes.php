<?php

// TODO: Migrate to IIFE once PHP 5 support is removed
call_user_func(function () {
    global $_EnginePath;

    $includePath = $_EnginePath . 'modules/info/';

    include($includePath . './components/BuildingDestructionSection/BuildingDestructionSection.component.php');
    include($includePath . './components/MissileRangeTable/MissileRangeTable.component.php');
    include($includePath . './components/PhalanxRangeTable/PhalanxRangeTable.component.php');
    include($includePath . './components/RapidFireCommonRow/RapidFireCommonRow.component.php');
    include($includePath . './components/RapidFireAgainstList/RapidFireAgainstList.component.php');
    include($includePath . './components/RapidFireFromList/RapidFireFromList.component.php');
    include($includePath . './components/ResourceProductionTable/ResourceProductionTable.component.php');
    include($includePath . './components/ResourceStorageTable/ResourceStorageTable.component.php');
    include($includePath . './components/TeleportFleetUnitSelectorsList/TeleportFleetUnitSelectorsList.component.php');
    include($includePath . './components/TeleportTargetMoonsList/TeleportTargetMoonsList.component.php');

    include($includePath . './utils/ranges.utils.php');

});

?>
