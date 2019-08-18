<?php

function ShowTopNavigationBar($CurrentUser, $CurrentPlanet)
{
    global $_Lang, $_GET, $_GameConfig, $_User, $_SkinPath, $NewMSGCount;

    if (!$CurrentUser || !$CurrentPlanet) {
        return;
    }

    $parse = $_Lang;
    $parse['skinpath'] = $_SkinPath;
    $parse['image'] = $CurrentPlanet['image'];

    // Update Planet Resources
    $IsOnVacation = isOnVacation($CurrentUser);
    PlanetResourceUpdate($CurrentUser, $CurrentPlanet, time());

    $parse = array_merge(
        $parse,
        _createPlanetsSelectorTplData($CurrentUser, $CurrentPlanet)
    );

    // Calculate resources for JS RealTime Counters

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

    // > Metal
    if($CurrentPlanet['metal'] >= $CurrentPlanet['metal_max'])
    {
        $parse['ShowCount_Metal'] = prettyNumber($CurrentPlanet['metal']);
        $parse['ShowStore_Metal'] = prettyNumber($CurrentPlanet['metal_max']);
        $parse['ShowCountColor_Metal'] = getColorHTMLValue('red');
        $parse['ShowStoreColor_Metal'] = getColorHTMLValue('red');
    }
    else
    {
        $parse['ShowCount_Metal'] = prettyNumber($CurrentPlanet['metal']);
        $parse['ShowStore_Metal'] = prettyNumber($CurrentPlanet['metal_max']);
        $parse['ShowCountColor_Metal'] = getColorHTMLValue('green');
        $parse['ShowStoreColor_Metal'] = getColorHTMLValue('green');
    }

    // > Crystal
    if($CurrentPlanet['crystal'] >= $CurrentPlanet['crystal_max'])
    {
        $parse['ShowCount_Crystal'] = prettyNumber($CurrentPlanet['crystal']);
        $parse['ShowStore_Crystal'] = prettyNumber($CurrentPlanet['crystal_max']);
        $parse['ShowCountColor_Crystal'] = getColorHTMLValue('red');
        $parse['ShowStoreColor_Crystal'] = getColorHTMLValue('red');
    }
    else
    {
        $parse['ShowCount_Crystal'] = prettyNumber($CurrentPlanet['crystal']);
        $parse['ShowStore_Crystal'] = prettyNumber($CurrentPlanet['crystal_max']);
        $parse['ShowCountColor_Crystal'] = getColorHTMLValue('green');
        $parse['ShowStoreColor_Crystal'] = getColorHTMLValue('green');
    }

    // > Deuterium
    if($CurrentPlanet['deuterium'] >= $CurrentPlanet['deuterium_max'])
    {
        $parse['ShowCount_Deuterium'] = prettyNumber($CurrentPlanet['deuterium']);
        $parse['ShowStore_Deuterium'] = prettyNumber($CurrentPlanet['deuterium_max']);
        $parse['ShowCountColor_Deuterium'] = getColorHTMLValue('red');
        $parse['ShowStoreColor_Deuterium'] = getColorHTMLValue('red');
    }
    else
    {
        $parse['ShowCount_Deuterium'] = prettyNumber($CurrentPlanet['deuterium']);
        $parse['ShowStore_Deuterium'] = prettyNumber($CurrentPlanet['deuterium_max']);
        $parse['ShowCountColor_Deuterium'] = getColorHTMLValue('green');
        $parse['ShowStoreColor_Deuterium'] = getColorHTMLValue('green');
    }

    // > JS Vars
    $parse['JSCount_Metal'] = $CurrentPlanet['metal'];
    $parse['JSCount_Crystal'] = $CurrentPlanet['crystal'];
    $parse['JSCount_Deuterium'] = $CurrentPlanet['deuterium'];
    $parse['JSStore_Metal'] = $CurrentPlanet['metal_max'];
    $parse['JSStore_Crystal'] = $CurrentPlanet['crystal_max'];
    $parse['JSStore_Deuterium'] = $CurrentPlanet['deuterium_max'];
    $parse['JSStoreOverflow_Metal'] = $CurrentPlanet['metal_max'] * MAX_OVERFLOW;
    $parse['JSStoreOverflow_Crystal'] = $CurrentPlanet['crystal_max'] * MAX_OVERFLOW;
    $parse['JSStoreOverflow_Deuterium'] = $CurrentPlanet['deuterium_max'] * MAX_OVERFLOW;

    // > Production Level
    if(!$IsOnVacation)
    {
        if($CurrentPlanet['energy_max'] == 0 AND abs($CurrentPlanet['energy_used']) > 0)
        {
            $production_level = 0;
            $CurrentPlanet['metal_perhour'] = $_GameConfig['metal_basic_income'];
            $CurrentPlanet['crystal_perhour'] = $_GameConfig['crystal_basic_income'];
            $CurrentPlanet['deuterium_perhour'] = $_GameConfig['deuterium_basic_income'];
        }
        else if($CurrentPlanet['energy_max'] > 0 AND abs($CurrentPlanet['energy_used']) > $CurrentPlanet['energy_max'])
        {
            $production_level = floor(($CurrentPlanet['energy_max'] * 100) / abs($CurrentPlanet['energy_used']));
        }
        else
        {
            $production_level = 100;
        }
        if($production_level > 100)
        {
            $production_level = 100;
        }
    }
    else
    {
        $production_level = 0;
    }

    // > Income
    $parse['JSPerHour_Metal']        = ($CurrentPlanet['metal_perhour'] * 0.01 * $production_level) + (($CurrentPlanet['planet_type'] == 1) ? ($_GameConfig['metal_basic_income'] * $_GameConfig['resource_multiplier']) : 0);
    $parse['JSPerHour_Crystal']        = ($CurrentPlanet['crystal_perhour'] * 0.01 * $production_level) + (($CurrentPlanet['planet_type'] == 1) ? ($_GameConfig['crystal_basic_income'] * $_GameConfig['resource_multiplier']) : 0);
    $parse['JSPerHour_Deuterium']    = ($CurrentPlanet['deuterium_perhour'] * 0.01 * $production_level) + (($CurrentPlanet['planet_type'] == 1) ? ($_GameConfig['deuterium_basic_income'] * $_GameConfig['resource_multiplier']) : 0);

    // > Create ToolTip Infos
    $parse['TipIncome_Metal'] = '('.(($parse['JSPerHour_Metal'] >= 0) ? '+' : '-').prettyNumber(abs(round($parse['JSPerHour_Metal']))).'/h)';
    $parse['TipIncome_Crystal'] = '('.(($parse['JSPerHour_Crystal'] >= 0) ? '+' : '-').prettyNumber(abs(round($parse['JSPerHour_Crystal']))).'/h)';
    $parse['TipIncome_Deuterium'] = '('.(($parse['JSPerHour_Deuterium'] >= 0) ? '+' : '-').prettyNumber(abs(round($parse['JSPerHour_Deuterium']))).'/h)';

    $IncomePerSecond['met'] = $parse['JSPerHour_Metal'] / 3600;
    $IncomePerSecond['cry'] = $parse['JSPerHour_Crystal'] / 3600;
    $IncomePerSecond['deu'] = $parse['JSPerHour_Deuterium'] / 3600;

    if($IncomePerSecond['met'] > 0)
    {
        $parse['Metal_full_time'] = (round($parse['JSStoreOverflow_Metal']) - round($CurrentPlanet['metal'])) / $IncomePerSecond['met'];
        if($parse['Metal_full_time'] > 0)
        {
            $parse['Metal_full_time'] = $parse['full_in'].' <span id="metal_fullstore_counter">'.pretty_time($parse['Metal_full_time']).'</span>';
        }
        else
        {
            $parse['Metal_full_time'] = '<span class="red">'.$parse['full'].'</span>';
        }
    }
    else
    {
        $parse['Metal_full_time'] = $_Lang['income_no_mine'];
    }
    if($IsOnVacation)
    {
        $parse['Metal_full_time'] = $_Lang['income_vacation'];
    }

    if($IncomePerSecond['cry'] > 0)
    {
        $parse['Crystal_full_time'] = (round($parse['JSStoreOverflow_Crystal']) - round($CurrentPlanet['crystal'])) / $IncomePerSecond['cry'];
        if($parse['Crystal_full_time'] > 0)
        {
            $parse['Crystal_full_time'] = $parse['full_in'].' <span id="crystal_fullstore_counter">'.pretty_time($parse['Crystal_full_time']).'</span>';
        }
        else
        {
            $parse['Crystal_full_time'] = '<span class="red">'.$parse['full'].'</span>';
        }
    }
    else
    {
        $parse['Crystal_full_time'] = $_Lang['income_no_mine'];
    }
    if($IsOnVacation)
    {
        $parse['Crystal_full_time'] = $_Lang['income_vacation'];
    }

    if($IncomePerSecond['deu'] > 0)
    {
        $parse['Deuterium_full_time'] = (round($parse['JSStoreOverflow_Deuterium']) - round($CurrentPlanet['deuterium'])) / $IncomePerSecond['deu'];
        if($parse['Deuterium_full_time'] > 0)
        {
            $parse['Deuterium_full_time'] = $parse['full_in'].' <span id="deuterium_fullstore_counter">'.pretty_time($parse['Deuterium_full_time']).'</span>';
        }
        else
        {
            $parse['Deuterium_full_time'] = '<span class="red">'.$parse['full'].'</span>';
        }
    }
    elseif($IncomePerSecond['deu'] < 0)
    {
        $parse['Deuterium_full_time'] = $_Lang['income_minus'];
    }
    else
    {
        $parse['Deuterium_full_time'] = $_Lang['income_no_mine'];
    }
    if($IsOnVacation)
    {
        $parse['Deuterium_full_time'] = $_Lang['income_vacation'];
    }

    // > Create ToolTip Storage Status
    if($CurrentPlanet['metal'] > $CurrentPlanet['metal_max'])
    {
        if($CurrentPlanet['metal'] == $parse['JSStoreOverflow_Metal'])
        {
            $parse['Metal_store_status'] = $parse['Store_status_Full'];
        }
        else
        {
            $parse['Metal_store_status'] = $parse['Store_status_Overload'];
        }
    }
    else
    {
        if($CurrentPlanet['metal'] > 0)
        {
            if($CurrentPlanet['metal'] >= ($CurrentPlanet['metal_max'] * 0.8))
            {
                $parse['Metal_store_status'] = $parse['Store_status_NearFull'];
            }
            else
            {
                $parse['Metal_store_status'] = $parse['Store_status_OK'];
            }
        }
        else
        {
            $parse['Metal_store_status'] = $parse['Store_status_Empty'];
        }
    }

    if($CurrentPlanet['crystal'] > $CurrentPlanet['crystal_max'])
    {
        if($CurrentPlanet['crystal'] == $parse['JSStoreOverflow_Crystal'])
        {
            $parse['Crystal_store_status'] = $parse['Store_status_Full'];
        }
        else
        {
            $parse['Crystal_store_status'] = $parse['Store_status_Overload'];
        }
    }
    else
    {
        if($CurrentPlanet['crystal'] > 0)
        {
            if($CurrentPlanet['crystal'] >= ($CurrentPlanet['crystal_max'] * 0.8))
            {
                $parse['Crystal_store_status'] = $parse['Store_status_NearFull'];
            }
            else
            {
                $parse['Crystal_store_status'] = $parse['Store_status_OK'];
            }
        }
        else
        {
            $parse['Crystal_store_status'] = $parse['Store_status_Empty'];
        }
    }

    if($CurrentPlanet['deuterium'] > $CurrentPlanet['deuterium_max'])
    {
        if($CurrentPlanet['deuterium'] == $parse['JSStoreOverflow_Deuterium'])
        {
            $parse['Deuterium_store_status'] = $parse['Store_status_Full'];
        }
        else
        {
            $parse['Deuterium_store_status'] = $parse['Store_status_Overload'];
        }
    }
    else
    {
        if($CurrentPlanet['metal'] > 0)
        {
            if($CurrentPlanet['deuterium'] >= ($CurrentPlanet['deuterium_max'] * 0.8))
            {
                $parse['Deuterium_store_status'] = $parse['Store_status_NearFull'];
            }
            else
            {
                $parse['Deuterium_store_status'] = $parse['Store_status_OK'];
            }
        }
        else
        {
            $parse['Deuterium_store_status'] = $parse['Store_status_Empty'];
        }
    }

    // Dark Energy
    if($_User['darkEnergy'] > 0)
    {
        $parse['ShowCount_DarkEnergy'] = '<span class="lime">'.prettyNumber($_User['darkEnergy']).'</span>';
    }
    else
    {
        $parse['ShowCount_DarkEnergy'] = '<span class="orange">'.$_User['darkEnergy'].'</span>';
    }

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

function _createUnreadMessagesCounterTplData($userID) {
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

    $html_messagesLink = buildLinkHTML([
        'href' => 'messages.php',
        'text' => prettyNumber($unreadMessagesCount)
    ]);

    $tplData['ShowCount_Messages'] = "[ {$html_messagesLink} ]";

    return $tplData;
}

?>
