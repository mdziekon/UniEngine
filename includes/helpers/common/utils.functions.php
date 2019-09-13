<?php

namespace UniEngine\Engine\Includes\Helpers\Common\Utils;

function groupInRows (&$elements, $rowSize) {
    $rows = [];

    foreach ($elements as $idx => $element) {
        $rowIdx = floor($idx / $rowSize);

        if (!isset($rows[$rowIdx])) {
            $rows[$rowIdx] = [];
        }

        $rows[$rowIdx][] = $element;
    }

    return $rows;
}

?>
