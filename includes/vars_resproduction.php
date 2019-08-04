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
            'production' => function ($params) {
                $level = $params['level'];
                $productionFactor = $params['productionFactor'];

                return [
                    'metal' => ((30 * $level * pow((1.1), $level)) * (0.1 * $productionFactor)),
                    'energy' => (-1 * ((10 * $level * pow((1.1), $level)) * (0.1 * $productionFactor)))
                ];
            }
        ),
        // Crystal Mine
        2 => array
        (
            'metal' => 30,
            'crystal' => 15,
            'deuterium' => 0,
            'energy' => 0,
            'factor' => 1.6,
            'production' => function ($params) {
                $level = $params['level'];
                $productionFactor = $params['productionFactor'];

                return [
                    'crystal' => ((20 * $level * pow((1.1), $level)) * (0.1 * $productionFactor)),
                    'energy' => (-1 * ((10 * $level * pow((1.1), $level)) * (0.1 * $productionFactor)))
                ];
            }
        ),
        // Deuterium Extractor
        3 => array
        (
            'metal' => 150,
            'crystal' => 50,
            'deuterium' => 0,
            'energy' => 0,
            'factor' => 1.5,
            'production' => function ($params) {
                $level = $params['level'];
                $productionFactor = $params['productionFactor'];
                $planetTemp = $params['planetTemp'];

                return [
                    'deuterium' => (((10 * $level * pow((1.1), $level)) * (-0.002 * $planetTemp + 1.28)) * (0.1 * $productionFactor)),
                    'energy' => (-1 * ((20 * $level * pow((1.1), $level)) * (0.1 * $productionFactor)))
                ];
            }
        ),
        // Solar Energy Plant
        4 => array
        (
            'metal' => 50,
            'crystal' => 20,
            'deuterium' => 0,
            'energy' => 0,
            'factor' => 1.5,
            'production' => function ($params) {
                $level = $params['level'];
                $productionFactor = $params['productionFactor'];

                return [
                    'energy' => ((20 * $level * pow((1.1), $level)) * (0.1 * $productionFactor))
                ];
            }
        ),
        // Fusion Energy Planet
        12 => array
        (
            'metal' => 500,
            'crystal' => 200,
            'deuterium' => 100,
            'energy' => 0,
            'factor' => 1.8,
            'production' => function ($params) {
                $level = $params['level'];
                $productionFactor = $params['productionFactor'];

                return [
                    'deuterium' => (-1 * ((10 * $level * pow((1.1), $level)) * (0.1 * $productionFactor))),
                    'energy' => ((50 * $level * pow((1.1), $level)) * (0.1 * $productionFactor))
                ];
            }
        ),
        // Solar Satelite
        212 => array
        (
            'metal' => 0,
            'crystal' => 2000,
            'deuterium' => 500,
            'energy' => 0,
            'factor' => 0.5,
            'production' => function ($params) {
                // In this case, "level" means "count"
                $level = $params['level'];
                $productionFactor = $params['productionFactor'];
                $planetTemp = $params['planetTemp'];

                return [
                    'energy' => ((($planetTemp / 4) + 20) * $level * (0.1 * $productionFactor))
                ];
            }
        )
    );
}

?>
