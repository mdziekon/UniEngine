<?php

namespace UniEngine\Engine\Includes\Ares\Evaluators;

/**
 * Determine if the targeted ship's shield is powerful enough to entirely absorb
 * a singular shot, without depleting its durability.
 *
 * [Alternatively] determine if the targeted shot is NOT strong enough to
 * deplete shield's durability, meaning that the shield is able to absorb it entirely.
 */
function isShieldImpenetrable($params) {
    $shotForce = $params['shotForce'];
    $targetShipShield = $params['targetShipShield'];

    return ($shotForce < ($targetShipShield * 0.01));
}

?>
