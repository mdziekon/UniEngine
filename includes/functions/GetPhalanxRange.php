<?php

function GetPhalanxRange ($PhalanxLevel) {
    if ($PhalanxLevel < 1) {
        return 0;
    }

    return ($PhalanxLevel * $PhalanxLevel) - 1;
}

?>
