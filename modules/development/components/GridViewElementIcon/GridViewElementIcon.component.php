<?php

namespace UniEngine\Engine\Modules\Development\Components\GridViewElementIcon;

use UniEngine\Engine\Includes\Helpers\World\Elements;

//  Arguments
//      - $props (Object)
//          - elementID (String)
//          - elementDetails (Object)
//              - currentState (Number)
//              - queueLevelModifier (Number)
//              - isInQueue (Boolean)
//              - isUpgradeAvailableNow (Boolean)
//              - isUpgradeQueueableNow (Boolean)
//              - whyUpgradeImpossible (String[])
//          - getUpgradeElementActionLinkHref (Function: () => String)
//              Should return the link to the appropriate command invoker.
//              Note: returning empty string will make the link "invalid",
//              meaning the button won't be displayed at all, even if the action is possible.
//          - tabIdx (Number | undefined)
//              Index for the input element when in "countable" mode.
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_SkinPath, $_Lang;

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $tplBodyCache = [
        'body' => $localTemplateLoader('body'),
        'disable_overlay' => $localTemplateLoader('disable_overlay'),
        'level_modifier' => $localTemplateLoader('level_modifier'),
        'addon_countable_inputs' => $localTemplateLoader('addon_countable_inputs'),
    ];

    $elementID = $props['elementID'];
    $elementDetails = $props['elementDetails'];
    $getUpgradeElementActionLinkHref = $props['getUpgradeElementActionLinkHref'];
    $tabIdx = (
        isset($props['tabIdx']) && is_numeric($props['tabIdx']) ?
        $props['tabIdx'] :
        ''
    );

    $elementCurrentState = $elementDetails['currentState'];
    $elementQueueLevelModifier = $elementDetails['queueLevelModifier'];
    $isInQueue = $elementDetails['isInQueue'];
    $isUpgradeAvailableNow = $elementDetails['isUpgradeAvailableNow'];
    $isUpgradeQueueableNow = $elementDetails['isUpgradeQueueableNow'];
    $whyUpgradeImpossible = $elementDetails['whyUpgradeImpossible'];

    $upgradeElementActionBtnLinkHref = $getUpgradeElementActionLinkHref();
    $hasValidUpgradeElementActionLink = (strlen($upgradeElementActionBtnLinkHref) > 0);

    $isCountable = Elements\isConstructibleInHangar($elementID);
    $elementCurrentStateDisplay = prettyNumber($elementCurrentState);


    $subcomponentDisableReasonHTML = '';
    $subcomponentDisableOverlayHTML = '';
    $subcomponentLevelModifierHTML = '';
    $subcomponentAddonCountableInputsHTML = '';

    if (!$isUpgradeAvailableNow) {
        $subcomponentDisableReasonHTML = implode('<br/>', $whyUpgradeImpossible);

        $subcomponentDisableOverlayHTML = parsetemplate(
            $tplBodyCache['disable_overlay'],
            [
                'Data_Overlay_OpacityLevelClass' => classNames([
                    'dPart' => ($isUpgradeQueueableNow),
                ])
            ]
        );
    }

    if ($isInQueue) {
        $subcomponentLevelModifierHTML = parsetemplate(
            $tplBodyCache['level_modifier'],
            [
                'Data_Modifier_ColorClass' => classNames([
                    'red' => ($elementQueueLevelModifier < 0),
                    'orange' => ($elementQueueLevelModifier == 0),
                    'lime' => ($elementQueueLevelModifier > 0),
                ]),
                'Data_Modifier_Text' => (
                    ($elementQueueLevelModifier > 0 ? '+' : '') .
                    prettyNumber($elementQueueLevelModifier)
                ),
            ]
        );
    }

    if ($isCountable) {
        $subcomponentAddonCountableInputsHTML = parsetemplate(
            $tplBodyCache['addon_countable_inputs'],
            [
                'Data_ElementID' => $elementID,
                'Data_TabIdx' => $tabIdx,
                'Data_CountInput_InvisibleClass' => classNames([
                    'inv' => (
                        !$isUpgradeAvailableNow ||
                        !$isUpgradeQueueableNow
                    ),
                ]),
            ]
        );
    }


    $componentTPLData = [
        'Data_SkinPath'                         => $_SkinPath,

        'Data_ElementID'                        => $elementID,
        'Data_ElementName'                      => $_Lang['tech'][$elementID],
        'Data_ElementCurrentState'              => $elementCurrentStateDisplay,

        'Data_UpgradeBtn_HideClass'             => classNames([
            'hide' => (
                (
                    !$isUpgradeAvailableNow &&
                    !$isUpgradeQueueableNow
                ) ||
                !$hasValidUpgradeElementActionLink
            ),
        ]),
        'Data_UpgradeBtn_ColorClass'            => classNames([
            'buildDo_Green' => $isUpgradeAvailableNow,
            'buildDo_Orange' => (!$isUpgradeAvailableNow && $isUpgradeQueueableNow),
        ]),

        'Data_ElementBackground_ShipClass'      => classNames([
            'ship' => $isCountable,
        ]),
        'Data_ElementState_ShipClass'           => classNames([
            'count' => $isCountable,
            'bignum' => (
                $isCountable &&
                (strlen($elementCurrentStateDisplay) > 10)
            ),
        ]),

        'Data_UpgradeElementAction_LinkHref'    => $upgradeElementActionBtnLinkHref,

        'Subcomponent_UpgradeImpossibleReason'  => $subcomponentDisableReasonHTML,
        'Subcomponent_DisableOverlay'           => $subcomponentDisableOverlayHTML,
        'Subcomponent_LevelModifier'            => $subcomponentLevelModifierHTML,
        'Subcomponent_Addon_CountableInputs'    => $subcomponentAddonCountableInputsHTML,
    ];

    $componentHTML = parsetemplate($tplBodyCache['body'], $componentTPLData);

    return [
        'componentHTML' => $componentHTML
    ];
}

?>
