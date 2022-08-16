<?php

namespace UniEngine\Engine\Modules\Info\Screens\ElementInfo;

use UniEngine\Engine\Includes\Helpers\World;
use UniEngine\Engine\Modules\Info;

/**
 * @param array $params
 * @param number $params['elementId']
 * @param arrayRef $params['user']
 * @param arrayRef $params['planet']
 * @param number $params['currentTimestamp']
 */
function render($props) {
    global $_Lang, $_SkinPath;

    $elementId = $props['elementId'];
    $user = &$props['user'];
    $planet = &$props['planet'];
    $currentTimestamp = $props['currentTimestamp'];

    $screenTitle = $_Lang['nfo_page_title'];
    $QUANTUMGATE_ELEMENTID = 50;

    $isValidElement = (
        World\Elements\isStructure($elementId) ||
        World\Elements\isTechnology($elementId) ||
        World\Elements\isConstructibleInHangar($elementId)
    );

    if (!$isValidElement) {
        return message($_Lang['Infos_BadElementID'], $screenTitle);
    }

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $elementDescription = (
        !empty($_Lang['WorldElements_Detailed'][$elementId]['description_alt']) ?
            $_Lang['WorldElements_Detailed'][$elementId]['description_alt'] :
            (
                $_Lang['WorldElements_Detailed'][$elementId]['description_short'] .
                (
                    !empty($_Lang['WorldElements_Detailed'][$elementId]['description_extra']) ?
                        ('<br/><br/>' . $_Lang['WorldElements_Detailed'][$elementId]['description_extra']) :
                        ''
                )
            )
    );
    $isCurrentlyOnPlanet = ($planet['planet_type'] == 1);

    $tplBodyProps = [
        'skinpath' => $_SkinPath,
        'elementId' => $elementId,
        'elementTypeLabel' => Info\Screens\ElementInfo\Utils\getElementtypeLabel($elementId),
        'name' => $_Lang['tech'][$elementId],
        'description' => $elementDescription,
        'Insert_AllowPrettyInputBox' => (
            $user['settings_useprettyinputbox'] == 1 ?
                'true' :
                'false'
        ),

        'component_ProductionTable' => null,
        'component_UnitDetails' => null,
        'component_AdditionalControls' => null,
        'rf_info_to' => null,
        'rf_info_fr' => null,
        'AdditionalInfo' => null,
    ];

    if (
        (
            World\Elements\isPlanetaryMine($elementId) &&
            $isCurrentlyOnPlanet
        ) ||
        (
            $elementId == 4 &&
            $isCurrentlyOnPlanet
        ) ||
        (
            $elementId == 12 &&
            $isCurrentlyOnPlanet
        ) ||
        (
            $elementId == 42 &&
            !$isCurrentlyOnPlanet
        ) ||
        World\Elements\isStorageStructure($elementId) ||
        ($elementId == 117)
    ) {
        $tplBodyProps['component_ProductionTable'] = Info\Components\ProductionTable\render([
            'elementId' => $elementId,
            'user' => &$user,
            'planet' => &$planet,
            'currentTimestamp' => $currentTimestamp,
        ])['componentHTML'];
    }

    if (World\Elements\isConstructibleInHangar($elementId)) {
        $isShip = World\Elements\isShip($elementId);
        $isDefenseSystem = World\Elements\isDefenseSystem($elementId);

        $tplBodyProps['component_UnitDetails'] = Info\Components\UnitDetailsTable\render([
            'elementId' => $elementId,
            'user' => &$user,
        ])['componentHTML'];

        if (
            $isShip ||
            $isDefenseSystem
        ) {
            $tplBodyProps['rf_info_to'] = Info\Components\RapidFireAgainstList\render([
                'elementId' => $elementId,
            ])['componentHTML'];
            $tplBodyProps['rf_info_fr'] = Info\Components\RapidFireFromList\render([
                'elementId' => $elementId,
            ])['componentHTML'];
        }
    }

    if (!isOnVacation($user)) {
        $additionalControls = [];

        if ($elementId == 44) {
            $additionalControls[] = Info\Components\MissileDestructionSection\render([
                'elementId' => $elementId,
                'planet' => &$planet,
                'user' => &$user,
            ])['componentHTML'];
        }

        if ($elementId == 43) {
            $additionalControls[] = Info\Components\TeleportSection\render([
                'elementId' => $elementId,
                'planet' => &$planet,
                'user' => &$user,
            ])['componentHTML'];
        }

        if ($elementId == $QUANTUMGATE_ELEMENTID) {
            $elementLevel = World\Elements\getElementCurrentLevel($QUANTUMGATE_ELEMENTID, $planet, $user);

            if ($elementLevel > 0) {
                $tplBodyProps['AdditionalInfo'] = Info\Components\QuantumGateState\render([
                    'planet' => &$planet,
                    'currentTimestamp' => $currentTimestamp,
                ])['componentHTML'];
            }
        }

        $additionalControls[] = Info\Components\BuildingDestructionSection\render([
            'elementId' => $elementId,
            'planet' => &$planet,
            'user' => &$user,
        ])['componentHTML'];

        $tplBodyProps['component_AdditionalControls'] = implode('', $additionalControls);
    }

    $screenHTML = parsetemplate(
        $localTemplateLoader('body'),
        array_merge($_Lang, $tplBodyProps)
    );

    return display($screenHTML, $screenTitle, false);
}

?>
