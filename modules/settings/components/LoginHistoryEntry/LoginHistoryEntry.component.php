<?php

namespace UniEngine\Engine\Modules\Settings\Components\LoginHistoryEntry;

use UniEngine\Engine\Includes\Helpers\Common\Collections;

/**
 * @param object $props
 * @param object $props['entryData']
 * @param string $props['userLastIp']
 * @param number $props['currentTimestamp']
 *
 * @return object $result
 * @return string $result['componentHTML']
 */
function render($props) {
    global $_Lang;

    $entryData = $props['entryData'];
    $userLastIp = $props['userLastIp'];
    $currentTimestamp = $props['currentTimestamp'];

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $tplBodyCache = [
        'body' => $localTemplateLoader('body'),
    ];

    $isSuccessfulLogin = ($entryData['State'] === true);
    $isSameIpAsLastSeen = ($entryData['IP'] == $userLastIp);

    $dateColorClassNames = Collections\compact([
        (
            (
                !$isSuccessfulLogin &&
                !$isSameIpAsLastSeen
            ) ?
                'red' :
                null
        )
    ]);
    $ipColorClassNames = Collections\compact([
        (
            $isSuccessfulLogin ?
                'lime' :
                null
        ),
        (
            (
                !$isSuccessfulLogin &&
                !$isSameIpAsLastSeen
            ) ?
                'red' :
                null
        )
    ]);
    $stateColorClassNames = Collections\compact([
        (
            (
                !$isSuccessfulLogin &&
                !$isSameIpAsLastSeen
            ) ?
                'red' :
                null
        ),
        (
            (
                !$isSuccessfulLogin &&
                $isSameIpAsLastSeen
            ) ?
                'orange' :
                null
        )
    ]);

    $dateColorClassName = reset($dateColorClassNames);
    $ipColorClassName = reset($ipColorClassNames);
    $stateColorClassName = reset($stateColorClassNames);

    $componentTplData = [
        'inject_dateColorClass'     => $dateColorClassName,
        'inject_ipColorClass'       => $ipColorClassName,
        'inject_stateColorClass'    => $stateColorClassName,
        'data_loginTimeAbsolute'    => prettyDate('d m Y, H:i:s', $entryData['Time'], 1),
        'data_loginTimeRelative'    => implode(
            ' ',
            [
                pretty_time($currentTimestamp - $entryData['Time'], true, 'D'),
                $_Lang['Logons_ago']
            ]
        ),
        'data_ip'                   => $entryData['IP'],
        'data_stateLabel'           => (
            $isSuccessfulLogin ?
                $_Lang['Logons_Success'] :
                $_Lang['Logons_Failed']
        ),
    ];

    $componentHTML = parsetemplate(
        $tplBodyCache['body'],
        $componentTplData
    );

    return [
        'componentHTML' => $componentHTML
    ];
}

?>
