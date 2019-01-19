<?php

define('INSIDE', true);

$_EnginePath = './';
include($_EnginePath.'common.php');

loggedCheck();

includeLang('empire');

if(!isPro())
{
    message($_Lang['ThisPageOnlyForPro'], $_Lang['ProAccount']);
}

if(!isset($_GET['type']) || ($_GET['type'] != 1 && $_GET['type'] != 3))
{
    $_GET['type'] = 1;
}

$SQLResult_SelectedRows = doquery(
    "SELECT * FROM {{table}} WHERE `id_owner` = {$_User['id']} AND `planet_type` = '{$_GET['type']}';",
    'planets'
);

if($SQLResult_SelectedRows->num_rows == 0)
{
    $_GET['type'] = 1;
    $_Lang['HideMoons'] = ' style="display: none;"';
    $SelectedRows = doquery("SELECT * FROM {{table}} WHERE `id_owner` = {$_User['id']} AND `planet_type` = 1;", 'planets');
}
else
{
    if($_GET['type'] == 1)
    {
        $CheckMoons = doquery("SELECT COUNT(`id`) AS `count` FROM {{table}} WHERE `id_owner` = {$_User['id']} AND `planet_type` = 3;", 'planets', true);
        if($CheckMoons['count'] <= 0)
        {
            $_Lang['HideMoons'] = ' style="display: none;"';
        }
    }
}

$parse = $_Lang;

$planet = [];
while($p = $SQLResult_SelectedRows->fetch_assoc())
{
    $planet[] = $p;
}

$parse['PlCount'] = count($planet) + 1;

$TPL_Row = gettemplate('empire_row');
$TPL_Row_PlanetCell = gettemplate('empire_row_planetcell');
$TPL_Addon_Overflow = gettemplate('empire_addon_overflow');
$TPL_Row_StdInfo['img'] = gettemplate('empire_row_stdinfo_img');
$TPL_Row_StdInfo['name'] = gettemplate('empire_row_stdinfo_name');
$TPL_Row_StdInfo['coords'] = gettemplate('empire_row_stdinfo_coords');
$TPL_Row_StdInfo['fields'] = gettemplate('empire_row_stdinfo_fields');
$TPL_Row_StdInfo['metal'] = $TPL_Row_StdInfo['crystal'] = $TPL_Row_StdInfo['deuterium'] = gettemplate('empire_row_stdinfo_resource');
$TPL_Row_StdInfo['energy'] = gettemplate('empire_row_stdinfo_energy');

$Loop = 1;
$UserCopy = $_User;
$Now = time();

$BasicIncome['metal'] = $_GameConfig['metal_basic_income'] * $_GameConfig['resource_multiplier'];
$BasicIncome['crystal'] = $_GameConfig['crystal_basic_income'] * $_GameConfig['resource_multiplier'];
$BasicIncome['deuterium'] = $_GameConfig['deuterium_basic_income'] * $_GameConfig['resource_multiplier'];

$_Vars_ElementCategories['fleetNdef'] = array_merge($_Vars_ElementCategories['fleet'], $_Vars_ElementCategories['defense']);
foreach($_Vars_ElementCategories['build'] as $ElementID)
{
    if(!in_array($ElementID, $_Vars_ElementCategories['buildOn'][(int) $_GET['type']]))
    {
        continue;
    }
    $_Vars_ElementCategories['allowedBuild'][] = $ElementID;
}

foreach($planet as $p)
{
    $data['i'] = $Loop++;

    HandlePlanetUpdate($p, $UserCopy, $Now, true);

    if($p['energy_max'] == 0 AND abs($p['energy_used']) > 0)
    {
        $production_level = 0;
    }
    else if($p['energy_max'] > 0 AND abs($p['energy_used']) > $p['energy_max'])
    {
        $production_level = floor(($p['energy_max'] * 100) / abs($p['energy_used']));
    }
    else
    {
        $production_level = 100;
    }
    if($production_level > 100)
    {
        $production_level = 100;
    }
    $production_level = 0.01 * $production_level;

    $StoreColor = array('metal' => '', 'crystal' => '', 'deuterium' => '');
    $AddSign = array('metal' => '', 'crystal' => '', 'deuterium' => '');
    $BaseProduct = array('metal' => 0, 'crystal' => 0, 'deuterium' => 0);
    if($p['planet_type'] == 1)
    {
        $BaseProduct['metal'] = ($p['metal_perhour'] * $production_level) + $BasicIncome['metal'];
        $BaseProduct['crystal'] = ($p['crystal_perhour'] * $production_level) + $BasicIncome['crystal'];
        $BaseProduct['deuterium'] = ($p['deuterium_perhour'] * $production_level) + $BasicIncome['deuterium'];
        foreach($BaseProduct as $Type => $Value)
        {
            if($Value > 0)
            {
                $ProductColor[$Type] = 'lime';
                $AddSign[$Type] = '+';
            }
            else if($Value == 0)
            {
                $ProductColor[$Type] = 'orange';
            }
            else
            {
                $ProductColor[$Type] = 'red';
                $AddSign[$Type] = '-';
                $BaseProduct[$Type] *= -1;
            }
        }
        $StoreColor['metal'] = ($p['metal'] >= ($p['metal_max'] * MAX_OVERFLOW) ? $TPL_Addon_Overflow : '');
        $StoreColor['crystal'] = ($p['crystal'] >= ($p['crystal_max'] * MAX_OVERFLOW) ? $TPL_Addon_Overflow : '');
        $StoreColor['deuterium'] = ($p['deuterium'] >= ($p['deuterium_max'] * MAX_OVERFLOW) ? $TPL_Addon_Overflow : '');
    }
    else
    {
        $ProductColor['metal'] = 'orange';
        $ProductColor['crystal'] = 'orange';
        $ProductColor['deuterium'] = 'orange';
    }

    $EnergyTotal = $p['energy_max'] + $p['energy_used'];
    if($EnergyTotal > 0)
    {
        $EnergyColor = 'lime';
    }
    else if($EnergyTotal == 0)
    {
        $EnergyColor = 'orange';
    }
    else
    {
        $EnergyColor = 'red';
    }

    if($p['id'] == $_User['current_planet'])
    {
        $data['AddCurrent'] = 'select';
    }
    else
    {
        $data['AddCurrent'] = '';
    }
    $ElementTHStart = "<th class=\"addhover{$data['i']} pad2 fmin2 w75x {$data['AddCurrent']}\">";

    $datat = array
    (
        array('ID' => $p['id'], 'skinpath' => $_SkinPath, 'Img' => $p['image']),
        array('Name' => $p['name']),
        array('G' => $p['galaxy'], 'S' => $p['system'], 'P' => $p['planet']),
        array('Current' => $p['field_current'], 'Max' => $p['field_max']),
        array
        (
            'ID' => $p['id'],
            'res' => prettyNumber($p['metal']),
            'overflow' => $StoreColor['metal'],
            'color' => $ProductColor['metal'],
            'sign' => $AddSign['metal'],
            'production' => prettyNumber($BaseProduct['metal'])
        ),
        array
        (
            'ID' => $p['id'],
            'res' => prettyNumber($p['crystal']),
            'overflow' => $StoreColor['crystal'],
            'color' => $ProductColor['crystal'],
            'sign' => $AddSign['crystal'],
            'production' => prettyNumber($BaseProduct['crystal'])
        ),
        array
        (
            'ID' => $p['id'],
            'res' => prettyNumber($p['deuterium']),
            'overflow' => $StoreColor['deuterium'],
            'color' => $ProductColor['deuterium'],
            'sign' => $AddSign['deuterium'],
            'production' => prettyNumber($BaseProduct['deuterium'])
        ),
        array
        (
            'color' => $EnergyColor,
            'val' => prettyNumber($EnergyTotal)
        )
    );
    $f = array
    (
        'img', 'name', 'coords', 'fields', 'metal', 'crystal', 'deuterium', 'energy'
    );
    for($k = 0; $k < 8; $k += 1)
    {
        if(!isset($parse['row_'.$f[$k]]))
        {
            $parse['row_'.$f[$k]] = '';
        }
        $data['parsed'] = parsetemplate($TPL_Row_StdInfo[$f[$k]], $datat[$k]);
        $parse['row_'.$f[$k]] .= parsetemplate($TPL_Row_PlanetCell, $data);
    }

    foreach($_Vars_ElementCategories['allowedBuild'] as $ElementID)
    {
        if(!isset($r[$ElementID]))
        {
            $r[$ElementID] = '';
        }
        $data['text'] = prettyNumber($p[$_Vars_GameElements[$ElementID]])." <span class=\"fr\"><a href=\"buildings.php?cp={$p['id']}&amp;re=0&amp;cmd=insert&amp;building={$ElementID}\" class=\"lime\">+</a></span>";
        $r[$ElementID] .= $ElementTHStart.$data['text'].'</th>';
    }
    foreach($_Vars_ElementCategories['fleetNdef'] as $ElementID)
    {
        if(!isset($r[$ElementID]))
        {
            $r[$ElementID] = '';
        }
        if(in_array($ElementID, $_Vars_ElementCategories['fleet']))
        {
            $restype = 'fleet';
        }
        else
        {
            $restype = 'defense';
        }
        $data['text'] = "<a href=\"buildings.php?mode={$restype}&cp={$p['id']}&amp;re=0\">".prettyNumber($p[$_Vars_GameElements[$ElementID]])."</a>";
        $r[$ElementID] .= $ElementTHStart.$data['text'].'</th>';
    }
}

$m = array('allowedBuild', 'fleet', 'defense');
$n = array('row_buildings', 'row_ships', 'row_defense');
for($j = 0; $j < 3; $j += 1)
{
    foreach($_Vars_ElementCategories[$m[$j]] as $i)
    {
        if(!isset($parse[$n[$j]]))
        {
            $parse[$n[$j]] = '';
        }
        $data['ElementID'] = $i;
        $data['ElementName'] = $_Lang['tech'][$i];
        $data['PlanetsCells'] = $r[$i];
        $parse[$n[$j]] .= parsetemplate($TPL_Row, $data);
    }
}

display(parsetemplate(gettemplate('empire_table'), $parse), $_Lang['empire_vision'], false);

?>
