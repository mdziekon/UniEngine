<?php

namespace UniEngine\Engine\Includes\Helpers\Common\Collections;

function firstN ($collection, $elementsCount) {
    return array_slice($collection, 0, $elementsCount);
}

?>
