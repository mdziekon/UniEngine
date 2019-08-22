<?php

define('INSIDE', true);
define('IN_ADMIN', true);

$_SetAccessLogPreFilename = 'admin/';
$_SetAccessLogPath = '../';
$_EnginePath = './../';

include($_EnginePath.'common.php');
include($_EnginePath.'includes/functions/GetElementRessources.php');

if(!CheckAuth('go'))
{
    message($_Lang['sys_noalloaw'], $_Lang['sys_noaccess']);
}

includeLang('admin/userdevscanner');

$AllowScan = false;
$Search = false;
$Hide = ' class="hide"';
$PermDiffSet = 5;
$PermDiff = (100 + $PermDiffSet) / 100;

function ResourceUpdate(&$CurrentPlanet, $CurrentUser, $StartTime, $EndTime) {
    global $_Vars_GameElements, $PremiumItemsArchive;

    if ($StartTime == 'LA') {
        $StartTime = $CurrentPlanet['last_update'];
    }

    $ProductionTime = $EndTime - $StartTime;

    if ($ProductionTime <= 0) {
        return false;
    }
    if ($CurrentPlanet['planet_type'] == 3) {
        return false;
    }
    if (empty($CurrentPlanet['planet_type'])) {
        $CurrentPlanet['planet_type'] = 3;

        return false;
    }

    $CurrentPlanet[$_Vars_GameElements[22]] = (
        isset($CurrentPlanet[$_Vars_GameElements[22]]) ?
        $CurrentPlanet[$_Vars_GameElements[22]] :
        0
    );
    $CurrentPlanet[$_Vars_GameElements[23]] = (
        isset($CurrentPlanet[$_Vars_GameElements[23]]) ?
        $CurrentPlanet[$_Vars_GameElements[23]] :
        0
    );
    $CurrentPlanet[$_Vars_GameElements[24]] = (
        isset($CurrentPlanet[$_Vars_GameElements[24]]) ?
        $CurrentPlanet[$_Vars_GameElements[24]] :
        0
    );

    // Calculate Place in Storages
    $totalCapacities = getPlanetTotalStorageCapacities($CurrentPlanet);

    foreach ($totalCapacities as $resourceKey => $resourceCapacity) {
        $CurrentPlanet["{$resourceKey}_max"] = $resourceCapacity;
    }

    $geologistTimelineSubperiods = [];
    $engineerTimelineSubperiods = [];

    if (!empty($PremiumItemsArchive[5])) {
        foreach ($PremiumItemsArchive[5] as $premiumItemData) {
            $subperiod = [
                'start' => $premiumItemData['start'],
                'end' => $premiumItemData['end'],
                'data' => [
                    'hasGeologist' => true
                ]
            ];

            if ($subperiod['start'] < $StartTime) {
                $subperiod['start'] = $StartTime;
            }
            if ($subperiod['end'] > $EndTime) {
                $subperiod['end'] = $EndTime;
            }

            $geologistTimelineSubperiods[] = $subperiod;
        }
    }
    if (!empty($PremiumItemsArchive[6])) {
        foreach ($PremiumItemsArchive[6] as $premiumItemData) {
            $subperiod = [
                'start' => $premiumItemData['start'],
                'end' => $premiumItemData['end'],
                'data' => [
                    'hasEngineer' => true
                ]
            ];

            if ($subperiod['start'] < $StartTime) {
                $subperiod['start'] = $StartTime;
            }
            if ($subperiod['end'] > $EndTime) {
                $subperiod['end'] = $EndTime;
            }

            $engineerTimelineSubperiods[] = $subperiod;
        }
    }

    $timeranges = mergeTimelines([
        createTimeline(
            [ 'start' => $StartTime, 'end' => $EndTime ],
            $geologistTimelineSubperiods
        ),
        createTimeline(
            [ 'start' => $StartTime, 'end' => $EndTime ],
            $engineerTimelineSubperiods
        )
    ]);

    $totalIncome = [
        'metal' => 0,
        'crystal' => 0,
        'deuterium' => 0
    ];

    foreach ($timeranges as $timerange) {
        $income = calculateTotalResourcesIncome(
            $CurrentPlanet,
            $CurrentUser,
            $timerange,
            [
                'isVacationCheckEnabled' => false
            ]
        );

        foreach ($income as $resourceKey => $resourceIncome) {
            $CurrentPlanet[$resourceKey] += $resourceIncome['income'];
            $totalIncome[$resourceKey] += $resourceIncome['income'];
        }
    }

    $CurrentPlanet['last_update'] = $EndTime;

    return [
        'MetalProduction' => $totalIncome['metal'],
        'CrystalProduction' => $totalIncome['crystal'],
        'DeuteriumProduction' => $totalIncome['deuterium'],
    ];
}

$UID = isset($_POST['uid']) ? $_POST['uid'] : 0;
$Username = isset($_POST['username']) ? $_POST['username'] : null;
if(!empty($UID) OR !empty($Username))
{
    $Search = true;
    if(!empty($UID))
    {
        $UID = intval($UID);
        if($UID > 0)
        {
            $WhereClausure = "`id` = {$UID}";
        }
    }
    if(!empty($Username) AND empty($WhereClausure))
    {
        if(preg_match(REGEXP_USERNAME_ABSOLUTE, $Username))
        {
            $WhereClausure = "`username` = '{$Username}'";
        }
    }
}

if($Search AND !empty($WhereClausure))
{
    $GetUser = doquery("SELECT * FROM {{table}} WHERE {$WhereClausure} LIMIT 1;", 'users', true);
    if($GetUser['id'] > 0)
    {
        $AllowScan = true;
    }
    else
    {
        $_Lang['Error_Found'] = $_Lang['Error_UserNoExist'];
    }
}
else
{
    if($Search)
    {
        $_Lang['Error_Found'] = $_Lang['Error_BadPost'];
    }
}

if($AllowScan)
{
    $BreakScan = false;

    $UserNewData = &$GetUser;
    $LoadLastDump = doquery("SELECT * FROM {{table}} WHERE `UserID` = {$GetUser['id']} LIMIT 1;", 'user_developmentdumps', true);
    if($LoadLastDump['UserID'] != $GetUser['id'])
    {
        $_Lang['Error_Found'] = $_Lang['Critical_NoDump'];
        $BreakScan = true;
    }
    if(!$BreakScan)
    {
        $LastDumpTimestamp = $LoadLastDump['Date'] - SERVER_MAINOPEN_TSTAMP;

        $SQLResult_GetLogs = doquery(
            "SELECT * FROM {{table}} WHERE `UserID` = {$GetUser['id']} ORDER BY `ID` ASC;",
            'user_developmentlog'
        );

        if($SQLResult_GetLogs->num_rows == 0)
        {
            $_Lang['Notice_Found'] = $_Lang['Notice_NoLogs'];
            $BreakScan = true;
        }
        if(!$BreakScan)
        {
            $ScanStartTime = microtime(true);

            $SQLResult_PlanetsNewData = doquery(
                "SELECT * FROM {{table}} WHERE `id_owner` = {$GetUser['id']};",
                'planets'
            );

            // TODO: Implement fleets scanning
            // $FleetsNewData = doquery(
            //     "SELECT * FROM {{table}} WHERE `fleet_owner` = {$GetUser['id']};",
            //     'fleets'
            // );

            $SQLResult_UserPremiumItems = doquery("SELECT * FROM {{table}} WHERE `UserID` = {$GetUser['id']} AND `Item` IN (5,6);", 'premiumpayments');

            if($SQLResult_UserPremiumItems->num_rows > 0)
            {
                while($PremiumItem = $SQLResult_UserPremiumItems->fetch_assoc())
                {
                    $Length = 14 * TIME_DAY;
                    $PremiumItemsArchive[$PremiumItem['Item']][] = array
                    (
                        'start' => $PremiumItem['Date'],
                        'end' => $PremiumItem['Date'] + $Length
                    );
                }
            }

            $PlanetsDumpData = json_decode($LoadLastDump['Planets'], true);
            foreach($PlanetsDumpData as $PlanetID => $PlanetData)
            {
                $PlanetsDumpData[$PlanetID]['id'] = $PlanetID;
                foreach($_Vars_ElementCategories as $reskey => $resvals)
                {
                    if(in_array($reskey, array('tech', 'buildOn', 'units')))
                    {
                        continue;
                    }
                    foreach($resvals as $resID)
                    {
                        if($reskey != 'prod')
                        {
                            $PlanetsDumpData[$PlanetID][$_Vars_GameElements[$resID]] = 0;
                        }
                        else
                        {
                            $PlanetsDumpData[$PlanetID][$_Vars_GameElements[$resID].'_workpercent'] = 0;
                        }
                    }
                }

                $Resources = explode(',', $PlanetData['res']);
                $PlanetData['b'] = isset($PlanetData['b']) ? explode(';', $PlanetData['b']) : array();
                $PlanetData['p'] = isset($PlanetData['p']) ? explode(';', $PlanetData['p']) : array();
                $PlanetData['f'] = isset($PlanetData['f']) ? explode(';', $PlanetData['f']) : array();

                foreach($PlanetData['b'] as $ElementData)
                {
                    $ElementData = explode(',', $ElementData);
                    $PlanetsDumpData[$PlanetID][$_Vars_GameElements[$ElementData[0]]] = $ElementData[1];
                }
                foreach($PlanetData['f'] as $ElementData)
                {
                    $ElementData = explode(',', $ElementData);
                    $PlanetsDumpData[$PlanetID][$_Vars_GameElements[$ElementData[0]]] = $ElementData[1];
                }
                foreach($PlanetData['p'] as $ElementData)
                {
                    $ElementData = explode(',', $ElementData);
                    $PlanetsDumpData[$PlanetID][$_Vars_GameElements[$ElementData[0]].'_workpercent'] = $ElementData[1];
                }

                if($Resources[0] > 0)
                {
                    $PlanetsDumpData[$PlanetID]['metal'] = $Resources[0];
                }
                else
                {
                    $PlanetsDumpData[$PlanetID]['metal'] = 0;
                }
                if($Resources[1] > 0)
                {
                    $PlanetsDumpData[$PlanetID]['crystal'] = $Resources[1];
                }
                else
                {
                    $PlanetsDumpData[$PlanetID]['crystal'] = 0;
                }
                if($Resources[2] > 0)
                {
                    $PlanetsDumpData[$PlanetID]['deuterium'] = $Resources[2];
                }
                else
                {
                    $PlanetsDumpData[$PlanetID]['deuterium'] = 0;
                }

                $PlanetsDumpData[$PlanetID]['last_update'] = $PlanetData['lu'];
                $PlanetsDumpData[$PlanetID]['planet_type'] = $PlanetData['pt'];
                $PlanetsDumpData[$PlanetID]['temp_max'] = $PlanetData['t'];
                $PlanetsDumpData[$PlanetID]['metal_max'] = (floor(BASE_STORAGE_SIZE * pow (1.7, $PlanetsDumpData[$PlanetID][$_Vars_GameElements[22]]))) * MAX_OVERFLOW;
                $PlanetsDumpData[$PlanetID]['crystal_max'] = (floor(BASE_STORAGE_SIZE * pow (1.7, $PlanetsDumpData[$PlanetID][$_Vars_GameElements[23]]))) * MAX_OVERFLOW;
                $PlanetsDumpData[$PlanetID]['deuterium_max'] = (floor(BASE_STORAGE_SIZE * pow (1.7, $PlanetsDumpData[$PlanetID][$_Vars_GameElements[24]]))) * MAX_OVERFLOW;
            }
            $UserData = $GetUser;
            foreach($_Vars_ElementCategories['tech'] as $TechID)
            {
                $UserData[$_Vars_GameElements[$TechID]] = 0;
            }
            $UserTechsDump = !empty($LoadLastDump['Techs']) ? explode(';', $LoadLastDump['Techs']) : array();
            foreach($UserTechsDump as $Exploded)
            {
                $Exploded = explode(',', $Exploded);
                $UserData[$_Vars_GameElements[$Exploded[0]]] = $Exploded[1];
            }

            $ScaningNo = 0;
            $ScanLog['LogScanned'] = 0;
            $ScanLog['ResUpdates'] = 0;

            $UsedResources = array
            (
                'metal' => 0,
                'crystal' => 0,
                'deuterium' => 0,
            );

            while($Log = $SQLResult_GetLogs->fetch_assoc())
            {
                $Log['Date'] += SERVER_MAINOPEN_TSTAMP;

                $ScaningNo += 1;

                $PreventResourceUpdate = false;
                $ResourcesChanged = false;
                $ResUpdateReturn = false;
                $ShipsChanged = false;
                $ChangedShipsTypes = array();
                $Needed = array
                (
                'metal' => 0,
                    'crystal' => 0,
                    'deuterium' => 0,
                );

                // Main Checking Part
                $Place = &$Log['Place'];
                if($Place == 1)
                {
                    // HandlePlanetQueue_StructuresSetNext [Remove Resources, Move Element in Structures Queue to first position]
                    $ResUpdateReturn = ResourceUpdate($PlanetsDumpData[$Log['PlanetID']], $UserData, 'LA', $Log['Date']);
                    if($ResUpdateReturn !== false)
                    {
                        $ScanLog['ResUpdates'] += 1;
                    }

                    $Needed = GetBuildingPrice($UserData, $PlanetsDumpData[$Log['PlanetID']], $Log['ElementID'], true, ($Log['Code'] == 1 ? false : true));

                    $UsedResources['metal'] += $Needed['metal'];
                    $UsedResources['crystal'] += $Needed['crystal'];
                    $UsedResources['deuterium'] += $Needed['deuterium'];
                    $PlanetsDumpData[$Log['PlanetID']]['metal'] -= $Needed['metal'];
                    $PlanetsDumpData[$Log['PlanetID']]['crystal'] -= $Needed['crystal'];
                    $PlanetsDumpData[$Log['PlanetID']]['deuterium'] -= $Needed['deuterium'];

                    $ResourcesChanged = $Log['PlanetID'];
                }
                else if($Place == 2)
                {
                    // CancelBuildingFromQueue [Restore Resources]
                    $ResUpdateReturn = ResourceUpdate($PlanetsDumpData[$Log['PlanetID']], $UserData, 'LA', $Log['Date']);
                    if($ResUpdateReturn !== false)
                    {
                        $ScanLog['ResUpdates'] += 1;
                    }
                    $Needed = GetBuildingPrice($UserData, $PlanetsDumpData[$Log['PlanetID']], $Log['ElementID'], true, ($Log['Code'] == 1 ? false : true));

                    $UsedResources['metal'] -= $Needed['metal'];
                    $UsedResources['crystal'] -= $Needed['crystal'];
                    $UsedResources['deuterium'] -= $Needed['deuterium'];
                    $PlanetsDumpData[$Log['PlanetID']]['metal'] += $Needed['metal'];
                    $PlanetsDumpData[$Log['PlanetID']]['crystal'] += $Needed['crystal'];
                    $PlanetsDumpData[$Log['PlanetID']]['deuterium'] += $Needed['deuterium'];

                    $ResourcesChanged = $Log['PlanetID'];
                }
                else if($Place == 3)
                {
                    // HandlePlanetQueue_OnStructureBuildEnd [Building has ended]

                    $ResUpdateReturn = ResourceUpdate($PlanetsDumpData[$Log['PlanetID']], $UserData, 'LA', $Log['Date']);
                    if($ResUpdateReturn !== false)
                    {
                        $ScanLog['ResUpdates'] += 1;
                    }

                    $PlanetsDumpData[$Log['PlanetID']][$_Vars_GameElements[$Log['ElementID']]] += ($Log['Code'] == 1 ? 1 : -1);

                    if($Log['ElementID'] == 22)
                    {
                        $PlanetsDumpData[$Log['PlanetID']]['metal_max'] = (floor(BASE_STORAGE_SIZE * pow (1.7, $PlanetsDumpData[$Log['PlanetID']][$_Vars_GameElements[22]]))) * MAX_OVERFLOW;
                    }
                    else if($Log['ElementID'] == 23)
                    {
                        $PlanetsDumpData[$Log['PlanetID']]['crystal_max'] = (floor(BASE_STORAGE_SIZE * pow (1.7, $PlanetsDumpData[$Log['PlanetID']][$_Vars_GameElements[23]]))) * MAX_OVERFLOW;
                    }
                    else if($Log['ElementID'] == 24)
                    {
                        $PlanetsDumpData[$Log['PlanetID']]['deuterium_max'] = (floor(BASE_STORAGE_SIZE * pow (1.7, $PlanetsDumpData[$Log['PlanetID']][$_Vars_GameElements[24]]))) * MAX_OVERFLOW;
                    }
                }
                else if($Place == 4)
                {
                    // On Code 1 - HandlePlanetQueue_TechnologySetNext [Remove Resources, Move Element in Technology Queue to first position]
                    // On Code 2 - TechQueue_Remove [Fall Back Resource Removal]
                    $ResUpdateReturn = ResourceUpdate($PlanetsDumpData[$Log['PlanetID']], $UserData, 'LA', $Log['Date']);
                    if($ResUpdateReturn !== false)
                    {
                        $ScanLog['ResUpdates'] += 1;
                    }

                    $Needed = GetBuildingPrice($UserData, $PlanetsDumpData[$Log['PlanetID']], $Log['ElementID']);
                    if($Log['Code'] == 2)
                    {
                        foreach($Needed as $Key => $Value)
                        {
                            $Needed[$Key] *= -1;
                        }
                    }

                    $UsedResources['metal'] += $Needed['metal'];
                    $UsedResources['crystal'] += $Needed['crystal'];
                    $UsedResources['deuterium'] += $Needed['deuterium'];
                    $PlanetsDumpData[$Log['PlanetID']]['metal'] -= $Needed['metal'];
                    $PlanetsDumpData[$Log['PlanetID']]['crystal'] -= $Needed['crystal'];
                    $PlanetsDumpData[$Log['PlanetID']]['deuterium'] -= $Needed['deuterium'];

                    $ResourcesChanged = $Log['PlanetID'];
                }
                else if($Place == 5)
                {
                    // HandlePlanetQueue_OnTechnologyEnd [Research has ended]
                    $UserData[$_Vars_GameElements[$Log['ElementID']]] += 1;

                    $PreventResourceUpdate = true;
                }
                else if($Place == 6)
                {
                    // FleetBuildingPage / ShipyardPage (Ships) [Remove Resources]
                    $ResUpdateReturn = ResourceUpdate($PlanetsDumpData[$Log['PlanetID']], $UserData, 'LA', $Log['Date']);
                    if($ResUpdateReturn !== false)
                    {
                        $ScanLog['ResUpdates'] += 1;
                    }

                    $Fleets = explode(';', $Log['AdditionalData']);
                    foreach($Fleets as $Ship)
                    {
                        $Ship = explode(',', $Ship);

                        $Temp = GetElementRessources($Ship[0], $Ship[1]);
                        $Needed['metal'] += $Temp['metal'];
                        $Needed['crystal'] += $Temp['crystal'];
                        $Needed['deuterium'] += $Temp['deuterium'];
                    }

                    $UsedResources['metal'] += $Needed['metal'];
                    $UsedResources['crystal'] += $Needed['crystal'];
                    $UsedResources['deuterium'] += $Needed['deuterium'];
                    $PlanetsDumpData[$Log['PlanetID']]['metal'] -= $Needed['metal'];
                    $PlanetsDumpData[$Log['PlanetID']]['crystal'] -= $Needed['crystal'];
                    $PlanetsDumpData[$Log['PlanetID']]['deuterium'] -= $Needed['deuterium'];

                    $ResourcesChanged = $Log['PlanetID'];
                }
                else if($Place == 7)
                {
                    // DefensesBuildingPage / ShipyardPage (Defense) [Remove Resources]
                    $ResUpdateReturn = ResourceUpdate($PlanetsDumpData[$Log['PlanetID']], $UserData, 'LA', $Log['Date']);
                    if($ResUpdateReturn !== false)
                    {
                        $ScanLog['ResUpdates'] += 1;
                    }

                    $Defs = explode(';', $Log['AdditionalData']);
                    foreach($Defs as $Def)
                    {
                        $Def = explode(',', $Def);

                        $Temp = GetElementRessources($Def[0], $Def[1]);
                        $Needed['metal'] += $Temp['metal'];
                        $Needed['crystal'] += $Temp['crystal'];
                        $Needed['deuterium'] += $Temp['deuterium'];
                    }

                    $UsedResources['metal'] += $Needed['metal'];
                    $UsedResources['crystal'] += $Needed['crystal'];
                    $UsedResources['deuterium'] += $Needed['deuterium'];
                    $PlanetsDumpData[$Log['PlanetID']]['metal'] -= $Needed['metal'];
                    $PlanetsDumpData[$Log['PlanetID']]['crystal'] -= $Needed['crystal'];
                    $PlanetsDumpData[$Log['PlanetID']]['deuterium'] -= $Needed['deuterium'];

                    $ResourcesChanged = $Log['PlanetID'];
                }
                else if($Place == 8)
                {
                    // HandlePlanetQueue / HandlePlanetUpdate / PlanetResourceUpdate (Shipyard Queue Change) [Insert Fleets & Defenses to PlanetRow]
                    $Builded = explode(';', $Log['AdditionalData']);

                    foreach($Builded as $Item)
                    {
                        $Item = explode(',', $Item);

                        $PlanetsDumpData[$Log['PlanetID']][$_Vars_GameElements[$Item[0]]] += $Item[1];
                    }
                }
                else if($Place == 9)
                {
                    // galaxyfleet.php [Remove Deuterium, Remove Ships]
                    $ResUpdateReturn = ResourceUpdate($PlanetsDumpData[$Log['PlanetID']], $UserData, 'LA', $Log['Date']);
                    if($ResUpdateReturn !== false)
                    {
                        $ScanLog['ResUpdates'] += 1;
                    }

                    $Fleet = explode(';', $Log['AdditionalData']);
                    foreach($Fleet as $Ship)
                    {
                        $Ship = explode(',', $Ship);

                        if($Ship[0] == 'F')
                        {
                            $UsedResources['deuterium'] += $Ship[1];
                            $PlanetsDumpData[$Log['PlanetID']]['deuterium'] -= $Ship[1];

                            $ResourcesChanged = $Log['PlanetID'];
                        }
                        else
                        {
                            $PlanetsDumpData[$Log['PlanetID']][$_Vars_GameElements[$Ship[0]]] -= $Ship[1];
                            $ChangedShipsTypes[] = $Ship[0];
                        }
                    }

                    $ShipsChanged = array('Where' => $Log['PlanetID'], 'Types' => $ChangedShipsTypes);
                }
                else if($Place == 10)
                {
                    // fleet3.php [Remove Resources, Remove Ships]

                    $ResUpdateReturn = ResourceUpdate($PlanetsDumpData[$Log['PlanetID']], $UserData, 'LA', $Log['Date']);
                    if($ResUpdateReturn !== false)
                    {
                        $ScanLog['ResUpdates'] += 1;
                    }

                    $Fleet = explode(';', $Log['AdditionalData']);
                    $Removed = array
                    (
                        'metal' => 0,
                        'crystal' => 0,
                        'deuterium' => 0,
                    );

                    foreach($Fleet as $Ship)
                    {
                        $Ship = explode(',', $Ship);

                        if($Ship[0] == 'F' OR $Ship[0] == 'D')
                        {
                            $UsedResources['deuterium'] += $Ship[1];
                            $PlanetsDumpData[$Log['PlanetID']]['deuterium'] -= $Ship[1];
                            $Removed['deuterium'] += $Ship[1];

                            $ResourcesChanged = $Log['PlanetID'];
                        }
                        else if($Ship[0] == 'M')
                        {
                            $UsedResources['metal'] += $Ship[1];
                            $PlanetsDumpData[$Log['PlanetID']]['metal'] -= $Ship[1];
                            $Removed['metal'] += $Ship[1];

                            $ResourcesChanged = $Log['PlanetID'];
                        }
                        else if($Ship[0] == 'C')
                        {
                            $UsedResources['crystal'] += $Ship[1];
                            $PlanetsDumpData[$Log['PlanetID']]['crystal'] -= $Ship[1];
                            $Removed['crystal'] += $Ship[1];

                            $ResourcesChanged = $Log['PlanetID'];
                        }
                        else
                        {
                            $PlanetsDumpData[$Log['PlanetID']][$_Vars_GameElements[$Ship[0]]] -= $Ship[1];
                            $ChangedShipsTypes[] = $Ship[0];
                        }
                    }

                    $ShipsChanged = array('Where' => $Log['PlanetID'], 'Types' => $ChangedShipsTypes);
                }
                else if($Place == 11)
                {
                    // sendmissiles.php
                    $ResUpdateReturn = ResourceUpdate($PlanetsDumpData[$Log['PlanetID']], $UserData, 'LA', $Log['Date']);
                    if($ResUpdateReturn !== false)
                    {
                        $ScanLog['ResUpdates'] += 1;
                    }

                    $Missiles = explode(',', $Log['AdditionalData']);
                    $PlanetsDumpData[$Log['PlanetID']][$_Vars_GameElements[503]] -= $Missiles[1];
                    $ChangedShipsTypes[] = 503;

                    $ShipsChanged = array('Where' => $Log['PlanetID'], 'Types' => $ChangedShipsTypes);
                }
                else if($Place == 12 OR $Place == 13 OR $Place == 15)
                {
                    // MissionCaseAttack.php (12) & MissionCaseGroupAttack.php (13) & MissionCaseDestruction.php (15)
                    if($Log['Code'] == 1)
                    {
                        // User is PlanetDefender
                        $ResUpdateReturn = ResourceUpdate($PlanetsDumpData[$Log['PlanetID']], $UserData, 'LA', $Log['Date']);
                        if($ResUpdateReturn !== false)
                        {
                            $ScanLog['ResUpdates'] += 1;
                        }

                        $Lost = explode(';', $Log['AdditionalData']);
                        foreach($Lost as $LostData)
                        {
                            $LostData = explode(',', $LostData);
                            if($LostData[0] == 'M')
                            {
                                $PlanetsDumpData[$Log['PlanetID']]['metal'] -= $LostData[1];

                                $ResourcesChanged = $Log['PlanetID'];
                            }
                            else if($LostData[0] == 'C')
                            {
                                $PlanetsDumpData[$Log['PlanetID']]['crystal'] -= $LostData[1];

                                $ResourcesChanged = $Log['PlanetID'];
                            }
                            else if($LostData[0] == 'D')
                            {
                                $PlanetsDumpData[$Log['PlanetID']]['deuterium'] -= $LostData[1];

                                $ResourcesChanged = $Log['PlanetID'];
                            }
                            else if($LostData[0] == 'L')
                            {
                                $PlanetsDumpData[$LostData[1]]['id'] = $LostData[1];
                                $PlanetsDumpData[$LostData[1]]['metal'] = 0;
                                $PlanetsDumpData[$LostData[1]]['crystal'] = 0;
                                $PlanetsDumpData[$LostData[1]]['deuterium'] = 0;
                                $PlanetsDumpData[$LostData[1]]['last_update'] = $Log['Date'];
                                $PlanetsDumpData[$LostData[1]]['planet_type'] = 3;
                            }
                            else
                            {
                                $PlanetsDumpData[$Log['PlanetID']][$_Vars_GameElements[$LostData[0]]] -= $LostData[1];
                                $ChangedShipsTypes[] = $LostData[0];

                                $ShipsChanged = true;
                            }
                        }

                        if($ShipsChanged)
                        {
                            $ShipsChanged = array('Where' => $Log['PlanetID'], 'Types' => $ChangedShipsTypes);
                        }

                        if($Place == 15 AND $Log['ElementID'] == 1)
                        {
                            $ShipsChanged = false;
                            $PreventResourceUpdate = true;
                            unset($PlanetsDumpData[$Log['PlanetID']]);
                        }
                    }
                    else if($Log['Code'] == 2)
                    {
                        // User is Attacker (Regular Attacker or ACS Leader)

                        $PreventResourceUpdate = true;
                    }
                    else if($Log['Code'] == 3)
                    {
                        // User is FriendlyDefender

                        $PreventResourceUpdate = true;
                    }
                    else if($Log['Code'] == 4)
                    {
                        // User is Additional Attacker (ACS Member) [only in 13!]

                        $PreventResourceUpdate = true;
                    }
                }
                else if($Place == 14)
                {
                    // MissionCaseColonisation.php
                    $ExplodeRes = explode(';', $Log['AdditionalData']);
                    foreach($ExplodeRes as $ExpRes)
                    {
                        $ExpRes = explode(',', $ExpRes);
                        if($ExpRes[0] == 'M')
                        {
                            $PlanetsDumpData[$Log['PlanetID']]['metal'] = $ExpRes[1];
                        }
                        else if($ExpRes[0] == 'C')
                        {
                            $PlanetsDumpData[$Log['PlanetID']]['crystal'] = $ExpRes[1];
                        }
                        else if($ExpRes[0] == 'D')
                        {
                            $PlanetsDumpData[$Log['PlanetID']]['deuterium'] = $ExpRes[1];
                        }
                        else if($ExpRes[0] == 'T')
                        {
                            $PlanetsDumpData[$Log['PlanetID']]['temp_max'] = $ExpRes[1];
                        }
                    }

                    foreach($_Vars_ElementCategories['prod'] as $ProdID)
                    {
                        $PlanetsDumpData[$Log['PlanetID']][$_Vars_GameElements[$ProdID].'_workpercent'] = 10;
                    }

                    $PlanetsDumpData[$Log['PlanetID']]['last_update'] = $Log['Date'];
                    $PlanetsDumpData[$Log['PlanetID']]['planet_type'] = 1;
                    $PlanetsDumpData[$Log['PlanetID']]['id'] = $Log['PlanetID'];

                    $PreventResourceUpdate = true;
                }
                else if($Place == 16)
                {
                    // MissionCaseMIP.php
                    $Lost = explode(';', $Log['AdditionalData']);
                    foreach($Lost as $LostData)
                    {
                        $LostData = explode(',', $LostData);
                        $PlanetsDumpData[$Log['PlanetID']][$_Vars_GameElements[$LostData[0]]] -= $LostData[1];
                        $ChangedShipsTypes[] = $LostData[0];
                    }

                    $ShipsChanged = array('Where' => $Log['PlanetID'], 'Types' => $ChangedShipsTypes);
                }
                else if($Place == 17)
                {
                    // MissionCaseRecycling.php

                    $PreventResourceUpdate = true;
                }
                else if($Place == 18)
                {
                    // MissionCaseSpy.php

                    $PreventResourceUpdate = true;
                }
                else if($Place == 19)
                {
                    // MissionCaseTransport.php

                    if(($Log['Code'] == 1 AND isset($PlanetsDumpData[$Log['PlanetID']])) OR $Log['Code'] == 2)
                    {
                        // Planet belongs to that user
                        $ExplodeRes = explode(';', $Log['AdditionalData']);
                        foreach($ExplodeRes as $ExpRes)
                        {
                            $ExpRes = explode(',', $ExpRes);
                            if($ExpRes[0] == 'M')
                            {
                                $PlanetsDumpData[$Log['PlanetID']]['metal'] += $ExpRes[1];
                            }
                            else if($ExpRes[0] == 'C')
                            {
                                $PlanetsDumpData[$Log['PlanetID']]['crystal'] += $ExpRes[1];
                            }
                            else if($ExpRes[0] == 'D')
                            {
                                $PlanetsDumpData[$Log['PlanetID']]['deuterium'] += $ExpRes[1];
                            }
                        }

                        $PreventResourceUpdate = true;
                    }
                    else
                    {
                        // Friendly Transport

                        $PreventResourceUpdate = true;
                    }
                }
                else if($Place == 20)
                {
                    // Place Holder for Expeditions (maybe)
                }
                else if($Place == 21)
                {
                    // RestoreFleetToPlanet.php
                    $ResUpdateReturn = ResourceUpdate($PlanetsDumpData[$Log['PlanetID']], $UserData, 'LA', $Log['Date']);
                    if($ResUpdateReturn !== false)
                    {
                        $ScanLog['ResUpdates'] += 1;
                    }

                    $Restore = explode(';', $Log['AdditionalData']);
                    foreach($Restore as $Restored)
                    {
                        $Restored = explode(',', $Restored);
                        if($Restored[0] == 'M')
                        {
                            $PlanetsDumpData[$Log['PlanetID']]['metal'] += $Restored[1];
                            $ResourcesChanged = $Log['PlanetID'];
                        }
                        else if($Restored[0] == 'C')
                        {
                            $PlanetsDumpData[$Log['PlanetID']]['crystal'] += $Restored[1];
                            $ResourcesChanged = $Log['PlanetID'];
                        }
                        else if($Restored[0] == 'D')
                        {
                            $PlanetsDumpData[$Log['PlanetID']]['deuterium'] += $Restored[1];
                            $ResourcesChanged = $Log['PlanetID'];
                        }
                        else
                        {
                            if (empty($PlanetsDumpData[$Log['PlanetID']][$_Vars_GameElements[$Restored[0]]])) {
                                $PlanetsDumpData[$Log['PlanetID']][$_Vars_GameElements[$Restored[0]]] = 0;
                            }

                            $PlanetsDumpData[$Log['PlanetID']][$_Vars_GameElements[$Restored[0]]] += $Restored[1];
                            $ChangedShipsTypes[] = $Restored[0];

                            $ShipsChanged = true;
                        }
                    }

                    if($ShipsChanged)
                    {
                        $ShipsChanged = array('Where' => $Log['PlanetID'], 'Types' => $ChangedShipsTypes);
                    }
                }
                else if($Place == 22)
                {
                    // resources.php
                    $ResUpdateReturn = ResourceUpdate($PlanetsDumpData[$Log['PlanetID']], $UserData, 'LA', $Log['Date']);
                    if($ResUpdateReturn !== false)
                    {
                        $ScanLog['ResUpdates'] += 1;
                    }

                    $Explode = explode(';', $Log['AdditionalData']);
                    foreach($Explode as $Exploded)
                    {
                        $Exploded = explode(',', $Exploded);
                        if($Log['Code'] == 1)
                        {
                            $PlanetsDumpData[$Log['PlanetID']][$Exploded[0].'_workpercent'] = $Exploded[1];
                        }
                        else if($Log['Code'] == 2)
                        {
                            foreach($PlanetsDumpData as $ThisPlanetID => $ThisData)
                            {
                                if($ThisData['planet_type'] == 1)
                                {
                                    $PlanetsDumpData[$ThisPlanetID][$Exploded[0].'_workpercent'] = $Exploded[1];
                                }
                            }
                        }
                    }

                    $PreventResourceUpdate = true;
                }
                else if($Place == 23)
                {
                    // merchant.php
                    $ResUpdateReturn = ResourceUpdate($PlanetsDumpData[$Log['PlanetID']], $UserData, 'LA', $Log['Date']);
                    if($ResUpdateReturn !== false)
                    {
                        $ScanLog['ResUpdates'] += 1;
                    }

                    $Traded = explode(';', $Log['AdditionalData']);
                    foreach($Traded as $TradeData)
                    {
                        $TradeData = explode(',', $TradeData);
                        if($Log['ElementID'] == 1)
                        {
                            // If Selling Resources
                            if($TradeData[0] == 'R')
                            {
                                if($Log['Code'] == 1)
                                {
                                    $PlanetsDumpData[$Log['PlanetID']]['metal'] -= $TradeData[1];
                                }
                                else if($Log['Code'] == 2)
                                {
                                    $PlanetsDumpData[$Log['PlanetID']]['crystal'] -= $TradeData[1];
                                }
                                else if($Log['Code'] == 3)
                                {
                                    $PlanetsDumpData[$Log['PlanetID']]['deuterium'] -= $TradeData[1];
                                }
                            }
                            else if($TradeData[0] == 'M')
                            {
                                $PlanetsDumpData[$Log['PlanetID']]['metal'] += $TradeData[1];
                            }
                            else if($TradeData[0] == 'C')
                            {
                                $PlanetsDumpData[$Log['PlanetID']]['crystal'] += $TradeData[1];
                            }
                            else if($TradeData[0] == 'D')
                            {
                                $PlanetsDumpData[$Log['PlanetID']]['deuterium'] += $TradeData[1];
                            }
                        }
                        else
                        {
                            // If Buying Resources
                            if($TradeData[0] == 'R')
                            {
                                if($Log['Code'] == 1)
                                {
                                    $PlanetsDumpData[$Log['PlanetID']]['metal'] += $TradeData[1];
                                }
                                else if($Log['Code'] == 2)
                                {
                                    $PlanetsDumpData[$Log['PlanetID']]['crystal'] += $TradeData[1];
                                }
                                else if($Log['Code'] == 3)
                                {
                                    $PlanetsDumpData[$Log['PlanetID']]['deuterium'] += $TradeData[1];
                                }
                            }
                            else if($TradeData[0] == 'M')
                            {
                                $PlanetsDumpData[$Log['PlanetID']]['metal'] -= $TradeData[1];
                            }
                            else if($TradeData[0] == 'C')
                            {
                                $PlanetsDumpData[$Log['PlanetID']]['crystal'] -= $TradeData[1];
                            }
                            else if($TradeData[0] == 'D')
                            {
                                $PlanetsDumpData[$Log['PlanetID']]['deuterium'] -= $TradeData[1];
                            }
                        }
                    }

                    $ResourcesChanged = $Log['PlanetID'];
                }
                else if($Place == 24)
                {
                    // disassembler.php
                    $ResUpdateReturn = ResourceUpdate($PlanetsDumpData[$Log['PlanetID']], $UserData, 'LA', $Log['Date']);
                    if($ResUpdateReturn !== false)
                    {
                        $ScanLog['ResUpdates'] += 1;
                    }

                    $Disassembled = explode(';', $Log['AdditionalData']);
                    foreach($Disassembled as $Item)
                    {
                        $Item = explode(',', $Item);
                        if($Item[0] == 'P')
                        {
                            $Log['Disassebler_Percent'] = $Item[1] / 100;
                        }
                        else
                        {
                            $PlanetsDumpData[$Log['PlanetID']][$_Vars_GameElements[$Item[0]]] -= $Item[1];
                            $PlanetsDumpData[$Log['PlanetID']]['metal'] += $_Vars_Prices[$Item[0]]['metal'] * $Item[1] * $Log['Disassebler_Percent'];
                            $PlanetsDumpData[$Log['PlanetID']]['crystal'] += $_Vars_Prices[$Item[0]]['crystal'] * $Item[1] * $Log['Disassebler_Percent'];
                            $PlanetsDumpData[$Log['PlanetID']]['deuterium'] += $_Vars_Prices[$Item[0]]['deuterium'] * $Item[1] * $Log['Disassebler_Percent'];
                            $ChangedShipsTypes[] = $Item[0];
                        }
                    }

                    $ShipsChanged = array('Where' => $Log['PlanetID'], 'Types' => $ChangedShipsTypes);
                    $ResourcesChanged = $Log['PlanetID'];
                }
                else if($Place == 25)
                {
                    // overview.php [Deleting Planet/Moon]
                    unset($PlanetsDumpData[$Log['PlanetID']]);

                    $PreventResourceUpdate = true;
                }
                else if($Place == 26)
                {
                    // Settings.php [Go to/Leave VacationMode]

                    if($Log['Code'] == 2)
                    {
                        foreach($PlanetsDumpData as $PlanetID => $PlanetData)
                        {
                            $PlanetsDumpData[$PlanetID]['last_update'] = $Log['Date'];
                        }
                    }

                    $PreventResourceUpdate = true;
                }
                else if($Place == 27)
                {
                    // JumpGate.php [Move Fleet]

                    $Move = explode(';', $Log['AdditionalData']);
                    foreach($Move as $MoveShip)
                    {
                        $MoveShip = explode(',', $MoveShip);
                        $PlanetsDumpData[$Log['PlanetID']][$_Vars_GameElements[$MoveShip[0]]] -= $MoveShip[1];
                        $PlanetsDumpData[$Log['ElementID']][$_Vars_GameElements[$MoveShip[0]]] += $MoveShip[1];
                    }

                    $PreventResourceUpdate = true;
                }
                else if($Place == 28)
                {
                    // Destroy_rockets.php [Delete Rockets]

                    $Delete = explode(';', $Log['AdditionalData']);
                    foreach($Delete as $RocketData)
                    {
                        $RocketData = explode(',', $RocketData);
                        $PlanetsDumpData[$Log['PlanetID']][$_Vars_GameElements[$RocketData[0]]] -= $RocketData[1];
                    }

                    $PreventResourceUpdate = true;
                }
                else if($Place == 29)
                {
                    // Phalanx.php [Remove Deuterium from Scan]

                    $PlanetsDumpData[$Log['PlanetID']]['deuterium'] -= PHALANX_DEUTERIUMCOST;
                    $PreventResourceUpdate = true;
                }
                else if($Place == 30)
                {
                    // common.php [Task System]

                    $Added = explode(';', $Log['AdditionalData']);
                    foreach($Added as $ElementData)
                    {
                        $ElementData = explode(',', $ElementData);
                        if($ElementData[0] == 'M')
                        {
                            $PlanetsDumpData[$Log['PlanetID']]['metal'] += $ElementData[1];
                        }
                        else if($ElementData[0] == 'C')
                        {
                            $PlanetsDumpData[$Log['PlanetID']]['crystal'] += $ElementData[1];
                        }
                        else if($ElementData[0] == 'D')
                        {
                            $PlanetsDumpData[$Log['PlanetID']]['deuterium'] += $ElementData[1];
                        }
                        else
                        {
                            $PlanetsDumpData[$Log['PlanetID']][$_Vars_GameElements[$ElementData[0]]] += $ElementData[1];
                        }
                    }

                    $PreventResourceUpdate = true;
                }
                else
                {
                    $ScanLog['Fatal'][] = array('ID' => '001', 'LogNo' => $ScaningNo, 'LogID' => $Log['ID'], 'Data' => array($Place));
                    $PreventResourceUpdate = true;
                }
                // --- END of Main Checking Part


                if($ResourcesChanged > 0)
                {
                    if($PlanetsDumpData[$ResourcesChanged]['metal'] < 0 OR $PlanetsDumpData[$ResourcesChanged]['crystal'] < 0 OR $PlanetsDumpData[$ResourcesChanged]['deuterium'] < 0)
                    {
                        $ScanLog['Warning'][] = array
                        (
                            'ID' => '001',
                            'LogNo' => $ScaningNo,
                            'LogID' => $Log['ID'],
                            'Date' => $Log['Date'],
                            'PlanetID' => $ResourcesChanged,
                            'Place' => $Place,
                            'Code' => $Log['Code'],
                            'ElementID' => $Log['ElementID'],
                            'Data' => array
                            (
                                $PlanetsDumpData[$ResourcesChanged]['metal'],
                                $PlanetsDumpData[$ResourcesChanged]['crystal'],
                                $PlanetsDumpData[$ResourcesChanged]['deuterium']
                            )
                        );
                    }
                }

                if($ShipsChanged !== false)
                {
                    $CreateWarning = false;
                    foreach($ShipsChanged['Types'] as $ShipID)
                    {
                        if($PlanetsDumpData[$ShipsChanged['Where']][$_Vars_GameElements[$ShipID]] < 0)
                        {
                            $CreateWarning[$ShipID] = $PlanetsDumpData[$ShipsChanged['Where']][$_Vars_GameElements[$ShipID]];
                        }
                    }
                    if(!empty($CreateWarning))
                    {
                        $ScanLog['Warning'][] = array
                        (
                            'ID' => '003',
                            'LogNo' => $ScaningNo,
                            'LogID' => $Log['ID'],
                            'Date' => $Log['Date'],
                            'PlanetID' => $ShipsChanged['Where'],
                            'Place' => $Place,
                            'Code' => $Log['Code'],
                            'ElementID' => $Log['ElementID'],
                            'Data' => $CreateWarning
                        );
                    }
                }

                if(!$PreventResourceUpdate)
                {
                    if($Log['PlanetID'] > 0)
                    {
                        $ResUpdateReturn = ResourceUpdate($PlanetsDumpData[$Log['PlanetID']], $UserData, 'LA', $Log['Date']);
                        if($ResUpdateReturn !== false)
                        {
                            $ScanLog['ResUpdates'] += 1;
                        }
                    }
                    else
                    {
                        $ScanLog['Warning'][] = array
                        (
                            'ID' => '002',
                            'LogNo' => $ScaningNo,
                            'LogID' => $Log['ID'],
                            'Place' => $Place
                        );
                    }
                }

                $ScanLog['LogScanned'] += 1;
                if(!isset($ScanLog['ScannedPlaces'][$Place]))
                {
                    $ScanLog['ScannedPlaces'][$Place] = 0;
                }
                $ScanLog['ScannedPlaces'][$Place] += 1;
            }

            $SummaryElements = array();
            $SummaryNew['metal'] = 0;
            $SummaryNew['crystal'] = 0;
            $SummaryNew['deuterium'] = 0;
            $SummaryLog['metal'] = 0;
            $SummaryLog['crystal'] = 0;
            $SummaryLog['deuterium'] = 0;

            while($PlanetNew = $SQLResult_PlanetsNewData->fetch_assoc())
            {
                $PlanetNew['metal'] += 0;
                $PlanetNew['crystal'] += 0;
                $PlanetNew['deuterium'] += 0;

                $SummaryNew['metal'] += $PlanetNew['metal'];
                $SummaryNew['crystal'] += $PlanetNew['crystal'];
                $SummaryNew['deuterium'] += $PlanetNew['deuterium'];

                foreach($_Vars_ElementCategories as $Key => $Values)
                {
                    if(in_array($Key, array('build', 'tech', 'prod', 'buildOn', 'units')))
                    {
                        continue;
                    }
                    foreach($Values as $ItemID)
                    {
                        $PlanetNew[$_Vars_GameElements[$ItemID]] += 0;
                        if(!isset($SummaryNew[$ItemID]))
                        {
                            $SummaryNew[$ItemID] = 0;
                        }
                        $SummaryNew[$ItemID] += $PlanetNew[$_Vars_GameElements[$ItemID]];
                        if(!in_array($ItemID, $SummaryElements))
                        {
                            $SummaryElements[] = $ItemID;
                        }
                    }
                }

                $CurrentPlanets[$PlanetNew['id']] = $PlanetNew;
            }

            foreach($PlanetsDumpData as $PlanetID => $PlanetData)
            {
                if($PlanetData['last_update'] < $CurrentPlanets[$PlanetID]['last_update'])
                {
                    ResourceUpdate($PlanetsDumpData[$PlanetID], $UserData, 'LA', $CurrentPlanets[$PlanetID]['last_update']);
                    $PlanetData = $PlanetsDumpData[$PlanetID];
                }

                $PlanetData['metal'] += 0;
                $PlanetData['crystal'] += 0;
                $PlanetData['deuterium'] += 0;

                $SummaryLog['metal'] += $PlanetData['metal'];
                $SummaryLog['crystal'] += $PlanetData['crystal'];
                $SummaryLog['deuterium'] += $PlanetData['deuterium'];

                if($PlanetData['metal'] != $CurrentPlanets[$PlanetID]['metal'])
                {
                    if(!isset($AcceptableDifferences))
                    {
                        $AcceptableDifferences = 0;
                    }
                    $AcceptableDifferences += 1;
                    $Difference = $CurrentPlanets[$PlanetID]['metal'] - $PlanetData['metal'];
                    if(($Difference > 0 AND $Difference > 1) OR ($Difference < 0 AND $Difference < -1))
                    {
                        if($PlanetData['metal'] > $CurrentPlanets[$PlanetID]['metal'] * $PermDiff)
                        {
                            $ScanLog['Summary'][] = array
                            (
                                'ID' => '003',
                                'PlanetID' => $PlanetID,
                                'ElementID' => $_Lang['Metal'],
                                'Data' => array
                                (
                                    $PlanetData['metal'],
                                    $CurrentPlanets[$PlanetID]['metal'],
                                    ($PlanetData['metal'] != 0 ? $CurrentPlanets[$PlanetID]['metal'] / $PlanetData['metal'] : 'x')
                                )
                            );
                        }
                        else if($PlanetData['metal'] * $PermDiff < $CurrentPlanets[$PlanetID]['metal'])
                        {
                            $ScanLog['Summary'][] = array
                            (
                                'ID' => '004',
                                'PlanetID' => $PlanetID,
                                'ElementID' => $_Lang['Metal'],
                                'Data' => array
                                (
                                    $PlanetData['metal'],
                                    $CurrentPlanets[$PlanetID]['metal'],
                                    ($PlanetData['metal'] != 0 ? $CurrentPlanets[$PlanetID]['metal'] / $PlanetData['metal'] : 'x')
                                )
                            );
                        }
                        else
                        {
                            $ScanLog['Notice'][] = array
                            (
                                'ID' => '001',
                                'PlanetID' => $PlanetID,
                                'Data' => array
                                (
                                    ($PlanetData['metal'] != 0 ? $CurrentPlanets[$PlanetID]['metal'] / $PlanetData['metal'] : 'x'),
                                    $Difference
                                )
                            );
                        }
                    }
                }
                if($PlanetData['crystal'] != $CurrentPlanets[$PlanetID]['crystal'])
                {
                    if(!isset($AcceptableDifferences))
                    {
                        $AcceptableDifferences = 0;
                    }
                    $AcceptableDifferences += 1;
                    $Difference = $CurrentPlanets[$PlanetID]['crystal'] - $PlanetData['crystal'];
                    if(($Difference > 0 AND $Difference > 1) OR ($Difference < 0 AND $Difference < -1))
                    {
                        if($PlanetData['crystal'] > $CurrentPlanets[$PlanetID]['crystal'] * $PermDiff)
                        {
                            $ScanLog['Summary'][] = array
                            (
                                'ID' => '003',
                                'PlanetID' => $PlanetID,
                                'ElementID' => $_Lang['Crystal'],
                                'Data' => array
                                (
                                    $PlanetData['crystal'],
                                    $CurrentPlanets[$PlanetID]['crystal'],
                                    ($PlanetData['crystal'] != 0 ? $CurrentPlanets[$PlanetID]['crystal'] / $PlanetData['crystal'] : 'x')
                                )
                            );
                        }
                        else if($PlanetData['crystal'] * $PermDiff < $CurrentPlanets[$PlanetID]['crystal'])
                        {
                            $ScanLog['Summary'][] = array
                            (
                                'ID' => '004',
                                'PlanetID' => $PlanetID,
                                'ElementID' => $_Lang['Crystal'],
                                'Data' => array
                                (
                                    $PlanetData['crystal'],
                                    $CurrentPlanets[$PlanetID]['crystal'],
                                    ($PlanetData['crystal'] != 0 ? $CurrentPlanets[$PlanetID]['crystal'] / $PlanetData['crystal'] : 'x')
                                )
                            );
                        }
                        else
                        {
                            $ScanLog['Notice'][] = array
                            (
                                'ID' => '002',
                                'PlanetID' => $PlanetID,
                                'Data' => array
                                (
                                    ($PlanetData['crystal'] != 0 ? $CurrentPlanets[$PlanetID]['crystal'] / $PlanetData['crystal'] : 'x'),
                                    $Difference
                                )
                            );
                        }
                    }
                }
                if($PlanetData['deuterium'] != $CurrentPlanets[$PlanetID]['deuterium'])
                {
                    if(!isset($AcceptableDifferences))
                    {
                        $AcceptableDifferences = 0;
                    }
                    $AcceptableDifferences += 1;
                    $Difference = $CurrentPlanets[$PlanetID]['deuterium'] - $PlanetData['deuterium'];
                    if(($Difference > 0 AND $Difference > 1) OR ($Difference < 0 AND $Difference < -1))
                    {
                        if($PlanetData['deuterium'] > $CurrentPlanets[$PlanetID]['deuterium'] * $PermDiff)
                        {
                            $ScanLog['Summary'][] = array
                            (
                                'ID' => '003',
                                'PlanetID' => $PlanetID,
                                'ElementID' => $_Lang['Deuterium'],
                                'Data' => array
                                (
                                    $PlanetData['deuterium'],
                                    $CurrentPlanets[$PlanetID]['deuterium'],
                                    ($PlanetData['deuterium'] != 0 ? $CurrentPlanets[$PlanetID]['deuterium'] / $PlanetData['deuterium'] : 'x')
                                )
                            );
                        }
                        else if($PlanetData['deuterium'] * $PermDiff < $CurrentPlanets[$PlanetID]['deuterium'])
                        {
                            $ScanLog['Summary'][] = array
                            (
                                'ID' => '004',
                                'PlanetID' => $PlanetID,
                                'ElementID' => $_Lang['Deuterium'],
                                'Data' => array
                                (
                                    $PlanetData['deuterium'],
                                    $CurrentPlanets[$PlanetID]['deuterium'],
                                    ($PlanetData['deuterium'] != 0 ? $CurrentPlanets[$PlanetID]['deuterium'] / $PlanetData['deuterium'] : 'x')
                                )
                            );
                        }
                        else
                        {
                            $ScanLog['Notice'][] = array
                            (
                                'ID' => '003',
                                'PlanetID' => $PlanetID,
                                'Data' => array
                                (
                                    ($PlanetData['deuterium'] != 0 ? $CurrentPlanets[$PlanetID]['deuterium'] / $PlanetData['deuterium'] : 'x'),
                                    $Difference
                                )
                            );
                        }
                    }
                }

                foreach($_Vars_ElementCategories as $Key => $Values)
                {
                    if(in_array($Key, array('tech', 'prod', 'buildOn', 'units')))
                    {
                        continue;
                    }
                    foreach($Values as $ItemID)
                    {
                        if($Key != 'build')
                        {
                            if(!isset($SummaryLog[$ItemID]))
                            {
                                $SummaryLog[$ItemID] = 0;
                            }

                            $SummaryLog[$ItemID] += (
                                isset($PlanetData[$_Vars_GameElements[$ItemID]]) ?
                                $PlanetData[$_Vars_GameElements[$ItemID]] :
                                0
                            );

                            if(!in_array($ItemID, $SummaryElements))
                            {
                                $SummaryElements[] = $ItemID;
                            }
                        }
                        if(empty($PlanetData[$_Vars_GameElements[$ItemID]]))
                        {
                            $PlanetData[$_Vars_GameElements[$ItemID]] = 0;
                        }
                        if(empty($CurrentPlanets[$PlanetID][$_Vars_GameElements[$ItemID]]))
                        {
                            $CurrentPlanets[$PlanetID][$_Vars_GameElements[$ItemID]] = 0;
                        }

                        if($PlanetData[$_Vars_GameElements[$ItemID]] > $CurrentPlanets[$PlanetID][$_Vars_GameElements[$ItemID]])
                        {
                            $ScanLog['Summary'][] = array
                            (
                                'ID' => '001',
                                'PlanetID' => $PlanetID,
                                'ElementID' => $ItemID,
                                'Data' => array
                                (
                                    $PlanetData[$_Vars_GameElements[$ItemID]],
                                    $CurrentPlanets[$PlanetID][$_Vars_GameElements[$ItemID]],
                                    ($PlanetData[$_Vars_GameElements[$ItemID]] != 0 ? $CurrentPlanets[$PlanetID][$_Vars_GameElements[$ItemID]] / $PlanetData[$_Vars_GameElements[$ItemID]] : 'x')
                                )
                            );
                        }
                        else if($PlanetData[$_Vars_GameElements[$ItemID]] < $CurrentPlanets[$PlanetID][$_Vars_GameElements[$ItemID]])
                        {
                            $ScanLog['Summary'][] = array
                            (
                                'ID' => '002',
                                'PlanetID' => $PlanetID,
                                'ElementID' => $ItemID,
                                'Data' => array
                                (
                                    $PlanetData[$_Vars_GameElements[$ItemID]],
                                    $CurrentPlanets[$PlanetID][$_Vars_GameElements[$ItemID]],
                                    ($PlanetData[$_Vars_GameElements[$ItemID]] != 0 ? $CurrentPlanets[$PlanetID][$_Vars_GameElements[$ItemID]] / $PlanetData[$_Vars_GameElements[$ItemID]] : 'x')
                                )
                            );
                        }
                    }
                }
            }

            if($SummaryLog['metal'] != $SummaryNew['metal'])
            {
                $Difference = $SummaryLog['metal'] - $SummaryNew['metal'];
                if(($Difference > 0 AND $Difference > (1 * $AcceptableDifferences)) OR ($Difference < 0 AND $Difference < (-1 * $AcceptableDifferences)))
                {
                    if($SummaryLog['metal'] > $SummaryNew['metal'] * $PermDiff)
                    {
                        $ScanLog['Summary'][] = array
                        (
                            'ID' => '005',
                            'ElementID' => $_Lang['Metal'],
                            'Data' => array
                            (
                                $SummaryLog['metal'],
                                $SummaryNew['metal'],
                                ($SummaryLog['metal'] != 0 ? $SummaryNew['metal'] / $SummaryLog['metal'] : 'x')
                            )
                        );
                    }
                    else if($SummaryLog['metal'] * $PermDiff < $SummaryNew['metal'])
                    {
                        $ScanLog['Summary'][] = array
                        (
                            'ID' => '006',
                            'ElementID' => $_Lang['Metal'],
                            'Data' => array
                            (
                                $SummaryLog['metal'],
                                $SummaryNew['metal'],
                                ($SummaryLog['metal'] != 0 ? $SummaryNew['metal'] / $SummaryLog['metal'] : 'x')
                            )
                        );
                    }
                    else
                    {
                        $ScanLog['Notice'][] = array
                        (
                            'ID' => '004'
                        );
                    }
                }
            }
            if($SummaryLog['crystal'] != $SummaryNew['crystal'])
            {
                $Difference = $SummaryLog['crystal'] - $SummaryNew['crystal'];
                if(($Difference > 0 AND $Difference > (1 * $AcceptableDifferences)) OR ($Difference < 0 AND $Difference < (-1 * $AcceptableDifferences)))
                {
                    if($SummaryLog['crystal'] > $SummaryNew['crystal'] * $PermDiff)
                    {
                        $ScanLog['Summary'][] = array
                        (
                            'ID' => '005',
                            'ElementID' => $_Lang['Crystal'],
                            'Data' => array
                            (
                                $SummaryLog['crystal'],
                                $SummaryNew['crystal'],
                                ($SummaryLog['crystal'] != 0 ? $SummaryNew['crystal'] / $SummaryLog['crystal'] : 'x')
                            )
                        );
                    }
                    else if($SummaryLog['crystal'] * $PermDiff < $SummaryNew['crystal'])
                    {
                        $ScanLog['Summary'][] = array
                        (
                            'ID' => '006',
                            'ElementID' => $_Lang['Crystal'],
                            'Data' => array
                            (
                                $SummaryLog['crystal'],
                                $SummaryNew['crystal'],
                                ($SummaryLog['crystal'] != 0 ? $SummaryNew['crystal'] / $SummaryLog['crystal'] : 'x')
                            )
                        );
                    }
                    else
                    {
                        $ScanLog['Notice'][] = array
                        (
                            'ID' => '005'
                        );
                    }
                }
            }
            if($SummaryLog['deuterium'] != $SummaryNew['deuterium'])
            {
                $Difference = $SummaryLog['deuterium'] - $SummaryNew['deuterium'];
                if(($Difference > 0 AND $Difference > (1 * $AcceptableDifferences)) OR ($Difference < 0 AND $Difference < (-1 * $AcceptableDifferences)))
                {
                    if($SummaryLog['deuterium'] > $SummaryNew['deuterium'] * $PermDiff)
                    {
                        $ScanLog['Summary'][] = array
                        (
                            'ID' => '005',
                            'ElementID' => $_Lang['Deuterium'],
                            'Data' => array
                            (
                                $SummaryLog['deuterium'],
                                $SummaryNew['deuterium'],
                                ($SummaryLog['deuterium'] != 0 ? $SummaryNew['deuterium'] / $SummaryLog['deuterium'] : 'x')
                            )
                        );
                    }
                    else if($SummaryLog['deuterium'] * $PermDiff < $SummaryNew['deuterium'])
                    {
                        $ScanLog['Summary'][] = array
                        (
                            'ID' => '006',
                            'ElementID' => $_Lang['Deuterium'],
                            'Data' => array
                            (
                                $SummaryLog['deuterium'],
                                $SummaryNew['deuterium'],
                                ($SummaryLog['deuterium'] != 0 ? $SummaryNew['deuterium'] / $SummaryLog['deuterium'] : 'x')
                            )
                        );
                    }
                    else
                    {
                        $ScanLog['Notice'][] = array
                        (
                            'ID' => '006'
                        );
                    }
                }
            }

            if(!empty($SummaryElements))
            {
                if(empty($SummaryLog[$ItemID]))
                {
                    $SummaryLog[$ItemID] = 0;
                }
                if(empty($SummaryNew[$ItemID]))
                {
                    $SummaryNew[$ItemID] = 0;
                }

                foreach($SummaryElements as $ItemID)
                {
                    if($SummaryLog[$ItemID] > $SummaryNew[$ItemID])
                    {
                        $ScanLog['Summary'][] = array
                        (
                            'ID' => '007',
                            'ElementID' => $ItemID,
                            'Data' => array
                            (
                                $SummaryLog[$ItemID],
                                $SummaryNew[$ItemID],
                                ($SummaryLog[$ItemID] != 0 ? $SummaryNew[$ItemID] / $SummaryLog[$ItemID] : 'x')
                            )
                        );
                    }
                    else if($PlanetData[$_Vars_GameElements[$ItemID]] < $CurrentPlanets[$PlanetID][$_Vars_GameElements[$ItemID]])
                    {
                        $ScanLog['Summary'][] = array
                        (
                            'ID' => '008',
                            'ElementID' => $ItemID,
                            'Data' => array
                            (
                                $SummaryLog[$ItemID],
                                $SummaryNew[$ItemID],
                                ($SummaryLog[$ItemID] != 0 ? $SummaryNew[$ItemID] / $SummaryLog[$ItemID] : 'x')
                            )
                        );
                    }
                }
            }

            foreach($_Vars_ElementCategories['tech'] as $TechID)
            {
                if(empty($UserData[$_Vars_GameElements[$TechID]]))
                {
                    $UserData[$_Vars_GameElements[$TechID]] = 0;
                }
                if(empty($UserNewData[$_Vars_GameElements[$TechID]]))
                {
                    $UserNewData[$_Vars_GameElements[$TechID]] = 0;
                }

                if($UserData[$_Vars_GameElements[$TechID]] > $UserNewData[$_Vars_GameElements[$TechID]])
                {
                    $ScanLog['Summary'][] = array
                    (
                        'ID' => '009',
                        'ElementID' => $TechID,
                        'Data' => array
                        (
                            $UserData[$_Vars_GameElements[$TechID]],
                            $UserNewData[$_Vars_GameElements[$TechID]]
                        )
                    );
                }
                else if($UserData[$_Vars_GameElements[$TechID]] < $UserNewData[$_Vars_GameElements[$TechID]])
                {
                    $ScanLog['Summary'][] = array
                    (
                        'ID' => '010',
                        'ElementID' => $TechID,
                        'Data' => array
                        (
                            $UserData[$_Vars_GameElements[$TechID]],
                            $UserNewData[$_Vars_GameElements[$TechID]]
                        )
                    );
                }
            }

            $ScanEndTime = microtime(true);
        }
    }

    $_Lang['PHP_Username'] = $GetUser['username'];
    $_Lang['PHP_UID'] = $GetUser['id'];

    if($BreakScan OR empty($ScanLog))
    {
        $_Lang['PHP_HideScanResult'] = $Hide;

        if(!empty($_Lang['Error_Found']))
        {
            $_Lang['PHP_BreakErrorColor'] = 'red';
            $_Lang['PHP_BreakErrorText'] = $_Lang['Error_Found'];
        }
        else if(!empty($_Lang['Notice_Found']))
        {
            $_Lang['PHP_BreakErrorColor'] = 'orange';
            $_Lang['PHP_BreakErrorText'] = $_Lang['Notice_Found'];
        }
        else
        {
            if(empty($ScanLog))
            {
                $_Lang['PHP_BreakErrorColor'] = 'red';
                $_Lang['PHP_BreakErrorText'] = $_Lang['Critical_EmptyScanLog'];
            }
            else
            {
                $_Lang['PHP_BreakErrorColor'] = 'red';
                $_Lang['PHP_BreakErrorText'] = $_Lang['Critical_EmptyBreakErrorVar'];
            }
        }
    }
    else
    {
        $_Lang['PHP_HideBreakError'] = $Hide;

        $_Lang['PHP_ScanedLogs'] = $ScanLog['LogScanned'];
        $_Lang['PHP_ScanTime'] = sprintf('%0.10f', ($ScanEndTime - $ScanStartTime));
        $_Lang['PHP_DumpDate'] = prettyDate('d m Y - H:i:s', $LoadLastDump['Date'], 1).'<br/>('.$LoadLastDump['Date'].')';
        $_Lang['PHP_DateDifference'] = pretty_time(time() - $LoadLastDump['Date']);

        $ResultRowTPL = gettemplate('admin/userdevscanner_result_row');

        // Found "Fatal"/"Warning"/"Notice" Exceptions & Handle Summary
        if(empty($ScanLog['Fatal']))
        {
            $ScanLog['Fatal'] = array();
        }
        if(empty($ScanLog['Warning']))
        {
            $ScanLog['Warning'] = array();
        }
        if(empty($ScanLog['Notice']))
        {
            $ScanLog['Notice'] = array();
        }
        if(empty($ScanLog['Summary']))
        {
            $ScanLog['Summary'] = array();
        }

        foreach($ScanLog as $ScanKey => $ScanData)
        {
            if($ScanKey != 'Fatal' AND $ScanKey != 'Warning' AND $ScanKey != 'Notice' AND $ScanKey != 'Summary')
            {
                continue;
            }
            if(!empty($ScanData))
            {
                if($ScanKey == 'Fatal')
                {
                    $ParseRowPattern = array('ModuleNumber' => '01', 'TextColor' => 'red', 'Table2_LogID' => $_Lang['Table2_LogID']);
                }
                else if($ScanKey == 'Warning')
                {
                    $ParseRowPattern = array('ModuleNumber' => '02', 'TextColor' => 'orange', 'Table2_LogID' => $_Lang['Table2_LogID']);
                }
                else if($ScanKey == 'Notice')
                {
                    $ParseRowPattern = array('ModuleNumber' => '03', 'TextColor' => 'yellow', 'Table2_LogID' => $_Lang['Table2_LogID']);
                }
                else if($ScanKey == 'Summary')
                {
                    $ParseRowPattern = array('ModuleNumber' => '04', 'TextColor' => 'orange', 'Table2_LogID' => $_Lang['Table2_LogID']);
                }
                $LastLogIDLen = end($ScanData);
                $LastLogIDLen = isset($LastLogIDLen['LogID']) ? strlen($LastLogIDLen['LogID']) : 0;
                reset($ScanData);
                $_Lang['Table2_Final'.$ScanKey.'Count'] = count($ScanData);
                foreach($ScanData as $Index => $Data)
                {
                    $ParseRow = $ParseRowPattern;
                    $Point = &$_Lang['Found'.$ScanKey.'s'][$Data['ID']];
                    $ParseRow['Index'] = str_pad($Index + 1, 4, '0', STR_PAD_LEFT);
                    $ParseRow['RowTitle'] = $Point['label'];
                    if (
                        $ScanKey == 'Summary' ||
                        (
                            !isset($Data['LogID']) ||
                            $Data['LogID'] <= 0
                        )
                    ) {
                        $ParseRow['HideLogID'] = $Hide;
                    }
                    else
                    {
                        $ParseRow['RowLogID'] = str_pad($Data['LogID'], $LastLogIDLen, '0', STR_PAD_LEFT);
                        if($Data['LogNo'] > 0)
                        {
                            $ParseRow['RowLogID'] .= '<br/>LogNo: '.$Data['LogNo'];
                        }
                    }

                    if (isset($Point['msgGenerator']) && is_callable($Point['msgGenerator'])) {
                        $ParseRow['RowData'] = $Point['msgGenerator']($Data, $_Lang);
                    } else {
                        $ParseRow['RowData'] = '-';
                    }

                    if($ScanKey == 'Notice')
                    {
                        $ParseRow['IsCollapsed'] = 'collapsed';
                    }

                    if(!isset($_Lang['PHP_AllFound'.$ScanKey.'s']))
                    {
                        $_Lang['PHP_AllFound'.$ScanKey.'s'] = '';
                    }
                    $_Lang['PHP_AllFound'.$ScanKey.'s'] .= parsetemplate($ResultRowTPL, $ParseRow);
                }
            }
            else
            {
                $_Lang['PHP_HideFound'.$ScanKey.'s'] = $Hide;
                $_Lang['PHP_HideFound'.$ScanKey.'s2'] = 'hide';
            }
        }

        if(empty($ScanLog['Summary']))
        {
            $_Lang['PHP_OverallResultColor'] = 'lime';
            $_Lang['PHP_OverallResultText'] = $_Lang['FoundSummarys']['000']['ok'];
        }
        else
        {
            $_Lang['PHP_OverallResultColor'] = 'red';
            $_Lang['PHP_OverallResultText'] = $_Lang['FoundSummarys']['000']['found_problems'];
        }
    }

    $Page = parsetemplate(gettemplate('admin/userdevscanner_result'), $_Lang);
}
else
{
    if(empty($_Lang['Error_Found']))
    {
        $_Lang['PHP_ShowError'] = $Hide;
    }

    $Page = parsetemplate(gettemplate('admin/userdevscanner_form'), $_Lang);
}

display($Page, $_Lang['UserDevScanner_Title'], false, true);

?>
