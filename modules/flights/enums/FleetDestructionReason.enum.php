<?php

namespace UniEngine\Engine\Modules\Flights\Enums;

abstract class FleetDestructionReason {
    const INBATTLE_FIRSTROUND_NODAMAGE = 1;
    const INBATTLE_OTHERROUND_MADEDAMAGE = 2;
    const FRIENDDEFENSE = 3;
    const DRAW_NOBASH = 4;
    const INBATTLE_ACSLEADER = 5;
    const INBATTLE_ACSMEMBER = 6;
    const MOONDESTRUCTION_BYMOON = 7;
    const COLONIZATION = 8;
    const MISSILEATTACK = 9;
    const ESPIONAGE_SHOTDOWN = 10;
    const INBATTLE_OTHERROUND_NODAMAGE = 11;
    const INBATTLE_FIRSTROUND_MADEDAMAGE = 12;
    const ONEXPEDITION_UNKNOWN = 13;
}

?>
