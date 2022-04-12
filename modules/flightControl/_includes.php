<?php

// TODO: Migrate to IIFE once PHP 5 support is removed
call_user_func(function () {
    global $_EnginePath;

    $includePath = $_EnginePath . 'modules/flightControl/';

    include($includePath . './components/RetreatInfoBox/RetreatInfoBox.component.php');

    include($includePath . './utils/factories/createAlertFiltersSearchParams.factory.php');
    include($includePath . './utils/factories/createFleetDevLogEntry.factory.php');
    include($includePath . './utils/fetchers/fetchActiveSmartFleetsBlockadeEntries.fetcher.php');
    include($includePath . './utils/fetchers/fetchBashValidatorFlightLogEntries.fetcher.php');
    include($includePath . './utils/fetchers/fetchPlanetOwnerDetails.fetcher.php');
    include($includePath . './utils/fetchers/fetchTargetGalaxyDetails.fetcher.php');
    include($includePath . './utils/helpers/getAvailableHoldTimes.helper.php');
    include($includePath . './utils/helpers/getAvailableSpeeds.helper.php');
    include($includePath . './utils/helpers/getFleetsInFlightCounters.helper.php');
    include($includePath . './utils/helpers/getFleetUnionJoinData.helper.php');
    include($includePath . './utils/helpers/getFlightParams.helper.php');
    include($includePath . './utils/helpers/getTargetInfo.helper.php');
    include($includePath . './utils/helpers/getUserExpeditionSlotsCount.helper.php');
    include($includePath . './utils/helpers/getUserFleetSlotsCount.helper.php');
    include($includePath . './utils/helpers/getValidMissionTypes.helper.php');
    include($includePath . './utils/helpers/noobProtection.helper.php');
    include($includePath . './utils/updaters/fleetArchiveACSEntries.updaters.php');
    include($includePath . './utils/updaters/fleetArchiveEntryPersist.updaters.php');
    include($includePath . './utils/updaters/fleetPersist.updaters.php');
    include($includePath . './utils/validators/bashLimit.validator.php');
    include($includePath . './utils/validators/fleetArray.validator.php');
    include($includePath . './utils/validators/joinUnion.validator.php');
    include($includePath . './utils/validators/missionHold.validator.php');
    include($includePath . './utils/validators/noobProtection.validator.php');
    include($includePath . './utils/validators/quantumGate.validator.php');
    include($includePath . './utils/validators/smartFleetsBlockadeState.validator.php');
    include($includePath . './utils/errors/bashLimit.utils.php');
    include($includePath . './utils/errors/fleetArray.utils.php');
    include($includePath . './utils/errors/joinUnion.utils.php');
    include($includePath . './utils/errors/noobProtection.utils.php');
    include($includePath . './utils/errors/quantumGate.utils.php');
    include($includePath . './utils/errors/smartFleetsBlockade.utils.php');

});

?>
