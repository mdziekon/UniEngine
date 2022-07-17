<?php

namespace UniEngine\Engine\Modules\Overview\Screens\Overview\Components\StatsList;

use UniEngine\Engine\Modules\Overview\Screens\Overview\Components\StatsList;

/**
 * @param array $props
 * @param number $props['userId']
 */
function render($props) {
    global $_Lang;

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);
    $tplBodyCache = [
        'body' => $localTemplateLoader('body'),
        'statCategoryRow' => $localTemplateLoader('statCategoryRow'),
    ];

    $statsData = StatsList\Utils\getUserGameStats([
        'userId' => $props['userId'],
    ]);

    $categories = [
        'general' => [
            'categoryName' => $_Lang['Box_statGeneral'],
            'categoryType' => '',
            'pointsKey' => 'total_points',
            'recordsCurrentKey' => 'total_old_rank',
            'recordsOldKey' => 'total_rank',
        ],
        'buildings' => [
            'categoryName' => $_Lang['Box_statBuildings'],
            'categoryType' => '4',
            'pointsKey' => 'build_points',
            'recordsCurrentKey' => 'build_rank',
            'recordsOldKey' => 'build_old_rank',
        ],
        'fleet' => [
            'categoryName' => $_Lang['Box_statFleet'],
            'categoryType' => '2',
            'pointsKey' => 'fleet_points',
            'recordsCurrentKey' => 'fleet_rank',
            'recordsOldKey' => 'fleet_old_rank',
        ],
        'defenses' => [
            'categoryName' => $_Lang['Box_statDefense'],
            'categoryType' => '5',
            'pointsKey' => 'defs_points',
            'recordsCurrentKey' => 'defs_rank',
            'recordsOldKey' => 'defs_old_rank',
        ],
        'research' => [
            'categoryName' => $_Lang['Box_statResearch'],
            'categoryType' => '3',
            'pointsKey' => 'tech_points',
            'recordsCurrentKey' => 'tech_rank',
            'recordsOldKey' => 'tech_old_rank',
        ],
    ];

    $categoriesHTML = [];

    foreach ($categories as $category) {
        $categoryTplBodyParams = [
            'categoryName' => $category['categoryName'],
            'statCategoryType' => $category['categoryType'],
            'userCategoryRankLabel' => '0',
            'userCategoryRankPosition' => '0',
            'userCategoryPoints' => prettyNumber($statsData[$category['pointsKey']]),
            'statsUnit' => $_Lang['_statUnit'],
        ];

        $recordsCurrentKey = $category['recordsCurrentKey'];
        $recordsOldKey = $category['recordsOldKey'];

        if (
            !isset($statsData[$recordsCurrentKey]) ||
            $statsData[$recordsCurrentKey] <= 0
        ) {
            $categoriesHTML[] = parsetemplate($tplBodyCache['statCategoryRow'], $categoryTplBodyParams);

            continue;
        }

        $oldPosition = $statsData[$recordsOldKey];
        $currentPosition = $statsData[$recordsCurrentKey];

        $positionDifference = $oldPosition - $currentPosition;
        $positionDifferenceLabel = null;

        if ($positionDifference > 0) {
            $positionDifferenceLabel = "<span class=\"lime\">(+{$positionDifference})</span>";
        } elseif ($positionDifference == 0) {
            $positionDifferenceLabel = "<span class=\"lightblue\">(*)</span>";
        } else {
            $positionDifferenceLabel = "<span class=\"red\">({$positionDifference})</span>";
        }

        $categoryTplBodyParams['userCategoryRankPosition'] = $currentPosition;
        $categoryTplBodyParams['userCategoryRankLabel'] = implode(
            ' ',
            [
                $currentPosition,
                $positionDifferenceLabel,
            ]
        );

        $categoriesHTML[] = parsetemplate($tplBodyCache['statCategoryRow'], $categoryTplBodyParams);
    }

    $tplBodyParams = [
        'statsCategories' => implode('', $categoriesHTML),
    ];
    $tplBodyParams = array_merge($_Lang, $tplBodyParams);

    $componentHTML = parsetemplate(
        $tplBodyCache['body'],
        $tplBodyParams
    );

    return [
        'componentHTML' => $componentHTML,
    ];
}

?>
