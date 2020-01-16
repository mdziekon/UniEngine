<?php

namespace UniEngine\Engine\Includes\Helpers\World\Elements;

use UniEngine\Engine\Common\Exceptions;

function isStructure($elementID) {
    global $_Vars_ElementCategories;

    return in_array($elementID, $_Vars_ElementCategories['build']);
}

function isTechnology($elementID) {
    global $_Vars_ElementCategories;

    return in_array($elementID, $_Vars_ElementCategories['tech']);
}

function isShip($elementID) {
    global $_Vars_ElementCategories;

    return in_array($elementID, $_Vars_ElementCategories['fleet']);
}

function isDefenseSystem($elementID) {
    global $_Vars_ElementCategories;

    return in_array($elementID, $_Vars_ElementCategories['defense']);
}

function isMissile($elementID) {
    global $_Vars_ElementCategories;

    return in_array($elementID, $_Vars_ElementCategories['rockets']);
}

function isStructureAvailableOnPlanetType($elementID, $planetType) {
    global $_Vars_ElementCategories;

    if ($planetType != 1 && $planetType != 3) {
        throw new Exceptions\UniEngineException("Invalid planetType '{$planetType}'");
    }

    return in_array($elementID, $_Vars_ElementCategories['buildOn'][$planetType]);
}

function isPremiumStructure($elementID) {
    global $_Vars_PremiumBuildings;

    return (
        isset($_Vars_PremiumBuildings[$elementID]) &&
        $_Vars_PremiumBuildings[$elementID]
    );
}

function isStorageStructure($elementID) {
    global $_Vars_ElementCategories;

    return in_array($elementID, $_Vars_ElementCategories['storages']);
}

function isIndestructibleStructure($elementID) {
    global $_Vars_IndestructibleBuildings;

    return (
        isset($_Vars_IndestructibleBuildings[$elementID]) &&
        $_Vars_IndestructibleBuildings[$elementID]
    );
}

function isCancellableOnceInProgress($elementID) {
    return (
        !isPremiumStructure($elementID)
    );
}

function isConstructibleInHangar($elementID) {
    return (
        isShip($elementID) ||
        isDefenseSystem($elementID) ||
        isMissile($elementID)
    );
}

function isPurchaseable($elementID) {
    return (
        isStructure($elementID) ||
        isTechnology($elementID) ||
        isShip($elementID) ||
        isDefenseSystem($elementID) ||
        isMissile($elementID)
    );
}

function isPurchaseableByUnits($elementID) {
    return (
        isShip($elementID) ||
        isDefenseSystem($elementID) ||
        isMissile($elementID)
    );
}

function isUpgradeable($elementID) {
    return (
        isStructure($elementID) ||
        isTechnology($elementID)
    );
}

function isDowngradeable($elementID) {
    return (
        isStructure($elementID) &&
        !isIndestructibleStructure($elementID)
    );
}

function getElementMaxUpgradeLevel($elementID) {
    global $_Vars_MaxElementLevel;

    if (!isset($_Vars_MaxElementLevel[$elementID])) {
        return INF;
    }

    return $_Vars_MaxElementLevel[$elementID];
}

function getElementCurrentLevel($elementID, &$planet, &$user) {
    global $_Vars_GameElements;

    $elementKey = $_Vars_GameElements[$elementID];

    if (isStructure($elementID)) {
        if (empty($planet[$elementKey])) {
            return 0;
        }

        return $planet[$elementKey];
    }

    if (isTechnology($elementID)) {
        if (empty($user[$elementKey])) {
            return 0;
        }

        return $user[$elementKey];
    }

    throw new Exceptions\UniEngineException("Cannot retrieve element's level of an element with ID '{$elementID}'");
}

function getElementCurrentCount($elementID, &$planet, &$user) {
    global $_Vars_GameElements;

    $elementKey = $_Vars_GameElements[$elementID];

    if (isConstructibleInHangar($elementID)) {
        if (empty($planet[$elementKey])) {
            return 0;
        }

        return $planet[$elementKey];
    }

    throw new Exceptions\UniEngineException("Cannot retrieve element's level of an element with ID '{$elementID}'");
}

function getElementState($elementID, &$planet, &$user) {
    if (
        isStructure($elementID) ||
        isTechnology($elementID)
    ) {
        return [
            'level' => getElementCurrentLevel($elementID, $planet, $user)
        ];
    }

    if (isConstructibleInHangar($elementID)) {
        return [
            'count' => getElementCurrentCount($elementID, $planet, $user)
        ];
    }

    throw new Exceptions\UniEngineException("Cannot retrieve element's state of an element with ID '{$elementID}'");
}

?>
