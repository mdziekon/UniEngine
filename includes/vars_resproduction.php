<?php

if(defined('INSIDE'))
{
    $_Vars_ResProduction = [
        // Metal Mine
        1 => [
            'production' => function ($params) {
                $level = $params['level'];
                $productionFactor = $params['productionFactor'];

                return [
                    'metal' => ((30 * $level * pow((1.1), $level)) * (0.1 * $productionFactor)),
                    'energy' => (-1 * ((10 * $level * pow((1.1), $level)) * (0.1 * $productionFactor)))
                ];
            }
        ],
        // Crystal Mine
        2 => [
            'production' => function ($params) {
                $level = $params['level'];
                $productionFactor = $params['productionFactor'];

                return [
                    'crystal' => ((20 * $level * pow((1.1), $level)) * (0.1 * $productionFactor)),
                    'energy' => (-1 * ((10 * $level * pow((1.1), $level)) * (0.1 * $productionFactor)))
                ];
            }
        ],
        // Deuterium Extractor
        3 => [
            'production' => function ($params) {
                $level = $params['level'];
                $productionFactor = $params['productionFactor'];
                $planetTemp = $params['planetTemp'];

                return [
                    'deuterium' => (((10 * $level * pow((1.1), $level)) * (-0.002 * $planetTemp + 1.28)) * (0.1 * $productionFactor)),
                    'energy' => (-1 * ((20 * $level * pow((1.1), $level)) * (0.1 * $productionFactor)))
                ];
            }
        ],
        // Solar Energy Plant
        4 => [
            'production' => function ($params) {
                $level = $params['level'];
                $productionFactor = $params['productionFactor'];

                return [
                    'energy' => ((20 * $level * pow((1.1), $level)) * (0.1 * $productionFactor))
                ];
            }
        ],
        // Fusion Energy Planet
        12 => [
            'production' => function ($params) {
                $level = $params['level'];
                $productionFactor = $params['productionFactor'];

                return [
                    'deuterium' => (-1 * ((10 * $level * pow((1.1), $level)) * (0.1 * $productionFactor))),
                    'energy' => ((50 * $level * pow((1.1), $level)) * (0.1 * $productionFactor))
                ];
            }
        ],
        // Solar Satelite
        212 => [
            'production' => function ($params) {
                // In this case, "level" means "count"
                $level = $params['level'];
                $productionFactor = $params['productionFactor'];
                $planetTemp = $params['planetTemp'];

                return [
                    'energy' => ((($planetTemp / 4) + 20) * $level * (0.1 * $productionFactor))
                ];
            }
        ]
    ];
}

?>
