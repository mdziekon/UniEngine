<?php

namespace UniEngine\Engine\Modules\Info\Components\TeleportSection;

use UniEngine\Engine\Includes\Helpers\Common\Collections;
use UniEngine\Engine\Modules\Info;

/**
 * @param array $props
 * @param string $props['elementId']
 * @param arrayRef $props['planet']
 * @param arrayRef $props['user']
 */
function render($props) {
    global $_EnginePath, $_Lang, $_Vars_GameElements;

    $elementId = $props['elementId'];
    $planet = &$props['planet'];
    $user = &$props['user'];

    $elementLevel = $planet[$_Vars_GameElements[$elementId]];

    if ($elementLevel <= 0) {
        return [
            'componentHTML' => '',
        ];
    }

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $nextJumpWaitTime = GetNextJumpWaitTime($planet);


    $teleportTargetMoonsListHTML = Info\Components\TeleportTargetMoonsList\render([
        'planet' => &$planet,
        'user' => &$user,
    ])['componentHTML'];
    $teleportFleetUnitSelectorsListHTML = Info\Components\TeleportFleetUnitSelectorsList\render([
        'planet' => &$planet,
    ])['componentHTML'];

    $canJumpNow = ($nextJumpWaitTime['value'] == 0);
    $hasAvailableTargetMoons = !empty($teleportTargetMoonsListHTML);
    $hasAvailableUnitsToTeleport = !empty($teleportFleetUnitSelectorsListHTML);

    if (!$canJumpNow) {
        include_once("{$_EnginePath}/includes/functions/InsertJavaScriptChronoApplet.php");
    }

    $jumpWarnings = [
        (
            !$hasAvailableTargetMoons ?
                $_Lang['gate_nomoonswithtp'] :
                null
        ),
        (
            !$hasAvailableUnitsToTeleport ?
                $_Lang['gate_noshipstotp'] :
                null
        ),
    ];
    $jumpWarnings = Collections\compact($jumpWarnings);

    $tplProps = [
        'gate_start_link' => "<a href=\"galaxy.php?mode=3&galaxy={$planet['galaxy']}&system={$planet['system']}&planet={$planet['planet']}\">[{$planet['galaxy']}:{$planet['system']}:{$planet['planet']}] {$planet['name']}</a>",

        'gate_time_script' => (
            $canJumpNow ?
                '' :
                InsertJavaScriptChronoApplet('Gate', '1', $nextJumpWaitTime['value'])
        ),
        'gate_wait_time' => (
            $canJumpNow ?
                '' :
                $_Lang['gate_nextjump_timer'].' <div id="bxxGate1">'.pretty_time($nextJumpWaitTime['value'], true).'</div>'
        ),
        'PHP_JumpGate_SubmitColor' => (
            $canJumpNow ?
                'lime' :
                'orange'
        ),
        'Gate_HideNextJumpTimer' => (
            $canJumpNow ?
                'style="display: none;"' :
                ''
        ),

        'gate_infobox' => implode('<br/>', $jumpWarnings),
        'Gate_HideInfoBox' => (
            !empty($jumpWarnings) ?
                '' :
                'style="display: none;"'
        ),
        'Gate_HideShips' => (
            (
                !$hasAvailableUnitsToTeleport ||
                !$hasAvailableTargetMoons
            ) ?
                'style="display: none;"' :
                ''
        ),
        'Gate_HideSelector' => (
            !$hasAvailableTargetMoons ?
                'style="display: none;"' :
                ''
        ),

        'gate_dest_moons' => $teleportTargetMoonsListHTML,
        'gate_fleet_rows' => $teleportFleetUnitSelectorsListHTML,
    ];

    return [
        'componentHTML' => parsetemplate(
            $localTemplateLoader('body'),
            array_merge($_Lang, $tplProps)
        ),
    ];
}

?>