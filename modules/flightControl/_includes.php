<?php

// TODO: Migrate to IIFE once PHP 5 support is removed
call_user_func(function () {
    global $_EnginePath;

    $includePath = $_EnginePath . 'modules/flightControl/';

    include($includePath . './utils/fetchers/fetchPlanetOwnerDetails.fetcher.php');
    include($includePath . './utils/helpers/getAvailableHoldTimes.helper.php');
    include($includePath . './utils/helpers/getAvailableSpeeds.helper.php');
    include($includePath . './utils/helpers/getFleetUnionJoinData.helper.php');
    include($includePath . './utils/helpers/getUserExpeditionSlotsCount.helper.php');
    include($includePath . './utils/helpers/getUserFleetSlotsCount.helper.php');
    include($includePath . './utils/helpers/getValidMissionTypes.helper.php');
    include($includePath . './utils/validators/fleetArray.validator.php');
    include($includePath . './utils/validators/joinUnion.validator.php');
    include($includePath . './utils/validators/missionHold.validator.php');

});

?>
