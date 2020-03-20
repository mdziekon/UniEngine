<?php

namespace UniEngine\Engine\Modules\Development\Screens\StructuresView\Components\ListView;

use UniEngine\Engine\Includes\Helpers\Common;
use UniEngine\Engine\Modules\Development;
use UniEngine\Engine\Modules\Development\Components\LegacyQueue;

//  Arguments
//      - $props (Object)
//          - planet (Object)
//          - user (Object)
//          - timestamp (Number)
//          - elements (Map<elementID: String, elementDetails: Object>)
//          - queueContent (Array<QueueElement>)
//          - isQueueActive (Boolean)
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_Lang;

    $planet = $props['planet'];
    $user = $props['user'];
    $currentTimestamp = $props['timestamp'];
    $elementsDetails = $props['elementsDetails'];
    $queueContent = $props['queueContent'];
    $isQueueActive = $props['isQueueActive'];

    includeLang('worldElements.detailed');

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $tplBodyCache = [
        'pageBody' => $localTemplateLoader('page_body'),
    ];
    $componentTplData = &$_Lang;

    $planetsMaxFieldsCount = CalculateMaxPlanetFields($planet);

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
                $upgradeBlockReasons['isBlockedByResearchInProgress'] ?
                $_Lang['ListBox_Disallow_LabResearch'] :
                ''
            ),
            (
                $upgradeBlockReasons['hasInsufficientPlanetFieldsLeft'] ?
                $_Lang['ListBox_Disallow_NoFreeFields'] :
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
                return "?cmd=insert&amp;building={$elementID}";
            },
            'showInactiveUpgradeActionLink' => $showInactiveUpgradeActionLink,
        ]);

        $elementsRowComponents[] = $listElement['componentHTML'];
    }

    $queueComponent = LegacyQueue\render([
        'queue' => $queueContent,
        'currentTimestamp' => $currentTimestamp,

        'getQueueElementCancellationLinkHref' => function ($queueElement) {
            $queueElementIdx = $queueElement['queueElementIdx'];
            $listID = $queueElement['listID'];
            $isFirstQueueElement = ($queueElementIdx === 0);
            $cmd = ($isFirstQueueElement ? "cancel" : "remove");

            return buildHref([
                'path' => 'buildings.php',
                'query' => [
                    'cmd' => $cmd,
                    'listid' => $listID
                ]
            ]);
        }
    ]);

    $componentTplData['Insert_Overview_Fields_Used'] = $planet['field_current'];
    $componentTplData['Insert_Overview_Fields_Max'] = $planetsMaxFieldsCount;
    $componentTplData['Insert_Overview_Fields_Available'] = $planetsMaxFieldsCount - $planet['field_current'];

    $componentTplData['PHPInject_QueueHTML'] = $queueComponent['componentHTML'];
    $componentTplData['PHPInject_ElementsListHTML'] = join('', $elementsRowComponents);

    $componentHTML = parsetemplate($tplBodyCache['pageBody'], $componentTplData);

    return [
        'componentHTML' => $componentHTML
    ];
}

?>
