<?php

function ShowTopNavigationBar(&$user, $planet) {
    global $_Lang, $_SkinPath;

    if (!$user || !$planet) {
        return;
    }

    // Update Planet Resources
    PlanetResourceUpdate($user, $planet, time());

    $productionLevel = getPlanetsProductionEfficiency(
        $planet,
        $user,
        [
            'isVacationCheckEnabled' => true
        ]
    );

    $templateDetails = array_merge(
        [
            'skinpath' => $_SkinPath,
            'image' => $planet['image']
        ],
        $_Lang,
        [
            'PHPInject_commonJS_html' => buildCommonJSInjectionHTML(),

            'PHPInject_isOnVacation' => (isOnVacation($user) ? 'true' : 'false'),
        ],
        _createPlanetsSelectorTplData($user, $planet),
        _createPlanetsEnergyStatusDetailsTplData($planet),
        _createResourceStateDetailsTplData(
            'metal',
            $planet,
            [
                'productionLevel' => $productionLevel
            ]
        ),
        _createResourceStateDetailsTplData(
            'crystal',
            $planet,
            [
                'productionLevel' => $productionLevel
            ]
        ),
        _createResourceStateDetailsTplData(
            'deuterium',
            $planet,
            [
                'productionLevel' => $productionLevel
            ]
        ),
        _createPremiumResourceCounterTplData($user),
        _createUnreadMessagesCounterTplData($user['id'])
    );

    $templateBody = gettemplate('topnav');
    $componentBody = parsetemplate($templateBody, $templateDetails);

    return $componentBody;
}

function _createPlanetsSelectorTplData(&$user, &$planet) {
    global $_Lang, $_GET;

    $tplData = [];

    $SQLResult_ThisUsersPlanets = SortUserPlanets($user);

    $OtherType_ID = 0;

    $isMoonsSortingEnabled = ($user['planet_sort_moons'] == 1);
    $currentSelectionID = $user['current_planet'];

    // Capture any important possibly present query attributes from current page
    // and re-apply them to the planet changing request query
    $capturedQueryParams = [];

    if (!empty($_GET['mode'])) {
        $capturedQueryParams['mode'] = $_GET['mode'];
    }

    $entriesList = [];
    $entriesByPosition = [];

    while ($thisEntry = $SQLResult_ThisUsersPlanets->fetch_assoc()) {
        if (
            $thisEntry['galaxy'] == $planet['galaxy'] &&
            $thisEntry['system'] == $planet['system'] &&
            $thisEntry['planet'] == $planet['planet'] &&
            $thisEntry['id'] != $planet['id']
        ) {
            $OtherType_ID = $thisEntry['id'];
        }

        if (!$isMoonsSortingEnabled) {
            $entriesList[] = $thisEntry;

            continue;
        }

        $entryPosition = "{$thisEntry['galaxy']}:{$thisEntry['system']}:{$thisEntry['planet']}";

        if (!isset($entriesByPosition[$entryPosition])) {
            $entriesByPosition[$entryPosition] = [];
        }

        $entriesByPosition[$entryPosition][] = $thisEntry;
    }

    foreach ($entriesByPosition as $entryPosition => $entries) {
        usort($entries, function ($left, $right) {
            return (
                (intval($left['planet_type']) < intval($right['planet_type'])) ?
                -1 :
                1
            );
        });

        foreach ($entries as $entry) {
            $entriesList[] = $entry;
        }
    }

    $entriesList = array_map(function ($entry) use ($currentSelectionID, $capturedQueryParams, &$_Lang) {
        $isCurrentSelector = ($entry['id'] == $currentSelectionID);
        $isMoon = ($entry['planet_type'] == 3);

        $typeLabel = "";

        if ($isMoon) {
            $typeLabel = $_Lang['PlanetList_MoonChar'];
        }

        $entryPosition = "{$entry['galaxy']}:{$entry['system']}:{$entry['planet']}";
        $entryPositionDisplayValue = "[{$entryPosition}]";
        $entryTypeDisplayValue = (
            $typeLabel ?
            "[{$typeLabel}]" :
            ""
        );
        $entryLabel = "{$entry['name']} {$entryPositionDisplayValue} {$entryTypeDisplayValue} &nbsp;&nbsp;";

        $entryHTML = buildDOMElementHTML([
            'tagName' => 'option',
            'contentHTML' => $entryLabel,
            'attrs' => [
                'selected' => ($isCurrentSelector ? "selected" : null),
                'data-planet-id' => $entry['id'],
                'value' => buildHref([
                    'path' => '',
                    'query' => array_merge(
                        [
                            'cp' => $entry['id'],
                            're' => '0'
                        ],
                        $capturedQueryParams
                    )
                ])
            ]
        ]);

        return $entryHTML;
    }, $entriesList);

    $tplData['planetlist'] = implode("\n", $entriesList);

    if ($OtherType_ID > 0) {
        $tplData['Insert_TypeChange_ID'] = $OtherType_ID;
        if ($planet['planet_type'] == 1) {
            $tplData['Insert_TypeChange_Sign'] = $_Lang['PlanetList_TypeChange_Sign_M'];
            $tplData['Insert_TypeChange_Title'] = $_Lang['PlanetList_TypeChange_Title_M'];
        } else {
            $tplData['Insert_TypeChange_Sign'] = $_Lang['PlanetList_TypeChange_Sign_P'];
            $tplData['Insert_TypeChange_Title'] = $_Lang['PlanetList_TypeChange_Title_P'];
        }
    } else {
        $tplData['Insert_TypeChange_Hide'] = 'hide';
    }

    return $tplData;
}

function _createPlanetsEnergyStatusDetailsTplData(&$planet) {
    $tplData = [];

    $unusedEnergy = ($planet['energy_max'] + $planet['energy_used']);

    $tplData['PHPData_resource_energy_unused_html'] = colorizeString(
        prettyNumber($unusedEnergy),
        (
            ($unusedEnergy >= 0) ?
            "green" :
            "red"
        )
    );

    $tplData['PHPInject_energy_unused'] = $unusedEnergy;
    $tplData['PHPInject_energy_used'] = ($planet['energy_used'] * (-1));
    $tplData['PHPInject_energy_total'] = ($planet['energy_max']);

    return $tplData;
}

function _createResourceStateDetailsTplData($resourceKey, &$planet, $params) {
    $tplData = [];

    $productionLevel = $params['productionLevel'];

    $resourceAmount = $planet[$resourceKey];
    $resourceMaxStorage = $planet["{$resourceKey}_max"];
    $resourceMaxOverflowStorage = $resourceMaxStorage * MAX_OVERFLOW;

    $theoreticalResourceIncomePerSecond = calculateTotalTheoreticalResourceIncomePerSecond(
        $resourceKey,
        $planet
    );
    $realResourceIncomePerSecond = calculateTotalRealResourceIncomePerSecond([
        'theoreticalIncomePerSecond' => $theoreticalResourceIncomePerSecond,
        'productionLevel' => $productionLevel
    ]);
    $totalResourceIncomePerSecond = (
        $realResourceIncomePerSecond['production'] +
        (
            ($planet['planet_type'] == 1) ?
            $realResourceIncomePerSecond['base'] :
            0
        )
    );
    $totalResourceIncomePerHour = (
        $totalResourceIncomePerSecond *
        3600
    );

    $hasOverflownStorage = ($resourceAmount >= $resourceMaxStorage);

    $tplData["PHPInject_resource_{$resourceKey}_state_amount"] = $resourceAmount;
    $tplData["PHPInject_resource_{$resourceKey}_state_incomePerHour"] = $totalResourceIncomePerHour;
    $tplData["PHPInject_resource_{$resourceKey}_storage_maxCapacity"] = $resourceMaxStorage;
    $tplData["PHPInject_resource_{$resourceKey}_storage_overflowCapacity"] = $resourceMaxOverflowStorage;

    $tplData["PHPData_resource_{$resourceKey}_state_amount_value"] = prettyNumber($resourceAmount);
    $tplData["PHPData_resource_{$resourceKey}_storage_maxCapacity_value"] = prettyNumber($resourceMaxStorage);
    $tplData["PHPData_resource_{$resourceKey}_state_amount_color"] = getColorHTMLValue(
        (!$hasOverflownStorage ? 'green' : 'red')
    );

    return $tplData;
}

function _createPremiumResourceCounterTplData(&$user) {
    $tplData = [];

    $premiumResourceAmount = $user['darkEnergy'];

    $tplData['PHPData_premiumresource_darkenergy_amount_html'] = colorizeString(
        prettyNumber($premiumResourceAmount),
        (
            ($premiumResourceAmount > 0) ?
            'green' :
            'orange'
        )
    );

    return $tplData;
}

function _createUnreadMessagesCounterTplData($userID) {
    global $NewMSGCount;

    $tplData = [];

    $Query_MsgCount  = '';
    $Query_MsgCount .= "SELECT COUNT(*) AS `count` FROM {{table}} WHERE ";
    $Query_MsgCount .= "`id_owner` = {$userID} AND ";
    $Query_MsgCount .= "`deleted` = false AND ";
    $Query_MsgCount .= "`read` = false ";
    $Query_MsgCount .= "LIMIT 1;";

    $Result_MsgCount = doquery($Query_MsgCount, 'messages', true);

    $unreadMessagesCount = $Result_MsgCount['count'];

    if ($unreadMessagesCount <= 0) {
        $tplData['PHPData_messages_unread_amount_html'] = '0';

        return $tplData;
    }

    $NewMSGCount = $unreadMessagesCount;

    $html_messagesLink = buildLinkHTML([
        'href' => 'messages.php',
        'text' => prettyNumber($unreadMessagesCount)
    ]);

    $tplData['PHPData_messages_unread_amount_html'] = "[ {$html_messagesLink} ]";

    return $tplData;
}

?>
