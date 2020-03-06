<?php

namespace UniEngine\Engine\Includes\Helpers\Common\Collections;

function firstN ($collection, $elementsCount) {
    return array_slice($collection, 0, $elementsCount);
}

function groupBy($collection, $iteratee) {
    $groupedCollection = [];

    foreach ($collection as $key => $value) {
        $groupKey = $iteratee($value, $key);

        if (!isset($groupedCollection[$groupKey])) {
            $groupedCollection[$groupKey] = [];
        }

        $groupedCollection[$groupKey][] = $value;
    }

    return $groupedCollection;
}

?>
