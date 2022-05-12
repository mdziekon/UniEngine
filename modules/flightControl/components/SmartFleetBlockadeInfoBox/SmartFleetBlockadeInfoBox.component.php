<?php

namespace UniEngine\Engine\Modules\FlightControl\Components\SmartFleetBlockadeInfoBox;

use UniEngine\Engine\Modules\FlightControl\Components\SmartFleetBlockadeInfoBox;

//  Returns: Object
//      - componentHTML (String)
//
function render () {
    $currentTimestamp = time();
    $mostRecentBlockadeEntry = SmartFleetBlockadeInfoBox\Utils\fetchMostRecentBlockadeEntry();

    if (
        !$mostRecentBlockadeEntry ||
        $mostRecentBlockadeEntry['EndTime'] <= $currentTimestamp
    ) {
        return [
            'componentHTML' => '',
        ];
    }

    $lang = includeLang('sfbInfos', true);

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);
    $tplBodyCache = [
        'body' => $localTemplateLoader('body'),
    ];

    $tplProps = [
        '_Width' => 750,
        '_MarginBottom' => 10,
        '_Text' => sprintf(
            $lang['sfb_GlobalText'],
            prettyDate('d m Y', $mostRecentBlockadeEntry['EndTime'], 1),
            date('H:i:s', $mostRecentBlockadeEntry['EndTime']),
            SmartFleetBlockadeInfoBox\Utils\getMissionsInfo($mostRecentBlockadeEntry, $lang),
            SmartFleetBlockadeInfoBox\Utils\getReason($mostRecentBlockadeEntry, $lang)
        ),
    ];

    $componentHTML = parsetemplate($tplBodyCache['body'], $tplProps);

    return [
        'componentHTML' => $componentHTML
    ];
}

?>
