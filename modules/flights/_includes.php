<?php

// TODO: Migrate to IIFE once PHP 5 support is removed
call_user_func(function () {
    global $_EnginePath;

    $includePath = $_EnginePath . 'modules/flights/';

    include($includePath . './enums/FleetDestructionReason.enum.php');
    include($includePath . './utils/calculations/calculateMoonCreationRoll.utils.php');
    include($includePath . './utils/calculations/calculatePostCombatMorale.utils.php');
    include($includePath . './utils/calculations/calculatePostCombatMoraleUpdates.utils.php');
    include($includePath . './utils/calculations/calculatePillageFactor.utils.php');
    include($includePath . './utils/calculations/calculatePillageStorage.utils.php');
    include($includePath . './utils/calculations/calculateResourcesLoss.utils.php');
    include($includePath . './utils/calculations/calculateUnitsRebuild.utils.php');
    include($includePath . './utils/factories/createCombatMessages.utils.php');
    include($includePath . './utils/factories/createCombatReportData.utils.php');
    include($includePath . './utils/factories/createCombatReportMoraleEntry.utils.php');
    include($includePath . './utils/factories/createFleetDevelopmentLogEntries.utils.php');
    include($includePath . './utils/factories/createFleetUpdateEntry.utils.php');
    include($includePath . './utils/fleetCache/updateGalaxyDebris.utils.php');
    include($includePath . './utils/fleetCache/morale.utils.php');
    include($includePath . './utils/fleetCache/updateUserStats.utils.php');
    include($includePath . './utils/helpers/getRandomExpeditionEvent.utils.php');
    include($includePath . './utils/helpers/hasLostAnyDefenseSystem.utils.php');
    include($includePath . './utils/initializers/combatUserDetails.utils.php');
    include($includePath . './utils/initializers/technologies.utils.php');
    include($includePath . './utils/initializers/userStats.utils.php');
    include($includePath . './utils/missions/expeditions/createEventMessage.utils.php');
    include($includePath . './utils/modifiers/calculateMoraleModifiers.utils.php');
    include($includePath . './utils/missions.utils.php');

});

?>
