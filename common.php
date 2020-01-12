<?php

include('includes/phpBench.php'); $_BenchTool = new phpBench();
if(!empty($_BenchTool)){ $_BenchTool->simpleCountStart(false, 'telemetry__c'); }

ini_set('default_charset', 'UTF-8');

$_GameConfig = [];
$_User = [];
$_Lang = [];
$_DBLink = '';
$Common_TimeNow = time();

include($_EnginePath . 'common.minimal.php');

if(!empty($_BenchTool)){ $_BenchTool->simpleCountStart(false, 'telemetry__c_maininc'); }

include($_EnginePath.'includes/constants.php');

if (defined('INSTALL_NOTDONE')) {
    header('Location: ./install/');
    die();
}

include($_EnginePath.'common/_includes.php');
include($_EnginePath.'includes/functions.php');
include($_EnginePath.'includes/unlocalised.php');
include($_EnginePath.'includes/helpers/_includes.php');
include($_EnginePath.'includes/ingamefunctions.php');
include($_EnginePath.'class/UniEngine_Cache.class.php');

use UniEngine\Engine\Includes\Helpers\Users;

$_MemCache = new UniEngine_Cache();

$_POST = secureUserInput($_POST);
$_GET = secureUserInput($_GET);

include($_EnginePath.'includes/vars.php');
include($_EnginePath.'includes/db.php');
include($_EnginePath.'includes/strings.php');

if(!empty($_BenchTool)){ $_BenchTool->simpleCountStop(); }

include($_EnginePath . 'includes/per_module/common/_includes.php');

// Load game configuration
$_GameConfig = loadGameConfig([
    'cache' => &$_MemCache
]);

if (!defined('UEC_INLOGIN')) {
    $_User = CheckUserSession();
}

$_SkinPath = getSkinPath([
    'user' => &$_User,
    'enginePath' => $_EnginePath
]);

includeLang('tech');
includeLang('system');

if (isGameClosed($_GameConfig)) {
    $_DontShowMenus = true;

    if (
        isset($_CommonSettings['gamedisable_callback']) &&
        is_callable($_CommonSettings['gamedisable_callback'])
    ) {
        $_CommonSettings['gamedisable_callback']();
    }

    message(getGameCloseReason($_GameConfig), $_GameConfig['game_name']);
}

if (!isset($_SetAccessLogPath)) {
    $_SetAccessLogPath = '';
}
if (!isset($_SetAccessLogPreFilename)) {
    $_SetAccessLogPreFilename = '';
}
CreateAccessLog($_SetAccessLogPath, $_SetAccessLogPreFilename);

if (isIPBanned(Users\Session\getCurrentIP(), $_GameConfig)) {
    message($_Lang['Game_blocked_for_this_IP'], $_GameConfig['game_name']);
}

if(isLogged()) {
    $userIPChangeCheckResult = handleUserIPChangeCheck($_User);
    $isIPandUALogRefreshRequired = isIPandUALogRefreshRequired(
        $_User,
        [ 'timestamp' => $Common_TimeNow ]
    );

    if (
        $isIPandUALogRefreshRequired ||
        $userIPChangeCheckResult['isIPDifferent']
    ) {
        include("{$_EnginePath}includes/functions/IPandUA_Logger.php");
        IPandUA_Logger($_User);
    }

    // FIXME: try to prevent "on login, but IP changed" kicks
    if ($userIPChangeCheckResult['isKickRequired']) {
        header('Location: logout.php?badip=1');
        safeDie();
    }

    if (!isGameStartTimeReached($Common_TimeNow)) {
        $serverStartMessage = sprintf(
            $_Lang['ServerStart_NotReached'],
            prettyDate('d m Y', SERVER_MAINOPEN_TSTAMP, 1),
            date('H:i:s', SERVER_MAINOPEN_TSTAMP)
        );

        message($serverStartMessage, $_Lang['Title_System']);
    }

    $isUserBlockedByActivationRequirement = isUserBlockedByActivationRequirement(
        $_User,
        [ 'timestamp' => $Common_TimeNow ]
    );

    if ($isUserBlockedByActivationRequirement) {
        $_DontShowMenus = true;
        message($_Lang['NonActiveBlock'], $_GameConfig['game_name']);
    }

    $userCookieBlockadeResult = handleUserBlockadeByCookie(
        $_User,
        [ 'timestamp' => $Common_TimeNow ]
    );

    if ($userCookieBlockadeResult) {
        $_DontShowMenus = true;
        message($_Lang['GameBlock_CookieStyle'], $_GameConfig['game_name']);
    }

    $userKickCheckResult = handleUserKick(
        $_User,
        [ 'timestamp' => $Common_TimeNow ]
    );

    if ($userKickCheckResult) {
        header('Location: logout.php?kicked=1');
        safeDie();
    }

    // --- Handle Tasks ---
    if (!isset($_UseMinimalCommon) || $_UseMinimalCommon !== true) {
        if (!isset($_DontShowMenus) || $_DontShowMenus !== true) {
            $handleTasksResult = parseCompletedTasks($_User);

            // Dispay the infobox
            if ($handleTasksResult['completedTasks'] > 0) {
                $tasksInfobox_html = prepareTasksInfoboxHTML($handleTasksResult);

                GlobalTemplate_AppendToTaskBox($tasksInfobox_html);
            }

            // Apply updates on the DB and global vars
            if ($handleTasksResult['completedTasks'] > 0) {
                $taskUpdatesApplicationResult = applyTaskUpdates(
                    $handleTasksResult['postTaskDataUpdates'],
                    [
                        'unixTimestamp' => $Common_TimeNow,
                        'user' => $_User
                    ]
                );

                foreach ($taskUpdatesApplicationResult['devlogEntries'] as $entry) {
                    $UserDev_Log[] = $entry;
                }
                foreach ($taskUpdatesApplicationResult['userUpdatedEntries'] as $entry) {
                    $_User[$entry['key']] += $entry['value'];
                }
            }
        }
    }
    // --- Handling Tasks ends here ---

    if (!isset($_AllowInVacationMode) || $_AllowInVacationMode != true) {
        // If this place do not allow User to be in VacationMode, show him a message if it's necessary
        if (isOnVacation()) {
            $vacationModeMessageHTML = prepareVacationModeMessageHTML(
                $_User,
                [ 'timestamp' => $Common_TimeNow ]
            );

            display(
                $vacationModeMessageHTML,
                $_Lang['Vacation'],
                false
            );

            die();
        }
    }

    if (!isset($_UseMinimalCommon) || $_UseMinimalCommon !== true) {
        try {
            // Change Planet (if user wants to do this)
            $planetChangeID = getPlanetChangeRequestedID($_GET);

            if ($planetChangeID) {
                SetSelectedPlanet($_User, $planetChangeID);
            }

            $_Planet = fetchCurrentPlanetData($_User);
            $_GalaxyRow = fetchGalaxyData($_Planet);

            if (
                !isset($_BlockFleetHandler) ||
                $_BlockFleetHandler !== true
            ) {
                $FleetHandlerReturn = FlyingFleetHandler($_Planet);
                if (
                    isset($FleetHandlerReturn['ThisMoonDestroyed']) &&
                    $FleetHandlerReturn['ThisMoonDestroyed']
                ) {
                    // Redirect User to Planet (from Destroyed Moon)
                    $motherPlanetID = $_User['id_planet'];

                    SetSelectedPlanet($_User, $motherPlanetID);

                    $_Planet = fetchCurrentPlanetData($_User);

                    if ($_GalaxyRow['id_planet'] != $_Planet['id']) {
                        $_GalaxyRow = fetchGalaxyData($_Planet);
                    }
                }
            }
        } catch (UniEnginePlanetDataFetchException $error) {
            message($_Lang['FatalError_PlanetRowEmpty'], 'FatalError');

            die();
        }

        if (!isset($_DontForceRulesAcceptance) || $_DontForceRulesAcceptance !== true) {
            if (isRulesAcceptanceRequired($_User, $_GameConfig)) {
                if (
                    isset($_DontShowRulesBox) &&
                    $_DontShowRulesBox === true
                ) {
                    message($_Lang['RulesAcceptBox_CantUseFunction'], $_Lang['SystemInfo']);

                    die();
                }

                if (
                    !defined("IN_RULES") ||
                    IN_RULES !== true
                ) {
                    header('Location: rules.php');
                    safeDie();
                }
            }
        }

        if (
            (!isset($_DontCheckPolls) || $_DontCheckPolls !== true) &&
            isPollsCheckRequired($_User, [ 'timestamp' => $Common_TimeNow ])
        ) {
            $pollsCount = fetchObligatoryPollsCount($_User['id']);

            if ($pollsCount > 0) {
                message(sprintf($_Lang['YouHaveToVoteInSurveys'], $pollsCount), $_Lang['SystemInfo'], 'polls.php', 10);
            }
        }
    }
} else {
    $_DontShowMenus = true;
}

if(!empty($_BenchTool)){ $_BenchTool->simpleCountStop(); }

?>
