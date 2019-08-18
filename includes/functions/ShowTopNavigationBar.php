<?php

function ShowTopNavigationBar($CurrentUser, $CurrentPlanet)
{
    global $_Lang, $_GET, $_User, $_SkinPath;

    if (!$CurrentUser || !$CurrentPlanet) {
        return;
    }

    $parse = $_Lang;
    $parse['skinpath'] = $_SkinPath;
    $parse['image'] = $CurrentPlanet['image'];

    // Update Planet Resources
    PlanetResourceUpdate($CurrentUser, $CurrentPlanet, time());

    $parse = array_merge(
        $parse,
        _createPlanetsSelectorTplData($CurrentUser, $CurrentPlanet)
    );

    // > Energy
    $EnergyFree = $CurrentPlanet['energy_max'] + $CurrentPlanet['energy_used'];
    $EnergyPretty = prettyNumber($EnergyFree);
    if($EnergyFree < 0)
    {
        $parse['Energy_free'] = colorRed($EnergyPretty);
    }
    else
    {
        $parse['Energy_free'] = colorGreen($EnergyPretty);
    }
    $parse['Energy_used'] = prettyNumber($CurrentPlanet['energy_max'] - $EnergyFree);
    $parse['Energy_total'] = prettyNumber($CurrentPlanet['energy_max']);

    // > Calculate incomes
    $productionLevel = getPlanetsProductionEfficiency(
        $CurrentPlanet,
        $CurrentUser,
        [
            'isVacationCheckEnabled' => true
        ]
    );

    foreach ([ 'metal', 'crystal', 'deuterium' ] as $resourceKey) {
        $parse = array_merge(
            $parse,
            _createResourceStateDetailsTplData(
                $resourceKey,
                $CurrentPlanet,
                $CurrentUser,
                [
                    'productionLevel' => $productionLevel
                ]
            )
        );
    }

    $parse = array_merge(
        $parse,
        _createPremiumResourceCounterTplData($CurrentUser)
    );

    $parse = array_merge(
        $parse,
        _createUnreadMessagesCounterTplData($CurrentUser['id'])
    );

    $TopBar = parsetemplate(gettemplate('topnav'), $parse);

    return $TopBar;
}

function _createPlanetsSelectorTplData($CurrentUser, $CurrentPlanet) {
    global $_Lang, $_GET;

    $tplData = [];

    $SQLResult_ThisUsersPlanets = SortUserPlanets($CurrentUser);

    $OtherType_ID = 0;

    $isMoonsSortingEnabled = ($CurrentUser['planet_sort_moons'] == 1);
    $currentSelectionID = $CurrentUser['current_planet'];

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
            $thisEntry['galaxy'] == $CurrentPlanet['galaxy'] &&
            $thisEntry['system'] == $CurrentPlanet['system'] &&
            $thisEntry['planet'] == $CurrentPlanet['planet'] &&
            $thisEntry['id'] != $CurrentPlanet['id']
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
        if ($CurrentPlanet['planet_type'] == 1) {
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

function _createResourceStateDetailsTplData($resourceKey, &$CurrentPlanet, &$CurrentUser, $params) {
    global $_Lang;

    $tplData = [];

    $productionLevel = $params['productionLevel'];

    $thresholds = [
        'capacityAlmostFull' => 0.8
    ];

    $resourceKeyCamelCase = ucfirst($resourceKey);

    $resourceAmount = $CurrentPlanet[$resourceKey];
    $resourceMaxStorage = $CurrentPlanet["{$resourceKey}_max"];
    $resourceMaxOverflowStorage = $resourceMaxStorage * MAX_OVERFLOW;

    $theoreticalResourceIncomePerSecond = calculateTotalTheoreticalResourceIncomePerSecond(
        $resourceKey,
        $CurrentPlanet
    );
    $realResourceIncomePerSecond = calculateTotalRealResourceIncomePerSecond([
        'theoreticalIncomePerSecond' => $theoreticalResourceIncomePerSecond,
        'productionLevel' => $productionLevel
    ]);
    $totalResourceIncomePerSecond = (
        $realResourceIncomePerSecond['production'] +
        (
            ($CurrentPlanet['planet_type'] == 1) ?
            $realResourceIncomePerSecond['base'] :
            0
        )
    );
    $totalResourceIncomePerHour = (
        $totalResourceIncomePerSecond *
        3600
    );

    $hasOverflownStorage = ($resourceAmount >= $resourceMaxStorage);
    $hasReachedMaxCapacity = ($resourceAmount >= $resourceMaxOverflowStorage);
    $hasPositiveIncome = ($totalResourceIncomePerHour > 0);
    $hasNegativeIncome = ($totalResourceIncomePerHour < 0);

    $labelsIncomeSign = "";
    if ($hasPositiveIncome) {
        $labelsIncomeSign = "+";
    } else if ($hasNegativeIncome) {
        $labelsIncomeSign = "-";
    }

    $tplData["JSCount_{$resourceKeyCamelCase}"] = $resourceAmount;
    $tplData["JSStore_{$resourceKeyCamelCase}"] = $resourceMaxStorage;
    $tplData["JSStoreOverflow_{$resourceKeyCamelCase}"] = $resourceMaxOverflowStorage;
    $tplData["ShowCount_{$resourceKeyCamelCase}"] = prettyNumber($resourceAmount);
    $tplData["ShowStore_{$resourceKeyCamelCase}"] = prettyNumber($resourceMaxStorage);
    $tplData["ShowCountColor_{$resourceKeyCamelCase}"] = getColorHTMLValue(
        (!$hasOverflownStorage ? 'green' : 'red')
    );
    $tplData["ShowStoreColor_{$resourceKeyCamelCase}"] = getColorHTMLValue(
        (!$hasOverflownStorage ? 'green' : 'red')
    );
    $tplData["JSPerHour_{$resourceKeyCamelCase}"] = $totalResourceIncomePerHour;
    $tplData["TipIncome_{$resourceKeyCamelCase}"] = (
        '(' .
        $labelsIncomeSign .
        prettyNumber(abs(round($totalResourceIncomePerHour))) .
        '/h' .
        ')'
    );

    $tplKeys_FullTime = "{$resourceKeyCamelCase}_full_time";
    if ($hasPositiveIncome) {
        $storageFullIn = ceil(($resourceMaxOverflowStorage - $resourceAmount) / $totalResourceIncomePerSecond);

        if ($hasReachedMaxCapacity) {
            $tplData[$tplKeys_FullTime] = colorRed($_Lang['full']);
        } else {
            $tplData[$tplKeys_FullTime] = (
                $_Lang['full_in'] .
                ' ' .
                (
                    '<span id="' . $resourceKey . '_fullstore_counter">' .
                    pretty_time($storageFullIn) .
                    '</span>'
                )
            );
        }
    } else if ($hasNegativeIncome) {
        $tplData[$tplKeys_FullTime] = $_Lang['income_minus'];
    } else if (isOnVacation($CurrentUser)) {
        $tplData[$tplKeys_FullTime] = $_Lang['income_vacation'];
    } else {
        $tplData[$tplKeys_FullTime] = $_Lang['income_no_mine'];
    }

    $tplKeys_StoreStatus = "{$resourceKeyCamelCase}_store_status";
    if ($hasOverflownStorage) {
        if ($resourceMaxOverflowStorage > $resourceMaxStorage) {
            $tplData[$tplKeys_StoreStatus] = $_Lang['Store_status_Overload'];
        } else {
            $tplData[$tplKeys_StoreStatus] = $_Lang['Store_status_Full'];
        }
    } else {
        if ($resourceAmount <= 0) {
            $tplData[$tplKeys_StoreStatus] = $_Lang['Store_status_Empty'];
        } else if ($resourceAmount >= ($resourceMaxStorage * $thresholds['capacityAlmostFull'])) {
            $tplData[$tplKeys_StoreStatus] = $_Lang['Store_status_NearFull'];
        } else {
            $tplData[$tplKeys_StoreStatus] = $_Lang['Store_status_OK'];
        }
    }

    return $tplData;
}

function _createPremiumResourceCounterTplData($user) {
    $tplData = [];

    $premiumResourceAmount = $user['darkEnergy'];

    $tplData['ShowCount_DarkEnergy'] = colorizeString(
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
        $tplData['ShowCount_Messages'] = '0';

        return $tplData;
    }

    $NewMSGCount = $unreadMessagesCount;

    $html_messagesLink = buildLinkHTML([
        'href' => 'messages.php',
        'text' => prettyNumber($unreadMessagesCount)
    ]);

    $tplData['ShowCount_Messages'] = "[ {$html_messagesLink} ]";

    return $tplData;
}

?>
