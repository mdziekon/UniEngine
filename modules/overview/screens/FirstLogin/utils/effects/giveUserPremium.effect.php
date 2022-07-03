<?php

namespace UniEngine\Engine\Modules\Overview\Screens\FirstLogin\Utils\Effects;

/**
 * Give Free ProAccount for 7 days
 *
 * @param array $params
 * @param number $params['userId']
 * @param number $params['currentTimestamp']
 */
function giveUserPremium($props) {
    $userId = $props['userId'];
    $currentTimestamp = $props['currentTimestamp'];

    $itemId = '11';

    $insertPremiumEntryQuery = (
        "INSERT INTO {{table}} " .
        "VALUES " .
        "(NULL, {$userId}, {$currentTimestamp}, 0, 0, {$itemId}, 0) " .
        ";"
    );

    doquery($insertPremiumEntryQuery, 'premium_free');
}

?>
