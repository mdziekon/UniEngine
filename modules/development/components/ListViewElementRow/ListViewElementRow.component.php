<?php

namespace UniEngine\Engine\Modules\Development\Components\ListViewElementRow;

use UniEngine\Engine\Includes\Helpers\World\Elements;

//  Arguments
//      - $props (Object)
//          - elementID (String)
//          - user (Object)
//          - planet (Object)
//          - timestamp (Number)
//          - isQueueActive (Number)
//          - elementDetails (Object)
//              - currentState (Number)
//              - isInQueue (Boolean)
//              - queueLevelModifier (Number)
//              - hasTechnologyRequirementMet (Boolean)
//              - isUpgradeAvailableNow (Boolean)
//              - isUpgradeQueueableNow (Boolean)
//              - whyUpgradeImpossible (String[])
//          - getUpgradeElementActionLinkHref (Function: () => String)
//              Should return the link to the appropriate command invoker.
//              Note: returning empty string will make the link "invalid",
//              meaning the button won't be displayed at all, even if the action is possible.
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_EnginePath, $_SkinPath, $_Lang, $_Vars_PremiumBuildingPrices, $_Vars_ElementCategories;

    include_once($_EnginePath . 'includes/functions/GetElementTechReq.php');
    include_once($_EnginePath . 'includes/functions/GetElementPrice.php');
    include_once($_EnginePath . 'includes/functions/GetRestPrice.php');

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $tplBodyCache = [
        'body' => $localTemplateLoader('body'),
        'current_state_label' => $localTemplateLoader('current_state_label'),
    ];

    $elementID = $props['elementID'];
    $user = $props['user'];
    $planet = $props['planet'];
    $timestamp = $props['timestamp'];
    $isQueueActive = $props['isQueueActive'];
    $elementDetails = $props['elementDetails'];
    $getUpgradeElementActionLinkHref = $props['getUpgradeElementActionLinkHref'];

    $elementCurrentState = $elementDetails['currentState'];
    $isInQueue = $elementDetails['isInQueue'];
    $elementQueueLevelModifier = $elementDetails['queueLevelModifier'];
    $hasTechnologyRequirementMet = $elementDetails['hasTechnologyRequirementMet'];
    $isUpgradeAvailableNow = $elementDetails['isUpgradeAvailableNow'];
    $isUpgradeQueueableNow = $elementDetails['isUpgradeQueueableNow'];
    $whyUpgradeImpossible = $elementDetails['whyUpgradeImpossible'];

    $elementQueuedLevel = ($elementCurrentState + $elementQueueLevelModifier);
    $elementNextLevelToQueue = ($elementQueuedLevel + 1);

    if (!$hasTechnologyRequirementMet && $user['settings_ExpandedBuildView'] == 0) {
        return [
            'componentHTML' => '',
        ];
    }

    $subcomponentCurrentStateLabelHTML = '';
    $subcomponentProductionChangeRowHTML = '';
    $subcomponentUpgradeLevelHeaderRowHTML = '';
    $subcomponentUpgradeTimeRowHTML = '';
    $subcomponentUpgradeCostRowHTML = '';
    $subcomponentUpgradeResourcesLeftoverRowHTML = '';
    $subcomponentTechnologyRequirementsListHTML = '';
    $subcomponentUpgradeActionLinkHTML = '';

    if (in_array($elementID, $_Vars_ElementCategories['prod'])) {
        $thisLevelProduction = getElementProduction(
            $elementID,
            $planet,
            $user,
            [
                'useCurrentBoosters' => true,
                'currentTimestamp' => $timestamp,
                'customLevel' => $elementQueuedLevel,
                'customProductionFactor' => 10
            ]
        );
        $nextLevelProduction = getElementProduction(
            $elementID,
            $planet,
            $user,
            [
                'useCurrentBoosters' => true,
                'currentTimestamp' => $timestamp,
                'customLevel' => $elementNextLevelToQueue,
                'customProductionFactor' => 10
            ]
        );

        $energyDifference = ($nextLevelProduction['energy'] - $thisLevelProduction['energy']);
        $deuteriumDifference = ($nextLevelProduction['deuterium'] - $thisLevelProduction['deuterium']);

        $energyDifferenceFormatted = prettyColorNumber(floor($energyDifference));

        if ($elementID >= 1 && $elementID <= 3) {
            $subcomponentProductionChangeRowHTML = "(<span class=\"red\">{$_Lang['Energy']}: {$energyDifferenceFormatted}</span>)";
        } else if ($elementID == 4) {
            $subcomponentProductionChangeRowHTML = "(<span class=\"lime\">{$_Lang['Energy']}: +{$energyDifferenceFormatted}</span>)";
        } else if ($elementID == 12) {
            $deuteriumDifferenceFormatted = prettyColorNumber(floor($deuteriumDifference));

            $subcomponentProductionChangeRowHTML = "(<span class=\"lime\">{$_Lang['Energy']}: +{$energyDifferenceFormatted}</span> | <span class=\"red\">{$_Lang['Deuterium']}: {$deuteriumDifferenceFormatted}</span>)";
        }
    }

    if ($elementCurrentState > 0) {
        $subcomponentCurrentStateLabelHTML = parsetemplate(
            $tplBodyCache['current_state_label'],
            [
                'Lang_Level' => $_Lang['level'],
                'Data_ElementCurrentState' => prettyNumber($elementCurrentState),
            ]
        );
    }

    if ($hasTechnologyRequirementMet) {
        $elementUpgradeTime = GetBuildingTime($user, $planet, $elementID);

        $subcomponentUpgradeTimeRowHTML = ShowBuildTime($elementUpgradeTime);

        if (!Elements\isPremiumStructure($elementID)) {
            $subcomponentUpgradeCostRowHTML = GetElementPrice($user, $planet, $elementID);
            $subcomponentUpgradeResourcesLeftoverRowHTML = GetRestPrice($user, $planet, $elementID);
        } else {
            $resourceDiffValue = $user['darkEnergy'] - $_Vars_PremiumBuildingPrices[$elementID];
            $resourceDiffCostColor = (
                $resourceDiffValue < 0 ?
                'red' :
                'lime'
            );
            $resourceDiffRestColor = (
                $resourceDiffValue < 0 ?
                'rgb(127, 95, 96)' :
                'rgb(95, 127, 108)'
            );

            $subcomponentUpgradeCostRowHTML = (
                "{$_Lang['Requires']}: {$_Lang['DarkEnergy']} <span class=\"noresources\">" .
                " <b class=\"{$resourceDiffCostColor}\"> " . prettyNumber($_Vars_PremiumBuildingPrices[$elementID]) . "</b></span> "
            );

            $subcomponentUpgradeResourcesLeftoverRowHTML = (
                "<br/>" .
                "<font color=\"#7f7f7f\">{$_Lang['ResourcesLeft']}: {$_Lang['DarkEnergy']}" .
                "<b style=\"color: {$resourceDiffRestColor};\"> " . prettyNumber($resourceDiffValue) . "</b>" .
                "</font>"
            );
        }

        if ($isInQueue && $elementQueueLevelModifier != 0) {
            $subcomponentUpgradeLevelHeaderRowHTML = (
                "<b>[{$_Lang['level']}: " . prettyNumber($elementNextLevelToQueue) . "]</b><br/>"
            );
        }
    }

    if (!$hasTechnologyRequirementMet) {
        $subcomponentTechnologyRequirementsListHTML = GetElementTechReq($user, $planet, $elementID);
    }

    if ($isUpgradeAvailableNow || $isUpgradeQueueableNow) {
        $upgradeActionLinkURL = $getUpgradeElementActionLinkHref();
        $upgradeActionLinkColorClass = classNames([
            'lime' => $isUpgradeAvailableNow,
            'orange' => (!$isUpgradeAvailableNow && $isUpgradeQueueableNow),
        ]);
        $upgradeActionLinkText = (
            $isQueueActive ?
            "{$_Lang['InBuildQueue']}<br/>({$_Lang['level']} " . prettyNumber($elementNextLevelToQueue) . ")" :
            (
                $elementNextLevelToQueue === 1 ?
                $_Lang['BuildFirstLevel'] :
                "{$_Lang['BuildNextLevel']} " . prettyNumber($elementNextLevelToQueue)
            )
        );

        $subcomponentUpgradeActionLinkHTML = (
            "<a href=\"{$upgradeActionLinkURL}\" class=\"{$upgradeActionLinkColorClass}\">" .
            $upgradeActionLinkText .
            "</a>"
        );
    } else {
        $upgradeUnavailableJoinedReasons = (
            count($whyUpgradeImpossible) > 0 ?
            implode('<br/>', $whyUpgradeImpossible) :
            '&nbsp;'
        );

        $subcomponentUpgradeActionLinkHTML = (
            '<span class="red">' .
            $upgradeUnavailableJoinedReasons .
            '</span>'
        );
    }


    $componentTPLData = [
        'Data_SkinPath'                             => $_SkinPath,

        'Data_ElementID'                            => $elementID,
        'Data_ElementName'                          => $_Lang['tech'][$elementID],
        'Data_ElementDescription'                   => $_Lang['WorldElements_Detailed'][$elementID]['description_short'],

        'Subcomponent_CurrentStateLabel'            => $subcomponentCurrentStateLabelHTML,
        'Subcomponent_ProductionChange'             => $subcomponentProductionChangeRowHTML,
        'Subcomponent_UpgradeLevelHeaderRow'        => $subcomponentUpgradeLevelHeaderRowHTML,
        'Subcomponent_UpgradeTimeRow'               => $subcomponentUpgradeTimeRowHTML,
        'Subcomponent_UpgradeCostRow'               => $subcomponentUpgradeCostRowHTML,
        'Subcomponent_UpgradeResourcesLeftoverRow'  => $subcomponentUpgradeResourcesLeftoverRowHTML,
        'Subcomponent_TechnologyRequirementsList'   => $subcomponentTechnologyRequirementsListHTML,
        'Subcomponent_UpgradeActionLink'            => $subcomponentUpgradeActionLinkHTML,
    ];

    $componentHTML = parsetemplate($tplBodyCache['body'], $componentTPLData);

    return [
        'componentHTML' => $componentHTML
    ];
}

?>
