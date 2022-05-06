<?php

function GalaxyRowMoon($GalaxyRow, $GalaxyRowPlanet, $GalaxyRowUser, $Galaxy, $System, $Planet, $MyBuddies, $MyAllyPacts) {
    global $_Lang, $_User, $_SkinPath, $CanDestroy, $MoonCount;
    static $TPL = false;
    if($TPL === false)
    {
        $TPL = gettemplate('galaxy_row_moon');
    }

    if ($GalaxyRow['id_moon'] <= 0) {
        return renderEmptyGalaxyCell();
    }

    $PlanetType = 3;
    $menuActions = [];

    $MoonCount += 1;

    if ($GalaxyRowUser['id'] != $_User['id']) {
        $menuActions[1] = [
            'txt' => "<a href=# onclick=&#039return sendShips(1, {$Galaxy}, {$System}, {$Planet}, {$PlanetType})&#039 >{$_Lang['type_mission'][6]}</a>",
        ];
        $menuActions[2] = [
            'txt' => '',
            'html' => '<td class=\"c hiFnt\">&nbsp;</td>',
        ];
        $menuActions[3] = [
            'txt' => "<a href=fleet.php?galaxy={$Galaxy}&amp;system={$System}&amp;planet={$Planet}&amp;planettype={$PlanetType}&amp;target_mission=1>{$_Lang['type_mission'][1]}</a>",
        ];
        if ($GalaxyRowUser['id'] > 0) {
            if (
                (
                    $GalaxyRowUser['ally_id'] == $_User['ally_id'] && $_User['ally_id'] > 0
                ) ||
                in_array($GalaxyRowUser['id'], $MyBuddies) ||
                (
                    isset($MyAllyPacts[$GalaxyRowUser['ally_id']]) && $MyAllyPacts[$GalaxyRowUser['ally_id']] >= 3
                )
            ) {
                $menuActions[4] = [
                    'txt' => "<a href=fleet.php?galaxy={$Galaxy}&system={$System}&planet={$Planet}&planettype={$PlanetType}&target_mission=5>{$_Lang['type_mission'][5]}</a>",
                ];
            }
        }
        if ($CanDestroy) {
            $menuActions[5] = [
                'txt' => "<a href=fleet.php?galaxy={$Galaxy}&system={$System}&planet={$Planet}&planettype={$PlanetType}&target_mission=9>{$_Lang['type_mission'][9]}</a>",
            ];
        }
    } else {
        $menuActions[6] = [
            'txt' => "<a href=fleet.php?galaxy={$Galaxy}&system={$System}&planet={$Planet}&planettype={$PlanetType}&target_mission=4>{$_Lang['type_mission'][4]}</a>",
        ];
    }
    $menuActions[7] = [
        'txt' => "<a href=fleet.php?galaxy={$Galaxy}&system={$System}&planet={$Planet}&planettype={$PlanetType}&target_mission=3>{$_Lang['type_mission'][3]}</a>",
    ];

    ksort($menuActions);

    $Parse = [
        'Lang_Moon'         => $_Lang['Moon'],
        'MoonName'          => $GalaxyRowPlanet['name'],
        'Galaxy'            => $Galaxy,
        'System'            => $System,
        'Planet'            => $Planet,
        'SkinPath'          => $_SkinPath,
        'Lang_Diameter'     => $_Lang['diameter'],
        'Diameter'          => prettyNumber($GalaxyRowPlanet['diameter']),
        'Diameter_Units'    => $_Lang['diameter_units'],
        'RowCount'          => (count($menuActions) + 2),
        'Links'             => '',
    ];

    foreach ($menuActions as $menuAction) {
        if (!empty($menuAction['html'])) {
            $Parse['Links'] .= "<tr>{$menuAction['html']}</tr>";

            continue;
        }

        $Parse['Links'] .= "<tr><th".($menuAction['txt'] == '&nbsp;' ? ' class=tipS' : '').">{$menuAction['txt']}</th></tr>";
    }

    return parsetemplate($TPL, $Parse);
}

?>
