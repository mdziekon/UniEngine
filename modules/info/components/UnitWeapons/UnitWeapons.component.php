<?php

namespace UniEngine\Engine\Modules\Info\Components\UnitWeapons;

/**
 * @param array $props
 * @param number $props['elementId']
 */
function render($props) {
    global $_Lang, $_Vars_Prices;

    $elementId = $props['elementId'];

    if (empty($_Vars_Prices[$elementId]['weapons'])) {
        return [
            'componentHTML' => $_Lang['weaponTypes'][0],
        ];
    }

    $weaponParts = array_map_withkeys(
        $_Vars_Prices[$elementId]['weapons'],
        function ($weaponTechId) use ($elementId, &$_Lang) {
            global $_Vars_CombatUpgrades;

            $weaponName = $_Lang['weaponTypes'][$weaponTechId];
            $isUpgradeable = !empty($_Vars_CombatUpgrades[$elementId][$weaponTechId]);

            if (!$isUpgradeable) {
                return $weaponName;
            }

            $upgradeMinTechLevel = $_Vars_CombatUpgrades[$elementId][$weaponTechId];

            return buildLinkHTML([
                'href' => 'infos.php',
                'query' => [
                    'gid' => $weaponTechId,
                ],
                'text' => "{$weaponName} ({$upgradeMinTechLevel})",
            ]);
        }
    );

    $componentHTML = implode(', ', $weaponParts);

    return [
        'componentHTML' => $componentHTML,
    ];
}

?>
