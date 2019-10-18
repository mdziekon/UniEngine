<?php

namespace UniEngine\Engine\Includes\Helpers\Common\Collections;

function firstN ($collection, $elementsCount) {
    $newCollection = [];

    $counter = 0;

    foreach ($collection as $value) {
        if ($counter >= $elementsCount) {
            break;
        }

        $newCollection[] = $value;

        $counter++;
    }

    return $newCollection;
}

?>
