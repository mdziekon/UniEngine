<?php

function CalculateBasicResourceSet()
{
    if(SERVER_MAINOPEN_TSTAMP > 0)
    {
        $RegisterDays = floor((time() - SERVER_MAINOPEN_TSTAMP) / (24*60*60));
    }
    else
    {
        $RegisterDays = 0;
    }
    if($RegisterDays >= 1)
    {
        $Calc['metal'] = round(1000 * pow($RegisterDays / 0.2, 2));
        $Calc['crystal'] = round($Calc['metal'] / 2);
        $Calc['deuterium'] = round($Calc['metal'] / 4);
        foreach($Calc as &$Val)
        {
            if($Val > MAX_REFUND_VALUE)
            {
                $Val = MAX_REFUND_VALUE;
            }
        }
    }
    else
    {
        $Calc['metal'] = BUILD_METAL;
        $Calc['crystal'] = BUILD_CRISTAL;
        $Calc['deuterium'] = BUILD_DEUTERIUM;
    }

    return $Calc;
}

function PlanetSizeRandomiser($Position, $HomeWorld = false)
{
    global $_GameConfig;

    if(!$HomeWorld)
    {
        $ClassicBase = 150;
        $SettingSize = $_GameConfig['initial_fields'];
        $PlanetRatio = floor(($ClassicBase / $SettingSize) * 10000) / 100;
        $RandomMin = array(90, 125, 125, 205, 205, 205, 205, 205, 225, 205, 165, 155, 145, 80, 125);
        $RandomMax = array(91, 135, 135, 280, 280, 270, 220, 220, 230, 225, 180, 170, 200, 420, 190);
        $CalculMin = floor($RandomMin[$Position - 1] + ($RandomMin[$Position - 1] * $PlanetRatio) / 100);
        $CalculMax = floor($RandomMax[$Position - 1] + ($RandomMax[$Position - 1] * $PlanetRatio) / 100);
        $RandomSize = mt_rand($CalculMin, $CalculMax);
        $MaxAddon = mt_rand(0, 110);
        $MinAddon = mt_rand(0, 60);
        $Addon = ($MaxAddon - $MinAddon);
        $PlanetFields = ($RandomSize + $Addon);
    }
    else
    {
        $PlanetFields = $_GameConfig['initial_fields'];
    }
    $PlanetSize = ($PlanetFields ^ (14 / 1.5)) * 75;

    $return['diameter'] = $PlanetSize;
    $return['field_max'] = $PlanetFields;
    return $return;
}

function CreateOnePlanetRecord($Galaxy, $System, $Position, $PlanetOwnerID, $PlanetName = '', $HomeWorld = false, $AdditionalResources = false, $GetPlanetData = false, $DontCheckExistence = false)
{
    global $_Lang, $_GameConfig;

    // First, check if there is no planet on that position
    if($DontCheckExistence !== true)
    {
        $QrySelectPlanet = "SELECT `id` FROM {{table}} WHERE `galaxy` = '{$Galaxy}' AND `system` = '{$System}' AND `planet` = '{$Position}';";
        $PlanetExist = doquery($QrySelectPlanet, 'planets', true);
    }
    else
    {
        $PlanetExist = false;
    }

    // If this position is free, let's make a new planet!
    if(!$PlanetExist)
    {
        $planet = PlanetSizeRandomiser($Position, $HomeWorld);
        $planet['diameter'] = ($planet['field_max'] ^ (14 / 1.5)) * 75 ;
        if($HomeWorld)
        {
            $Res = CalculateBasicResourceSet();
            $planet['metal'] = $Res['metal'];
            $planet['crystal'] = $Res['crystal'];
            $planet['deuterium'] = $Res['deuterium'];
        }
        else
        {
            $planet['metal'] = BUILD_METAL;
            $planet['crystal'] = BUILD_CRISTAL;
            $planet['deuterium'] = BUILD_DEUTERIUM;
            if($AdditionalResources !== false)
            {
                $planet['metal'] += $AdditionalResources['metal'];
                $planet['crystal'] += $AdditionalResources['crystal'];
                $planet['deuterium'] += $AdditionalResources['deuterium'];
            }
        }
        $planet['metal_perhour'] = $_GameConfig['metal_basic_income'];
        $planet['crystal_perhour'] = $_GameConfig['crystal_basic_income'];
        $planet['deuterium_perhour'] = $_GameConfig['deuterium_basic_income'];
        $planet['metal_max'] = BASE_STORAGE_SIZE;
        $planet['crystal_max'] = BASE_STORAGE_SIZE;
        $planet['deuterium_max'] = BASE_STORAGE_SIZE;

        if($Position == 1 || $Position == 2 || $Position == 3)
        {
            $PlanetType = array('trocken');
            $PlanetDesign = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10');
            $planet['temp_min'] = rand(0, 100);
        }
        else if($Position == 4 || $Position == 5 || $Position == 6)
        {
            $PlanetType = array('dschjungel');
            $PlanetDesign = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10');
            $planet['temp_min'] = rand(-25, 75);
        }
        else if($Position == 7 || $Position == 8 || $Position == 9)
        {
            $PlanetType = array('normaltemp');
            $PlanetDesign = array('01', '02', '03', '04', '05', '06', '07');
            $planet['temp_min'] = rand(-50, 50);
        }
        else if($Position == 10 || $Position == 11 || $Position == 12)
        {
            $PlanetType = array('wasser');
            $PlanetDesign = array('01', '02', '03', '04', '05', '06', '07', '08', '09');
            $planet['temp_min'] = rand(-75, 25);
        }
        else if($Position == 13 || $Position == 14 || $Position == 15)
        {
            $PlanetType = array('eis');
            $PlanetDesign = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10');
            $planet['temp_min'] = rand(-100, 10);
        }
        else
        {
            $PlanetType = array('dschjungel', 'gas', 'normaltemp', 'trocken', 'wasser', 'wuesten', 'eis');
            $PlanetDesign = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '00',);
            $planet['temp_min'] = rand(-120, 0);
        }

        $PlanetClass = array('planet');
        $planet['temp_maxi'] = rand(30, 100);
        $planet['temp_max'] = $planet['temp_min'] + $planet['temp_maxi'];
        $planet['image'] = $PlanetType[rand(0, count($PlanetType) -1)];
        $planet['image'] .= $PlanetClass[rand(0, count($PlanetClass) - 1)];
        $planet['image'] .= $PlanetDesign[rand(0, count($PlanetDesign) - 1)];
        $planet['name'] = ($PlanetName == '') ? $_Lang['sys_colo_defaultname'] : $PlanetName;

        $QryInsertPlanet = "INSERT INTO {{table}} SET ";
        $QryInsertPlanet .= "`name` = '{$planet['name']}', ";
        $QryInsertPlanet .= "`id_owner` = '{$PlanetOwnerID}', ";
        $QryInsertPlanet .= "`galaxy` = '{$Galaxy}', ";
        $QryInsertPlanet .= "`system` = '{$System}', ";
        $QryInsertPlanet .= "`planet` = '{$Position}', ";
        $QryInsertPlanet .= "`last_update` = UNIX_TIMESTAMP(), ";
        $QryInsertPlanet .= "`planet_type` = 1, ";
        $QryInsertPlanet .= "`image` = '{$planet['image']}', ";
        $QryInsertPlanet .= "`diameter` = '{$planet['diameter']}', ";
        $QryInsertPlanet .= "`field_max` = '{$planet['field_max']}', ";
        $QryInsertPlanet .= "`temp_min` = '{$planet['temp_min']}', ";
        $QryInsertPlanet .= "`temp_max` = '{$planet['temp_max']}', ";
        $QryInsertPlanet .= "`metal` = '{$planet['metal']}', ";
        $QryInsertPlanet .= "`metal_perhour` = '{$planet['metal_perhour']}', ";
        $QryInsertPlanet .= "`metal_max` = '{$planet['metal_max']}', ";
        $QryInsertPlanet .= "`crystal` = '{$planet['crystal']}', ";
        $QryInsertPlanet .= "`crystal_perhour` = '{$planet['crystal_perhour']}', ";
        $QryInsertPlanet .= "`crystal_max` = '{$planet['crystal_max']}', ";
        $QryInsertPlanet .= "`deuterium` = '{$planet['deuterium']}', ";
        $QryInsertPlanet .= "`deuterium_perhour` = '{$planet['deuterium_perhour']}', ";
        $QryInsertPlanet .= "`deuterium_max` = '{$planet['deuterium_max']}';";
        doquery($QryInsertPlanet, 'planets');

        // Select CreatedPlanet ID
        $QrySelectPlanet = "SELECT `id` FROM {{table}} WHERE `galaxy` = '{$Galaxy}' AND `system` = '{$System}' AND `planet` = '{$Position}' AND `id_owner` = '{$PlanetOwnerID}';";
        $GetPlanetID = doquery($QrySelectPlanet , 'planets', true);

        // Select Galaxy, if there was a planet on that place already
        $QrySelectGalaxy = "SELECT * FROM {{table}} WHERE `galaxy` = '{$Galaxy}' AND `system` = '{$System}' AND `planet` = '{$Position}';";
        $GetGalaxyID = doquery($QrySelectGalaxy, 'galaxy', true);

        if($GetGalaxyID['galaxy_id'] > 0)
        {
            // Update Galaxy Record
            $QryUpdateGalaxy = "UPDATE {{table}} SET `id_planet` = {$GetPlanetID['id']} WHERE `galaxy_id` = {$GetGalaxyID['galaxy_id']};";
            doquery($QryUpdateGalaxy, 'galaxy');
        }
        else
        {
            // Create new Galaxy Record
            $QryInsertGalaxy = "INSERT INTO {{table}} SET `galaxy` = '{$Galaxy}', `system` = '{$System}', `planet` = '{$Position}', `id_planet` = {$GetPlanetID['id']};";
            doquery($QryInsertGalaxy, 'galaxy');
        }

        if($GetPlanetData)
        {
            return array('ID' => $GetPlanetID['id'], 'temp_max' => $planet['temp_max'], 'metal' => $planet['metal'], 'crystal' => $planet['crystal'], 'deuterium' => $planet['deuterium']);
        }
        else
        {
            return $GetPlanetID['id'];
        }
    }
    else
    {
        return false;
    }
}

?>
