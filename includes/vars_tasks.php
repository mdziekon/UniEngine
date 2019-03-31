<?php

if(defined('INSIDE'))
{
    $_Vars_TasksData = array
    (
        1 => array
        (
            'requirements' => array(),
            'skip' => array
            (
                'possible' => true,
                'tasksrew' => false,
                'catrew' => true
            ),
            'reward' => array
            (
                array('type' => 'PREMIUM_ITEM', 'elementID' => 12)
            ),
            'tasks' => array
            (
                1    => array
                (
                    'details' => array(
                        'img' => 'gebaeude/1.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'CONSTRUCTION_END',
                    'jobs' => array
                    (
                        array('type' => 'CONSTRUCTION_END', 'elementID' => 1, 'level' => 8),
                        array('type' => 'CONSTRUCTION_END', 'elementID' => 2, 'level' => 8),
                        array('type' => 'CONSTRUCTION_END', 'elementID' => 4, 'level' => 10)
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'met' => 10000, 'cry' => 10000)
                    )
                ),
                2    => array
                (
                    'details' => array(
                        'img' => 'gebaeude/3.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'CONSTRUCTION_END',
                    'jobs' => array
                    (
                        array('type' => 'CONSTRUCTION_END', 'elementID' => 3, 'level' => 8)
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 5000)
                    )
                ),
                3    => array
                (
                    'details' => array(
                        'img' => 'gebaeude/401.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'CONSTRUCTION_END|CONSTRUCT_SHIPS_OR_DEFENSE',
                    'jobs' => array
                    (
                        array('type' => 'CONSTRUCTION_END', 'elementID' => 14, 'level' => 2),
                        array('type' => 'CONSTRUCTION_END', 'elementID' => 21, 'level' => 1),
                        array('type' => 'CONSTRUCT_SHIPS_OR_DEFENSE', 'elementID' => 401, 'count' => 5000, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'PLANET_ELEMENT', 'elementID' => 401, 'count' => 5000)
                    )
                ),
                4    => array
                (
                    'details' => array(
                        'img' => 'gebaeude/22.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'CONSTRUCTION_END',
                    'jobs' => array
                    (
                        array('type' => 'CONSTRUCTION_END', 'elementID' => 22, 'level' => 5),
                        array('type' => 'CONSTRUCTION_END', 'elementID' => 23, 'level' => 5),
                        array('type' => 'CONSTRUCTION_END', 'elementID' => 24, 'level' => 5)
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'met' => 50000, 'cry' => 50000, 'deu' => 20000)
                    )
                ),
                5    => array
                (
                    'details' => array(
                        'img' => 'gebaeude/31.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'CONSTRUCTION_END|RESEARCH_END',
                    'jobs' => array
                    (
                        array('type' => 'CONSTRUCTION_END', 'elementID' => 31, 'level' => 1),
                        array('type' => 'RESEARCH_END', 'elementID' => 108, 'level' => 2),
                        array('type' => 'RESEARCH_END', 'elementID' => 113, 'level' => 2)
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'cry' => 25000, 'deu' => 25000)
                    )
                ),
                6    => array
                (
                    'details' => array(
                        'img' => 'gebaeude/202.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'CONSTRUCTION_END|CONSTRUCT_SHIPS_OR_DEFENSE|RESEARCH_END',
                    'jobs' => array
                    (
                        array('type' => 'CONSTRUCTION_END', 'elementID' => 21, 'level' => 2),
                        array('type' => 'RESEARCH_END', 'elementID' => 115, 'level' => 1),
                        array('type' => 'CONSTRUCT_SHIPS_OR_DEFENSE', 'elementID' => 202, 'count' => 500, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'PLANET_ELEMENT', 'elementID' => 202, 'count' => 500),
                        array('type' => 'PLANET_ELEMENT', 'elementID' => 204, 'count' => 500)
                    )
                ),
                7    => array
                (
                    'details' => array(
                        'img' => 'gebaeude/208.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'CONSTRUCT_SHIPS_OR_DEFENSE|COLONIZE_PLANET',
                    'jobs' => array
                    (
                        array('type' => 'CONSTRUCT_SHIPS_OR_DEFENSE', 'elementID' => 208, 'count' => 1, 'statusField' => 'count'),
                        array('type' => 'COLONIZE_PLANET', 'count' => 1, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'PLANET_ELEMENT', 'elementID' => 208, 'count' => 2)
                    )
                ),
                8    => array
                (
                    'details' => array(
                        'img' => 'gebaeude/212.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'CONSTRUCT_SHIPS_OR_DEFENSE',
                    'jobs' => array
                    (
                        array('type' => 'CONSTRUCT_SHIPS_OR_DEFENSE', 'elementID' => 212, 'count' => 200, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'PLANET_ELEMENT', 'elementID' => 212, 'count' => 200)
                    )
                ),
                9    => array
                (
                    'details' => array(
                        'img' => 'gebaeude/210.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'CONSTRUCT_SHIPS_OR_DEFENSE|SPY_OTHER_USER|USE_SIMULATOR',
                    'jobs' => array
                    (
                        array('type' => 'CONSTRUCT_SHIPS_OR_DEFENSE', 'elementID' => 210, 'count' => 100, 'statusField' => 'count'),
                        array('type' => 'SPY_OTHER_USER'),
                        array('type' => 'USE_SIMULATOR', 'd' => 1)
                    ),
                    'reward' => array
                    (
                        array('type' => 'PLANET_ELEMENT', 'elementID' => 210, 'count' => 500),
                        array('type' => 'PLANET_ELEMENT', 'elementID' => 206, 'count' => 20)
                    )
                ),
                10    => array
                (
                    'details' => array(
                        'img' => 'gebaeude/209.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'CONSTRUCT_SHIPS_OR_DEFENSE|RECYCLE_DEBRIS',
                    'jobs' => array
                    (
                        array('type' => 'CONSTRUCT_SHIPS_OR_DEFENSE', 'elementID' => 209, 'count' => 100, 'statusField' => 'count'),
                        array('type' => 'RECYCLE_DEBRIS', 'd' => 1)
                    ),
                    'reward' => array
                    (
                        array('type' => 'PLANET_ELEMENT', 'elementID' => 209, 'count' => 200)
                    )
                ),
                11    => array
                (
                    'details' => array(
                        'img' => 'officiers/602.jpg',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'BUDDY_OR_ALLY_TASK',
                    'jobs' => array
                    (
                        array('type' => 'BUDDY_OR_ALLY_TASK')
                    ),
                    'reward' => array
                    (
                        array('type' => 'PLANET_ELEMENT', 'elementID' => 207, 'count' => 10)
                    )
                ),
                12    => array
                (
                    'details' => array(
                        'img' => 'gebaeude/219.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'CONSTRUCT_SHIPS_OR_DEFENSE',
                    'jobs' => array
                    (
                        array('type' => 'CONSTRUCT_SHIPS_OR_DEFENSE', 'elementID' => 219, 'count' => 1, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'met' => 150000, 'cry' => 150000, 'deu' => 150000)
                    )
                ),
                13 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/15.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'RESEARCH_END|MULTIPLE_CONSTRUCTION_END',
                    'jobs' => array
                    (
                        array('type' => 'RESEARCH_END', 'elementID' => 108, 'level' => 10),
                        array('type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 15, 'level' => 1, 'count' => 3, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'met' => 2000000, 'cry' => 1000000, 'deu' => 200000)
                    )
                ),
                14 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/214.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'CONSTRUCT_SHIPS_OR_DEFENSE|DESTROY_MOON',
                    'jobs' => array
                    (
                        array('type' => 'CONSTRUCT_SHIPS_OR_DEFENSE', 'elementID' => 214, 'count' => 1, 'statusField' => 'count'),
                        array('type' => 'DESTROY_MOON', 'count' => 1, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'PLANET_ELEMENT', 'elementID' => 214, 'count' => 10),
                    )
                ),
                15 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/217.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'CONSTRUCT_SHIPS_OR_DEFENSE',
                    'jobs' => array
                    (
                        array('type' => 'CONSTRUCT_SHIPS_OR_DEFENSE', 'elementID' => 217, 'count' => 50, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'PLANET_ELEMENT', 'elementID' => 217, 'count' => 50),
                    )
                ),
                16 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/208.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'COLONIZE_PLANET',
                    'jobs' => array
                    (
                        array('type' => 'COLONIZE_PLANET', 'count' => 9, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'PLANET_ELEMENT', 'elementID' => 217, 'count' => 200)
                    )
                ),
                17 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/2.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'MULTIPLE_CONSTRUCTION_END',
                    'jobs' => array
                    (
                        array('type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 1, 'level' => 20, 'count' => 5, 'statusField' => 'count'),
                        array('type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 2, 'level' => 20, 'count' => 5, 'statusField' => 'count'),
                        array('type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 3, 'level' => 20, 'count' => 5, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'met' => 5000000, 'cry' => 5000000, 'deu' => 2000000)
                    )
                ),
                18 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/41.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'CONSTRUCTION_END',
                    'jobs' => array
                    (
                        array('type' => 'CONSTRUCTION_END', 'elementID' => 41, 'level' => 1)
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'met' => 4000000, 'cry' => 8000000, 'deu' => 4000000)
                    )
                ),
                19 => array
                (
                    'details' => array(
                        'img' => 'planeten/mond.jpg',
                    ),

                    'requirements' => array
                    (
                        array('type' => 'TASK', 'elementID' => 12),
                        array('type' => 'TASK', 'elementID' => 18)
                    ),
                    'jobtypes' => 'INTRODUCTION_FLEETSAVE_END',
                    'jobs' => array
                    (
                        array('type' => 'INTRODUCTION_FLEETSAVE_END')
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 10000000)
                    )
                )
            ),
        ),
        2 => array
        (
            'requirements' => array(),
            'skip' => array
            (
                'possible' => false,
            ),
            'reward' => array(),
            'tasks' => array
            (
                20 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/214.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'DESTROY_MOON',
                    'jobs' => array
                    (
                        array('type' => 'DESTROY_MOON', 'count' => 50, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 1000000000)
                    )
                ),
                21 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/214.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'DESTROY_MOON',
                    'jobs' => array
                    (
                        array('type' => 'DESTROY_MOON', 'count' => 150, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 3000000000)
                    )
                ),
                22 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/214.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'DESTROY_MOON',
                    'jobs' => array
                    (
                        array('type' => 'DESTROY_MOON', 'count' => 500, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 10000000000)
                    )
                ),
                23 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/199.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'DESTROY_MOON_NOFLEETLOSS',
                    'jobs' => array
                    (
                        array('type' => 'DESTROY_MOON_NOFLEETLOSS', 'count' => 50, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 5000000000)
                    )
                ),
                24 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/199.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'DESTROY_MOON_NOFLEETLOSS',
                    'jobs' => array
                    (
                        array('type' => 'DESTROY_MOON_NOFLEETLOSS', 'count' => 100, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 10000000000)
                    )
                ),
                25 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/199.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'DESTROY_MOON_NOFLEETLOSS',
                    'jobs' => array
                    (
                        array('type' => 'DESTROY_MOON_NOFLEETLOSS', 'count' => 200, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 20000000000)
                    )
                ),
                26 => array
                (
                    'details' => array(
                        'img' => 'planeten/mond.jpg',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'DESTROY_MOON_NOFLEETLOSS',
                    'jobs' => array
                    (
                        array('type' => 'DESTROY_MOON_NOFLEETLOSS', 'count' => 50, 'statusField' => 'count', 'minimalDiameter' => 8000)
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 25000000000)
                    )
                ),
                27 => array
                (
                    'details' => array(
                        'img' => 'planeten/mond.jpg',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'DESTROY_MOON',
                    'jobs' => array
                    (
                        array('type' => 'DESTROY_MOON', 'count' => 25, 'statusField' => 'count', 'minimalDiameter' => 9000)
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 25000000000)
                    )
                ),
                28 => array
                (
                    'details' => array(
                        'img' => 'planeten/mond.jpg',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'DESTROY_MOON',
                    'jobs' => array
                    (
                        array('type' => 'DESTROY_MOON', 'count' => 5, 'statusField' => 'count', 'minimalDiameter' => 9900)
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 100000000000)
                    )
                ),
                29 => array
                (
                    'details' => array(
                        'img' => 'planeten/mond.jpg',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'CREATE_MOON',
                    'jobs' => array
                    (
                        array('type' => 'CREATE_MOON', 'count' => 100, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 20000000000)
                    )
                ),
                30 => array
                (
                    'details' => array(
                        'img' => 'planeten/mond.jpg',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'CREATE_MOON',
                    'jobs' => array
                    (
                        array('type' => 'CREATE_MOON', 'count' => 250, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 50000000000)
                    )
                ),
                31 => array
                (
                    'details' => array(
                        'img' => 'planeten/mond.jpg',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'CREATE_MOON',
                    'jobs' => array
                    (
                        array('type' => 'CREATE_MOON', 'count' => 500, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 100000000000)
                    )
                ),
                32 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/204.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'BATTLE_DESTROY_MILITARYUNITS',
                    'jobs' => array
                    (
                        array('type' => 'BATTLE_DESTROY_MILITARYUNITS', 'count' => 100000000, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 1000000000)
                    )
                ),
                33 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/204.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'BATTLE_DESTROY_MILITARYUNITS',
                    'jobs' => array
                    (
                        array('type' => 'BATTLE_DESTROY_MILITARYUNITS', 'count' => 1000000000, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 10000000000)
                    )
                ),
                34 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/204.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'BATTLE_DESTROY_MILITARYUNITS',
                    'jobs' => array
                    (
                        array('type' => 'BATTLE_DESTROY_MILITARYUNITS', 'count' => 5000000000, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 50000000000)
                    )
                ),
                35 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/215.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'BATTLE_DESTROY_MOREEXPENSIVEFLEET_SOLO',
                    'jobs' => array
                    (
                        array('type' => 'BATTLE_DESTROY_MOREEXPENSIVEFLEET_SOLO', 'count' => 1, 'statusField' => 'count', 'minimalEnemyCost' => 10000000000, 'maximalOwnValue' => 0.6)
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'cry' => 500000000)
                    )
                ),
                36 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/215.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'BATTLE_DESTROY_MOREEXPENSIVEFLEET_SOLO',
                    'jobs' => array
                    (
                        array('type' => 'BATTLE_DESTROY_MOREEXPENSIVEFLEET_SOLO', 'count' => 1, 'statusField' => 'count', 'minimalEnemyCost' => 300000000000, 'maximalOwnValue' => 0.5)
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'cry' => 15000000000)
                    )
                ),
                37 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/215.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'BATTLE_DESTROY_MOREEXPENSIVEFLEET_SOLO',
                    'jobs' => array
                    (
                        array('type' => 'BATTLE_DESTROY_MOREEXPENSIVEFLEET_SOLO', 'count' => 1, 'statusField' => 'count', 'minimalEnemyCost' => 1000000000000, 'maximalOwnValue' => 0.4)
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'cry' => 50000000000)
                    )
                ),
                38 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/213.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'BATTLE_DESTROY_MOREEXPENSIVEFLEET_ACS',
                    'jobs' => array
                    (
                        array('type' => 'BATTLE_DESTROY_MOREEXPENSIVEFLEET_ACS', 'count' => 3, 'statusField' => 'count', 'minimalEnemyCost' => 10000000000, 'maximalOwnValue' => 0.35)
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'cry' => 1000000000)
                    )
                ),
                39 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/213.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'BATTLE_DESTROY_MOREEXPENSIVEFLEET_ACS',
                    'jobs' => array
                    (
                        array('type' => 'BATTLE_DESTROY_MOREEXPENSIVEFLEET_ACS', 'count' => 3, 'statusField' => 'count', 'minimalEnemyCost' => 500000000000, 'maximalOwnValue' => 0.30)
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'cry' => 50000000000)
                    )
                ),
                40 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/213.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'BATTLE_DESTROY_MOREEXPENSIVEFLEET_ACS',
                    'jobs' => array
                    (
                        array('type' => 'BATTLE_DESTROY_MOREEXPENSIVEFLEET_ACS', 'count' => 3, 'statusField' => 'count', 'minimalEnemyCost' => 1500000000000, 'maximalOwnValue' => 0.25)
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'cry' => 150000000000)
                    )
                ),
                41 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/205.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'BATTLE_WIN',
                    'jobs' => array
                    (
                        array('type' => 'BATTLE_WIN', 'count' => 1000, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 30000000)
                    )
                ),
                42 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/205.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'BATTLE_WIN',
                    'jobs' => array
                    (
                        array('type' => 'BATTLE_WIN', 'count' => 5000, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 100000000)
                    )
                ),
                43 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/205.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'BATTLE_WIN',
                    'jobs' => array
                    (
                        array('type' => 'BATTLE_WIN', 'count' => 10000, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 300000000)
                    )
                ),
                44 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/205.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'BATTLE_WIN',
                    'jobs' => array
                    (
                        array('type' => 'BATTLE_WIN', 'count' => 25000, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 1000000000)
                    )
                ),
                45 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/205.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'BATTLE_WIN',
                    'jobs' => array
                    (
                        array('type' => 'BATTLE_WIN', 'count' => 50000, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 3000000000)
                    )
                ),
                46 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/206.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'BATTLE_WINORDRAW_SOLO_LIMIT',
                    'jobs' => array
                    (
                        array('type' => 'BATTLE_WINORDRAW_SOLO_LIMIT', 'count' => 25, 'statusField' => 'count', 'minimalEnemyPercentLimit' => 100000)
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 4000000000)
                    )
                ),
                47 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/206.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'BATTLE_WINORDRAW_SOLO_LIMIT',
                    'jobs' => array
                    (
                        array('type' => 'BATTLE_WINORDRAW_SOLO_LIMIT', 'count' => 50, 'statusField' => 'count', 'minimalEnemyPercentLimit' => 100000)
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 16000000000)
                    )
                ),
                48 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/206.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'BATTLE_WINORDRAW_SOLO_LIMIT',
                    'jobs' => array
                    (
                        array('type' => 'BATTLE_WINORDRAW_SOLO_LIMIT', 'count' => 100, 'statusField' => 'count', 'minimalEnemyPercentLimit' => 100000)
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 40000000000)
                    )
                ),
                49 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/218.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'BATTLE_WINORDRAW_SOLO_LIMIT',
                    'jobs' => array
                    (
                        array('type' => 'BATTLE_WINORDRAW_SOLO_LIMIT', 'count' => 15, 'statusField' => 'count', 'minimalEnemyPercentLimit' => 500000)
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 10000000000)
                    )
                ),
                50 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/218.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'BATTLE_WINORDRAW_SOLO_LIMIT',
                    'jobs' => array
                    (
                        array('type' => 'BATTLE_WINORDRAW_SOLO_LIMIT', 'count' => 30, 'statusField' => 'count', 'minimalEnemyPercentLimit' => 500000)
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 40000000000)
                    )
                ),
                51 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/218.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'BATTLE_WINORDRAW_SOLO_LIMIT',
                    'jobs' => array
                    (
                        array('type' => 'BATTLE_WINORDRAW_SOLO_LIMIT', 'count' => 50, 'statusField' => 'count', 'minimalEnemyPercentLimit' => 500000)
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 100000000000)
                    )
                ),
                52 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/218.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'BATTLE_WINORDRAW_LIMIT',
                    'jobs' => array
                    (
                        array('type' => 'BATTLE_WINORDRAW_LIMIT', 'count' => 15, 'statusField' => 'count', 'minimalEnemyPercentLimit' => 1000000)
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 15000000000)
                    )
                ),
                53 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/218.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'BATTLE_WINORDRAW_LIMIT',
                    'jobs' => array
                    (
                        array('type' => 'BATTLE_WINORDRAW_LIMIT', 'count' => 30, 'statusField' => 'count', 'minimalEnemyPercentLimit' => 1000000)
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 50000000000)
                    )
                ),
                54 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/218.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'BATTLE_WINORDRAW_LIMIT',
                    'jobs' => array
                    (
                        array('type' => 'BATTLE_WINORDRAW_LIMIT', 'count' => 50, 'statusField' => 'count', 'minimalEnemyPercentLimit' => 1000000)
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 125000000000)
                    )
                ),
                55 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/218.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'BATTLE_WINORDRAW_ACS_LIMIT',
                    'jobs' => array
                    (
                        array('type' => 'BATTLE_WINORDRAW_ACS_LIMIT', 'count' => 5, 'statusField' => 'count', 'minimalEnemyPercentLimit' => 5000000)
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 500000000000)
                    )
                ),
                56 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/223.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'BATTLE_BLOCK_MOONDESTROY',
                    'jobs' => array
                    (
                        array('type' => 'BATTLE_BLOCK_MOONDESTROY')
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 4000000000)
                    )
                ),
                57 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/224.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'BATTLE_WINORDRAW_SOLO_TOTALLIMIT',
                    'jobs' => array
                    (
                        array('type' => 'BATTLE_WINORDRAW_SOLO_TOTALLIMIT', 'count' => 50000000, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 1000000000000)
                    )
                ),
                58 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/209.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'DEBRIS_COLLECT_METAL|DEBRIS_COLLECT_CRYSTAL',
                    'jobs' => array
                    (
                        array('type' => 'DEBRIS_COLLECT_METAL', 'count' => 30000000000000, 'statusField' => 'count'),
                        array('type' => 'DEBRIS_COLLECT_CRYSTAL', 'count' => 15000000000000, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'met' => 500000000000, 'cry' => 500000000000)
                    )
                ),
                59 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/217.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'BATTLE_COLLECT_METAL|BATTLE_COLLECT_CRYSTAL|BATTLE_COLLECT_DEUTERIUM',
                    'jobs' => array
                    (
                        array('type' => 'BATTLE_COLLECT_METAL', 'count' => 100000000000, 'statusField' => 'count'),
                        array('type' => 'BATTLE_COLLECT_CRYSTAL', 'count' => 100000000000, 'statusField' => 'count'),
                        array('type' => 'BATTLE_COLLECT_DEUTERIUM', 'count' => 100000000000, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 5000000000)
                    )
                ),
                60 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/217.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'BATTLE_COLLECT_METAL|BATTLE_COLLECT_CRYSTAL|BATTLE_COLLECT_DEUTERIUM',
                    'jobs' => array
                    (
                        array('type' => 'BATTLE_COLLECT_METAL', 'count' => 500000000000, 'statusField' => 'count'),
                        array('type' => 'BATTLE_COLLECT_CRYSTAL', 'count' => 500000000000, 'statusField' => 'count'),
                        array('type' => 'BATTLE_COLLECT_DEUTERIUM', 'count' => 500000000000, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 20000000000)
                    )
                ),
                61 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/217.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'BATTLE_COLLECT_METAL|BATTLE_COLLECT_CRYSTAL|BATTLE_COLLECT_DEUTERIUM',
                    'jobs' => array
                    (
                        array('type' => 'BATTLE_COLLECT_METAL', 'count' => 1000000000000, 'statusField' => 'count'),
                        array('type' => 'BATTLE_COLLECT_CRYSTAL', 'count' => 1000000000000, 'statusField' => 'count'),
                        array('type' => 'BATTLE_COLLECT_DEUTERIUM', 'count' => 1000000000000, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 40000000000)
                    )
                ),
                62 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/204.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'CONSTRUCT_SHIPS_OR_DEFENSE_MULTIID',
                    'jobs' => array
                    (
                        array('type' => 'CONSTRUCT_SHIPS_OR_DEFENSE_MULTIID', 'elementIDs' => array(204, 205), 'count' => 1000000000, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'met' => 90000000000, 'cry' => 30000000000)
                    )
                ),
                63 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/205.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'CONSTRUCT_SHIPS_OR_DEFENSE_MULTIID',
                    'jobs' => array
                    (
                        array('type' => 'CONSTRUCT_SHIPS_OR_DEFENSE_MULTIID', 'elementIDs' => array(204, 205), 'count' => 10000000000, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'met' => 1000000000000, 'cry' => 350000000000)
                    )
                ),
            ),
        ),
        3 => array
        (
            'requirements' => array(),
            'skip' => array
            (
                'possible' => false,
            ),
            'reward' => array(),
            'tasks' => array
            (
                64 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/1.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'MULTIPLE_CONSTRUCTION_END',
                    'jobs' => array
                    (
                        array('type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 1, 'level' => 30, 'count' => 10, 'statusField' => 'count'),
                        array('type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 2, 'level' => 30, 'count' => 10, 'statusField' => 'count'),
                        array('type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 3, 'level' => 30, 'count' => 10, 'statusField' => 'count'),
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'met' => 25000000, 'cry' => 10000000)
                    )
                ),
                65 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/2.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'MULTIPLE_CONSTRUCTION_END',
                    'jobs' => array
                    (
                        array('type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 1, 'level' => 40, 'count' => 10, 'statusField' => 'count'),
                        array('type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 2, 'level' => 40, 'count' => 10, 'statusField' => 'count'),
                        array('type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 3, 'level' => 40, 'count' => 10, 'statusField' => 'count'),
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'met' => 2000000000, 'cry' => 850000000)
                    )
                ),
                66 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/3.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'MULTIPLE_CONSTRUCTION_END',
                    'jobs' => array
                    (
                        array('type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 1, 'level' => 50, 'count' => 5, 'statusField' => 'count'),
                        array('type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 2, 'level' => 50, 'count' => 5, 'statusField' => 'count'),
                        array('type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 3, 'level' => 50, 'count' => 5, 'statusField' => 'count'),
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'met' => 60000000000, 'cry' => 28000000000)
                    )
                ),
                67 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/3.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'MULTIPLE_CONSTRUCTION_END',
                    'jobs' => array
                    (
                        array('type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 1, 'level' => 50, 'count' => 10, 'statusField' => 'count'),
                        array('type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 2, 'level' => 50, 'count' => 10, 'statusField' => 'count'),
                        array('type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 3, 'level' => 50, 'count' => 10, 'statusField' => 'count'),
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'met' => 120000000000, 'cry' => 60000000000)
                    )
                ),
                68 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/2.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'REACH_EXTRACTION_LEVEL',
                    'jobs' => array
                    (
                        array('type' => 'REACH_EXTRACTION_LEVEL', 'buildingID' => 1, 'resource' => 'metal', 'level' => 45000000),
                        array('type' => 'REACH_EXTRACTION_LEVEL', 'buildingID' => 2, 'resource' => 'crystal', 'level' => 30000000),
                        array('type' => 'REACH_EXTRACTION_LEVEL', 'buildingID' => 3, 'resource' => 'deuterium', 'level' => 22000000),
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'met' => 75000000000, 'cry' => 50000000000, 'deu' => 37000000000)
                    )
                ),
                69 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/23.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'MULTIPLE_CONSTRUCTION_END',
                    'jobs' => array
                    (
                        array('type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 22, 'level' => 25, 'count' => 5, 'statusField' => 'count'),
                        array('type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 23, 'level' => 25, 'count' => 5, 'statusField' => 'count'),
                        array('type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 24, 'level' => 25, 'count' => 5, 'statusField' => 'count'),
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'met' => 100000000, 'cry' => 50000000)
                    )
                ),
                70 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/33.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'REACH_TERRAFORMING_LEVEL',
                    'jobs' => array
                    (
                        array('type' => 'REACH_TERRAFORMING_LEVEL', 'fields' => 700),
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'cry' => 200000000, 'deu' => 400000000)
                    )
                ),
                71 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/3.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'REACH_TOTAL_EXTRACTION_LEVEL',
                    'jobs' => array
                    (
                        array('type' => 'REACH_TOTAL_EXTRACTION_LEVEL', 'buildingID' => 3, 'resource' => 'deuterium', 'level' => 200000000, 'statusField' => 'level'),
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 75000000000)
                    )
                ),
            )
        ),
        4 => array
        (
            'requirements' => array(),
            'skip' => array
            (
                'possible' => false,
            ),
            'reward' => array(),
            'tasks' => array
            (
                72 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/109.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'RESEARCH_END',
                    'jobs' => array
                    (
                        array('type' => 'RESEARCH_END', 'elementID' => 109, 'level' => 25),
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'cry' => 2000000000)
                    )
                ),
                73 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/109.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'RESEARCH_END',
                    'jobs' => array
                    (
                        array('type' => 'RESEARCH_END', 'elementID' => 109, 'level' => 30),
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'cry' => 64000000000)
                    )
                ),
                74 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/110.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'RESEARCH_END',
                    'jobs' => array
                    (
                        array('type' => 'RESEARCH_END', 'elementID' => 110, 'level' => 25),
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'cry' => 5000000000)
                    )
                ),
                75 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/110.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'RESEARCH_END',
                    'jobs' => array
                    (
                        array('type' => 'RESEARCH_END', 'elementID' => 110, 'level' => 30),
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'cry' => 160000000000)
                    )
                ),
                76 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/111.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'RESEARCH_END',
                    'jobs' => array
                    (
                        array('type' => 'RESEARCH_END', 'elementID' => 111, 'level' => 25),
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'met' => 10000000000)
                    )
                ),
                77 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/111.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'RESEARCH_END',
                    'jobs' => array
                    (
                        array('type' => 'RESEARCH_END', 'elementID' => 111, 'level' => 30),
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'met' => 320000000000)
                    )
                ),
                78 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/120.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'RESEARCH_END',
                    'jobs' => array
                    (
                        array('type' => 'RESEARCH_END', 'elementID' => 120, 'level' => 25),
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'met' => 1000000000)
                    )
                ),
                79 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/120.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'RESEARCH_END',
                    'jobs' => array
                    (
                        array('type' => 'RESEARCH_END', 'elementID' => 120, 'level' => 30),
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'met' => 30000000000)
                    )
                ),
                80 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/120.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'RESEARCH_END',
                    'jobs' => array
                    (
                        array('type' => 'RESEARCH_END', 'elementID' => 120, 'level' => 33),
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'met' => 300000000000)
                    )
                ),
                81 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/121.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'RESEARCH_END',
                    'jobs' => array
                    (
                        array('type' => 'RESEARCH_END', 'elementID' => 121, 'level' => 20),
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 50000000)
                    )
                ),
                82 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/121.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'RESEARCH_END',
                    'jobs' => array
                    (
                        array('type' => 'RESEARCH_END', 'elementID' => 121, 'level' => 25),
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 1500000000)
                    )
                ),
                83 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/121.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'RESEARCH_END',
                    'jobs' => array
                    (
                        array('type' => 'RESEARCH_END', 'elementID' => 121, 'level' => 28),
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 15000000000)
                    )
                ),
                84 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/122.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'RESEARCH_END',
                    'jobs' => array
                    (
                        array('type' => 'RESEARCH_END', 'elementID' => 122, 'level' => 20),
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'cry' => 500000000)
                    )
                ),
                85 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/122.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'RESEARCH_END',
                    'jobs' => array
                    (
                        array('type' => 'RESEARCH_END', 'elementID' => 122, 'level' => 25),
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'cry' => 15000000000)
                    )
                ),
                86 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/122.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'RESEARCH_END',
                    'jobs' => array
                    (
                        array('type' => 'RESEARCH_END', 'elementID' => 122, 'level' => 28),
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'cry' => 150000000000)
                    )
                ),
                87 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/123.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'RESEARCH_END',
                    'jobs' => array
                    (
                        array('type' => 'RESEARCH_END', 'elementID' => 123, 'level' => 9),
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'cry' => 50000000)
                    )
                ),
                88 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/31.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'MULTIPLE_CONSTRUCTION_END',
                    'jobs' => array
                    (
                        array('type' => 'MULTIPLE_CONSTRUCTION_END', 'elementID' => 31, 'level' => 22, 'count' => 10, 'statusField' => 'count'),
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'cry' => 1000000000, 'deu' => 1000000000)
                    )
                ),
                89 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/108.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'REACH_TECHPOINTS_LEVEL',
                    'jobs' => array
                    (
                        array('type' => 'REACH_TECHPOINTS_LEVEL', 'level' => 5000000, 'statusField' => 'level'),
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 5000000)
                    )
                ),
                90 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/108.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'REACH_TECHPOINTS_LEVEL',
                    'jobs' => array
                    (
                        array('type' => 'REACH_TECHPOINTS_LEVEL', 'level' => 10000000, 'statusField' => 'level'),
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 10000000)
                    )
                ),
                91 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/108.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'REACH_TECHPOINTS_LEVEL',
                    'jobs' => array
                    (
                        array('type' => 'REACH_TECHPOINTS_LEVEL', 'level' => 50000000, 'statusField' => 'level'),
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 50000000)
                    )
                ),
                92 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/108.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'REACH_TECHPOINTS_LEVEL',
                    'jobs' => array
                    (
                        array('type' => 'REACH_TECHPOINTS_LEVEL', 'level' => 200000000, 'statusField' => 'level'),
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 200000000)
                    )
                ),
                93 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/108.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'REACH_TECHPOINTS_LEVEL',
                    'jobs' => array
                    (
                        array('type' => 'REACH_TECHPOINTS_LEVEL', 'level' => 500000000, 'statusField' => 'level'),
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 500000000)
                    )
                ),
            )
        ),
        5 => array
        (
            'requirements' => array(),
            'skip' => array
            (
                'possible' => false,
            ),
            'reward' => array(),
            'tasks' => array
            (
                94 => array
                (
                    'details' => array(
                        'img' => 'planeten/mond.jpg',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'CREATE_MOON_FRIENDLY',
                    'jobs' => array
                    (
                        array('type' => 'CREATE_MOON_FRIENDLY', 'count' => 5, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 2500000)
                    )
                ),
                95 => array
                (
                    'details' => array(
                        'img' => 'planeten/mond.jpg',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'CREATE_MOON_FRIENDLY',
                    'jobs' => array
                    (
                        array('type' => 'CREATE_MOON_FRIENDLY', 'count' => 20, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 7500000)
                    )
                ),
                96 => array
                (
                    'details' => array(
                        'img' => 'planeten/mond.jpg',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'CREATE_MOON_FRIENDLY',
                    'jobs' => array
                    (
                        array('type' => 'CREATE_MOON_FRIENDLY', 'count' => 50, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 15000000)
                    )
                ),
                97 => array
                (
                    'details' => array(
                        'img' => 'planeten/mond.jpg',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'CREATE_MOON_FRIENDLY',
                    'jobs' => array
                    (
                        array('type' => 'CREATE_MOON_FRIENDLY', 'count' => 125, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 30000000)
                    )
                ),
                98 => array
                (
                    'details' => array(
                        'img' => 'planeten/mond.jpg',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'CREATE_MOON_FRIENDLY',
                    'jobs' => array
                    (
                        array('type' => 'CREATE_MOON_FRIENDLY', 'count' => 250, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 50000000)
                    )
                ),
                99 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/218.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'BATTLE_WINORDRAW_ACS_LIMIT',
                    'jobs' => array
                    (
                        array('type' => 'BATTLE_WINORDRAW_ACS_LIMIT', 'count' => 25, 'statusField' => 'count', 'minimalEnemyPercentLimit' => 10000, 'hasToBeLeader' => true)
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 10000000)
                    )
                ),
                100 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/218.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'BATTLE_WINORDRAW_ACS_LIMIT',
                    'jobs' => array
                    (
                        array('type' => 'BATTLE_WINORDRAW_ACS_LIMIT', 'count' => 50, 'statusField' => 'count', 'minimalEnemyPercentLimit' => 10000, 'hasToBeLeader' => true)
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 20000000)
                    )
                ),
                101 => array
                (
                    'details' => array(
                        'img' => 'gebaeude/218.gif',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'BATTLE_WINORDRAW_ACS_LIMIT',
                    'jobs' => array
                    (
                        array('type' => 'BATTLE_WINORDRAW_ACS_LIMIT', 'count' => 150, 'statusField' => 'count', 'minimalEnemyPercentLimit' => 10000, 'hasToBeLeader' => true)
                    ),
                    'reward' => array
                    (
                        array('type' => 'RESOURCES', 'deu' => 50000000)
                    )
                ),
            )
        ),
        6 => array
        (
            'requirements' => array(),
            'skip' => array
            (
                'possible' => false,
            ),
            'reward' => array(),
            'tasks' => array
            (
                102 => array
                (
                    'details' => array(
                        'img' => 'img/proacc.jpg',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'NEWUSER_REGISTER',
                    'jobs' => array
                    (
                        array('type' => 'NEWUSER_REGISTER', 'count' => 10, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'PREMIUM_RESOURCE', 'value' => 25)
                    )
                ),
                103 => array
                (
                    'details' => array(
                        'img' => 'img/proacc.jpg',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'NEWUSER_REGISTER',
                    'jobs' => array
                    (
                        array('type' => 'NEWUSER_REGISTER', 'count' => 20, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'PREMIUM_RESOURCE', 'value' => 25)
                    )
                ),
                104 => array
                (
                    'details' => array(
                        'img' => 'img/proacc.jpg',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'NEWUSER_REGISTER',
                    'jobs' => array
                    (
                        array('type' => 'NEWUSER_REGISTER', 'count' => 40, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'PREMIUM_RESOURCE', 'value' => 50)
                    )
                ),
                105 => array
                (
                    'details' => array(
                        'img' => 'img/proacc.jpg',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'INVITEDUSER_BOUGHT_DE',
                    'jobs' => array
                    (
                        array('type' => 'INVITEDUSER_BOUGHT_DE', 'count' => 40)
                    ),
                    'reward' => array
                    (
                        array('type' => 'PREMIUM_RESOURCE', 'value' => 10)
                    )
                ),
                106 => array
                (
                    'details' => array(
                        'img' => 'img/proacc.jpg',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'INVITEDUSERS_BOUGHT_DE_USERCOUNT',
                    'jobs' => array
                    (
                        array('type' => 'INVITEDUSERS_BOUGHT_DE_USERCOUNT', 'count' => 5, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'PREMIUM_RESOURCE', 'value' => 25)
                    )
                ),
                107 => array
                (
                    'details' => array(
                        'img' => 'img/proacc.jpg',
                    ),

                    'requirements' => array(),
                    'jobtypes' => 'INVITEDUSERS_BOUGHT_DE_LIMIT',
                    'jobs' => array
                    (
                        array('type' => 'INVITEDUSERS_BOUGHT_DE_LIMIT', 'count' => 400, 'statusField' => 'count')
                    ),
                    'reward' => array
                    (
                        array('type' => 'PREMIUM_RESOURCE', 'value' => 50)
                    )
                ),
            )
        ),
    );
    // Last Cat ID: 6
    // Last Task ID: 107
}

?>
