<?php

namespace UniEngine\Engine\Modules\Overview\Screens\FirstLogin\Utils\Effects;

/**
 * @param array $params
 * @param number $params['userId']
 */
function createUserDevLogDump($props) {
    global $_EnginePath, $InnerUIDSet, $SkipDumpMsg;
    // TODO: This should be imported in the script, not here
    global $_Vars_ElementCategories, $_Vars_GameElements;

    $userId = $props['userId'];

    define('IN_USERFIRSTLOGIN', true);
    $InnerUIDSet = $userId;
    $SkipDumpMsg = true;

    include("{$_EnginePath}admin/scripts/script.createUserDevDump.php");
}

?>
