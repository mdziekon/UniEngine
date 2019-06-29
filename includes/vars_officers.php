<?php

if(!defined('INSIDE')){ die('Access Denied!');}

$_Vars_Officers = [
    'geologist' => [
        'type'      => 1,
        'price'     => [ 20 ],
        'time'      => [ 14 ],
        'itemid'    => [ 5 ],
    ],
    'engineer' => [
        'type'      => 1,
        'price'     => [ 20 ],
        'time'      => [ 14 ],
        'itemid'    => [ 6 ],
    ],
    'admiral' => [
        'type'      => 1,
        'price'     => [ 20 ],
        'time'      => [ 14 ],
        'itemid'    => [ 7 ],
    ],
    'technocrat' => [
        'type'      => 1,
        'price'     => [ 20 ],
        'time'      => [ 14 ],
        'itemid'    => [ 8 ],
    ],
    'salesman' => [
        'type'      => 2,
        'field'     => 'trader_usesCount',
        'thisState' => 'TransactionsLeft',
        'price'     => [
            10,
            30,
            50
        ],
        'count'     => [
            5,
            15,
            30
        ],
        'itemid'    => [
            14,
            15,
            16
        ],
    ]
];

?>
