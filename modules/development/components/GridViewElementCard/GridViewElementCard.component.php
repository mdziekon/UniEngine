<?php

namespace UniEngine\Engine\Modules\Development\Components\GridViewElementCard;

use UniEngine\Engine\Includes\Helpers\World\Elements;

//  Arguments
//      - $props (Object)
//          - elementID (String)
//          - user (Object)
//          - planet (Object)
//          - isQueueActive (Boolean)
//          - elementDetails (Object)
//              - currentState (Number)
//              - isInQueue (Boolean)
//              - queueLevelModifier (Number)
//              - isUpgradePossible (Boolean)
//              - isUpgradeAvailableNow (Boolean)
//                  Whether the upgrade is available at this moment,
//                  meaning that we can start it right now.
//              - isUpgradeQueueableNow (Boolean)
//                  Whether the requirements for the upgrade have been met,
//                  however it's impossible to start the upgrade right now,
//                  eg. because the resources are not yet available
//                  (but they MIGHT be once the queue reaches this element).
//                  Note: this flag works with conjunction with "isQueueActive".
//              - isDowngradePossible (Boolean)
//              - isDowngradeAvailable (Boolean)
//                  Similar to "isUpgradeAvailable".
//              - isDowngradeQueueable (Boolean)
//                  Similar to "isUpgradeQueueable".
//              - hasTechnologyRequirementMet (Boolean)
//              - whyUpgradeImpossible (String[])
//              - additionalUpgradeDetailsRows (Array<String>)
//          - getUpgradeElementActionLinkHref (Function: () => String)
//              Should return the link to the appropriate command invoker.
//              Note: returning empty string will make the link "invalid",
//              meaning the button won't be displayed at all, even if the action is possible.
//          - getDowngradeElementActionLinkHref (Function: () => String)
//              Should return the link to the appropriate command invoker.
//              Note: returning empty string will make the link "invalid",
//              meaning the button won't be displayed at all, even if the action is possible.
//          - hideActionBtnsContainerWhenUnavailable (Boolean | undefined) [default: false]
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_SkinPath, $_Lang;

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $tplBodyCache = [
        'body' => $localTemplateLoader('body'),
        'level_modifier_label' => $localTemplateLoader('level_modifier_label'),
    ];

    $elementID = $props['elementID'];
    $user = $props['user'];
    $planet = $props['planet'];
    $isQueueActive = $props['isQueueActive'];
    $elementDetails = $props['elementDetails'];
    $getUpgradeElementActionLinkHref = $props['getUpgradeElementActionLinkHref'];
    $getDowngradeElementActionLinkHref = $props['getDowngradeElementActionLinkHref'];
    $hideActionBtnsContainerWhenUnavailable = (
        isset($props['hideActionBtnsContainerWhenUnavailable']) ?
        $props['hideActionBtnsContainerWhenUnavailable'] :
        false
    );

    $isInQueue = $elementDetails['isInQueue'];
    $elementQueueLevelModifier = $elementDetails['queueLevelModifier'];
    $elementCurrentState = $elementDetails['currentState'];
    $isUpgradePossible = $elementDetails['isUpgradePossible'];
    $isUpgradeAvailableNow = $elementDetails['isUpgradeAvailableNow'];
    $isUpgradeQueueableNow = $elementDetails['isUpgradeQueueableNow'];
    $whyUpgradeImpossible = $elementDetails['whyUpgradeImpossible'];
    $isDowngradePossible = $elementDetails['isDowngradePossible'];
    $isDowngradeAvailable = $elementDetails['isDowngradeAvailable'];
    $isDowngradeQueueable = $elementDetails['isDowngradeQueueable'];
    $hasTechnologyRequirementMet = $elementDetails['hasTechnologyRequirementMet'];
    $additionalUpgradeDetailsRows = $elementDetails['additionalUpgradeDetailsRows'];

    $elementQueuedLevel = ($elementCurrentState + $elementQueueLevelModifier);
    $elementNextLevelToQueue = ($elementQueuedLevel + 1);
    $elementPrevLevelToQueue = ($elementQueuedLevel - 1);

    $upgradeElementActionBtnLinkHref = $getUpgradeElementActionLinkHref();
    $downgradeElementActionBtnLinkHref = $getDowngradeElementActionLinkHref();
    $hasValidUpgradeElementActionLink = (strlen($upgradeElementActionBtnLinkHref) > 0);
    $hasValidDowngradeElementActionLink = (strlen($downgradeElementActionBtnLinkHref) > 0);

    // Render subcomponents
    $subcomponentLevelModifierHTML = '';
    $subcomponentUpgradeTimeHTML = '';
    $subcomponentUpgradeRequirementsHTML = '';
    $subcomponentAdditionalInfoHTML = implode('', $additionalUpgradeDetailsRows);

    if ($isInQueue) {
        $elementLevelModifierTPLData = [
            'modColor' => null,
            'modText' => null
        ];

        if ($elementQueueLevelModifier < 0) {
            $elementLevelModifierTPLData['modColor'] = 'red';
            $elementLevelModifierTPLData['modText'] = prettyNumber($elementQueueLevelModifier);
        } else if ($elementQueueLevelModifier == 0) {
            $elementLevelModifierTPLData['modColor'] = 'orange';
            $elementLevelModifierTPLData['modText'] = '0';
        } else {
            $elementLevelModifierTPLData['modColor'] = 'lime';
            $elementLevelModifierTPLData['modText'] = '+' . prettyNumber($elementQueueLevelModifier);
        }

        $subcomponentLevelModifierHTML = parsetemplate(
            $tplBodyCache['level_modifier_label'],
            $elementLevelModifierTPLData
        );
    }

    if ($isUpgradePossible) {
        $subcomponentUpgradeRequirements = UpgradeRequirements\render([
            'elementID' => $elementID,
            'user' => $user,
            'planet' => $planet,
            'isQueueActive' => $isQueueActive,
            'elementDetails' => [
                'currentState' => $elementCurrentState,
                'queueLevelModifier' => $elementQueueLevelModifier,
                'hasTechnologyRequirementMet' => $hasTechnologyRequirementMet,
            ],
        ]);

        $subcomponentUpgradeRequirementsHTML = $subcomponentUpgradeRequirements['componentHTML'];

        $upgradeTime = GetBuildingTime($user, $planet, $elementID);

        $subcomponentUpgradeTimeHTML = pretty_time($upgradeTime);
    }


    $componentTPLData = [
        'Data_SkinPath'                     => $_SkinPath,

        'Data_ElementID'                    => $elementID,
        'Data_ElementName'                  => $_Lang['tech'][$elementID],
        'Data_ElementCurrentState'          => prettyNumber($elementCurrentState),
        'Data_NextUpgradeLevelToQueue'      => prettyNumber($elementNextLevelToQueue),
        'Data_NextDowngradeLevelToQueue'    => prettyNumber($elementPrevLevelToQueue),
        'Data_ElementDescription'           => $_Lang['WorldElements_Detailed'][$elementID]['description_short'],
        'Data_ElementImg_ShipClass'         => classNames([
            'shipImg' => Elements\isConstructibleInHangar($elementID),
        ]),

        'Data_UpgradeBtn_HideClass'         => classNames([
            'hide' => (
                !$isUpgradePossible ||
                !$hasValidUpgradeElementActionLink
            ),
        ]),
        'Data_DowngradeBtn_HideClass'       => classNames([
            'hide' => (
                !$isDowngradePossible ||
                !$hasValidDowngradeElementActionLink
            ),
        ]),
        'Data_ActionBtns_HideClass'         => classNames([
            'hide' => (
                (
                    !$isUpgradePossible ||
                    !$hasValidUpgradeElementActionLink
                ) &&
                (
                    !$isDowngradePossible ||
                    !$hasValidDowngradeElementActionLink
                ) &&
                $hideActionBtnsContainerWhenUnavailable
            ),
        ]),

        'Data_UpgradeBtn_ColorClass'        => classNames([
            'buildDo_Green' => $isUpgradeAvailableNow,
            'buildDo_Orange' => (!$isUpgradeAvailableNow && $isUpgradeQueueableNow),
            'buildDo_Gray' => !(
                $isUpgradeAvailableNow ||
                (!$isUpgradeAvailableNow && $isUpgradeQueueableNow)
            ),
        ]),
        'Data_DowngradeBtn_ColorClass'      => classNames([
            'buildDo_Red' => (
                $isDowngradeAvailable ||
                ($isDowngradeQueueable && $isQueueActive)
            ),
            'destroyDo_Gray' => !(
                $isDowngradeAvailable ||
                ($isDowngradeQueueable && $isQueueActive)
            ),
        ]),

        'Data_UpgradeInfo_HideClass'        => classNames([
            'hide' => (!$isUpgradePossible),
        ]),
        'Data_UpgradeImpossible_HideClass'  => classNames([
            'hide' => ($isUpgradePossible),
        ]),
        'Data_UpgradeImpossible_ReasonText' => implode(', ', $whyUpgradeImpossible),

        'Data_UpgradeElementAction_LinkHref'    => $upgradeElementActionBtnLinkHref,
        'Data_DowngradeElementAction_LinkHref'  => $downgradeElementActionBtnLinkHref,

        'Subcomponent_LevelModifier'        => $subcomponentLevelModifierHTML,
        'Subcomponent_BuildTime'            => $subcomponentUpgradeTimeHTML,
        'Subcomponent_AdditionalNfo'        => $subcomponentAdditionalInfoHTML,
        'Subcomponent_UpgradeRequirements'  => $subcomponentUpgradeRequirementsHTML,

        'Lang_InfoBox_CurrentState'         => (
            (Elements\isStructure($elementID) || Elements\isTechnology($elementID)) ?
            $_Lang['InfoBox_Level'] :
            "{$_Lang['InfoBox_Count']}:"
        ),
        'Lang_InfoBox_UpgradeAction'        => (
            Elements\isStructure($elementID) ?
            $_Lang['InfoBox_Build'] :
            (
                Elements\isTechnology($elementID) ?
                $_Lang['InfoBox_DoResearch'] :
                '-'
            )
        ),
        'Lang_InfoBox_DowngradeAction'       => (
            Elements\isStructure($elementID) ?
            $_Lang['InfoBox_Destroy'] :
            (
                Elements\isTechnology($elementID) ?
                '-' :
                '-'
            )
        ),
        'Lang_InfoBox_BuildTime'             => (
            Elements\isStructure($elementID) ?
            $_Lang['InfoBox_BuildTime'] :
            (
                Elements\isTechnology($elementID) ?
                $_Lang['InfoBox_ResearchTime'] :
                (
                    Elements\isConstructibleInHangar($elementID) ?
                    $_Lang['InfoBox_ConstructionTime'] :
                    '-'
                )
            )
        ),
    ];

    $componentHTML = parsetemplate($tplBodyCache['body'], $componentTPLData);

    return [
        'componentHTML' => $componentHTML
    ];
}

?>
