<?php

if(defined('INSIDE'))
{
    $_Vars_ResProduction = array
    (
        // Metal Mine
        1 => array
        (
            'metal' => 40,
            'crystal' => 10,
            'deuterium' => 0,
            'energy' => 0,
            'factor' => 1.5,
            'formule' => array
            (
                'metal'        => 'return (30 * $BuildLevel * pow((1.1), $BuildLevel)) * (0.1 * $BuildLevelFactor);',
                'crystal'    => 'return "0";',
                'deuterium' => 'return "0";',
                'energy'    => 'return - (10 * $BuildLevel * pow((1.1), $BuildLevel)) * (0.1 * $BuildLevelFactor);'
            )
        ),
        // Crystal Mine
        2 => array
        (
            'metal' => 30,
            'crystal' => 15,
            'deuterium' => 0,
            'energy' => 0,
            'factor' => 1.6,
            'formule' => array
            (
                'metal'        => 'return "0";',
                'crystal'    => 'return (20 * $BuildLevel * pow((1.1), $BuildLevel)) * (0.1 * $BuildLevelFactor);',
                'deuterium' => 'return "0";',
                'energy'    => 'return - (10 * $BuildLevel * pow((1.1), $BuildLevel)) * (0.1 * $BuildLevelFactor);'
            )
        ),
        // Deuterium Extractor
        3 => array
        (
            'metal' => 150,
            'crystal' => 50,
            'deuterium' => 0,
            'energy' => 0,
            'factor' => 1.5,
            'formule' => array
            (
                'metal'        => 'return "0";',
                'crystal'    => 'return "0";',
                'deuterium' => 'return((10 * $BuildLevel * pow((1.1), $BuildLevel)) * (-0.002 * $BuildTemp + 1.28)) * (0.1 * $BuildLevelFactor);',
                'energy'    => 'return - (20 * $BuildLevel * pow((1.1), $BuildLevel)) * (0.1 * $BuildLevelFactor);'
            )
        ),
        // Solar Energy Plant
        4 => array
        (
            'metal' => 50,
            'crystal' => 20,
            'deuterium' => 0,
            'energy' => 0,
            'factor' => 1.5,
            'formule' => array
            (
                'metal'        => 'return "0";',
                'crystal'    => 'return "0";',
                'deuterium' => 'return "0";',
                'energy'    => 'return (20 * $BuildLevel * pow((1.1), $BuildLevel)) * (0.1 * $BuildLevelFactor);'
            )
        ),
        // Fusion Energy Planet
        12 => array
        (
            'metal' => 500,
            'crystal' => 200,
            'deuterium' => 100,
            'energy' => 0,
            'factor' => 1.8,
            'formule' => array
            (
                'metal'        => 'return "0";',
                'crystal'    => 'return "0";',
                'deuterium' => 'return ((10 * $BuildLevel * pow((1.1), $BuildLevel)) * (0.1 * $BuildLevelFactor) * (-1));',
                'energy'    => 'return (50 * $BuildLevel * pow((1.1), $BuildLevel)) * (0.1 * $BuildLevelFactor);'
            )
        ),
        // Solar Satelite
        212 => array
        (
            'metal' => 0,
            'crystal' => 2000,
            'deuterium' => 500,
            'energy' => 0,
            'factor' => 0.5,
            'formule' => array
            (
                'metal'        => 'return "0";',
                'crystal'    => 'return "0";',
                'deuterium' => 'return "0";',
                'energy'    => 'return(($BuildTemp / 4) + 20) * $BuildLevel * (0.1 * $BuildLevelFactor);'
            )
        )
    );
}

?>
