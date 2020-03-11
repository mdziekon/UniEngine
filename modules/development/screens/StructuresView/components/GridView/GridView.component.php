<?php

namespace UniEngine\Engine\Modules\Development\Screens\StructuresView\Components\GridView;

use UniEngine\Engine\Includes\Helpers\Common;
use UniEngine\Engine\Modules\Development;
use UniEngine\Engine\Includes\Helpers\World\Elements;
use UniEngine\Engine\Modules\Development\Components\ModernQueue;
use UniEngine\Engine\Includes\Helpers\Users;

//  Arguments
//      - $props (Object)
//          - planet (Object)
//          - user (Object)
//          - timestamp (Number)
//          - elements (Map<elementID: String, elementDetails: Object>)
//          - highlightElementID (String | null)
//          - queueContent (Array<QueueElement>)
//          - isQueueActive (Boolean)
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_Lang, $_SkinPath;

    $planet = $props['planet'];
    $user = $props['user'];
    $currentTimestamp = $props['timestamp'];
    $elementsDetails = $props['elementsDetails'];
    $highlightElementID = $props['highlightElementID'];
    $queueContent = $props['queueContent'];
    $isQueueActive = $props['isQueueActive'];

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

    $planetsMaxFieldsCount = CalculateMaxPlanetFields($planet);

    $elementsIconComponents = [];
    $elementsCardComponents = [];
    $elementsDestructionDetails = [];

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

        if ($elementDetails['isDowngradePossible']) {
            $elementsDestructionDetails[$elementID] = Development\Utils\Structures\getDestructionDetails([
                'elementID' => $elementID,
                'planet' => $planet,
                'user' => $user,
                'isQueueActive' => $isQueueActive,
            ]);
        }

        $iconComponent = Development\Components\GridViewElementIcon\render([
            'elementID' => $elementID,
            'elementDetails' => array_merge(
                $elementDetails,
                [
                    'whyUpgradeImpossible' => [ end($blockReasonWarnings) ],
                ]
            ),
            'getUpgradeElementActionLinkHref' => function () use ($elementID) {
                return "?cmd=insert&amp;building={$elementID}";
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
                    'whyUpgradeImpossible' => [
                        (
                            $upgradeBlockReasons['hasReachedMaxLevel'] ?
                            $_Lang['ListBox_Disallow_MaxLevelReached'] :
                            ''
                        ),
                    ],
                    'additionalUpgradeDetailsRows' => [
                        (
                            Elements\isProductionRelated($elementID) ?
                            Development\Components\GridViewElementCard\UpgradeProductionChange\render([
                                'elementID' => $elementID,
                                'user' => $user,
                                'planet' => $planet,
                                'timestamp' => $currentTimestamp,
                                'elementDetails' => $elementDetails,
                            ])['componentHTML'] :
                            ''
                        ),
                    ],
                ]
            ),

            'getUpgradeElementActionLinkHref' => function () use ($elementID) {
                return "?cmd=insert&amp;building={$elementID}";
            },
            'getDowngradeElementActionLinkHref' => function () use ($elementID) {
                return "?cmd=destroy&amp;building={$elementID}";
            },
        ]);

        $elementsIconComponents[] = $iconComponent['componentHTML'];
        $elementsCardComponents[] = $cardInfoComponent['componentHTML'];
    }

    // Create Structures List
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

    $queueComponent = ModernQueue\render([
        'planet' => &$planet,
        'queue' => $queueContent,
        'queueMaxLength' => Users\getMaxStructuresQueueLength($user),
        'timestamp' => $currentTimestamp,
        'infoComponents' => [],

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

    $componentTplData['Create_Queue'] = $queueComponent['componentHTML'];
    $componentTplData['Create_StructuresList'] = implode(
        $tplBodyCache['list_breakrow'],
        $groupedIconRows
    );
    $componentTplData['Create_ElementsInfoBoxes'] = implode('', $elementsCardComponents);
    $componentTplData['Create_ShowElementOnStartup'] = (
        $highlightElementID > 0 ?
        $highlightElementID :
        ''
    );
    $componentTplData['Insert_Overview_Fields_Used_Color'] = classNames([
        'red' => ($planet['field_current'] >= $planetsMaxFieldsCount),
        'orange' => (
            ($planet['field_current'] < $planetsMaxFieldsCount) &&
            ($planet['field_current'] >= ($planetsMaxFieldsCount * 0.9))
        ),
        'lime' => ($planet['field_current'] < ($planetsMaxFieldsCount * 0.9)),
    ]);
    $componentTplData['Insert_SkinPath'] = $_SkinPath;
    $componentTplData['Insert_PlanetImg'] = $planet['image'];
    $componentTplData['Insert_PlanetType'] = $_Lang['PlanetType_'.$planet['planet_type']];
    $componentTplData['Insert_PlanetName'] = $planet['name'];
    $componentTplData['Insert_PlanetPos_Galaxy'] = $planet['galaxy'];
    $componentTplData['Insert_PlanetPos_System'] = $planet['system'];
    $componentTplData['Insert_PlanetPos_Planet'] = $planet['planet'];
    $componentTplData['Insert_Overview_Diameter'] = prettyNumber($planet['diameter']);
    $componentTplData['Insert_Overview_Fields_Used'] = prettyNumber($planet['field_current']);
    $componentTplData['Insert_Overview_Fields_Max'] = prettyNumber($planetsMaxFieldsCount);
    $componentTplData['Insert_Overview_Fields_Percent'] = sprintf(
        '%0.2f',
        (($planet['field_current'] / $planetsMaxFieldsCount) * 100)
    );
    $componentTplData['Insert_Overview_Temperature'] = sprintf(
        $_Lang['Overview_Form_Temperature'],
        $planet['temp_min'],
        $planet['temp_max']
    );
    $componentTplData['PHPData_ElementsDestructionDetailsJSON'] = json_encode($elementsDestructionDetails);

    $componentHTML = parsetemplate($tplBodyCache['pageBody'], $componentTplData);

    return [
        'componentHTML' => $componentHTML
    ];
}

?>
