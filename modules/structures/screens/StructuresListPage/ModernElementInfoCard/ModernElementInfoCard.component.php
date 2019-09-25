<?php

namespace UniEngine\Engine\Modules\Structures\Screens\StructuresListPage\ModernElementInfoCard;

use UniEngine\Engine\Includes\Helpers\World\Elements;
use UniEngine\Engine\Includes\Helpers\World\Resources;

//  Arguments
//      - $props (Object)
//          - user (Object)
//          - planet (Object)
//          - currentTimestamp (Number)
//          - elementID (String)
//          - elementCurrentLevel (Number)
//          - elementQueueLevelModifier (Number)
//          - isQueueActive (Boolean)
//          - isInQueue (Boolean)
//          - isUpgradeable (Boolean)
//          - isUpgradeHardBlocked (Boolean)
//          - isDowngradeHardBlocked (Boolean)
//          - hasReachedMaxLevel (Boolean)
//          - hasTechnologyRequirementsMet (Boolean)
//          - canStartUpgrade (Boolean)
//          - canQueueUpgrade (Boolean)
//          - canQueueDowngrade (Boolean)
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_Lang, $_SkinPath, $_Vars_ElementCategories;

    $tplBodyCache = [
        'infobox_card_body' => gettemplate('buildings_compact_infobox_body_structures'),
        'infobox_requirement_resource_element' => gettemplate('buildings_compact_infobox_req_res'),
        'infobox_levelmodif' => gettemplate('buildings_compact_infobox_levelmodif'),
        'infobox_additionalnfo' => gettemplate('buildings_compact_infobox_additionalnfo')
    ];

    $user = $props['user'];
    $planet = $props['planet'];
    $currentTimestamp = $props['currentTimestamp'];
    $elementID = $props['elementID'];
    $elementCurrentLevel = $props['elementCurrentLevel'];
    $elementQueueLevelModifier = $props['elementQueueLevelModifier'];
    $isQueueActive = $props['isQueueActive'];
    $isInQueue = $props['isInQueue'];
    $isUpgradeable = $props['isUpgradeable'];
    $isUpgradeHardBlocked = $props['isUpgradeHardBlocked'];
    $isDowngradeHardBlocked = $props['isDowngradeHardBlocked'];
    $hasReachedMaxLevel = $props['hasReachedMaxLevel'];
    $hasTechnologyRequirementsMet = $props['hasTechnologyRequirementsMet'];
    $canStartUpgrade = $props['canStartUpgrade'];
    $canQueueUpgrade = $props['canQueueUpgrade'];
    $canQueueDowngrade = $props['canQueueDowngrade'];

    $isProductionRelatedStructure = in_array($elementID, $_Vars_ElementCategories['prod']);

    $elementName = $_Lang['tech'][$elementID];

    $elementCurrentQueuedLevel = (
        $elementCurrentLevel +
        $elementQueueLevelModifier
    );
    $elementNextUpgradeLevelToQueue = (
        $elementCurrentLevel +
        $elementQueueLevelModifier +
        1
    );
    $elementNextDowngradeLevelToQueue = (
        $elementCurrentLevel +
        $elementQueueLevelModifier -
        1
    );

    $buildButtonColorClass = (
        $canStartUpgrade ?
        'buildDo_Green' :
        (
            $canQueueUpgrade ?
            'buildDo_Orange' :
            'buildDo_Gray'
        )
    );
    $destroyButtonColorClass = (
        $canQueueDowngrade ?
        'buildDo_Red' :
        'destroyDo_Gray'
    );

    $hideBuildInfoClass = (
        $isUpgradeHardBlocked ?
        'hide' :
        ''
    );
    $initiallyHideResourcesRequirementsClass = (
        !$hasTechnologyRequirementsMet ?
        'hide' :
        ''
    );
    $hideBuildButtonClass = (
        $isUpgradeHardBlocked ?
        'hide' :
        ''
    );
    $hideDestroyButtonClass = (
        $isDowngradeHardBlocked ?
        'hide' :
        ''
    );
    $hideBuildWarningTextClass = (
        !$isUpgradeHardBlocked ?
        'hide' :
        ''
    );

    $buildWarningComponentText = (
        $isUpgradeHardBlocked && $hasReachedMaxLevel ?
        $_Lang['ListBox_Disallow_MaxLevelReached'] :
        ''
    );
    $buildWarningComponentColorClass = (
        $isUpgradeHardBlocked ?
        'red' :
        ''
    );

    $elementLevelModifierComponentHTML = '';

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

        $elementLevelModifierComponentHTML = parsetemplate(
            $tplBodyCache['infobox_levelmodif'],
            $elementLevelModifierTPLData
        );
    }

    $elementRequirementsHeadlineComponentHTML = '';

    if ($hasTechnologyRequirementsMet) {
        $elementRequirementsHeadlineTPLBody = gettemplate('buildings_compact_infobox_req_selector_single');

        $elementRequirementsHeadlineComponentHTML = parsetemplate(
            $elementRequirementsHeadlineTPLBody,
            [
                'InfoBox_ResRequirements' => $_Lang['InfoBox_ResRequirements'],
                'BuildLevel' => prettyNumber($elementNextUpgradeLevelToQueue),
            ]
        );
    } else {
        $elementRequirementsHeadlineTPLBody = gettemplate('buildings_compact_infobox_req_selector_dual');

        $elementRequirementsHeadlineComponentHTML = parsetemplate(
            $elementRequirementsHeadlineTPLBody,
            [
                'InfoBox_RequirementsFor' => $_Lang['InfoBox_RequirementsFor'],
                'BuildLevel' => prettyNumber($elementNextUpgradeLevelToQueue),
                'InfoBox_Requirements_Res' => $_Lang['InfoBox_Requirements_Res'],
                'InfoBox_Requirements_Tech' => $_Lang['InfoBox_Requirements_Tech'],
            ]
        );
    }

    $elementTechRequirementsComponentHTML = '';

    if (!$hasTechnologyRequirementsMet) {
        $elementTechRequirementsComponentHTML = GetElementTechReq($user, $planet, $elementID, true);
    }

    $elementUpgradeTimeComponentHTML = '';

    if ($isUpgradeable) {
        $upgradeTime = GetBuildingTime($user, $planet, $elementID);

        $elementUpgradeTimeComponentHTML = pretty_time($upgradeTime);
    }

    $elementUpgradePriceComponentHTML = '';

    if ($isUpgradeable) {
        $resourceIcons = [
            'metal'         => 'metall',
            'crystal'       => 'kristall',
            'deuterium'     => 'deuterium',
            'energy'        => 'energie',
            'energy_max'    => 'energie',
            'darkEnergy'    => 'darkenergy'
        ];

        $upgradeCost = Elements\calculatePurchaseCost(
            $elementID,
            Elements\getElementState($elementID, $planet, $user),
            [
                'purchaseMode' => Elements\PurchaseMode::Upgrade
            ]
        );

        foreach ($upgradeCost as $costResourceKey => $costValue) {
            $currentResourceState = Resources\getResourceState(
                $costResourceKey,
                $user,
                $planet
            );

            $resourceCostColor = '';
            $resourceDeficitColor = '';
            $resourceDeficitValue = '&nbsp;';

            $resourceLeft = $currentResourceState - $costValue;
            $hasResourceDeficit = ($resourceLeft < 0);

            if ($hasResourceDeficit) {
                $resourceDeficitColor = 'red';
                $resourceDeficitValue = '(' . prettyNumber($resourceLeft) . ')';
                $resourceCostColor = (
                    $isQueueActive ?
                    'orange' :
                    'red'
                );
            }

            $resourceCostTPLData = [
                'SkinPath'      => $_SkinPath,
                'ResName'       => $costResourceKey,
                'ResImg'        => $resourceIcons[$costResourceKey],
                'ResColor'      => $resourceCostColor,
                'Value'         => prettyNumber($costValue),
                'ResMinusColor' => $resourceDeficitColor,
                'MinusValue'    => $resourceDeficitValue,
            ];

            $elementUpgradePriceComponentHTML .= parsetemplate(
                $tplBodyCache['infobox_requirement_resource_element'],
                $resourceCostTPLData
            );
        }
    }

    $productionUpgradeInfoComponentHTML = '';

    if ($isProductionRelatedStructure) {
        $resourceLabels = [
            'metal'         => $_Lang['Metal'],
            'crystal'       => $_Lang['Crystal'],
            'deuterium'     => $_Lang['Deuterium'],
            'energy'        => $_Lang['Energy'],
            'energy_max'    => $_Lang['Energy'],
            'darkEnergy'    => $_Lang['DarkEnergy']
        ];

        $elementProductionChangeTPLRows = [];

        // Calculate theoretical production increase
        $thisLevelProduction = getElementProduction(
            $elementID,
            $planet,
            $user,
            [
                'useCurrentBoosters' => true,
                'currentTimestamp' => $currentTimestamp,
                'customLevel' => $elementCurrentQueuedLevel,
                'customProductionFactor' => 10
            ]
        );
        $nextLevelProduction = getElementProduction(
            $elementID,
            $planet,
            $user,
            [
                'useCurrentBoosters' => true,
                'currentTimestamp' => $currentTimestamp,
                'customLevel' => $elementNextUpgradeLevelToQueue,
                'customProductionFactor' => 10
            ]
        );

        foreach ($nextLevelProduction as $resourceKey => $nextLevelResourceProduction) {
            $difference = ($nextLevelResourceProduction - $thisLevelProduction[$resourceKey]);

            if ($difference == 0) {
                continue;
            }

            $differenceFormatted = prettyNumber($difference);
            $label = $resourceLabels[$resourceKey];

            $elementProductionChangeTPLRows[] = parsetemplate(
                $tplBodyCache['infobox_additionalnfo'],
                [
                    'Label' => $label,
                    'ValueClasses' => (
                        $difference >= 0 ?
                        'lime' :
                        'red'
                    ),
                    'Value' => (
                        $difference >= 0 ?
                        ('+' . $differenceFormatted) :
                        $differenceFormatted
                    )
                ]
            );
        }

        $productionUpgradeInfoComponentHTML = implode('', $elementProductionChangeTPLRows);
    }

    $componentTPLData = [
        'SkinPath'                      => $_SkinPath,

        'ElementID'                     => $elementID,
        'ElementName'                   => $elementName,
        'ElementRealLevel'              => prettyNumber($elementCurrentLevel),
        'BuildLevel'                    => prettyNumber($elementNextUpgradeLevelToQueue),
        'DestroyLevel'                  => prettyNumber($elementNextDowngradeLevelToQueue),
        'LevelModifier'                 => $elementLevelModifierComponentHTML,
        'Desc'                          => $_Lang['WorldElements_Detailed'][$elementID]['description_short'],

        'HideBuildButton'               => $hideBuildButtonClass,
        'HideDestroyButton'             => $hideDestroyButtonClass,
        'BuildButtonColor'              => $buildButtonColorClass,
        'DestroyButtonColor'            => $destroyButtonColorClass,

        'HideBuildInfo'                 => $hideBuildInfoClass,
        'ElementRequirementsHeadline'   => $elementRequirementsHeadlineComponentHTML,
        'HideResReqDiv'                 => $initiallyHideResourcesRequirementsClass,
        'ElementPriceDiv'               => $elementUpgradePriceComponentHTML,
        'ElementTechDiv'                => $elementTechRequirementsComponentHTML,
        'BuildTime'                     => $elementUpgradeTimeComponentHTML,
        'AdditionalNfo'                 => $productionUpgradeInfoComponentHTML,

        'HideBuildWarn'                 => $hideBuildWarningTextClass,
        'BuildWarn_Color'               => $buildWarningComponentColorClass,
        'BuildWarn_Text'                => $buildWarningComponentText,

        'InfoBox_Level'                 => $_Lang['InfoBox_Level'],
        'InfoBox_Build'                 => $_Lang['InfoBox_Build'],
        'InfoBox_Destroy'               => $_Lang['InfoBox_Destroy'],
        'InfoBox_BuildTime'             => $_Lang['InfoBox_BuildTime'],
    ];

    $componentHTML = parsetemplate($tplBodyCache['infobox_card_body'], $componentTPLData);

    return [
        'componentHTML' => $componentHTML
    ];
}

?>
