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
//          - timestamp (Number)
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
    $timestamp = $props['timestamp'];
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

    $elementQueuedLevel = ($elementCurrentLevel + $elementQueueLevelModifier);
    $elementNextLevelToQueue = ($elementQueuedLevel + 1);
    $elementPrevLevelToQueue = ($elementQueuedLevel - 1);

    $isProductionRelatedStructure = in_array($elementID, $_Vars_ElementCategories['prod']);

    // Render subcomponents
    $subcomponentLevelModifierHTML = '';
    $subcomponentUpgradeTimeHTML = '';
    $subcomponentUpgradeRequirementsHTML = '';
    $subcomponentAdditionalInfoHTML = '';

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
                'isUpgradePossible' => $isUpgradePossible,
                'hasTechnologyRequirementMet' => $hasTechnologyRequirementMet,
            ],
        ]);

        $subcomponentUpgradeRequirementsHTML = $subcomponentUpgradeRequirements['componentHTML'];

        $upgradeTime = GetBuildingTime($user, $planet, $elementID);

        $subcomponentUpgradeTimeHTML = pretty_time($upgradeTime);
    }

    if ($isProductionRelatedStructure) {
        $subcomponentAdditionalInfo = UpgradeProductionChange\render([
            'elementID' => $elementID,
            'user' => $user,
            'planet' => $planet,
            'timestamp' => $timestamp,
            'elementDetails' => [
                'currentLevel' => $elementCurrentLevel,
                'queueLevelModifier' => $elementQueueLevelModifier,
            ],
        ]);

        $subcomponentAdditionalInfoHTML = $subcomponentAdditionalInfo['componentHTML'];
    }


    $componentTPLData = [
        'SkinPath'                      => $_SkinPath,

        'ElementID'                     => $elementID,
        'ElementName'                   => $_Lang['tech'][$elementID],
        'ElementRealLevel'              => prettyNumber($elementCurrentLevel),
        'BuildLevel'                    => prettyNumber($elementNextLevelToQueue),
        'DestroyLevel'                  => prettyNumber($elementPrevLevelToQueue),
        'Desc'                          => $_Lang['WorldElements_Detailed'][$elementID]['description_short'],

        'HideBuildButton'               => classNames([
            'hide' => (!$isUpgradePossible),
        ]),
        'HideDestroyButton'             => classNames([
            'hide' => (!$isDowngradePossible),
        ]),
        'BuildButtonColor'              => classNames([
            'buildDo_Green' => $isUpgradeAvailable,
            'buildDo_Orange' => (!$isUpgradeAvailable && $isUpgradeQueueable && $isQueueActive),
            'buildDo_Gray' => !(
                $isUpgradeAvailable ||
                (!$isUpgradeAvailable && $isUpgradeQueueable && $isQueueActive)
            ),
        ]),
        'DestroyButtonColor'        => classNames([
            'buildDo_Red' => (
                $isDowngradeAvailable ||
                ($isDowngradeQueueable && $isQueueActive)
            ),
            'destroyDo_Gray' => !(
                $isDowngradeAvailable ||
                ($isDowngradeQueueable && $isQueueActive)
            ),
        ]),

        'HideBuildInfo'                 => classNames([
            'hide' => (!$isUpgradePossible),
        ]),
        'HideBuildWarn'                 => classNames([
            'hide' => ($isUpgradePossible),
        ]),
        'BuildWarn_Color'               => classNames([
            'red' => (!$isUpgradePossible),
        ]),
        'BuildWarn_Text'                => implode(', ', $whyUpgradeImpossible),

        'LevelModifier'                 => $subcomponentLevelModifierHTML,
        'BuildTime'                     => $subcomponentUpgradeTimeHTML,
        'AdditionalNfo'                 => $subcomponentAdditionalInfoHTML,

        'Data_UpgradeElementAction_LinkHref' => $getUpgradeElementActionLinkHref(),
        'Data_DowngradeElementAction_LinkHref' => $getDowngradeElementActionLinkHref(),

        'SubcomponentHTML_UpgradeRequirements' => $subcomponentUpgradeRequirementsHTML,

        'InfoBox_Level'                 => $_Lang['InfoBox_Level'],
        'InfoBox_Build'                 => (
            Elements\isStructure($elementID) ?
            $_Lang['InfoBox_Build'] :
            (
                Elements\isTechnology($elementID) ?
                $_Lang['InfoBox_DoResearch'] :
                '-'
            )
        ),
        'InfoBox_Destroy'                 => (
            Elements\isStructure($elementID) ?
            $_Lang['InfoBox_Destroy'] :
            (
                Elements\isTechnology($elementID) ?
                '-' :
                '-'
            )
        ),
        'InfoBox_BuildTime'             => (
            Elements\isStructure($elementID) ?
            $_Lang['InfoBox_BuildTime'] :
            (
                Elements\isTechnology($elementID) ?
                $_Lang['InfoBox_ResearchTime'] :
                '-'
            )
        ),
    ];

    $componentHTML = parsetemplate($tplBodyCache['body'], $componentTPLData);

    return [
        'componentHTML' => $componentHTML
    ];
}

?>
