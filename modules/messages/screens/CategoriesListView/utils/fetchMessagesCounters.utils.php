<?php

namespace UniEngine\Engine\Modules\Messages\Screens\CategoriesListView\Utils;

function fetchMessagesCounters ($props) {
    $readerId = $props['readerId'];

    $fetchQuery = (
        "SELECT " .
        "`type`, `read`, `Thread_ID`, `Thread_IsLast`, COUNT(*) AS `Count` " .
        "FROM {{table}} " .
        "WHERE " .
        "`id_owner` = {$readerId} AND " .
        "`deleted` = false " .
        "GROUP BY " .
        "`type`, `read`, `Thread_ID`, `Thread_IsLast` " .
        "ORDER BY " .
        "`Thread_IsLast` DESC " .
        ";"
    );

    return doquery($fetchQuery, 'messages');
}

?>
