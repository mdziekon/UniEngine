<?php

namespace UniEngine\Engine\Modules\Messages\Utils;

function getMessageTypes() {
    return [
        // Special types
        100,

        // Regular types
        0,
        1,
        2,
        3,
        4,
        5,
        15,
        50,
        70,
        80,
    ];
}

function isValidMessageType($typeId) {
    return in_array(
        $typeId,
        getMessageTypes()
    );
}

function _getMessageTypeColors() {
    return [
        0 => '#FFFF00',
        1 => '#FF6699',
        2 => '#FF3300',
        3 => '#FF9900',
        4 => '#9540BF',
        5 => '#009933',
        15 => '#6661FF',
        80 => 'white',
        50 => 'skyblue',
        70 => '#75F121',
        100 => '#ABABAB',
    ];
}

function getMessageTypeColor($typeId) {
    return _getMessageTypeColors()[$typeId];
}

?>
