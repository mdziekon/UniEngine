<?php

function ShowGalaxyTitles()
{
    global $_Lang;

    $TPL = gettemplate('galaxy_headers');
    $Parse = array
    (
        'Pos'                => $_Lang['Pos'],
        'Planet'            => $_Lang['Planet'],
        'Name'                => $_Lang['Name'],
        'Moon'                => $_Lang['Moon'],
        'gl_debris'            => $_Lang['gl_debris'],
        'Player'            => $_Lang['Player'],
        'PlayerPos'            => $_Lang['PlayerPos'],
        'PlayerPosTitle'    => $_Lang['PlayerPosTitle'],
        'Alliance'            => $_Lang['Alliance'],
        'Actions'            => $_Lang['Actions'],
    );

    return parsetemplate($TPL, $Parse);
}

?>
