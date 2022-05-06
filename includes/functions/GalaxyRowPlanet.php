<?php

use UniEngine\Engine\Includes\Helpers\World\Checks;

function GalaxyRowPlanet($GalaxyRow, $GalaxyRowPlanet, $GalaxyRowUser, $Galaxy, $System, $Planet, $MyBuddies, $MyAllyPacts) {
    global $_Lang, $_SkinPath, $_User, $CurrentMIP, $SensonPhalanxLevel, $CurrentSystem, $CurrentGalaxy;
    static $TPL = false;

    if ($TPL === false) {
        $TPL = gettemplate('galaxy_row_planetimg');
    }

    if ($GalaxyRow['id_planet'] <= 0) {
        return renderEmptyGalaxyCell();
    }

    $PlanetType = 1;
    $Links = [];

    $isCurrentUser = $GalaxyRowUser['id'] == $_User['id'];
    $isCurrentGalaxy = $GalaxyRowPlanet['galaxy'] == $CurrentGalaxy;

    if (
        $SensonPhalanxLevel > 0 &&
        !$isCurrentUser &&
        $isCurrentGalaxy
    ) {
        $isInRange = Checks\isTargetInRange([
            'originPosition' => $CurrentSystem,
            'targetPosition' => $System,
            'range' => GetPhalanxRange($SensonPhalanxLevel),
        ]);

        if ($isInRange) {
            $Links[3] = [
                'txt' => "<a href=# onclick=&#039return Phalanx({$Galaxy},{$System},{$Planet},{$PlanetType});&#039 >{$_Lang['gl_phalanx']}</a>",
            ];
        }
    }

    if (
        $CurrentMIP > 0 &&
        !$isCurrentUser &&
        $isCurrentGalaxy
    ) {
        $isInRange = Checks\isTargetInRange([
            'originPosition' => $CurrentSystem,
            'targetPosition' => $System,
            'range' => GetMissileRange(),
        ]);

        if ($isInRange) {
            $Links[9] = [
                'txt' => "<a class=missileAttack href=galaxy.php?mode=2&galaxy={$Galaxy}&system={$System}&planet={$Planet} >{$_Lang['type_mission'][10]}</a>",
            ];
        }
    }

    if (!$isCurrentUser) {
        $Links[1] = [
            'txt' => "<a href=# onclick=&#039return sendShips(1, {$Galaxy}, {$System}, {$Planet}, {$PlanetType});&#039 >{$_Lang['type_mission'][6]}</a>",
        ];
        $Links[2] = [
            'txt' => '',
            'html' => '<td class=\"c hiFnt\">&nbsp;</td>',
        ];
        $Links[4] = [
            'txt' => "<a href=fleet.php?galaxy={$Galaxy}&amp;system={$System}&amp;planet={$Planet}&amp;planettype={$PlanetType}&amp;target_mission=1>{$_Lang['type_mission'][1]}</a>",
        ];

        if($GalaxyRowPlanet['id_owner'] > 0)
        {
            if(($GalaxyRowUser['ally_id'] == $_User['ally_id'] AND $_User['ally_id'] > 0) OR in_array($GalaxyRowUser['id'], $MyBuddies) OR (isset($MyAllyPacts[$GalaxyRowUser['ally_id']]) && $MyAllyPacts[$GalaxyRowUser['ally_id']] >= 3))
            {
                $Links[5] = [
                    'txt' => "<a href=fleet.php?galaxy={$Galaxy}&system={$System}&planet={$Planet}&planettype={$PlanetType}&target_mission=5>{$_Lang['type_mission'][5]}</a>",
                ];
            }
        }
    }
    else
    {
        $Links[6] = [
            'txt' => "<a href=fleet.php?galaxy={$Galaxy}&system={$System}&planet={$Planet}&planettype={$PlanetType}&target_mission=4>{$_Lang['type_mission'][4]}</a>",
        ];

        if(isPro() AND $_User['current_planet'] != $GalaxyRowPlanet['id'])
        {
            $Links[8] = [
                'txt' => "<a href=fleet.php?quickres=1&galaxy={$Galaxy}&system={$System}&planet={$Planet}&planettype={$PlanetType}&target_mission=3>{$_Lang['type_mission_quickres']}</a>",
            ];
        }
    }

    $Links[7] = [
        'txt' => "<a href=fleet.php?galaxy={$Galaxy}&system={$System}&planet={$Planet}&planettype={$PlanetType}&target_mission=3>{$_Lang['type_mission'][3]}</a>",
    ];

    ksort($Links);

    $firstLink = array_shift($Links);

    $Parse = [
        'Lang_Planet'   => $_Lang['gl_planet'],
        'PlanetName'    => $GalaxyRowPlanet['name'],
        'Galaxy'        => $Galaxy,
        'System'        => $System,
        'Planet'        => $Planet,
        'SkinPath'      => $_SkinPath,
        'PlanetImg'     => $GalaxyRowPlanet['image'],
        'RowCount'      => count($Links) + 1,
        'FirstLink'     => $firstLink['txt'],
        'OtherLinks'    => ''
    ];

    foreach ($Links as $linkEntry) {
        if (!empty($linkEntry['html'])) {
            $Parse['OtherLinks'] .= "<tr>{$linkEntry['html']}</tr>";

            continue;
        }

        $Parse['OtherLinks'] .= "<tr><th".($linkEntry['txt'] == '&nbsp;' ? ' class=tipS' : '').">{$linkEntry['txt']}</th></tr>";
    }

    return parsetemplate($TPL, $Parse);
}

?>
