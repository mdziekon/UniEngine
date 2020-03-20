<?php

namespace UniEngine\Engine\Modules\Development\Screens\ResearchView\Components\ListView;

use UniEngine\Engine\Includes\Helpers\Common;
use UniEngine\Engine\Modules\Development;
use UniEngine\Engine\Modules\Development\Components\LegacyQueue;

//  Arguments
//      - $props (Object)
//          - planet (Object)
//          - researchPlanet (Object)
//          - user (Object)
//          - timestamp (Number)
//          - elements (Map<elementID: String, elementDetails: Object>)
//          - queueContent (Array<QueueElement>)
//          - isQueueActive (Boolean)
//          - canQueueResearchOnThisPlanet (Boolean)
//          - planetsWithUnfinishedLabUpgrades (Array<Object>)
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_Lang;

    $planet = $props['planet'];
    $researchPlanet = $props['researchPlanet'];
    $user = $props['user'];
    $currentTimestamp = $props['timestamp'];
    $elementsDetails = $props['elementsDetails'];
    $queueContent = $props['queueContent'];
    $isQueueActive = $props['isQueueActive'];
    $canQueueResearchOnThisPlanet = $props['canQueueResearchOnThisPlanet'];
    $planetsWithUnfinishedLabUpgrades = $props['planetsWithUnfinishedLabUpgrades'];

    includeLang('worldElements.detailed');

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $tplBodyCache = [
        'pageBody' => $localTemplateLoader('page_body'),
        '_singleRow' => gettemplate('_singleRow'),
    ];
    $componentTplData = &$_Lang;

    $elementsRowComponents = [];

    // Iterate through all available elements
    foreach ($elementsDetails as $elementID => $elementDetails) {
        $upgradeBlockReasons = $elementDetails['upgradeBlockReasons'];
        $blockReasonWarnings = [
            (
                $upgradeBlockReasons['hasReachedMaxLevel'] ?
                $_Lang['ListBox_Disallow_MaxLevelReached'] :
                ''
            ),
            (
                $upgradeBlockReasons['hasNoLab'] ?
                $_Lang['ListBox_Disallow_NoLab'] :
                ''
            ),
            (
                $upgradeBlockReasons['isQueueFull'] ?
                $_Lang['ListBox_Disallow_QueueIsFull'] :
                ''
            ),
            (
                $upgradeBlockReasons['isUserOnVacation'] ?
                $_Lang['ListBox_Disallow_VacationMode'] :
                ''
            ),
        ];
        $blockReasonWarnings = Common\Collections\compact($blockReasonWarnings);

        $showInactiveUpgradeActionLink = (
            $elementDetails['isUpgradeQueueable'] &&
            $upgradeBlockReasons['hasInsufficientUpgradeResources']
        );

        $listElement = Development\Components\ListViewElementRow\render([
            'elementID' => $elementID,
            'user' => $user,
            'planet' => $planet,
            'timestamp' => $currentTimestamp,
            'isQueueActive' => $isQueueActive,
            'elementDetails' => array_merge(
                $elementDetails,
                [
                    'whyUpgradeImpossible' => (
                        !empty($blockReasonWarnings) ?
                        [ end($blockReasonWarnings) ] :
                        []
                    ),
                ]
            ),
            'getUpgradeElementActionLinkHref' => function () use ($elementID) {
                return "buildings.php?mode=research&amp;cmd=search&amp;tech={$elementID}";
            },
            'showInactiveUpgradeActionLink' => $showInactiveUpgradeActionLink,
        ]);

        $elementsRowComponents[] = $listElement['componentHTML'];
    }

    $queueComponent = LegacyQueue\render([
        'queue' => $queueContent,
        'currentTimestamp' => $currentTimestamp,

        'getQueueElementCancellationLinkHref' => function ($queueElement) {
            $listID = $queueElement['listID'];

            return buildHref([
                'path' => 'buildings.php',
                'query' => [
                    'mode' => 'research',
                    'cmd' => 'cancel',
                    'el' => ($listID - 1)
                ]
            ]);
        }
    ]);

    $componentTplData['technolist'] = implode('', $elementsRowComponents);
    $componentTplData['Data_QueueComponentHTML'] = $queueComponent['componentHTML'];

    if (!$canQueueResearchOnThisPlanet) {
        $componentTplData['Insert_QueueInfo'] = parsetemplate(
            $tplBodyCache['_singleRow'],
            [
                'Classes' => 'pad5 red',
                'Colspan' => 3,
                'Text' => (
                    $_Lang['Queue_ResearchOn'] .
                    ' ' .
                    "{$researchPlanet['name']} [{$researchPlanet['galaxy']}:{$researchPlanet['system']}:{$researchPlanet['planet']}]"
                )
            ]
        );
    }
    if (empty($planetsWithUnfinishedLabUpgrades)) {
        $componentTplData['Input_HideNoResearch'] = 'display: none;';
    } else {
        $labInQueueAt = array_map(
            function ($planetWithLabUpgrade) {
                return "{$planetWithLabUpgrade['name']} [{$planetWithLabUpgrade['galaxy']}:{$planetWithLabUpgrade['system']}:{$planetWithLabUpgrade['planet']}]";
            },
            $planetsWithUnfinishedLabUpgrades
        );

        $componentTplData['labo_on_update'] = sprintf($_Lang['labo_on_update'], implode(', ', $labInQueueAt));
    }

    $componentHTML = parsetemplate($tplBodyCache['pageBody'], $componentTplData);

    return [
        'componentHTML' => $componentHTML
    ];
}

?>
