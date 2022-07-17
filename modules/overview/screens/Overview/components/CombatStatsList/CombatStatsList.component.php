<?php

namespace UniEngine\Engine\Modules\Overview\Screens\Overview\Components\CombatStatsList;

use UniEngine\Engine\Modules\Overview\Screens\Overview\Components\CombatStatsList;

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

    $statsData = CombatStatsList\Utils\getUserCombatStats([
        'userId' => $props['userId'],
    ]);

    $categories = [
        'allBattles' => [
            'label' => $_Lang['Box_battlesAll'],
            'statsTableKeys' => [
                'ustat_raids_won',
                'ustat_raids_draw',
                'ustat_raids_lost',
                'ustat_raids_inAlly',
            ],
        ],
        'battlesWon' => [
            'label' => $_Lang['Box_battlesWon'],
            'statsTableKeys' => [
                'ustat_raids_won',
            ],
        ],
        'battlesWonUnited' => [
            'label' => $_Lang['Box_battlesACSWon'],
            'statsTableKeys' => [
                'ustat_raids_acs_won',
            ],
            'colorClass' => 'orange',
        ],
        'battlesDrawn' => [
            'label' => $_Lang['Box_battlesDraw'],
            'statsTableKeys' => [
                'ustat_raids_draw',
            ],
        ],
        'battlesLost' => [
            'label' => $_Lang['Box_battlesLost'],
            'statsTableKeys' => [
                'ustat_raids_lost',
            ],
        ],
        'battlesInAlly' => [
            'label' => $_Lang['Box_battlesInAlly'],
            'statsTableKeys' => [
                'ustat_raids_inAlly',
            ],
        ],
        'missileStrikes' => [
            'label' => $_Lang['Box_missileAttacks'],
            'statsTableKeys' => [
                'ustat_raids_missileAttack',
            ],
        ],
    ];

    $categoriesHTML = [];

    foreach ($categories as $categoryKey => $category) {
        $categorySubValues = array_map_withkeys($category['statsTableKeys'], function ($key) use ($statsData) {
            return $statsData[$key];
        });
        $categoryTotalValue = array_sum($categorySubValues);

        $categoryTplBodyParams = [
            'statCategoryColorClass' => (
                !empty($category['colorClass']) ?
                    $category['colorClass'] :
                    ''
            ),
            'statCategoryLabel' => $category['label'],
            'statCategoryValue' => prettyNumber($categoryTotalValue),
        ];

        $categoriesHTML[$categoryKey] = parsetemplate(
            $tplBodyCache['statCategoryRow'],
            $categoryTplBodyParams
        );
    }

    $tplBodyParams = [
        'categoryRow_allBattles' => $categoriesHTML['allBattles'],
        'categoryRow_battlesWon' => $categoriesHTML['battlesWon'],
        'categoryRow_battlesWonUnited' => $categoriesHTML['battlesWonUnited'],
        'categoryRow_battlesDrawn' => $categoriesHTML['battlesDrawn'],
        'categoryRow_battlesLost' => $categoriesHTML['battlesLost'],
        'categoryRow_battlesInAlly' => $categoriesHTML['battlesInAlly'],
        'categoryRow_missileStrikes' => $categoriesHTML['missileStrikes'],
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
