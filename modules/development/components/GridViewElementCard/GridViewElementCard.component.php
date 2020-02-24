<?php

namespace UniEngine\Engine\Modules\Development\Components\GridViewElementCard;

use UniEngine\Engine\Includes\Helpers\World\Elements;

//  Notes:
//  - Works only for Structures & Technologies
//
//  Arguments
//      - $props (Object)
//          - elementID (String)
//          - user (Object)
//          - planet (Object)
//          - isQueueActive (Boolean)
//          - elementDetails (Object)
//              - currentLevel (Number)
//              - isInQueue (Boolean)
//              - queueLevelModifier (Number)
//              - isUpgradePossible (Boolean)
//              - isUpgradeAvailable (Boolean)
//                  Whether the upgrade is available at this moment,
//                  meaning that we can start it right now.
//              - isUpgradeQueueable (Boolean)
//                  Whether the requirements for the upgrade have been met,
//                  however it's impossible to start the upgrade right now,
//                  eg. because the resources are not yet available
//                  (but they MIGHT be once the queue reaches this element).
//                  Note: this flag works with conjunction with "isQueueActive".
//              - whyUpgradeImpossible (String[])
//              - isDowngradePossible (Boolean)
//              - isDowngradeAvailable (Boolean)
//                  Similar to "isUpgradeAvailable".
//              - isDowngradeQueueable (Boolean)
//                  Similar to "isUpgradeQueueable".
//              - hasTechnologyRequirementMet (Boolean)
//              - additionalUpgradeDetailsRows (Array<String>)
//          - getUpgradeElementActionLinkHref (Function: () => String)
//          - getDowngradeElementActionLinkHref (Function: () => String)
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_SkinPath, $_Lang, $_Vars_ElementCategories;

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

    $isInQueue = $elementDetails['isInQueue'];
    $elementQueueLevelModifier = $elementDetails['queueLevelModifier'];
    $elementCurrentLevel = $elementDetails['currentLevel'];
    $isUpgradePossible = $elementDetails['isUpgradePossible'];
    $isUpgradeAvailable = $elementDetails['isUpgradeAvailable'];
    $isUpgradeQueueable = $elementDetails['isUpgradeQueueable'];
    $whyUpgradeImpossible = $elementDetails['whyUpgradeImpossible'];
    $isDowngradePossible = $elementDetails['isDowngradePossible'];
    $isDowngradeAvailable = $elementDetails['isDowngradeAvailable'];
    $isDowngradeQueueable = $elementDetails['isDowngradeQueueable'];
    $hasTechnologyRequirementMet = $elementDetails['hasTechnologyRequirementMet'];
    $additionalUpgradeDetailsRows = $elementDetails['additionalUpgradeDetailsRows'];

    $elementQueuedLevel = ($elementCurrentLevel + $elementQueueLevelModifier);
    $elementNextLevelToQueue = ($elementQueuedLevel + 1);
    $elementPrevLevelToQueue = ($elementQueuedLevel - 1);

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
                'currentLevel' => $elementCurrentLevel,
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
        'Data_ElementCurrentState'          => prettyNumber($elementCurrentLevel),
        'Data_NextUpgradeLevelToQueue'      => prettyNumber($elementNextLevelToQueue),
        'Data_NextDowngradeLevelToQueue'    => prettyNumber($elementPrevLevelToQueue),
        'Data_ElementDescription'           => $_Lang['WorldElements_Detailed'][$elementID]['description_short'],
        'Data_ElementImg_ShipClass'         => classNames([
            'shipImg' => Elements\isConstructibleInHangar($elementID),
        ]),

        'Data_UpgradeBtn_HideClass'         => classNames([
            'hide' => (!$isUpgradePossible),
        ]),
        'Data_DowngradeBtn_HideClass'       => classNames([
            'hide' => (!$isDowngradePossible),
        ]),
        'Data_UpgradeBtn_ColorClass'        => classNames([
            'buildDo_Green' => $isUpgradeAvailable,
            'buildDo_Orange' => (!$isUpgradeAvailable && $isUpgradeQueueable && $isQueueActive),
            'buildDo_Gray' => !(
                $isUpgradeAvailable ||
                (!$isUpgradeAvailable && $isUpgradeQueueable && $isQueueActive)
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

        'Data_UpgradeElementAction_LinkHref'    => $getUpgradeElementActionLinkHref(),
        'Data_DowngradeElementAction_LinkHref'  => $getDowngradeElementActionLinkHref(),

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
