<?php

namespace UniEngine\Engine\Modules\Development\Screens\ResearchView\Components\GridView;

use UniEngine\Engine\Modules\Development;
use UniEngine\Engine\Includes\Helpers\Common;
use UniEngine\Engine\Includes\Helpers\Users;
use UniEngine\Engine\Includes\Helpers\World\Elements;
use UniEngine\Engine\Modules\Development\Components\ModernQueue;
use UniEngine\Engine\Modules\Development\Screens\ResearchListPage\ModernQueuePlanetInfo;
use UniEngine\Engine\Modules\Development\Screens\ResearchListPage\ModernQueueLabUpgradeInfo;

//  Arguments
//      - $props (Object)
//          - planet (Object)
//          - researchPlanet (Object)
//          - user (Object)
//          - timestamp (Number)
//          - elements (Map<elementID: String, elementDetails: Object>)
//          - highlightElementID (String | null)
//          - queueContent (Array<QueueElement>)
//          - isQueueActive (Boolean)
//          - planetsWithUnfinishedLabUpgrades (Array<Object>)
//          - researchNetworkStatus (ReturnType<typeof fetchResearchNetworkStatus>)
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_Lang, $_SkinPath;

    $planet = $props['planet'];
    $researchPlanet = $props['researchPlanet'];
    $user = $props['user'];
    $currentTimestamp = $props['timestamp'];
    $elementsDetails = $props['elementsDetails'];
    $highlightElementID = $props['highlightElementID'];
    $queueContent = $props['queueContent'];
    $isQueueActive = $props['isQueueActive'];
    $planetsWithUnfinishedLabUpgrades = $props['planetsWithUnfinishedLabUpgrades'];
    $researchNetworkStatus = $props['researchNetworkStatus'];

    includeLang('worldElements.detailed');

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $tplBodyCache = [
        'pageBody' => $localTemplateLoader('page_body'),
        'list_hidden' => gettemplate('buildings_compact_list_hidden'),
        'list_row' => gettemplate('buildings_compact_list_row'),
        'list_breakrow' => gettemplate('buildings_compact_list_breakrow'),
    ];
    $componentTplData = &$_Lang;

    $const_ElementsPerRow = 7;

    $elementsIconComponents = [];
    $elementsCardComponents = [];

    // Iterate through all available elements
    foreach ($elementsDetails as $elementID => $elementDetails) {
        $upgradeBlockReasons = $elementDetails['upgradeBlockReasons'];
        $blockReasonWarnings = [
            (
                $upgradeBlockReasons['hasInsufficientUpgradeResources'] ?
                $_Lang['ListBox_Disallow_NoResources'] :
                ''
            ),
            (
                $upgradeBlockReasons['hasReachedMaxLevel'] ?
                $_Lang['ListBox_Disallow_MaxLevelReached'] :
                ''
            ),
            (
                $upgradeBlockReasons['hasUnmetTechnologyRequirements'] ?
                $_Lang['ListBox_Disallow_NoTech'] :
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
                $upgradeBlockReasons['hasOngoingResearchElsewhere'] ?
                $_Lang['ListBox_Disallow_NotThisLab'] :
                ''
            ),
            (
                $upgradeBlockReasons['isBlockedByLabUpgradeInProgress'] ?
                $_Lang['ListBox_Disallow_LabInQueue'] :
                ''
            ),
            (
                $upgradeBlockReasons['isUserOnVacation'] ?
                $_Lang['ListBox_Disallow_VacationMode'] :
                ''
            ),
        ];
        $blockReasonWarnings = Common\Collections\compact($blockReasonWarnings);

        $iconComponent = Development\Components\GridViewElementIcon\render([
            'elementID' => $elementID,
            'elementDetails' => array_merge(
                $elementDetails,
                [
                    'whyUpgradeImpossible' => [ end($blockReasonWarnings) ],
                ]
            ),
            'getUpgradeElementActionLinkHref' => function () use ($elementID) {
                return "?mode=research&amp;cmd=search&amp;tech={$elementID}";
            },
        ]);

        $cardInfoComponent = Development\Components\GridViewElementCard\render([
            'elementID' => $elementID,
            'user' => $user,
            'planet' => $planet,
            'isQueueActive' => $isQueueActive,
            'elementDetails' => array_merge(
                $elementDetails,
                [
                    'isDowngradePossible' => false,
                    'isDowngradeAvailable' => false,
                    'isDowngradeQueueable' => false,
                    'whyUpgradeImpossible' => [
                        (
                            $upgradeBlockReasons['hasReachedMaxLevel'] ?
                            $_Lang['ListBox_Disallow_MaxLevelReached'] :
                            ''
                        ),
                    ],
                    'additionalUpgradeDetailsRows' => [],
                ]
            ),

            'getUpgradeElementActionLinkHref' => function () use ($elementID) {
                return "?mode=research&amp;cmd=search&amp;tech={$elementID}";
            },
            'getDowngradeElementActionLinkHref' => function () use ($elementID) {
                return '';
            },
        ]);

        $elementsIconComponents[] = $iconComponent['componentHTML'];
        $elementsCardComponents[] = $cardInfoComponent['componentHTML'];
    }

    // Create Elements List
    $groupedIcons = Common\Collections\groupInRows($elementsIconComponents, $const_ElementsPerRow);
    $groupedIconRows = array_map(
        function ($elementsInRow) use (&$tplBodyCache, $const_ElementsPerRow) {
            $mergedElementsInRow = implode('', $elementsInRow);
            $emptySpaceFiller = '';

            $elementsInRowCount = count($elementsInRow);

            if ($elementsInRowCount < $const_ElementsPerRow) {
                $emptySpaceFiller = str_repeat(
                    $tplBodyCache['list_hidden'],
                    ($const_ElementsPerRow - $elementsInRowCount)
                );
            }

            return parsetemplate(
                $tplBodyCache['list_row'],
                [
                    'Elements' => ($mergedElementsInRow . $emptySpaceFiller)
                ]
            );
        },
        $groupedIcons
    );

    $planetInfoComponent = ModernQueuePlanetInfo\render([
        'currentPlanet'     => &$planet,
        'researchPlanet'    => &$researchPlanet,
        'queue'             => $queueContent,
        'timestamp'         => $currentTimestamp,
    ]);
    $labsUpgradeInfoComponent = ModernQueueLabUpgradeInfo\render([
        'planetsWithUnfinishedLabUpgrades' => $planetsWithUnfinishedLabUpgrades
    ]);

    $queueComponent = ModernQueue\render([
        'user' => $user,
        'planet' => &$researchPlanet,
        'queue' => $queueContent,
        'queueMaxLength' => Users\getMaxResearchQueueLength($user),
        'timestamp' => $currentTimestamp,
        'infoComponents'    => [
            $planetInfoComponent['componentHTML'],
            $labsUpgradeInfoComponent['componentHTML']
        ],
        'isQueueEmptyInfoHidden' => (
            !empty($labsUpgradeInfoComponent['componentHTML'])
        ),

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


    $componentTplData['Create_Queue'] = $queueComponent['componentHTML'];
    $componentTplData['Create_ElementsList'] = implode(
        $tplBodyCache['list_breakrow'],
        $groupedIconRows
    );
    $componentTplData['Create_ElementsInfoBoxes'] = implode('', $elementsCardComponents);
    $componentTplData['Create_ShowElementOnStartup'] = (
        $highlightElementID > 0 ?
        $highlightElementID :
        ''
    );
    $componentTplData['Insert_SkinPath'] = $_SkinPath;
    $componentTplData['Insert_PlanetImg'] = $planet['image'];
    $componentTplData['Insert_PlanetType'] = $_Lang['PlanetType_'.$planet['planet_type']];
    $componentTplData['Insert_PlanetName'] = $planet['name'];
    $componentTplData['Insert_PlanetPos_Galaxy'] = $planet['galaxy'];
    $componentTplData['Insert_PlanetPos_System'] = $planet['system'];
    $componentTplData['Insert_PlanetPos_Planet'] = $planet['planet'];
    $componentTplData['Insert_Overview_LabLevel'] = Elements\getElementState(31, $planet, $user)['level'];
    $componentTplData['Insert_Overview_LabsConnected'] = prettyNumber($researchNetworkStatus['connectedLabsCount']);
    $componentTplData['Insert_Overview_TotalLabsCount'] = prettyNumber($researchNetworkStatus['allLabsCount']);
    $componentTplData['Insert_Overview_LabPower'] = prettyNumber($researchNetworkStatus['connectedLabsLevel']);
    $componentTplData['Insert_Overview_LabPowerTotal'] = prettyNumber($researchNetworkStatus['allLabsLevel']);

    $componentHTML = parsetemplate($tplBodyCache['pageBody'], $componentTplData);

    return [
        'componentHTML' => $componentHTML
    ];
}

?>
