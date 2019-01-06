<?php

function ShowGalaxySelector($Galaxy, $System)
{
    global $_Lang;

    if($Galaxy > MAX_GALAXY_IN_WORLD)
    {
        $Galaxy = MAX_GALAXY_IN_WORLD;
    }
    else if($Galaxy < 1)
    {
        $Galaxy = 1;
    }
    if($System > MAX_SYSTEM_IN_GALAXY)
    {
        $System = MAX_SYSTEM_IN_GALAXY;
    }
    else if($System < 1)
    {
        $System = 1;
    }

    $TPL = gettemplate('galaxy_selector');
    $Parse = array
    (
        'Lang_Galaxy'    => $_Lang['Galaxy'],
        'Lang_System'    => $_Lang['Solar_system'],
        'Lang_Submit'    => $_Lang['Afficher'],
        'Input_Galaxy'    => $Galaxy,
        'Input_System'    => $System
    );
    return parsetemplate($TPL, $Parse);
}

?>
