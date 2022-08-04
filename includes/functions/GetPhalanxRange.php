<?php

function GetPhalanxRange ($PhalanxLevel) {
    if ($PhalanxLevel < 1) {
        return 0;
    }
    if ($PhalanxLevel == 1) {
        return 1;
    }

    return ($PhalanxLevel * $PhalanxLevel) - 1;
}

?>
