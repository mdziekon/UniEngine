<?php

namespace UniEngine\Engine\Includes\Ares\Initializers;

function initializeUserTechs(&$userTechs) {
    $userTechs[109] = 1 + (0.1 * $userTechs[109]);
    $userTechs[110] = 1 + (0.1 * $userTechs[110]);
    $userTechs[111] = 1 + (0.1 * $userTechs[111]);

    if (empty($userTechs['TotalForceFactor'])) {
        $userTechs['TotalForceFactor'] = 1;
    }
    if (empty($userTechs['TotalShieldFactor'])) {
        $userTechs['TotalShieldFactor'] = 1;
    }
}

function initializeShipRapidFire($params) {
    global $_Vars_CombatData;

    $rapidFireTableRef = &$params['rapidFireTableRef'];
    $userTechs = &$params['userTechs'];
    $shipId = $params['shipId'];

    foreach ($_Vars_CombatData[$shipId]['sd'] as $targetId => $rapidFireShots) {
        if ($rapidFireShots <= 1) {
            continue;
        }

        if (!empty($userTechs['SDAdd'])) {
            $rapidFireShots += $userTechs['SDAdd'];
        } else if (!empty($userTechs['SDFactor'])) {
            $rapidFireShots = round(
                $rapidFireShots *
                $userTechs['SDFactor']
            );
        }

        if ($rapidFireShots <= 1) {
            continue;
        }

        if (empty($rapidFireTableRef[$shipId])) {
            $rapidFireTableRef[$shipId] = [];
        }

        $rapidFireTableRef[$shipId][$targetId] = $rapidFireShots - 1;
    }

    if (!empty($rapidFireTableRef[$shipId])) {
        arsort($rapidFireTableRef[$shipId]);
    }
}

?>
