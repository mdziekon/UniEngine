<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Helpers;

function isNoobProtectionEnabled() {
    global $_GameConfig;

    return ($_GameConfig['noobprotection'] == 1);
}

?>
