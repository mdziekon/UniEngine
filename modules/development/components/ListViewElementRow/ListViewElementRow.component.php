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
//              - isUpgradePossible (Boolean)
//              - isUpgradeAvailableNow (Boolean)
//              - isUpgradeQueueableNow (Boolean)
//              - whyUpgradeImpossible (String[])
//          - getUpgradeElementActionLinkHref (Function: () => String)
//              Should return the link to the appropriate command invoker.
//              Note: returning empty string will make the link "invalid",
//              meaning the button won't be displayed at all, even if the action is possible.
//          - showInactiveUpgradeActionLink (Boolean | undefined) [default: false]
//              Determines whether the upgrade link should be shown when upgrade is neither
//              available now nor queueable, however the reason why upgrade
//              is impossible was not provided. The link is inactive though.
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_EnginePath, $_SkinPath, $_Lang, $_Vars_ElementCategories;

    include_once($_EnginePath . 'includes/functions/GetElementTechReq.php');

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
    $showInactiveUpgradeActionLink = (
        isset($props['showInactiveUpgradeActionLink']) ?
        $props['showInactiveUpgradeActionLink'] :
        false
    );

    $elementCurrentState = $elementDetails['currentState'];
    $isInQueue = $elementDetails['isInQueue'];
    $elementQueueLevelModifier = $elementDetails['queueLevelModifier'];
    $hasTechnologyRequirementMet = $elementDetails['hasTechnologyRequirementMet'];
    $isUpgradePossible = $elementDetails['isUpgradePossible'];
    $isUpgradeAvailableNow = $elementDetails['isUpgradeAvailableNow'];
    $isUpgradeQueueableNow = $elementDetails['isUpgradeQueueableNow'];
    $whyUpgradeImpossible = $elementDetails['whyUpgradeImpossible'];

    $elementQueuedLevel = ($elementCurrentState + $elementQueueLevelModifier);
    $elementNextLevelToQueue = ($elementQueuedLevel + 1);
    $hasUpgradeImpossibleReason = count($whyUpgradeImpossible) > 0;
    $isProductionRelatedElement = in_array($elementID, $_Vars_ElementCategories['prod']);

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

    if ($isUpgradePossible && $isProductionRelatedElement) {
        $subcomponentProductionChangeRowHTML = UpgradeProductionChange\render($props)['componentHTML'];
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

    if ($isUpgradePossible && $hasTechnologyRequirementMet) {
        $elementUpgradeTime = GetBuildingTime($user, $planet, $elementID);

        $subcomponentUpgradeTimeRowHTML = pretty_time($elementUpgradeTime);
        $subcomponentUpgradeCostRowHTML = UpgradeResourcesCost\render($props)['componentHTML'];
        $subcomponentUpgradeResourcesLeftoverRowHTML = UpgradeResourcesRest\render($props)['componentHTML'];

        if ($isInQueue && $elementQueueLevelModifier != 0) {
            $subcomponentUpgradeLevelHeaderRowHTML = (
                "<b>[{$_Lang['level']}: " . prettyNumber($elementNextLevelToQueue) . "]</b><br/>"
            );
        }
    }

    if (!$hasTechnologyRequirementMet) {
        $subcomponentTechnologyRequirementsListHTML = GetElementTechReq($user, $planet, $elementID);
    }

    if (
        $isUpgradeAvailableNow ||
        $isUpgradeQueueableNow ||
        ($showInactiveUpgradeActionLink && !$hasUpgradeImpossibleReason)
    ) {
        $upgradeActionLinkURL = $getUpgradeElementActionLinkHref();
        $upgradeActionLinkColorClass = classNames([
            'lime' => $isUpgradeAvailableNow,
            'orange' => (!$isUpgradeAvailableNow && $isUpgradeQueueableNow),
            'red' => (!$isUpgradeAvailableNow && !$isUpgradeQueueableNow),
        ]);
        $upgradeActionLinkText = '';

        if (Elements\isStructure($elementID)) {
            $upgradeActionLinkText = (
                $isQueueActive ?
                "{$_Lang['InBuildQueue']}<br/>({$_Lang['level']} " . prettyNumber($elementNextLevelToQueue) . ")" :
                (
                    $elementNextLevelToQueue === 1 ?
                    $_Lang['BuildFirstLevel'] :
                    "{$_Lang['BuildNextLevel']} " . prettyNumber($elementNextLevelToQueue)
                )
            );
        }
        if (Elements\isTechnology($elementID)) {
            $upgradeActionLinkText = (
                $elementNextLevelToQueue === 1 ?
                $_Lang['ResearchBtnLabel'] :
                "{$_Lang['ResearchBtnLabel']}<br/>({$_Lang['level']} " . prettyNumber($elementNextLevelToQueue) . ")"
            );
        }

        if ($isUpgradeAvailableNow || $isUpgradeQueueableNow) {
            $subcomponentUpgradeActionLinkHTML = (
                "<a href=\"{$upgradeActionLinkURL}\" class=\"{$upgradeActionLinkColorClass}\">" .
                $upgradeActionLinkText .
                "</a>"
            );
        } else {
            $subcomponentUpgradeActionLinkHTML = (
                "<span class=\"{$upgradeActionLinkColorClass}\">" .
                $upgradeActionLinkText .
                "</span>"
            );
        }
    } else {
        $upgradeUnavailableJoinedReasons = (
            $hasUpgradeImpossibleReason ?
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
        'Data_ElementImgClass'                      => classNames([
            'buildImg' => Elements\isStructure($elementID),
            'techImg' => Elements\isTechnology($elementID),
        ]),

        'Data_ProductionChangeLine_HideClass'       => classNames([
            'hide' => !($isUpgradePossible && $isProductionRelatedElement),
        ]),
        'Data_UpgradeDetailsLines_HideClass'        => classNames([
            'hide' => !($isUpgradePossible && $hasTechnologyRequirementMet),
        ]),

        'Subcomponent_CurrentStateLabel'            => $subcomponentCurrentStateLabelHTML,
        'Subcomponent_ProductionChange'             => $subcomponentProductionChangeRowHTML,
        'Subcomponent_UpgradeLevelHeaderRow'        => $subcomponentUpgradeLevelHeaderRowHTML,
        'Subcomponent_UpgradeTimeRow'               => $subcomponentUpgradeTimeRowHTML,
        'Subcomponent_UpgradeCostRow'               => $subcomponentUpgradeCostRowHTML,
        'Subcomponent_UpgradeResourcesLeftoverRow'  => $subcomponentUpgradeResourcesLeftoverRowHTML,
        'Subcomponent_TechnologyRequirementsList'   => $subcomponentTechnologyRequirementsListHTML,
        'Subcomponent_UpgradeActionLink'            => $subcomponentUpgradeActionLinkHTML,

        'Lang_ResourcesCost'                        => $_Lang['Requires'],
        'Lang_ResourcesRest'                        => $_Lang['ResourcesLeft'],
        'Lang_UpgradeTime'                          => (
            Elements\isStructure($elementID) ?
            $_Lang['InfoBox_ConstructionTime'] :
            (
                Elements\isTechnology($elementID) ?
                $_Lang['InfoBox_ResearchTime'] :
                ''
            )
        ),
        'Lang_ProductionChange'                     => $_Lang['ProductionChange'],
    ];

    $componentHTML = parsetemplate($tplBodyCache['body'], $componentTPLData);

    return [
        'componentHTML' => $componentHTML
    ];
}

?>
