<?php

if (defined('INSIDE')) {
    $_Vars_TasksData = [
        1 => [
            'requirements' => [],
            'skip' => [
                'possible' => true,
                'tasksrew' => false,
                'catrew' => true
            ],
            'reward' => [
                ['type' => 'PREMIUM_ITEM', 'elementID' => 12]
            ],
            'tasks' => [
                1 => [
                    'details' => [
                        'img' => 'gebaeude/1.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'CONSTRUCTION_END',
                    'jobs' => [
                        ['type' => 'CONSTRUCTION_END', 'elementID' => 1, 'level' => 8],
                        ['type' => 'CONSTRUCTION_END', 'elementID' => 2, 'level' => 8],
                        ['type' => 'CONSTRUCTION_END', 'elementID' => 4, 'level' => 10]
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'met' => 10000, 'cry' => 10000]
                    ]
                ],
                2 => [
                    'details' => [
                        'img' => 'gebaeude/3.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'CONSTRUCTION_END',
                    'jobs' => [
                        ['type' => 'CONSTRUCTION_END', 'elementID' => 3, 'level' => 8]
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 5000]
                    ]
                ],
                3 => [
                    'details' => [
                        'img' => 'gebaeude/401.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'CONSTRUCTION_END|CONSTRUCT_SHIPS_OR_DEFENSE',
                    'jobs' => [
                        ['type' => 'CONSTRUCTION_END', 'elementID' => 14, 'level' => 2],
                        ['type' => 'CONSTRUCTION_END', 'elementID' => 21, 'level' => 1],
                        ['type' => 'CONSTRUCT_SHIPS_OR_DEFENSE', 'elementID' => 401, 'count' => 5000, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'PLANET_ELEMENT', 'elementID' => 401, 'count' => 5000]
                    ]
                ],
                4 => [
                    'details' => [
                        'img' => 'gebaeude/22.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'CONSTRUCTION_END',
                    'jobs' => [
                        ['type' => 'CONSTRUCTION_END', 'elementID' => 22, 'level' => 5],
                        ['type' => 'CONSTRUCTION_END', 'elementID' => 23, 'level' => 5],
                        ['type' => 'CONSTRUCTION_END', 'elementID' => 24, 'level' => 5]
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'met' => 50000, 'cry' => 50000, 'deu' => 20000]
                    ]
                ],
                5 => [
                    'details' => [
                        'img' => 'gebaeude/31.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'CONSTRUCTION_END|RESEARCH_END',
                    'jobs' => [
                        ['type' => 'CONSTRUCTION_END', 'elementID' => 31, 'level' => 1],
                        ['type' => 'RESEARCH_END', 'elementID' => 108, 'level' => 2],
                        ['type' => 'RESEARCH_END', 'elementID' => 113, 'level' => 2]
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'cry' => 25000, 'deu' => 25000]
                    ]
                ],
                6 => [
                    'details' => [
                        'img' => 'gebaeude/202.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'CONSTRUCTION_END|CONSTRUCT_SHIPS_OR_DEFENSE|RESEARCH_END',
                    'jobs' => [
                        ['type' => 'CONSTRUCTION_END', 'elementID' => 21, 'level' => 2],
                        ['type' => 'RESEARCH_END', 'elementID' => 115, 'level' => 1],
                        ['type' => 'CONSTRUCT_SHIPS_OR_DEFENSE', 'elementID' => 202, 'count' => 500, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'PLANET_ELEMENT', 'elementID' => 202, 'count' => 500],
                        ['type' => 'PLANET_ELEMENT', 'elementID' => 204, 'count' => 500]
                    ]
                ],
                7 => [
                    'details' => [
                        'img' => 'gebaeude/208.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'CONSTRUCT_SHIPS_OR_DEFENSE|COLONIZE_PLANET',
                    'jobs' => [
                        ['type' => 'CONSTRUCT_SHIPS_OR_DEFENSE', 'elementID' => 208, 'count' => 1, 'statusField' => 'count'],
                        ['type' => 'COLONIZE_PLANET', 'count' => 1, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'PLANET_ELEMENT', 'elementID' => 208, 'count' => 2]
                    ]
                ],
                8 => [
                    'details' => [
                        'img' => 'gebaeude/212.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'CONSTRUCT_SHIPS_OR_DEFENSE',
                    'jobs' => [
                        ['type' => 'CONSTRUCT_SHIPS_OR_DEFENSE', 'elementID' => 212, 'count' => 200, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'PLANET_ELEMENT', 'elementID' => 212, 'count' => 200]
                    ]
                ],
                9 => [
                    'details' => [
                        'img' => 'gebaeude/210.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'CONSTRUCT_SHIPS_OR_DEFENSE|SPY_OTHER_USER|USE_SIMULATOR',
                    'jobs' => [
                        ['type' => 'CONSTRUCT_SHIPS_OR_DEFENSE', 'elementID' => 210, 'count' => 100, 'statusField' => 'count'],
                        ['type' => 'SPY_OTHER_USER'],
                        ['type' => 'USE_SIMULATOR', 'd' => 1]
                    ],
                    'reward' => [
                        ['type' => 'PLANET_ELEMENT', 'elementID' => 210, 'count' => 500],
                        ['type' => 'PLANET_ELEMENT', 'elementID' => 206, 'count' => 20]
                    ]
                ],
                10 => [
                    'details' => [
                        'img' => 'gebaeude/209.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'CONSTRUCT_SHIPS_OR_DEFENSE|RECYCLE_DEBRIS',
                    'jobs' => [
                        ['type' => 'CONSTRUCT_SHIPS_OR_DEFENSE', 'elementID' => 209, 'count' => 100, 'statusField' => 'count'],
                        ['type' => 'RECYCLE_DEBRIS', 'd' => 1]
                    ],
                    'reward' => [
                        ['type' => 'PLANET_ELEMENT', 'elementID' => 209, 'count' => 200]
                    ]
                ],
                11 => [
                    'details' => [
                        'img' => 'officiers/602.jpg',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'BUDDY_OR_ALLY_TASK',
                    'jobs' => [
                        ['type' => 'BUDDY_OR_ALLY_TASK']
                    ],
                    'reward' => [
                        ['type' => 'PLANET_ELEMENT', 'elementID' => 207, 'count' => 10]
                    ]
                ],
                12 => [
                    'details' => [
                        'img' => 'gebaeude/219.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'CONSTRUCT_SHIPS_OR_DEFENSE',
                    'jobs' => [
                        ['type' => 'CONSTRUCT_SHIPS_OR_DEFENSE', 'elementID' => 219, 'count' => 1, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'met' => 150000, 'cry' => 150000, 'deu' => 150000]
                    ]
                ],
                13 => [
                    'details' => [
                        'img' => 'gebaeude/15.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'RESEARCH_END|MULTIPLE_CONSTRUCTION_END',
                    'jobs' => [
                        ['type' => 'RESEARCH_END', 'elementID' => 108, 'level' => 10],
                        ['type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 15, 'level' => 1, 'count' => 3, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'met' => 2000000, 'cry' => 1000000, 'deu' => 200000]
                    ]
                ],
                14 => [
                    'details' => [
                        'img' => 'gebaeude/214.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'CONSTRUCT_SHIPS_OR_DEFENSE|DESTROY_MOON',
                    'jobs' => [
                        ['type' => 'CONSTRUCT_SHIPS_OR_DEFENSE', 'elementID' => 214, 'count' => 1, 'statusField' => 'count'],
                        ['type' => 'DESTROY_MOON', 'count' => 1, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'PLANET_ELEMENT', 'elementID' => 214, 'count' => 10],
                    ]
                ],
                15 => [
                    'details' => [
                        'img' => 'gebaeude/217.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'CONSTRUCT_SHIPS_OR_DEFENSE',
                    'jobs' => [
                        ['type' => 'CONSTRUCT_SHIPS_OR_DEFENSE', 'elementID' => 217, 'count' => 50, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'PLANET_ELEMENT', 'elementID' => 217, 'count' => 50],
                    ]
                ],
                16 => [
                    'details' => [
                        'img' => 'gebaeude/208.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'COLONIZE_PLANET',
                    'jobs' => [
                        ['type' => 'COLONIZE_PLANET', 'count' => 9, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'PLANET_ELEMENT', 'elementID' => 217, 'count' => 200]
                    ]
                ],
                17 => [
                    'details' => [
                        'img' => 'gebaeude/2.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'MULTIPLE_CONSTRUCTION_END',
                    'jobs' => [
                        ['type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 1, 'level' => 20, 'count' => 5, 'statusField' => 'count'],
                        ['type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 2, 'level' => 20, 'count' => 5, 'statusField' => 'count'],
                        ['type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 3, 'level' => 20, 'count' => 5, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'met' => 5000000, 'cry' => 5000000, 'deu' => 2000000]
                    ]
                ],
                18 => [
                    'details' => [
                        'img' => 'gebaeude/41.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'CONSTRUCTION_END',
                    'jobs' => [
                        ['type' => 'CONSTRUCTION_END', 'elementID' => 41, 'level' => 1]
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'met' => 4000000, 'cry' => 8000000, 'deu' => 4000000]
                    ]
                ],
                19 => [
                    'details' => [
                        'img' => 'planeten/mond.jpg',
                    ],

                    'requirements' => [
                        ['type' => 'TASK', 'elementID' => 12],
                        ['type' => 'TASK', 'elementID' => 18]
                    ],
                    'jobtypes' => 'INTRODUCTION_FLEETSAVE_END',
                    'jobs' => [
                        ['type' => 'INTRODUCTION_FLEETSAVE_END']
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 10000000]
                    ]
                ]
            ],
        ],
        2 => [
            'requirements' => [],
            'skip' => [
                'possible' => false,
            ],
            'reward' => [],
            'tasks' => [
                20 => [
                    'details' => [
                        'img' => 'gebaeude/214.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'DESTROY_MOON',
                    'jobs' => [
                        ['type' => 'DESTROY_MOON', 'count' => 50, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 1000000000]
                    ]
                ],
                21 => [
                    'details' => [
                        'img' => 'gebaeude/214.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'DESTROY_MOON',
                    'jobs' => [
                        ['type' => 'DESTROY_MOON', 'count' => 150, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 3000000000]
                    ]
                ],
                22 => [
                    'details' => [
                        'img' => 'gebaeude/214.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'DESTROY_MOON',
                    'jobs' => [
                        ['type' => 'DESTROY_MOON', 'count' => 500, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 10000000000]
                    ]
                ],
                23 => [
                    'details' => [
                        'img' => 'gebaeude/199.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'DESTROY_MOON_NOFLEETLOSS',
                    'jobs' => [
                        ['type' => 'DESTROY_MOON_NOFLEETLOSS', 'count' => 50, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 5000000000]
                    ]
                ],
                24 => [
                    'details' => [
                        'img' => 'gebaeude/199.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'DESTROY_MOON_NOFLEETLOSS',
                    'jobs' => [
                        ['type' => 'DESTROY_MOON_NOFLEETLOSS', 'count' => 100, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 10000000000]
                    ]
                ],
                25 => [
                    'details' => [
                        'img' => 'gebaeude/199.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'DESTROY_MOON_NOFLEETLOSS',
                    'jobs' => [
                        ['type' => 'DESTROY_MOON_NOFLEETLOSS', 'count' => 200, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 20000000000]
                    ]
                ],
                26 => [
                    'details' => [
                        'img' => 'planeten/mond.jpg',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'DESTROY_MOON_NOFLEETLOSS',
                    'jobs' => [
                        ['type' => 'DESTROY_MOON_NOFLEETLOSS', 'count' => 50, 'statusField' => 'count', 'minimalDiameter' => 8000]
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 25000000000]
                    ]
                ],
                27 => [
                    'details' => [
                        'img' => 'planeten/mond.jpg',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'DESTROY_MOON',
                    'jobs' => [
                        ['type' => 'DESTROY_MOON', 'count' => 25, 'statusField' => 'count', 'minimalDiameter' => 9000]
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 25000000000]
                    ]
                ],
                28 => [
                    'details' => [
                        'img' => 'planeten/mond.jpg',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'DESTROY_MOON',
                    'jobs' => [
                        ['type' => 'DESTROY_MOON', 'count' => 5, 'statusField' => 'count', 'minimalDiameter' => 9900]
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 100000000000]
                    ]
                ],
                29 => [
                    'details' => [
                        'img' => 'planeten/mond.jpg',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'CREATE_MOON',
                    'jobs' => [
                        ['type' => 'CREATE_MOON', 'count' => 100, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 20000000000]
                    ]
                ],
                30 => [
                    'details' => [
                        'img' => 'planeten/mond.jpg',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'CREATE_MOON',
                    'jobs' => [
                        ['type' => 'CREATE_MOON', 'count' => 250, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 50000000000]
                    ]
                ],
                31 => [
                    'details' => [
                        'img' => 'planeten/mond.jpg',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'CREATE_MOON',
                    'jobs' => [
                        ['type' => 'CREATE_MOON', 'count' => 500, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 100000000000]
                    ]
                ],
                32 => [
                    'details' => [
                        'img' => 'gebaeude/204.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'BATTLE_DESTROY_MILITARYUNITS',
                    'jobs' => [
                        ['type' => 'BATTLE_DESTROY_MILITARYUNITS', 'count' => 100000000, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 1000000000]
                    ]
                ],
                33 => [
                    'details' => [
                        'img' => 'gebaeude/204.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'BATTLE_DESTROY_MILITARYUNITS',
                    'jobs' => [
                        ['type' => 'BATTLE_DESTROY_MILITARYUNITS', 'count' => 1000000000, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 10000000000]
                    ]
                ],
                34 => [
                    'details' => [
                        'img' => 'gebaeude/204.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'BATTLE_DESTROY_MILITARYUNITS',
                    'jobs' => [
                        ['type' => 'BATTLE_DESTROY_MILITARYUNITS', 'count' => 5000000000, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 50000000000]
                    ]
                ],
                35 => [
                    'details' => [
                        'img' => 'gebaeude/215.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'BATTLE_DESTROY_MOREEXPENSIVEFLEET_SOLO',
                    'jobs' => [
                        ['type' => 'BATTLE_DESTROY_MOREEXPENSIVEFLEET_SOLO', 'count' => 1, 'statusField' => 'count', 'minimalEnemyCost' => 10000000000, 'maximalOwnValue' => 0.6]
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'cry' => 500000000]
                    ]
                ],
                36 => [
                    'details' => [
                        'img' => 'gebaeude/215.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'BATTLE_DESTROY_MOREEXPENSIVEFLEET_SOLO',
                    'jobs' => [
                        ['type' => 'BATTLE_DESTROY_MOREEXPENSIVEFLEET_SOLO', 'count' => 1, 'statusField' => 'count', 'minimalEnemyCost' => 300000000000, 'maximalOwnValue' => 0.5]
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'cry' => 15000000000]
                    ]
                ],
                37 => [
                    'details' => [
                        'img' => 'gebaeude/215.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'BATTLE_DESTROY_MOREEXPENSIVEFLEET_SOLO',
                    'jobs' => [
                        ['type' => 'BATTLE_DESTROY_MOREEXPENSIVEFLEET_SOLO', 'count' => 1, 'statusField' => 'count', 'minimalEnemyCost' => 1000000000000, 'maximalOwnValue' => 0.4]
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'cry' => 50000000000]
                    ]
                ],
                38 => [
                    'details' => [
                        'img' => 'gebaeude/213.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'BATTLE_DESTROY_MOREEXPENSIVEFLEET_ACS',
                    'jobs' => [
                        ['type' => 'BATTLE_DESTROY_MOREEXPENSIVEFLEET_ACS', 'count' => 3, 'statusField' => 'count', 'minimalEnemyCost' => 10000000000, 'maximalOwnValue' => 0.35]
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'cry' => 1000000000]
                    ]
                ],
                39 => [
                    'details' => [
                        'img' => 'gebaeude/213.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'BATTLE_DESTROY_MOREEXPENSIVEFLEET_ACS',
                    'jobs' => [
                        ['type' => 'BATTLE_DESTROY_MOREEXPENSIVEFLEET_ACS', 'count' => 3, 'statusField' => 'count', 'minimalEnemyCost' => 500000000000, 'maximalOwnValue' => 0.30]
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'cry' => 50000000000]
                    ]
                ],
                40 => [
                    'details' => [
                        'img' => 'gebaeude/213.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'BATTLE_DESTROY_MOREEXPENSIVEFLEET_ACS',
                    'jobs' => [
                        ['type' => 'BATTLE_DESTROY_MOREEXPENSIVEFLEET_ACS', 'count' => 3, 'statusField' => 'count', 'minimalEnemyCost' => 1500000000000, 'maximalOwnValue' => 0.25]
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'cry' => 150000000000]
                    ]
                ],
                41 => [
                    'details' => [
                        'img' => 'gebaeude/205.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'BATTLE_WIN',
                    'jobs' => [
                        ['type' => 'BATTLE_WIN', 'count' => 1000, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 30000000]
                    ]
                ],
                42 => [
                    'details' => [
                        'img' => 'gebaeude/205.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'BATTLE_WIN',
                    'jobs' => [
                        ['type' => 'BATTLE_WIN', 'count' => 5000, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 100000000]
                    ]
                ],
                43 => [
                    'details' => [
                        'img' => 'gebaeude/205.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'BATTLE_WIN',
                    'jobs' => [
                        ['type' => 'BATTLE_WIN', 'count' => 10000, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 300000000]
                    ]
                ],
                44 => [
                    'details' => [
                        'img' => 'gebaeude/205.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'BATTLE_WIN',
                    'jobs' => [
                        ['type' => 'BATTLE_WIN', 'count' => 25000, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 1000000000]
                    ]
                ],
                45 => [
                    'details' => [
                        'img' => 'gebaeude/205.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'BATTLE_WIN',
                    'jobs' => [
                        ['type' => 'BATTLE_WIN', 'count' => 50000, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 3000000000]
                    ]
                ],
                46 => [
                    'details' => [
                        'img' => 'gebaeude/206.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'BATTLE_WINORDRAW_SOLO_LIMIT',
                    'jobs' => [
                        ['type' => 'BATTLE_WINORDRAW_SOLO_LIMIT', 'count' => 25, 'statusField' => 'count', 'minimalEnemyPercentLimit' => 100000]
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 4000000000]
                    ]
                ],
                47 => [
                    'details' => [
                        'img' => 'gebaeude/206.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'BATTLE_WINORDRAW_SOLO_LIMIT',
                    'jobs' => [
                        ['type' => 'BATTLE_WINORDRAW_SOLO_LIMIT', 'count' => 50, 'statusField' => 'count', 'minimalEnemyPercentLimit' => 100000]
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 16000000000]
                    ]
                ],
                48 => [
                    'details' => [
                        'img' => 'gebaeude/206.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'BATTLE_WINORDRAW_SOLO_LIMIT',
                    'jobs' => [
                        ['type' => 'BATTLE_WINORDRAW_SOLO_LIMIT', 'count' => 100, 'statusField' => 'count', 'minimalEnemyPercentLimit' => 100000]
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 40000000000]
                    ]
                ],
                49 => [
                    'details' => [
                        'img' => 'gebaeude/218.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'BATTLE_WINORDRAW_SOLO_LIMIT',
                    'jobs' => [
                        ['type' => 'BATTLE_WINORDRAW_SOLO_LIMIT', 'count' => 15, 'statusField' => 'count', 'minimalEnemyPercentLimit' => 500000]
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 10000000000]
                    ]
                ],
                50 => [
                    'details' => [
                        'img' => 'gebaeude/218.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'BATTLE_WINORDRAW_SOLO_LIMIT',
                    'jobs' => [
                        ['type' => 'BATTLE_WINORDRAW_SOLO_LIMIT', 'count' => 30, 'statusField' => 'count', 'minimalEnemyPercentLimit' => 500000]
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 40000000000]
                    ]
                ],
                51 => [
                    'details' => [
                        'img' => 'gebaeude/218.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'BATTLE_WINORDRAW_SOLO_LIMIT',
                    'jobs' => [
                        ['type' => 'BATTLE_WINORDRAW_SOLO_LIMIT', 'count' => 50, 'statusField' => 'count', 'minimalEnemyPercentLimit' => 500000]
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 100000000000]
                    ]
                ],
                52 => [
                    'details' => [
                        'img' => 'gebaeude/218.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'BATTLE_WINORDRAW_LIMIT',
                    'jobs' => [
                        ['type' => 'BATTLE_WINORDRAW_LIMIT', 'count' => 15, 'statusField' => 'count', 'minimalEnemyPercentLimit' => 1000000]
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 15000000000]
                    ]
                ],
                53 => [
                    'details' => [
                        'img' => 'gebaeude/218.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'BATTLE_WINORDRAW_LIMIT',
                    'jobs' => [
                        ['type' => 'BATTLE_WINORDRAW_LIMIT', 'count' => 30, 'statusField' => 'count', 'minimalEnemyPercentLimit' => 1000000]
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 50000000000]
                    ]
                ],
                54 => [
                    'details' => [
                        'img' => 'gebaeude/218.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'BATTLE_WINORDRAW_LIMIT',
                    'jobs' => [
                        ['type' => 'BATTLE_WINORDRAW_LIMIT', 'count' => 50, 'statusField' => 'count', 'minimalEnemyPercentLimit' => 1000000]
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 125000000000]
                    ]
                ],
                55 => [
                    'details' => [
                        'img' => 'gebaeude/218.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'BATTLE_WINORDRAW_ACS_LIMIT',
                    'jobs' => [
                        ['type' => 'BATTLE_WINORDRAW_ACS_LIMIT', 'count' => 5, 'statusField' => 'count', 'minimalEnemyPercentLimit' => 5000000]
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 500000000000]
                    ]
                ],
                56 => [
                    'details' => [
                        'img' => 'gebaeude/223.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'BATTLE_BLOCK_MOONDESTROY',
                    'jobs' => [
                        ['type' => 'BATTLE_BLOCK_MOONDESTROY']
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 4000000000]
                    ]
                ],
                57 => [
                    'details' => [
                        'img' => 'gebaeude/224.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'BATTLE_WINORDRAW_SOLO_TOTALLIMIT',
                    'jobs' => [
                        ['type' => 'BATTLE_WINORDRAW_SOLO_TOTALLIMIT', 'count' => 50000000, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 1000000000000]
                    ]
                ],
                58 => [
                    'details' => [
                        'img' => 'gebaeude/209.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'DEBRIS_COLLECT_METAL|DEBRIS_COLLECT_CRYSTAL',
                    'jobs' => [
                        ['type' => 'DEBRIS_COLLECT_METAL', 'count' => 30000000000000, 'statusField' => 'count'],
                        ['type' => 'DEBRIS_COLLECT_CRYSTAL', 'count' => 15000000000000, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'met' => 500000000000, 'cry' => 500000000000]
                    ]
                ],
                59 => [
                    'details' => [
                        'img' => 'gebaeude/217.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'BATTLE_COLLECT_METAL|BATTLE_COLLECT_CRYSTAL|BATTLE_COLLECT_DEUTERIUM',
                    'jobs' => [
                        ['type' => 'BATTLE_COLLECT_METAL', 'count' => 100000000000, 'statusField' => 'count'],
                        ['type' => 'BATTLE_COLLECT_CRYSTAL', 'count' => 100000000000, 'statusField' => 'count'],
                        ['type' => 'BATTLE_COLLECT_DEUTERIUM', 'count' => 100000000000, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 5000000000]
                    ]
                ],
                60 => [
                    'details' => [
                        'img' => 'gebaeude/217.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'BATTLE_COLLECT_METAL|BATTLE_COLLECT_CRYSTAL|BATTLE_COLLECT_DEUTERIUM',
                    'jobs' => [
                        ['type' => 'BATTLE_COLLECT_METAL', 'count' => 500000000000, 'statusField' => 'count'],
                        ['type' => 'BATTLE_COLLECT_CRYSTAL', 'count' => 500000000000, 'statusField' => 'count'],
                        ['type' => 'BATTLE_COLLECT_DEUTERIUM', 'count' => 500000000000, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 20000000000]
                    ]
                ],
                61 => [
                    'details' => [
                        'img' => 'gebaeude/217.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'BATTLE_COLLECT_METAL|BATTLE_COLLECT_CRYSTAL|BATTLE_COLLECT_DEUTERIUM',
                    'jobs' => [
                        ['type' => 'BATTLE_COLLECT_METAL', 'count' => 1000000000000, 'statusField' => 'count'],
                        ['type' => 'BATTLE_COLLECT_CRYSTAL', 'count' => 1000000000000, 'statusField' => 'count'],
                        ['type' => 'BATTLE_COLLECT_DEUTERIUM', 'count' => 1000000000000, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 40000000000]
                    ]
                ],
                62 => [
                    'details' => [
                        'img' => 'gebaeude/204.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'CONSTRUCT_SHIPS_OR_DEFENSE_MULTIID',
                    'jobs' => [
                        ['type' => 'CONSTRUCT_SHIPS_OR_DEFENSE_MULTIID', 'elementIDs' => [204, 205], 'count' => 1000000000, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'met' => 90000000000, 'cry' => 30000000000]
                    ]
                ],
                63 => [
                    'details' => [
                        'img' => 'gebaeude/205.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'CONSTRUCT_SHIPS_OR_DEFENSE_MULTIID',
                    'jobs' => [
                        ['type' => 'CONSTRUCT_SHIPS_OR_DEFENSE_MULTIID', 'elementIDs' => [204, 205], 'count' => 10000000000, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'met' => 1000000000000, 'cry' => 350000000000]
                    ]
                ],
            ],
        ],
        3 => [
            'requirements' => [],
            'skip' => [
                'possible' => false,
            ],
            'reward' => [],
            'tasks' => [
                64 => [
                    'details' => [
                        'img' => 'gebaeude/1.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'MULTIPLE_CONSTRUCTION_END',
                    'jobs' => [
                        ['type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 1, 'level' => 30, 'count' => 10, 'statusField' => 'count'],
                        ['type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 2, 'level' => 30, 'count' => 10, 'statusField' => 'count'],
                        ['type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 3, 'level' => 30, 'count' => 10, 'statusField' => 'count'],
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'met' => 25000000, 'cry' => 10000000]
                    ]
                ],
                65 => [
                    'details' => [
                        'img' => 'gebaeude/2.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'MULTIPLE_CONSTRUCTION_END',
                    'jobs' => [
                        ['type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 1, 'level' => 40, 'count' => 10, 'statusField' => 'count'],
                        ['type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 2, 'level' => 40, 'count' => 10, 'statusField' => 'count'],
                        ['type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 3, 'level' => 40, 'count' => 10, 'statusField' => 'count'],
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'met' => 2000000000, 'cry' => 850000000]
                    ]
                ],
                66 => [
                    'details' => [
                        'img' => 'gebaeude/3.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'MULTIPLE_CONSTRUCTION_END',
                    'jobs' => [
                        ['type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 1, 'level' => 50, 'count' => 5, 'statusField' => 'count'],
                        ['type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 2, 'level' => 50, 'count' => 5, 'statusField' => 'count'],
                        ['type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 3, 'level' => 50, 'count' => 5, 'statusField' => 'count'],
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'met' => 60000000000, 'cry' => 28000000000]
                    ]
                ],
                67 => [
                    'details' => [
                        'img' => 'gebaeude/3.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'MULTIPLE_CONSTRUCTION_END',
                    'jobs' => [
                        ['type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 1, 'level' => 50, 'count' => 10, 'statusField' => 'count'],
                        ['type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 2, 'level' => 50, 'count' => 10, 'statusField' => 'count'],
                        ['type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 3, 'level' => 50, 'count' => 10, 'statusField' => 'count'],
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'met' => 120000000000, 'cry' => 60000000000]
                    ]
                ],
                68 => [
                    'details' => [
                        'img' => 'gebaeude/2.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'REACH_EXTRACTION_LEVEL',
                    'jobs' => [
                        ['type' => 'REACH_EXTRACTION_LEVEL', 'buildingID' => 1, 'resource' => 'metal', 'level' => 45000000],
                        ['type' => 'REACH_EXTRACTION_LEVEL', 'buildingID' => 2, 'resource' => 'crystal', 'level' => 30000000],
                        ['type' => 'REACH_EXTRACTION_LEVEL', 'buildingID' => 3, 'resource' => 'deuterium', 'level' => 22000000],
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'met' => 75000000000, 'cry' => 50000000000, 'deu' => 37000000000]
                    ]
                ],
                69 => [
                    'details' => [
                        'img' => 'gebaeude/23.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'MULTIPLE_CONSTRUCTION_END',
                    'jobs' => [
                        ['type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 22, 'level' => 25, 'count' => 5, 'statusField' => 'count'],
                        ['type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 23, 'level' => 25, 'count' => 5, 'statusField' => 'count'],
                        ['type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 24, 'level' => 25, 'count' => 5, 'statusField' => 'count'],
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'met' => 100000000, 'cry' => 50000000]
                    ]
                ],
                70 => [
                    'details' => [
                        'img' => 'gebaeude/33.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'REACH_TERRAFORMING_LEVEL',
                    'jobs' => [
                        ['type' => 'REACH_TERRAFORMING_LEVEL', 'fields' => 700],
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'cry' => 200000000, 'deu' => 400000000]
                    ]
                ],
                71 => [
                    'details' => [
                        'img' => 'gebaeude/3.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'REACH_TOTAL_EXTRACTION_LEVEL',
                    'jobs' => [
                        ['type' => 'REACH_TOTAL_EXTRACTION_LEVEL', 'buildingID' => 3, 'resource' => 'deuterium', 'level' => 200000000, 'statusField' => 'level'],
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 75000000000]
                    ]
                ],
            ]
        ],
        4 => [
            'requirements' => [],
            'skip' => [
                'possible' => false,
            ],
            'reward' => [],
            'tasks' => [
                72 => [
                    'details' => [
                        'img' => 'gebaeude/109.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'RESEARCH_END',
                    'jobs' => [
                        ['type' => 'RESEARCH_END', 'elementID' => 109, 'level' => 25],
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'cry' => 2000000000]
                    ]
                ],
                73 => [
                    'details' => [
                        'img' => 'gebaeude/109.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'RESEARCH_END',
                    'jobs' => [
                        ['type' => 'RESEARCH_END', 'elementID' => 109, 'level' => 30],
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'cry' => 64000000000]
                    ]
                ],
                74 => [
                    'details' => [
                        'img' => 'gebaeude/110.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'RESEARCH_END',
                    'jobs' => [
                        ['type' => 'RESEARCH_END', 'elementID' => 110, 'level' => 25],
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'cry' => 5000000000]
                    ]
                ],
                75 => [
                    'details' => [
                        'img' => 'gebaeude/110.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'RESEARCH_END',
                    'jobs' => [
                        ['type' => 'RESEARCH_END', 'elementID' => 110, 'level' => 30],
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'cry' => 160000000000]
                    ]
                ],
                76 => [
                    'details' => [
                        'img' => 'gebaeude/111.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'RESEARCH_END',
                    'jobs' => [
                        ['type' => 'RESEARCH_END', 'elementID' => 111, 'level' => 25],
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'met' => 10000000000]
                    ]
                ],
                77 => [
                    'details' => [
                        'img' => 'gebaeude/111.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'RESEARCH_END',
                    'jobs' => [
                        ['type' => 'RESEARCH_END', 'elementID' => 111, 'level' => 30],
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'met' => 320000000000]
                    ]
                ],
                78 => [
                    'details' => [
                        'img' => 'gebaeude/120.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'RESEARCH_END',
                    'jobs' => [
                        ['type' => 'RESEARCH_END', 'elementID' => 120, 'level' => 25],
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'met' => 1000000000]
                    ]
                ],
                79 => [
                    'details' => [
                        'img' => 'gebaeude/120.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'RESEARCH_END',
                    'jobs' => [
                        ['type' => 'RESEARCH_END', 'elementID' => 120, 'level' => 30],
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'met' => 30000000000]
                    ]
                ],
                80 => [
                    'details' => [
                        'img' => 'gebaeude/120.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'RESEARCH_END',
                    'jobs' => [
                        ['type' => 'RESEARCH_END', 'elementID' => 120, 'level' => 33],
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'met' => 300000000000]
                    ]
                ],
                81 => [
                    'details' => [
                        'img' => 'gebaeude/121.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'RESEARCH_END',
                    'jobs' => [
                        ['type' => 'RESEARCH_END', 'elementID' => 121, 'level' => 20],
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 50000000]
                    ]
                ],
                82 => [
                    'details' => [
                        'img' => 'gebaeude/121.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'RESEARCH_END',
                    'jobs' => [
                        ['type' => 'RESEARCH_END', 'elementID' => 121, 'level' => 25],
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 1500000000]
                    ]
                ],
                83 => [
                    'details' => [
                        'img' => 'gebaeude/121.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'RESEARCH_END',
                    'jobs' => [
                        ['type' => 'RESEARCH_END', 'elementID' => 121, 'level' => 28],
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 15000000000]
                    ]
                ],
                84 => [
                    'details' => [
                        'img' => 'gebaeude/122.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'RESEARCH_END',
                    'jobs' => [
                        ['type' => 'RESEARCH_END', 'elementID' => 122, 'level' => 20],
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'cry' => 500000000]
                    ]
                ],
                85 => [
                    'details' => [
                        'img' => 'gebaeude/122.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'RESEARCH_END',
                    'jobs' => [
                        ['type' => 'RESEARCH_END', 'elementID' => 122, 'level' => 25],
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'cry' => 15000000000]
                    ]
                ],
                86 => [
                    'details' => [
                        'img' => 'gebaeude/122.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'RESEARCH_END',
                    'jobs' => [
                        ['type' => 'RESEARCH_END', 'elementID' => 122, 'level' => 28],
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'cry' => 150000000000]
                    ]
                ],
                87 => [
                    'details' => [
                        'img' => 'gebaeude/123.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'RESEARCH_END',
                    'jobs' => [
                        ['type' => 'RESEARCH_END', 'elementID' => 123, 'level' => 9],
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'cry' => 50000000]
                    ]
                ],
                88 => [
                    'details' => [
                        'img' => 'gebaeude/31.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'MULTIPLE_CONSTRUCTION_END',
                    'jobs' => [
                        ['type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 31, 'level' => 22, 'count' => 10, 'statusField' => 'count'],
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'cry' => 1000000000, 'deu' => 1000000000]
                    ]
                ],
                89 => [
                    'details' => [
                        'img' => 'gebaeude/108.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'REACH_TECHPOINTS_LEVEL',
                    'jobs' => [
                        ['type' => 'REACH_TECHPOINTS_LEVEL', 'level' => 5000000, 'statusField' => 'level'],
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 5000000]
                    ]
                ],
                90 => [
                    'details' => [
                        'img' => 'gebaeude/108.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'REACH_TECHPOINTS_LEVEL',
                    'jobs' => [
                        ['type' => 'REACH_TECHPOINTS_LEVEL', 'level' => 10000000, 'statusField' => 'level'],
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 10000000]
                    ]
                ],
                91 => [
                    'details' => [
                        'img' => 'gebaeude/108.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'REACH_TECHPOINTS_LEVEL',
                    'jobs' => [
                        ['type' => 'REACH_TECHPOINTS_LEVEL', 'level' => 50000000, 'statusField' => 'level'],
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 50000000]
                    ]
                ],
                92 => [
                    'details' => [
                        'img' => 'gebaeude/108.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'REACH_TECHPOINTS_LEVEL',
                    'jobs' => [
                        ['type' => 'REACH_TECHPOINTS_LEVEL', 'level' => 200000000, 'statusField' => 'level'],
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 200000000]
                    ]
                ],
                93 => [
                    'details' => [
                        'img' => 'gebaeude/108.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'REACH_TECHPOINTS_LEVEL',
                    'jobs' => [
                        ['type' => 'REACH_TECHPOINTS_LEVEL', 'level' => 500000000, 'statusField' => 'level'],
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 500000000]
                    ]
                ],
            ]
        ],
        5 => [
            'requirements' => [],
            'skip' => [
                'possible' => false,
            ],
            'reward' => [],
            'tasks' => [
                94 => [
                    'details' => [
                        'img' => 'planeten/mond.jpg',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'CREATE_MOON_FRIENDLY',
                    'jobs' => [
                        ['type' => 'CREATE_MOON_FRIENDLY', 'count' => 5, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 2500000]
                    ]
                ],
                95 => [
                    'details' => [
                        'img' => 'planeten/mond.jpg',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'CREATE_MOON_FRIENDLY',
                    'jobs' => [
                        ['type' => 'CREATE_MOON_FRIENDLY', 'count' => 20, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 7500000]
                    ]
                ],
                96 => [
                    'details' => [
                        'img' => 'planeten/mond.jpg',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'CREATE_MOON_FRIENDLY',
                    'jobs' => [
                        ['type' => 'CREATE_MOON_FRIENDLY', 'count' => 50, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 15000000]
                    ]
                ],
                97 => [
                    'details' => [
                        'img' => 'planeten/mond.jpg',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'CREATE_MOON_FRIENDLY',
                    'jobs' => [
                        ['type' => 'CREATE_MOON_FRIENDLY', 'count' => 125, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 30000000]
                    ]
                ],
                98 => [
                    'details' => [
                        'img' => 'planeten/mond.jpg',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'CREATE_MOON_FRIENDLY',
                    'jobs' => [
                        ['type' => 'CREATE_MOON_FRIENDLY', 'count' => 250, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 50000000]
                    ]
                ],
                99 => [
                    'details' => [
                        'img' => 'gebaeude/218.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'BATTLE_WINORDRAW_ACS_LIMIT',
                    'jobs' => [
                        ['type' => 'BATTLE_WINORDRAW_ACS_LIMIT', 'count' => 25, 'statusField' => 'count', 'minimalEnemyPercentLimit' => 10000, 'hasToBeLeader' => true]
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 10000000]
                    ]
                ],
                100 => [
                    'details' => [
                        'img' => 'gebaeude/218.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'BATTLE_WINORDRAW_ACS_LIMIT',
                    'jobs' => [
                        ['type' => 'BATTLE_WINORDRAW_ACS_LIMIT', 'count' => 50, 'statusField' => 'count', 'minimalEnemyPercentLimit' => 10000, 'hasToBeLeader' => true]
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 20000000]
                    ]
                ],
                101 => [
                    'details' => [
                        'img' => 'gebaeude/218.gif',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'BATTLE_WINORDRAW_ACS_LIMIT',
                    'jobs' => [
                        ['type' => 'BATTLE_WINORDRAW_ACS_LIMIT', 'count' => 150, 'statusField' => 'count', 'minimalEnemyPercentLimit' => 10000, 'hasToBeLeader' => true]
                    ],
                    'reward' => [
                        ['type' => 'RESOURCES', 'deu' => 50000000]
                    ]
                ],
            ]
        ],
        6 => [
            'requirements' => [],
            'skip' => [
                'possible' => false,
            ],
            'reward' => [],
            'tasks' => [
                102 => [
                    'details' => [
                        'img' => 'img/proacc.jpg',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'NEWUSER_REGISTER',
                    'jobs' => [
                        ['type' => 'NEWUSER_REGISTER', 'count' => 10, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'PREMIUM_RESOURCE', 'value' => 25]
                    ]
                ],
                103 => [
                    'details' => [
                        'img' => 'img/proacc.jpg',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'NEWUSER_REGISTER',
                    'jobs' => [
                        ['type' => 'NEWUSER_REGISTER', 'count' => 20, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'PREMIUM_RESOURCE', 'value' => 25]
                    ]
                ],
                104 => [
                    'details' => [
                        'img' => 'img/proacc.jpg',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'NEWUSER_REGISTER',
                    'jobs' => [
                        ['type' => 'NEWUSER_REGISTER', 'count' => 40, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'PREMIUM_RESOURCE', 'value' => 50]
                    ]
                ],
                105 => [
                    'details' => [
                        'img' => 'img/proacc.jpg',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'INVITEDUSER_BOUGHT_DE',
                    'jobs' => [
                        ['type' => 'INVITEDUSER_BOUGHT_DE', 'count' => 40]
                    ],
                    'reward' => [
                        ['type' => 'PREMIUM_RESOURCE', 'value' => 10]
                    ]
                ],
                106 => [
                    'details' => [
                        'img' => 'img/proacc.jpg',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'INVITEDUSERS_BOUGHT_DE_USERCOUNT',
                    'jobs' => [
                        ['type' => 'INVITEDUSERS_BOUGHT_DE_USERCOUNT', 'count' => 5, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'PREMIUM_RESOURCE', 'value' => 25]
                    ]
                ],
                107 => [
                    'details' => [
                        'img' => 'img/proacc.jpg',
                    ],

                    'requirements' => [],
                    'jobtypes' => 'INVITEDUSERS_BOUGHT_DE_LIMIT',
                    'jobs' => [
                        ['type' => 'INVITEDUSERS_BOUGHT_DE_LIMIT', 'count' => 400, 'statusField' => 'count']
                    ],
                    'reward' => [
                        ['type' => 'PREMIUM_RESOURCE', 'value' => 50]
                    ]
                ],
            ]
        ],
    ];

    // Last Cat ID: 6
    // Last Task ID: 107
}

?>
