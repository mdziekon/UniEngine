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

/**
 * Determine if the targeted shot is strong enough to completely
 * bypass the shield without any attenuation of its force.
 *
 * [Alternatively] Determine if the targeted ship's shield is too weak to absorb
 * any shot's force, meaning that the entire force is passing through directly to the hull.
 */
function isShotBypassingShield($params) {
    $shotForce = $params['shotForce'];
    $targetShipShield = $params['targetShipShield'];

    return (($shotForce * 0.01) >= $targetShipShield);
}

?>
