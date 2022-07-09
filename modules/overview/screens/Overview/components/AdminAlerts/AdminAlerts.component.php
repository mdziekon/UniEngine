<?php

namespace UniEngine\Engine\Modules\Overview\Screens\Overview\Components\AdminAlerts;

/**
 * @param array $props
 * @param arrayRef $props['user']
 */
function render($props) {
    global $_Lang;

    $hasAccess = CheckAuth(
        'supportadmin',
        AUTHCHECK_NORMAL,
        $props['user']
    );

    if (!$hasAccess) {
        return [
            'componentHTML' => '',
        ];
    }

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $getAlertsCountsQuery = (
        (
            "SELECT " .
            "COUNT(*) AS `count`, " .
            "'reports' AS `type` " .
            "FROM `{{prefix}}reports` " .
            "WHERE " .
            "`status` = 0 "
        ) .
        "UNION " .
        (
            "SELECT " .
            "COUNT(*) AS `count`, " .
            "'declarations' AS `type` " .
            "FROM `{{prefix}}declarations` " .
            "WHERE " .
            "`status` = 0 "
        ) .
        "UNION " .
        (
            "SELECT " .
            "COUNT(*) AS `count`, " .
            "'system_alerts' AS `type` " .
            "FROM `{{prefix}}system_alerts` " .
            "WHERE " .
            "`status` = 0 "
        )
    );
    $getAlertsCountsResult = doquery($getAlertsCountsQuery, '');

    $alertsCounts = mapQueryResults($getAlertsCountsResult, function ($counter) {
        return $counter;
    });
    $alertsCounts = object_map($alertsCounts, function ($counter) {
        return [
            $counter['count'],
            $counter['type']
        ];
    });
    $totalAlertsCount = array_sum($alertsCounts);

    if ($totalAlertsCount <= 0) {
        return [
            'componentHTML' => '',
        ];
    }

    $tplBodyParams = [
        'content' => sprintf(
            $_Lang['AdminAlertsBox'],
            $alertsCounts['reports'],
            $alertsCounts['declarations'],
            $alertsCounts['system_alerts']
        ),
    ];
    $tplBodyParams = array_merge($_Lang, $tplBodyParams);

    $componentHTML = parsetemplate(
        $localTemplateLoader('body'),
        $tplBodyParams
    );

    return [
        'componentHTML' => $componentHTML,
    ];
}

?>
