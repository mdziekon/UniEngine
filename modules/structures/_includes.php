<?php

$includePath = $_EnginePath . 'modules/structures/';

include($includePath . './input/userCommands.php');
include($includePath . './screens/StructuresListPage/StructuresListPage.php');
include($includePath . './screens/StructuresListPage/LegacyElementListItem/LegacyElementListItem.component.php');
include($includePath . './screens/StructuresListPage/ModernQueue/ModernQueue.component.php');
include($includePath . './screens/StructuresListPage/ModernElementListIcon/ModernElementListIcon.component.php');
include($includePath . './screens/StructuresListPage/ModernElementInfoCard/ModernElementInfoCard.component.php');

?>
