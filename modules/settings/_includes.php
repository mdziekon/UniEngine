<?php

// TODO: Migrate to IIFE once PHP 5 support is removed
call_user_func(function () {
    global $_EnginePath;

    $includePath = $_EnginePath . 'modules/settings/';

    include($includePath . './components/LanguageSelectorList/LanguageSelectorList.component.php');
    include($includePath . './components/LoginHistoryEntry/LoginHistoryEntry.component.php');
    include($includePath . './components/QuickTransportPlanetsList/QuickTransportPlanetsList.component.php');

    include($includePath . './screens/InVacationMode/InVacationMode.screen.php');
    include($includePath . './screens/InVacationMode/InVacationMode.utils.php');
    include($includePath . './screens/UsernameChange/UsernameChange.screen.php');
    include($includePath . './screens/UsernameChange/UsernameChange.utils.php');

    include($includePath . './utils/content/prepareChangeProcessEmails.content.php');

    include($includePath . './utils/errorMappers/tryDeleteUserIgnoreEntries.errorMapper.php');
    include($includePath . './utils/errorMappers/tryEnableVacation.errorMapper.php');
    include($includePath . './utils/errorMappers/tryIgnoreUser.errorMapper.php');
    include($includePath . './utils/errorMappers/validatePasswordChange.errorMapper.php');
    include($includePath . './utils/errorMappers/validateEmailChange.errorMapper.php');
    include($includePath . './utils/errorMappers/validateUsernameChange.errorMapper.php');

    include($includePath . './utils/factories/createDevLogEntry.factory.php');

    include($includePath . './utils/helpers/getUsernameChangeCost.helper.php');
    include($includePath . './utils/helpers/getVacationEndTime.helper.php');
    include($includePath . './utils/helpers/parseLoginHistoryEntries.helper.php');
    include($includePath . './utils/helpers/tryDeleteUserIgnoreEntries.helper.php');
    include($includePath . './utils/helpers/tryEnableVacation.helper.php');
    include($includePath . './utils/helpers/tryIgnoreUser.helper.php');

    include($includePath . './utils/input/normalizeDeleteUserIgnoreEntries.input.php');

    include($includePath . './utils/queries/createEmailChangeProcessEntry.query.php');
    include($includePath . './utils/queries/createUserIgnoreEntry.query.php');
    include($includePath . './utils/queries/createUsernameChangeEntry.query.php');
    include($includePath . './utils/queries/deleteUserIgnoreEntries.query.php');
    include($includePath . './utils/queries/getAccountLoginHistory.query.php');
    include($includePath . './utils/queries/getMovingFleetsCount.query.php');
    include($includePath . './utils/queries/getUserIgnoreEntries.query.php');
    include($includePath . './utils/queries/getUserWithEmailAddress.query.php');
    include($includePath . './utils/queries/updateUserOnUsernameChange.query.php');
    include($includePath . './utils/queries/updateUserOnVacationFinish.query.php');
    include($includePath . './utils/queries/updateUserPlanetsOnVacationFinish.query.php');

    include($includePath . './utils/validators/validatePasswordChange.validator.php');
    include($includePath . './utils/validators/validateEmailChange.validator.php');
    include($includePath . './utils/validators/validateResourcesOrdering.validator.php');
    include($includePath . './utils/validators/validateUsernameChange.validator.php');

});

?>
