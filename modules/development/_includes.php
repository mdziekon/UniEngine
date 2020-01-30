<?php

// TODO: Migrate to IIFE once PHP 5 support is removed
call_user_func(function () {
    global $_EnginePath;

    $includePath = $_EnginePath . 'modules/development/';

    include($includePath . './components/ModernQueue/ModernQueue.component.php');
    include($includePath . './components/LegacyQueue/LegacyQueue.component.php');
    include($includePath . './input/research.userCommands.php');
    include($includePath . './input/structures.userCommands.php');

    include($includePath . './screens/ResearchListPage/common.helpers.php');
    include($includePath . './screens/ResearchListPage/queue.helpers.php');
    include($includePath . './screens/ResearchListPage/ResearchListPage.php');
    include($includePath . './screens/ResearchListPage/LegacyElementListItem/LegacyElementListItem.component.php');
    include($includePath . './screens/ResearchListPage/ModernQueueLabUpgradeInfo/ModernQueueLabUpgradeInfo.component.php');
    include($includePath . './screens/ResearchListPage/ModernQueuePlanetInfo/ModernQueuePlanetInfo.component.php');
    include($includePath . './screens/ResearchListPage/ModernElementListIcon/ModernElementListIcon.component.php');
    include($includePath . './screens/ResearchListPage/ModernElementInfoCard/ModernElementInfoCard.component.php');
});

?>
