<?php

namespace UniEngine\Engine\Modules\AttackSimulator\Utils\CombatTechs;

/**
 * Returns a list of combat techs with their "packed counterpart mapping"
 *
 * Note: should be synced with $_Vars_ElementCategories['techPurpose']['combat']
 */
function _getTechsMapping() {
    return [
        109 => 1,
        110 => 2,
        111 => 3,
        120 => 4,
        121 => 5,
        122 => 6,
        125 => 7,
        126 => 8,
        199 => 9,
    ];
}

function getTechsList() {
    return array_keys(_getTechsMapping());
}

function getTechPackedKey($elementId) {
    return _getTechsMapping()[$elementId];
}

function getTechStandardKey($packedId) {
    return array_flip(_getTechsMapping())[$packedId];
}

?>
