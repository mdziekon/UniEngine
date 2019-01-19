<?php

function GalaxyRowPlanet($GalaxyRow, $GalaxyRowPlanet, $GalaxyRowUser, $Galaxy, $System, $Planet, $PlanetType, $MyBuddies, $MyAllyPacts)
{
    global $_Lang, $_SkinPath, $_User, $CurrentMIP, $SensonPhalanxLevel, $CurrentSystem, $CurrentGalaxy;
    static $TPL = false;
    if($TPL === false)
    {
        $TPL = gettemplate('galaxy_row_planetimg');
    }

    $Links = [];

    if($GalaxyRow['id_planet'] > 0)
    {
        if($SensonPhalanxLevel > 0)
        {
            if($GalaxyRowUser['id'] != $_User['id'])
            {
                if($GalaxyRowPlanet['galaxy'] == $CurrentGalaxy)
                {
                    $PhRange = GetPhalanxRange($SensonPhalanxLevel);
                    $SystemLimitMin = $CurrentSystem - $PhRange;
                    if($SystemLimitMin < 1)
                    {
                        $SystemLimitMin = 1;
                    }
                    $SystemLimitMax = $CurrentSystem + $PhRange;
                    if($System <= $SystemLimitMax AND $System >= $SystemLimitMin)
                    {
                        $Links[] = array('prio' => 3, 'txt' => "<a href=# onclick=&#039return Phalanx({$Galaxy},{$System},{$Planet},{$PlanetType});&#039 >{$_Lang['gl_phalanx']}</a>");
                    }
                }
            }
        }

        if($CurrentMIP > 0)
        {
            if($GalaxyRowUser['id'] != $_User['id'])
            {
                if($GalaxyRowPlanet['galaxy'] == $CurrentGalaxy)
                {
                    $MiRange = GetMissileRange();
                    $SystemLimitMin = $CurrentSystem - $MiRange;
                    if($SystemLimitMin < 1)
                    {
                        $SystemLimitMin = 1;
                    }
                    $SystemLimitMax = $CurrentSystem + $MiRange;
                    if($System <= $SystemLimitMax AND $System >= $SystemLimitMin)
                    {
                        $Links[] = array('prio' => 9, 'txt' => "<a class=missileAttack href=galaxy.php?mode=2&galaxy={$Galaxy}&system={$System}&planet={$Planet} >{$_Lang['type_mission'][10]}</a>");
                    }
                }
            }
        }

        if($GalaxyRowUser['id'] != $_User['id'])
        {
            $Links[] = array('prio' => 1, 'txt' => "<a href=# onclick=&#039return sendShips(1, {$Galaxy}, {$System}, {$Planet}, {$PlanetType});&#039 >{$_Lang['type_mission'][6]}</a>");
            $Links[] = array('prio' => 2, 'txt' => '', 'html' => '<td class=\"c hiFnt\">&nbsp;</td>');
            $Links[] = array('prio' => 4, 'txt' => "<a href=fleet.php?galaxy={$Galaxy}&amp;system={$System}&amp;planet={$Planet}&amp;planettype={$PlanetType}&amp;target_mission=1>{$_Lang['type_mission'][1]}</a>");
            if($GalaxyRowPlanet['id_owner'] > 0)
            {
                if(($GalaxyRowUser['ally_id'] == $_User['ally_id'] AND $_User['ally_id'] > 0) OR in_array($GalaxyRowUser['id'], $MyBuddies) OR (isset($MyAllyPacts[$GalaxyRowUser['ally_id']]) && $MyAllyPacts[$GalaxyRowUser['ally_id']] >= 3))
                {
                    $Links[] = array('prio' => 5, 'txt' => "<a href=fleet.php?galaxy={$Galaxy}&system={$System}&planet={$Planet}&planettype={$PlanetType}&target_mission=5>{$_Lang['type_mission'][5]}</a>");
                }
            }
        }
        else
        {
            $Links[] = array('prio' => 6, 'txt' => "<a href=fleet.php?galaxy={$Galaxy}&system={$System}&planet={$Planet}&planettype={$PlanetType}&target_mission=4>{$_Lang['type_mission'][4]}</a>");
            if(isPro() AND $_User['current_planet'] != $GalaxyRowPlanet['id'])
            {
                $Links[] = array('prio' => 8, 'txt' => "<a href=fleet.php?quickres=1&galaxy={$Galaxy}&system={$System}&planet={$Planet}&planettype={$PlanetType}&target_mission=3>{$_Lang['type_mission_quickres']}</a>");
            }
        }

        $Links[] = array('prio' => 7, 'txt' => "<a href=fleet.php?galaxy={$Galaxy}&system={$System}&planet={$Planet}&planettype={$PlanetType}&target_mission=3>{$_Lang['type_mission'][3]}</a>");

        foreach($Links as $Index => $Data)
        {
            $PriorityArray[$Index] = $Data['prio'];
        }
        array_multisort($PriorityArray, SORT_ASC, $Links);

        $Parse = array
        (
            'Lang_Planet'    => $_Lang['gl_planet'],
            'PlanetName'    => $GalaxyRowPlanet['name'],
            'Galaxy'        => $Galaxy,
            'System'        => $System,
            'Planet'        => $Planet,
            'SkinPath'        => $_SkinPath,
            'PlanetImg'        => $GalaxyRowPlanet['image'],
            'RowCount'        => count($Links),
            'FirstLink'        => $Links[0]['txt'],
            'OtherLinks'    => ''
        );

        foreach($Links as $Index => $Data)
        {
            if($Index == 0)
            {
                continue;
            }
            if(!empty($Data['html']))
            {
                $Parse['OtherLinks'] .= "<tr>{$Data['html']}</tr>";
            }
            else
            {
                $Parse['OtherLinks'] .= "<tr><th".($Data['txt'] == '&nbsp;' ? ' class=tipS' : '').">{$Data['txt']}</th></tr>";
            }
        }

        $Result = parsetemplate($TPL, $Parse);
    }
    else
    {
        $Result = '<th class="hiFnt">&nbsp;</th>';
    }

    return $Result;
}

?>
