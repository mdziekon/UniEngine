<?php

function ShowGalaxyFooter($Galaxy, $System, $CurrentMIP, $CurrentRC, $CurrentSP, $CurrentCS)
{
    global $_Lang, $maxfleet_count, $fleetmax, $planetcount;

    $TPL = gettemplate('galaxy_footer');
    $Parse = array
    (
        'Galaxy'                    => $Galaxy,
        'System'                    => $System,
        'Footer_ColonizedPlanets'    => $_Lang['Footer_ColonizedPlanets'],
        'Footer_Missiles'            => $_Lang['Footer_Missiles'],
        'Footer_MissilesTitle'        => $_Lang['Footer_MissilesTitle'],
        'Footer_MissilesDestroy'    => $_Lang['Footer_MissilesDestroy'],
        'Footer_Ships'                => $_Lang['Footer_Ships'],
        'Footer_Ship_Recyclers'        => $_Lang['Footer_Ship_Recyclers'],
        'Footer_Ship_SpyProbes'        => $_Lang['Footer_Ship_SpyProbes'],
        'Footer_Ship_Colonizators'    => $_Lang['Footer_Ship_Colonizators'],
        'Footer_FlyingFleets'        => $_Lang['Footer_FlyingFleets'],
        'Input_ColonizedPlanets'    => prettyNumber($planetcount),
        'Input_LegendPopup'            => GalaxyLegendPopup(),
        'Input_Missiles'            => prettyNumber($CurrentMIP),
        'Input_Missiles_NoDisplay'    => ($CurrentMIP > 0 ? '' : 'display: none;'),
        'Input_Recyclers'            => prettyNumber($CurrentRC),
        'Input_SpyProbes'            => prettyNumber($CurrentSP),
        'Input_Colonizers'            => prettyNumber($CurrentCS),
        'Input_FlyingFleets'        => prettyNumber($maxfleet_count),
        'Input_MaxFleets'            => prettyNumber($fleetmax),
    );

    return parsetemplate($TPL, $Parse);
}

?>
