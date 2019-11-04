<?php

namespace UniEngine\Engine\Modules\Development\Screens\ResearchListPage\LegacyElementListItem;

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
//          - hasTechnologyRequirementsMet (Boolean)
//          - canStartUpgrade (Boolean)
//          - canQueueUpgrade (Boolean)
//          - upgradeBlockersList (Array<String>)
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_Lang, $_SkinPath, $_Vars_ElementCategories;

    $tplBodyCache = [
        'list_element_body' => gettemplate('buildings_legacy_list_element_research'),
        'list_element_resourcerequirements_element' => gettemplate('buildings_legacy_list_element_resourcerequirements_element'),
        'list_element_resourcerest_element' => gettemplate('buildings_legacy_list_element_resourcerest_element'),
        'list_element_productionchange_element' => gettemplate('buildings_legacy_list_element_productionchange_element')
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
    $hasTechnologyRequirementsMet = $props['hasTechnologyRequirementsMet'];
    $canStartUpgrade = $props['canStartUpgrade'];
    $canQueueUpgrade = $props['canQueueUpgrade'];
    $upgradeBlockersList = $props['upgradeBlockersList'];

    $elementName = $_Lang['tech'][$elementID];
    $elementUpgradeTime = GetBuildingTime($user, $planet, $elementID);
    $isProductionRelatedStructure = in_array($elementID, $_Vars_ElementCategories['prod']);
    $isExtendedViewEnabled = ($user['settings_ExpandedBuildView'] != 0);

    $elementCurrentQueuedLevel = (
        $elementCurrentLevel +
        $elementQueueLevelModifier
    );
    $elementNextUpgradeLevelToQueue = (
        $elementCurrentLevel +
        $elementQueueLevelModifier +
        1
    );

    $hideNextUpgradeLevelClass = (
        !$isInQueue ?
        'hide' :
        ''
    );
    $hideResourceRequirementsClass = (
        !$isUpgradeable ?
        'hide' :
        ''
    );
    $hideProductionChangeLineClass = (
        !$isProductionRelatedStructure ?
        'hide' :
        ''
    );
    $hideTechRequirementsClass = (
        !(
            $isUpgradeable &&
            !$hasTechnologyRequirementsMet &&
            $isExtendedViewEnabled
        ) ?
        'hide' :
        ''
    );

    $elementUpgradePriceComponentHTML = '';
    $elementUpgradeRestResourcesComponentHTML = '';

    if ($isUpgradeable) {
        $resourceLabels = [
            'metal'         => $_Lang['Metal'],
            'crystal'       => $_Lang['Crystal'],
            'deuterium'     => $_Lang['Deuterium'],
            'energy'        => $_Lang['Energy'],
            'energy_max'    => $_Lang['Energy'],
            'darkEnergy'    => $_Lang['DarkEnergy']
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

            $resourceLeft = $currentResourceState - $costValue;
            $hasResourceDeficit = ($resourceLeft < 0);

            $resourceCostColor = (
                !$hasResourceDeficit ?
                '' :
                (
                    $isQueueActive ?
                    'orange' :
                    'red'
                )
            );
            $resourceRestColor = (
                !$hasResourceDeficit ?
                'rgb(95, 127, 108)' :
                'rgb(127, 95, 96)'
            );

            $resourceCostTPLData = [
                'ResourceName'              => $resourceLabels[$costResourceKey],
                'ResourceStateColorClass'   => $resourceCostColor,
                'ResourceCurrentState'      => prettyNumber($costValue),
            ];
            $resourceRestTPLData = [
                'ResourceName'              => $resourceLabels[$costResourceKey],
                'ResourceStateColorClass'   => $resourceRestColor,
                'ResourceCurrentState'      => prettyNumber($resourceLeft),
            ];

            $elementUpgradePriceComponentHTML .= parsetemplate(
                $tplBodyCache['list_element_resourcerequirements_element'],
                $resourceCostTPLData
            );
            $elementUpgradeRestResourcesComponentHTML .= parsetemplate(
                $tplBodyCache['list_element_resourcerest_element'],
                $resourceRestTPLData
            );
        }
    }

    $elementTechnologicalRequirementsComponentHTML = '';

    if (
        $isUpgradeable &&
        !$hasTechnologyRequirementsMet &&
        $isExtendedViewEnabled
    ) {
        $elementTechnologicalRequirementsComponentHTML = GetElementTechReq($user, $planet, $elementID);
    }

    $elementUpgradeButtonComponentHTML = '';

    if ($canStartUpgrade || $canQueueUpgrade) {
        $upgradeButtonText = (
            $isQueueActive ?
            "{$_Lang['InBuildQueue']}<br/>({$_Lang['level']} {$elementNextUpgradeLevelToQueue})" :
            (
                $elementNextUpgradeLevelToQueue == 1 ?
                "{$_Lang['BuildFirstLevel']}" :
                "{$_Lang['BuildNextLevel']} {$elementNextUpgradeLevelToQueue}"
            )
        );

        $elementUpgradeButtonComponentHTML = buildLinkHTML([
            'text' => $upgradeButtonText,
            'href' => '',
            'query' => [
                'mode' => 'research',
                'cmd' => 'search',
                'tech' => $elementID
            ],
            'attrs' => [
                'class' => (
                    $canStartUpgrade ?
                    'lime' :
                    'orange'
                )
            ]
        ]);
    } else {
        $elementUpgradeButtonComponentHTML = buildDOMElementHTML([
            'tagName' => 'span',
            'contentHTML' => end($upgradeBlockersList),
            'attrs' => [
                'class' => 'red'
            ]
        ]);
    }

    $componentTPLData = [
        'SkinPath'                              => $_SkinPath,

        'ElementID'                             => $elementID,
        'ElementName'                           => $elementName,
        'ElementRealLevel'                      => prettyNumber($elementCurrentLevel),
        'Desc'                                  => $_Lang['WorldElements_Detailed'][$elementID]['description_short'],
        'UpgradeTime'                           => pretty_time($elementUpgradeTime),

        'Data_NextUpgradeLevel'                 => prettyNumber($elementNextUpgradeLevelToQueue),
        'Data_HideNextUpgradeLevelClass'        => $hideNextUpgradeLevelClass,
        'Data_HideResourceRequirements'         => $hideResourceRequirementsClass,
        'Data_HideProductionChangeLineClass'    => $hideProductionChangeLineClass,
        'Data_HideTechRequirementsClass'        => $hideTechRequirementsClass,

        'Component_UpgradePriceList'            => $elementUpgradePriceComponentHTML,
        'Component_UpgradeRestResourcesList'    => $elementUpgradeRestResourcesComponentHTML,
        'Component_TechnologicalRequirements'   => $elementTechnologicalRequirementsComponentHTML,
        'Component_UpgradeButton'               => $elementUpgradeButtonComponentHTML,

        'InfoBox_Level'                         => $_Lang['InfoBox_Level'],
        'Requires'                              => $_Lang['Requires'],
        'ConstructionTime'                      => $_Lang['ConstructionTime'],
        'Lang_ResourcesLeft'                    => $_Lang['ResourcesLeft'],
        'Lang_ProductionChange'                 => $_Lang['ProductionChange']
    ];

    $componentTPLBody = $tplBodyCache['list_element_body'];
    $componentHTML = parsetemplate($componentTPLBody, $componentTPLData);

    return [
        'componentHTML' => $componentHTML
    ];
}

?>
