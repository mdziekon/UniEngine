<?php

function GalaxyRowMoon($GalaxyRow, $GalaxyRowPlanet, $GalaxyRowUser, $Galaxy, $System, $Planet, $PlanetType, $MyBuddies, $MyAllyPacts)
{
    global $_Lang, $_User, $_SkinPath, $CanDestroy, $MoonCount;
    static $TPL = false;
    if($TPL === false)
    {
        $TPL = gettemplate('galaxy_row_moon');
    }

    $Links = [];

    if($GalaxyRow['id_moon'] > 0)
    {
        $MoonCount += 1;

        if($GalaxyRowUser['id'] != $_User['id'])
        {
            $Links[] = array('prio' => 1, 'txt' => "<a href=# onclick=&#039return sendShips(1, {$Galaxy}, {$System}, {$Planet}, {$PlanetType})&#039 >{$_Lang['type_mission'][6]}</a>");
            $Links[] = array('prio' => 2, 'txt' => '', 'html' => '<td class=\"c hiFnt\">&nbsp;</td>');
            $Links[] = array('prio' => 3, 'txt' => "<a href=fleet.php?galaxy={$Galaxy}&amp;system={$System}&amp;planet={$Planet}&amp;planettype={$PlanetType}&amp;target_mission=1>{$_Lang['type_mission'][1]}</a>");
            if($GalaxyRowUser['id'] > 0)
            {
                if(($GalaxyRowUser['ally_id'] == $_User['ally_id'] AND $_User['ally_id'] > 0) OR in_array($GalaxyRowUser['id'], $MyBuddies) OR (isset($MyAllyPacts[$GalaxyRowUser['ally_id']]) && $MyAllyPacts[$GalaxyRowUser['ally_id']] >= 3))
                {
                    $Links[] = array('prio' => 4, 'txt' => "<a href=fleet.php?galaxy={$Galaxy}&system={$System}&planet={$Planet}&planettype={$PlanetType}&target_mission=5>{$_Lang['type_mission'][5]}</a>");
                }
            }
            if($CanDestroy)
            {
                $Links[] = array('prio' => 5, 'txt' => "<a href=fleet.php?galaxy={$Galaxy}&system={$System}&planet={$Planet}&planettype={$PlanetType}&target_mission=9>{$_Lang['type_mission'][9]}</a>");
            }
        }
        else
        {
            $Links[] = array('prio' => 6, 'txt' => "<a href=fleet.php?galaxy={$Galaxy}&system={$System}&planet={$Planet}&planettype={$PlanetType}&target_mission=4>{$_Lang['type_mission'][4]}</a>");
        }
        $Links[] = array('prio' => 7, 'txt' => "<a href=fleet.php?galaxy={$Galaxy}&system={$System}&planet={$Planet}&planettype={$PlanetType}&target_mission=3>{$_Lang['type_mission'][3]}</a>");

        foreach($Links as $Index => $Data)
        {
            $PriorityArray[$Index] = $Data['prio'];
        }
        array_multisort($PriorityArray, SORT_ASC, $Links);

        $Parse = array
        (
            'Lang_Moon'            => $_Lang['Moon'],
            'MoonName'            => $GalaxyRowPlanet['name'],
            'Galaxy'            => $Galaxy,
            'System'            => $System,
            'Planet'            => $Planet,
            'SkinPath'            => $_SkinPath,
            'Lang_Diameter'        => $_Lang['diameter'],
            'Diameter'            => prettyNumber($GalaxyRowPlanet['diameter']),
            'Diameter_Units'    => $_Lang['diameter_units'],
            'RowCount'            => (count($Links) + 2),
            'Links'                => ''
        );
        foreach($Links as $Index => $Data)
        {
            if(!empty($Data['html']))
            {
                $Parse['Links'] .= "<tr>{$Data['html']}</tr>";
            }
            else
            {
                $Parse['Links'] .= "<tr><th".($Data['txt'] == '&nbsp;' ? ' class=tipS' : '').">{$Data['txt']}</th></tr>";
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
