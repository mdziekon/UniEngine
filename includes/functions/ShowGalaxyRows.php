<?php

function renderEmptyGalaxyCell() {
    return '<th class="hiFnt">&nbsp;</th>';
}

function ShowGalaxyRows($Galaxy, $System, $HighlightPlanet = false)
{
    global $planetcount, $_User;

    $Result = '';

    $UserNeedenFields = '';
    $UserNeedenFields .= "`usr`.`id` as `user_id`, `usr`.`username`, `usr`.`old_username`, `usr`.`old_username_expire`, `usr`.`ally_id`, ";
    $UserNeedenFields .= "`usr`.`is_banned`, `usr`.`is_onvacation`, `usr`.`onlinetime`, ";
    $UserNeedenFields .= "`usr`.`avatar`, `usr`.`authlevel`, `usr`.`activation_code`, ";
    $UserNeedenFields .= "`usr`.`first_login`, `usr`.`NoobProtection_EndTime`, ";
    $UserNeedenFields .= "`usr`.`morale_points`, ";
    $UserNeedenFields .= "`stats`.`total_points`, `stats`.`total_rank`, ";
    $UserNeedenFields .= "`ally_stats`.`total_rank` AS `ally_total_rank`, ";
    $UserNeedenFields .= "`ally`.`ally_name`, `ally`.`ally_web`, `ally`.`ally_web_reveal`, `ally`.`ally_tag`, `ally`.`ally_members`";
    $PlanetNeedenFields = '`pl`.`id`, `pl`.`name`, `pl`.`name`, `pl`.`id_owner`, `pl`.`galaxy`, `pl`.`planet`, `pl`.`last_update`, `pl`.`image`';

    $Query_Galaxies        = '';
    $Query_Galaxies        .= "SELECT `planet`, `id_planet`, `metal`, `crystal`, `id_moon`, `hide_planet` FROM {{table}} ";
    $Query_Galaxies        .= "WHERE `galaxy` = {$Galaxy} AND `system` = {$System} ORDER BY `planet` ASC;";
    $Query_Planets        = '';
    $Query_Planets        .= "SELECT {$PlanetNeedenFields}, {$UserNeedenFields} FROM {{table}} AS `pl` ";
    $Query_Planets        .= "LEFT JOIN `{{prefix}}users` AS `usr` ON `usr`.`id` = `pl`.`id_owner` ";
    $Query_Planets        .= "LEFT JOIN `{{prefix}}alliance` AS `ally` ON `ally`.`id` = `usr`.`ally_id` ";
    $Query_Planets        .= "LEFT JOIN `{{prefix}}statpoints` AS `stats` ON `stats`.`stat_type` = 1 AND `stats`.`id_owner` = `pl`.`id_owner` ";
    $Query_Planets        .= "LEFT JOIN `{{prefix}}statpoints` AS `ally_stats` ON `ally_stats`.`stat_type` = 2 AND `ally_stats`.`id_owner` = `usr`.`ally_id` ";
    $Query_Planets        .= "WHERE `pl`.`galaxy` = {$Galaxy} AND `pl`.`system` = {$System} AND `pl`.`planet_type` = 1 ";
    $Query_Planets        .= "ORDER BY `pl`.`planet` ASC;";
    $Query_Moons        = '';
    $Query_Moons        .= "SELECT `id`, `name`, `planet`, `temp_min`, `diameter` FROM {{table}} ";
    $Query_Moons        .= "WHERE `galaxy` = {$Galaxy} AND `system` = {$System} AND `planet_type` = 3 ORDER BY `planet` ASC;";
    $Query_SFB            = '';
    $Query_SFB            .= "SELECT `ID`, `StartTime`, `BlockMissions`, `EndTime` FROM {{table}} ";
    $Query_SFB            .= "WHERE `Type` = 1 AND `StartTime` <= UNIX_TIMESTAMP() AND `EndTime` <= UNIX_TIMESTAMP() AND `PostEndTime` > UNIX_TIMESTAMP() ";
    $Query_SFB            .= "ORDER BY `EndTime` DESC LIMIT 1;";
    $Query_Buddy        = '';
    $Query_Buddy        .= "SELECT `sender`, `owner` FROM {{table}} ";
    $Query_Buddy        .= "WHERE `active` = 1 AND (`sender` = {$_User['id']} OR `owner` = {$_User['id']});";
    $Query_AllyPacts    = '';
    $Query_AllyPacts    .= "SELECT IF(`AllyID_Sender` = {$_User['ally_id']}, `AllyID_Owner`, `AllyID_Sender`) AS `AllyID`, `Type` ";
    $Query_AllyPacts    .= "FROM {{table}} WHERE (`AllyID_Sender` = {$_User['ally_id']} OR `AllyID_Owner` = {$_User['ally_id']}) AND `Active` = 1;";

    $GalaxyResult    = doquery($Query_Galaxies, 'galaxy');
    $PlanetsResult    = doquery($Query_Planets, 'planets');
    $MoonsResult    = doquery($Query_Moons, 'planets');
    $SFBStatus        = doquery($Query_SFB, 'smart_fleet_blockade', true);
    $MyBuddyList    = doquery($Query_Buddy, 'buddy');

    $MyAllyPacts = [];
    if($_User['ally_id'] > 0)
    {
        $Result_MyAllyPacts = doquery($Query_AllyPacts, 'ally_pacts');
        if($Result_MyAllyPacts->num_rows > 0)
        {
            while($FetchData = $Result_MyAllyPacts->fetch_assoc())
            {
                $MyAllyPacts[$FetchData['AllyID']] = $FetchData['Type'];
            }
        }
    }

    $MyBuddies = [];
    if($MyBuddyList->num_rows > 0)
    {
        while($MyBuddyData = $MyBuddyList->fetch_assoc())
        {
            if($MyBuddyData['sender'] != $_User['id'])
            {
                if(!in_array($MyBuddyData['sender'], $MyBuddies))
                {
                    $MyBuddies[] = $MyBuddyData['sender'];
                }
                continue;
            }
            if($MyBuddyData['owner'] != $_User['id'])
            {
                if(!in_array($MyBuddyData['owner'], $MyBuddies))
                {
                    $MyBuddies[] = $MyBuddyData['owner'];
                }
            }
        }
    }

    $Planet = 1;
    $RowTPL = gettemplate('galaxy_row_body');

    $galaxyRows = mapQueryResults($GalaxyResult, function ($entry) {
        return $entry;
    });
    $galaxyRows = object_map($galaxyRows, function ($value) {
        return [ $value, $value['planet'] ];
    });

    $planetRows = mapQueryResults($PlanetsResult, function ($entry) {
        return $entry;
    });
    $planetRows = object_map($planetRows, function ($value) {
        return [ $value, $value['planet'] ];
    });

    $moonRows = mapQueryResults($MoonsResult, function ($entry) {
        return $entry;
    });
    $moonRows = object_map($moonRows, function ($value) {
        return [ $value, $value['planet'] ];
    });

    foreach (range(1, MAX_PLANET_IN_SYSTEM, 1) as $Planet) {
        if (!isset($galaxyRows[$Planet])) {
            $RowData = '';
            $RowData .= GalaxyRowPos($Galaxy, $System, $Planet);
            $RowData .= GalaxyRowPlanet(false, [], [], $Galaxy, $System, $Planet, $MyBuddies, $MyAllyPacts);
            $RowData .= GalaxyRowPlanetName(false, [], [], $Galaxy, $System, $Planet, $MyBuddies);
            $RowData .= GalaxyRowMoon(false, [], [], $Galaxy, $System, $Planet, $MyBuddies, $MyAllyPacts);
            $RowData .= GalaxyRowDebris(false, $Galaxy, $System, $Planet);
            $RowData .= GalaxyRowUser([], [], $MyBuddies, $SFBStatus);
            $RowData .= GalaxyRowAlly([], $MyAllyPacts);
            $RowData .= GalaxyRowActions([], [], $Galaxy, $System, $Planet, $MyBuddies);

            $Result .= parsetemplate($RowTPL, [
                'Data' => $RowData,
                'SetHighlight' => ($Planet == $HighlightPlanet ? ' class="rowHighlight"' : '')
            ]);

            continue;
        }

        $RowData = '';

        $GalaxyRow = $galaxyRows[$Planet];
        $GalaxyRowPlanet = [];
        $GalaxyRowMoon = [];
        $GalaxyRowPlayer = null;

        if ($GalaxyRow['id_planet'] != 0) {
            $GalaxyRowPlanet = $planetRows[$Planet];
            if (
                $GalaxyRow['hide_planet'] == 0 OR
                CheckAuth('supportadmin') OR
                $_User['id'] == $GalaxyRowPlanet['id_owner']
            ) {
                $planetcount += 1;
                $GalaxyRowPlayer = $GalaxyRowPlanet;
                $GalaxyRowPlayer['id'] = $GalaxyRowPlayer['user_id'];
                if ($GalaxyRow['id_moon'] != 0) {
                    $GalaxyRowMoon = $moonRows[$Planet];
                }
            }
        }

        $RowData .= GalaxyRowPos($Galaxy, $System, $Planet);
        $RowData .= GalaxyRowPlanet($GalaxyRow, $GalaxyRowPlanet, $GalaxyRowPlayer, $Galaxy, $System, $Planet, $MyBuddies, $MyAllyPacts);
        $RowData .= GalaxyRowPlanetName($GalaxyRow, $GalaxyRowPlanet, $GalaxyRowPlayer, $Galaxy, $System, $Planet, $MyBuddies);
        $RowData .= GalaxyRowMoon($GalaxyRow, $GalaxyRowMoon, $GalaxyRowPlayer, $Galaxy, $System, $Planet, $MyBuddies, $MyAllyPacts);
        $RowData .= GalaxyRowDebris($GalaxyRow, $Galaxy, $System, $Planet);
        $RowData .= GalaxyRowUser($GalaxyRowPlanet, $GalaxyRowPlayer, $MyBuddies, $SFBStatus);
        $RowData .= GalaxyRowAlly($GalaxyRowPlayer, $MyAllyPacts);
        $RowData .= GalaxyRowActions($GalaxyRowPlanet, $GalaxyRowPlayer, $Galaxy, $System, $Planet, $MyBuddies);

        $Result .= parsetemplate($RowTPL, [
            'Data' => $RowData,
            'SetHighlight' => ($Planet == $HighlightPlanet ? ' class="rowHighlight"' : '')
        ]);
    }

    if (isFeatureEnabled(FeatureType::Expeditions)) {
        $Result .= parsetemplate(
            $RowTPL,
            [
                'Data' => GalaxyRowExpedition($Galaxy, $System),
                'SetHighlight' => ''
            ]
        );
    }

    return $Result;
}

?>
