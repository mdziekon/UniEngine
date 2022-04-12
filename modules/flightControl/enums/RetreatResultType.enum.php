<?php

namespace UniEngine\Engine\Modules\FlightControl\Enums;

abstract class RetreatResultType {
    const ErrorCantRetreatAnymore = 1;
    const SuccessTurnedBack = 2;
    const SuccessRetreated = 3;
    const ErrorMissileStrikeRetreat = 4;
    const ErrorIsNotOwner = 5;
}

?>
