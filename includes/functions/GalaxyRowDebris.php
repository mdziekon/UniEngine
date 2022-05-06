<?php

function GalaxyRowDebris($GalaxyRow, $Galaxy, $System, $Planet)
{
    global $_Lang, $_SkinPath;

    static $TPL = false;
    if($TPL === false)
    {
        $TPL = gettemplate('galaxy_row_debris');
    }

    if (!$GalaxyRow) {
        return renderEmptyGalaxyCell();
    }

    $TotalDebris = $GalaxyRow['metal'] + $GalaxyRow['crystal'];

    if ($TotalDebris <= 0) {
        return renderEmptyGalaxyCell();
    }

    $PlanetType = 2;
    $BackgroundColor = null;
    if($TotalDebris >= 10000000)
    {
        $BackgroundColor = 'bgBig';
    }
    else if($TotalDebris >= 1000000)
    {
        $BackgroundColor = 'bgMed';
    }
    else if($TotalDebris >= 100000)
    {
        $BackgroundColor = 'bgSmall';
    }

    $Parse = array
    (
        'Lang_Debris'        => $_Lang['Debris'],
        'Galaxy'            => $Galaxy,
        'System'            => $System,
        'Planet'            => $Planet,
        'PlanetType'        => $PlanetType,
        'SkinPath'            => $_SkinPath,
        'Lang_Resource'        => $_Lang['gl_ressource'],
        'Lang_Metal'        => $_Lang['Metal'],
        'Metal'                => prettyNumber($GalaxyRow['metal']),
        'Lang_Crystal'        => $_Lang['Crystal'],
        'Crystal'            => prettyNumber($GalaxyRow['crystal']),
        'Lang_Actions'        => $_Lang['Actions'],
        'Lang_Mission'        => $_Lang['type_mission'][8],
        'BackgroundColor'    => $BackgroundColor,
    );

    return parsetemplate($TPL, $Parse);
}

?>
