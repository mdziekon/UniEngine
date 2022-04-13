<?php

namespace UniEngine\Engine\Modules\FlightControl\Components\SmartFleetBlockadeInfoBox\Utils;

function fetchMostRecentBlockadeEntry() {
    $fetchQuery = (
        "SELECT " .
        "`ID`, `EndTime`, `BlockMissions`, `DontBlockIfIdle`, `Reason` " .
        "FROM {{table}} " .
        "WHERE " .
        "`Type` = 1 AND  " .
        "`StartTime` <= UNIX_TIMESTAMP() AND " .
        "(`EndTime` > UNIX_TIMESTAMP() OR `PostEndTime` > UNIX_TIMESTAMP()) " .
        "ORDER BY  " .
        "`EndTime` DESC  " .
        "LIMIT 1 " .
        "; "
    );

    return doquery($fetchQuery, 'smart_fleet_blockade', true);
}

?>
