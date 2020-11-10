<?php

namespace UniEngine\Engine\Modules\Messages\Utils;

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
