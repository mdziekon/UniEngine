<?php

namespace UniEngine\Engine\Includes\Helpers\Common\Collections;

function get($collection, $path) {
    $key = $path[0];

    if (!isset($collection[$key])) {
        return null;
    }

    if (count($path) === 1) {
        return $collection[$key];
    }

    $nestedPath = $path;
    array_shift($nestedPath);

    return get($collection[$key], $nestedPath);
}

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

function groupInRows($collection, $rowSize) {
    return groupBy($collection, function ($value, $idx) use ($rowSize) {
        return floor($idx / $rowSize);
    });
}

function compact($collection) {
    return array_filter($collection, function ($value) {
        return $value;
    });
}

function without($collection, $excludedElement) {
    return array_filter($collection, function ($value) use ($excludedElement) {
        return $value !== $excludedElement;
    });
}

function map($collection, $iteratee) {
    $mappedObject = [];

    foreach ($collection as $key => $value) {
        $mappedObject[$key] = $iteratee($value, $key);
    }

    return $mappedObject;
}

function mapEntries($collection, $iteratee) {
    $mappedObject = [];

    foreach ($collection as $key => $value) {
        $entry = $iteratee($value, $key);

        $mappedObject[$entry[0]] = $entry[1];
    }

    return $mappedObject;
}

?>
