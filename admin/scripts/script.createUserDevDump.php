<?php

if(!defined('IN_SCRIPTRUNNER') && !defined('IN_USERFIRSTLOGIN'))
{
    header("Location: ../index.php");
    die();
}

$Now = time();

$StartTime = microtime(true);

$SelectedUID = (isset($_GET['uid']) ? intval($_GET['uid']) : 0);
if(isset($InnerUIDSet) && !empty($InnerUIDSet))
{
    $SelectedUID = $InnerUIDSet;
}
if($SelectedUID > 0)
{
    $WhereClausure1 = " WHERE `id` = {$SelectedUID}";
    $WhereClausure2 = " WHERE `id_owner` = {$SelectedUID}";
    $WhereClausure3 = " WHERE `fleet_owner` = {$SelectedUID}";
}

$GetUsers = doquery("SELECT * FROM {{table}}{$WhereClausure1};", 'users');
$GetPlanets = doquery("SELECT * FROM {{table}}{$WhereClausure2};", 'planets');
$GetFleets = doquery("SELECT * FROM {{table}}{$WhereClausure3};", 'fleets');

if(mysql_num_rows($GetUsers) > 0)
{
    while($UserData = mysql_fetch_assoc($GetUsers))
    {
        $DevDump[$UserData['id']] = array('planets' => array(), 'techs' => array(), 'inflight' => array());
        $Point = &$DevDump[$UserData['id']]['techs'];
        foreach($_Vars_ElementCategories['tech'] as $TechID)
        {
            if($UserData[$_Vars_GameElements[$TechID]] > 0)
            {
                $Point[$TechID] = $UserData[$_Vars_GameElements[$TechID]];
            }
        }
    }

    if(mysql_num_rows($GetPlanets) > 0)
    {
        while($PlanetData = mysql_fetch_assoc($GetPlanets))
        {
            if($PlanetData['id_owner'] <= 0)
            {
                continue;
            }
            $Point = &$DevDump[$PlanetData['id_owner']];

            $Point['planets'][$PlanetData['id']]['pt'] = $PlanetData['planet_type'];
            $Point['planets'][$PlanetData['id']]['t'] = $PlanetData['temp_max'];
            $Point['planets'][$PlanetData['id']]['lu'] = $PlanetData['last_update'];
            $Point['planets'][$PlanetData['id']]['res'] = floor($PlanetData['metal']).','.floor($PlanetData['crystal']).','.floor($PlanetData['deuterium']);


            foreach($_Vars_ElementCategories as $Key => $Array)
            {
                if(in_array($Key, array('tech', 'buildOn', 'units')))
                {
                    continue;
                }
                if($Key != 'build')
                {
                    if($Key != 'prod')
                    {
                        foreach($Array as $ElementID)
                        {
                            if(isset($PlanetData[$_Vars_GameElements[$ElementID]]) && $PlanetData[$_Vars_GameElements[$ElementID]] > 0)
                            {
                                $Point['planets'][$PlanetData['id']]['f'][] = "{$ElementID},{$PlanetData[$_Vars_GameElements[$ElementID]]}";
                            }
                        }
                    }
                    else
                    {
                        foreach($Array as $ElementID)
                        {
                            $Point['planets'][$PlanetData['id']]['p'][] = $ElementID.','.$PlanetData[$_Vars_GameElements[$ElementID].'_workpercent'];
                        }
                    }
                }
                else
                {
                    foreach($Array as $BuildID)
                    {
                        if(isset($PlanetData[$_Vars_GameElements[$BuildID]]) && $PlanetData[$_Vars_GameElements[$BuildID]] > 0)
                        {
                            $Point['planets'][$PlanetData['id']]['b'][] = "{$BuildID},{$PlanetData[$_Vars_GameElements[$BuildID]]}";
                        }
                    }
                }
            }
        }

        if(mysql_num_rows($GetFleets) > 0)
        {
            while($FleetData = mysql_fetch_assoc($GetFleets))
            {
                $Point = &$DevDump[$FleetData['fleet_owner']]['inflight'][$FleetData['fleet_id']];
                $Point = rtrim($FleetData['fleet_array'], ';');
                if($FleetData['fleet_resource_metal'] > 0)
                {
                    $Point .= ';M,'.$FleetData['fleet_resource_metal'];
                }
                if($FleetData['fleet_resource_crystal'] > 0)
                {
                    $Point .= ';C,'.$FleetData['fleet_resource_crystal'];
                }
                if($FleetData['fleet_resource_deuterium'] > 0)
                {
                    $Point .= ';D,'.$FleetData['fleet_resource_deuterium'];
                }
            }
        }
    }

    if(isset($DevDump) && !empty($DevDump))
    {
        if($SelectedUID > 0)
        {
            doquery("DELETE FROM {{table}} WHERE `UserID` = {$SelectedUID} LIMIT 1;", 'user_developmentdumps');
            doquery("DELETE FROM {{table}} WHERE `UserID` = {$SelectedUID};", 'user_developmentlog');
        }
        else
        {
            doquery("TRUNCATE {{table}};", 'user_developmentdumps');
            doquery("TRUNCATE {{table}};", 'user_developmentlog');
        }

        $InsertQry = "INSERT INTO {{table}} VALUES ";
        foreach($DevDump as $UserID => $DevData)
        {
            $TempElements = array();
            foreach($DevData['planets'] as $PlanetID => $Data)
            {
                if(isset($Data['b']) && !empty($Data['b']))
                {
                    $DevData['planets'][$PlanetID]['b'] = implode(';', $Data['b']);
                }
                if(isset($Data['p']) && !empty($Data['p']))
                {
                    $DevData['planets'][$PlanetID]['p'] = implode(';', $Data['p']);
                }
                if(isset($Data['f']) && !empty($Data['f']))
                {
                    $DevData['planets'][$PlanetID]['f'] = implode(';', $Data['f']);
                }
            }
            $DevData['planets'] = json_encode($DevData['planets']);
            foreach($DevData['techs'] as $ID => $Count)
            {
                $TempElements[] = "{$ID},{$Count}";
            }
            $DevData['techs'] = implode(';', $TempElements);

            $DevData['inflight'] = json_encode($DevData['inflight']);

            $InsertQryArr[] = "({$UserID}, {$Now}, '{$DevData['planets']}', '{$DevData['techs']}', '{$DevData['inflight']}')";
        }
        $InsertQry .= implode(', ', $InsertQryArr);
        doquery($InsertQry, 'user_developmentdumps');

        $EndTime = microtime(true);

        if(!isset($SkipDumpMsg) || $SkipDumpMsg !== true)
        {
            message('User Development Dump creation <b class="lime">DONE</b><br/>Generated in: '.sprintf('%0.10f', ($EndTime - $StartTime)), '');
        }
    }
    else
    {
        if(!isset($SkipDumpMsg) || $SkipDumpMsg !== true)
        {
            message('<b class="red">No Data to Insert!</b>', '');
        }
    }
}
else
{
    if(!isset($SkipDumpMsg) || $SkipDumpMsg !== true)
    {
        message('<b class="red">Users not Found!</b>', '');
    }
}

?>
