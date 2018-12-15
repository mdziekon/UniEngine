<?php

define('INSIDE', true);

$_EnginePath = './';
include($_EnginePath.'common.php');

loggedCheck();

includeLang('rocket_simulator');

$DefTech = null;
$AtkTech = null;
$IPMissilesOrg = null;
$ICMissiles = null;
$Target = 0;

if(isset($_POST['simulate']) && $_POST['simulate'] == 'yes')
{
    $FatalError = false;
    $TotalDefsCount = 0;
    $PlanetDefSys = false;

    foreach($_POST['def'] as $Item => $Value)
    {
        if($Item == 'tech')
        {
            $DefTech = intval($Value);
            if($DefTech < 0)
            {
                $DefTech = 0;
            }
        }
        else if($Item == '502')
        {
            $ICMissiles = round(floatval($Value));
            if($ICMissiles < 0)
            {
                $ICMissiles = 0;
            }
        }
        else if($Item == 'sys')
        {
            foreach($Value as $ID => $Count)
            {
                if($ID > 400 AND $ID < 500 AND !empty($_Vars_GameElements[$ID]))
                {
                    $Count = round(floatval($Count));
                    if($Count > 0)
                    {
                        $PlanetDefSys[$ID] = $Count;
                        $TotalDefsCount += $Count;
                    }
                }
            }
        }
    }

    foreach($_POST['atk'] as $Item => $Value)
    {
        if($Item == 'tech')
        {
            $AtkTech = intval($Value);
            if($AtkTech < 0)
            {
                $AtkTech = 0;
            }
        }
        else if($Item == '503')
        {
            $IPMissiles = round(floatval($Value));
            if($IPMissiles < 0)
            {
                $IPMissiles = 0;
            }
            $IPMissilesOrg = $IPMissiles;
        }
        else if($Item == 'target')
        {
            $Value = intval($Value);
            if($Value <= 0)
            {
                $Target = 0;
            }
            else
            {
                if($Value < 100 AND !empty($_Vars_GameElements[($Value + 400)]))
                {
                    $Target = $Value;
                }
                else
                {
                    $Target = 0;
                }
            }
        }
    }

    if($IPMissiles == 0)
    {
        $FatalError = $_Lang['No_IPM_Given'];
    }
    if($TotalDefsCount == 0)
    {
        if($FatalError !== false)
        {
            $FatalError .= '<br/>';
        }
        $FatalError .= $_Lang['No_Defs_Given'];
    }

    if($FatalError === false)
    {
        if($IPMissiles <= $ICMissiles)
        {
            $ResultContent = '<span style="color: orange;">'.$_Lang['IPMissiles_Destroyed'].'</span>';
        }
        else
        {
            if($ICMissiles > 0)
            {
                $IPMissiles -= $ICMissiles;
                $ResultContent = '<span style="color: orange;">'.sprintf($_Lang['IPMissiles_CapturedX'], $ICMissiles).'</span>';
            }

            include('includes/functions/CalcInterplanetaryAttack.php');

            $Attack = CalcInterplanetaryAttack($DefTech, $AtkTech, $IPMissiles, $PlanetDefSys, $Target);

            if($Attack['DestroyedTotal'] > 0)
            {
                $ResultTables['left'][] = $_Lang['Destroyed_Sys'];
                $ResultTables['left'][] = '';
                foreach($Attack['Destroyed'] as $key => $val)
                {
                    if($val > 0)
                    {
                        $ResultTables['left'][] = $_Lang['tech'][$key].' ( -'.prettyNumber($val).')';;
                    }
                }
                $ResultTables['left'][] = '';
                $ResultTables['left'][] = $_Lang['Destroyed_Sys_Count'].' '.prettyNumber($Attack['DestroyedTotal']);
                $ResultTables['left'][] = '';
                $ResultTables['left'][] = $_Lang['Lost_units_Sys'];
                $ResultTables['left'][] = prettyNumber($Attack['Metal_loss']).' '.$_Lang['units'].' '.$_Lang['Metal_rec'];
                $ResultTables['left'][] = prettyNumber($Attack['Crystal_loss']).' '.$_Lang['units'].' '.$_Lang['Crystal_rec'];
                $ResultTables['left'][] = prettyNumber($Attack['Deuterium_loss']).' '.$_Lang['units'].' '.$_Lang['Deuterium_rec'];
            }
            else
            {
                $ResultTables['left'][] = $_Lang['Nothing_destroyed'];
                $ResultTables['left'][] = '';
            }
            $ResultTables['right'][] = $_Lang['Range'];
            $ResultTables['right'][] = prettyNumber($Attack['IPM_Range']).' '.$_Lang['SquareMeters'];
            if($_GameConfig['Debris_Def_Rocket'] > 0)
            {
                $ResultTables['right'][] = '';
                $ResultTables['right'][] = $_Lang['DefDebris'];
                $ResultTables['right'][] = prettyNumber($Attack['Debris']['metal']).' '.$_Lang['units'].' '.$_Lang['Metal_rec'];
                $ResultTables['right'][] = prettyNumber($Attack['Debris']['crystal']).' '.$_Lang['units'].' '.$_Lang['Crystal_rec'];
            }
        }
        if($ICMissiles > 0)
        {
            if(!empty($ResultTables['left']))
            {
                $ResultTables['left'][] = '';
            }
            if($ICMissiles >= $IPMissilesOrg)
            {
                $ICMissilesUsed = $IPMissiles;
            }
            else
            {
                $ICMissilesUsed = $ICMissiles;
            }
            $_Lang['LostICM'] = '<span style="padding-left: 10px;">-'.prettyNumber($ICMissilesUsed).'</span>';
            $ResultTables['left'][] = $_Lang['Lost_units_ICM'];
            $ResultTables['left'][] = prettyNumber($ICMissilesUsed * $_Vars_Prices[502]['metal']).' '.$_Lang['units'].' '.$_Lang['Metal_rec'];
            $ResultTables['left'][] = prettyNumber($ICMissilesUsed * $_Vars_Prices[502]['deuterium']).' '.$_Lang['units'].' '.$_Lang['Deuterium_rec'];
        }
        if(!empty($ResultTables['right']))
        {
            $ResultTables['right'][] = '';
        }
        $ResultTables['right'][] = $_Lang['Lost_units_IPM'];
        $ResultTables['right'][] = prettyNumber($IPMissilesOrg * $_Vars_Prices[503]['metal']).' '.$_Lang['units'].' '.$_Lang['Metal_rec'];
        $ResultTables['right'][] = prettyNumber($IPMissilesOrg * $_Vars_Prices[503]['crystal']).' '.$_Lang['units'].' '.$_Lang['Crystal_rec'];
        $ResultTables['right'][] = prettyNumber($IPMissilesOrg * $_Vars_Prices[503]['deuterium']).' '.$_Lang['units'].' '.$_Lang['Deuterium_rec'];
    }
    else
    {
        $ResultContent = '<span style="color: red;">'.$FatalError.'</span>';
    }

    $_Lang['Result'] = '<tr><td class="c" colspan="4">'.$_Lang['Sim_Result'].'</td></tr>';
    if(!empty($ResultContent))
    {
        $_Lang['Result'] .= '<tr><th class="pad" colspan="4">'.$ResultContent.'</th></tr>';
    }
    if(!empty($ResultTables))
    {
        $_Lang['Result'] .= '<tr><th class="leftAl" colspan="2">'.implode('<br/>', $ResultTables['left']).'</th><th class="leftAl" colspan="2">'.implode('<br/>', $ResultTables['right']).'</th></tr>';
    }
    $_Lang['Result'] .= '<tr class="breakTR"><th></th></tr><tr class="breakTR"><th></th></tr>';

}

$_Lang['DefTech']= $_Lang['tech'][111];
$_Lang['AtkTech']= $_Lang['tech'][109];

$PrepareTargetList = '<option value="0">'.$_Lang['Everythink'].'</option>';

if($DefTech > 0)
{
    $_Lang['SetDefTech'] = $DefTech;
}
if($AtkTech > 0)
{
    $_Lang['SetAtkTech'] = $AtkTech;
}
if($IPMissilesOrg > 0)
{
    $_Lang['SetIPM'] = $IPMissilesOrg;
}
if($ICMissiles > 0)
{
    $_Lang['SetICM'] = $ICMissiles;
}

$_Lang['DefenceRows'] = '';
foreach($_Vars_GameElements as $ID => $DBName)
{
    if(!empty($DBName))
    {
        if($ID > 400 AND $ID < 500 AND !empty($_Lang['tech'][$ID]))
        {
            $ThisRow_InsertValue = null;
            if(isset($PlanetDefSys[$ID]) && $PlanetDefSys[$ID] > 0)
            {
                $ThisRow_InsertValue = ' value="'.$PlanetDefSys[$ID].'"';
            }

            $_Lang['DefenceRows'] .= '<tr><th class="leftAl">'.$_Lang['tech'][$ID].':</th><th class="leftAl"><input '.$ThisRow_InsertValue.' name="def[sys]['.$ID.']" type="text"/> '.((isset($Attack['Destroyed'][$ID]) && $Attack['Destroyed'][$ID] > 0) ? ('<span style="padding-left: 10px;">-'.prettyNumber($Attack['Destroyed'][$ID]).'</span>') : '').'</th>';
            if(!isset($TargetSel))
            {
                $_Lang['DefenceRows'] .= '<th class="leftAl">'.$_Lang['MainTarget'].'</th><th class="leftAl"><select name="atk[target]">{$PrepareTargetList}</select></th>';
                $TargetSel = true;
            }
            else
            {
                $_Lang['DefenceRows'] .= '<th class="leftAl" colspan="2">&nbsp;</th>';
            }
            $_Lang['DefenceRows'] .= '</tr>';
            $PrepareTargetList .= '<option value="'.($ID - 400).'" '.(($Target + 400) == $ID ? 'selected' : '').'>'.$_Lang['tech'][$ID].'</option>';
        }
    }
}
$_Lang['DefenceRows'] = str_replace('{$PrepareTargetList}', $PrepareTargetList, $_Lang['DefenceRows']);

//Display page
$page = parsetemplate(gettemplate('rocket_simulator'), $_Lang);

display($page, $_Lang['Title'], false);

?>
