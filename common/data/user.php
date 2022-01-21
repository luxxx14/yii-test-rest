<?php

$loginList = [
    'admin',
    'tester1',
    'tester2',
    'reporter1',
    'reporter2',
    'vitaliy',
    'nadya',
    'nadya',
    'gulzat',
    'rustam',
    'zhanara',
    'timur123',
    'vitaliy',
    'alex',
    'kate',
    'meirlen',
    'nadezhda1995',
    'gulzat2',
    'alex1',
];

$result = [];
foreach ($loginList as $i => $login) {
    $result[] = [
        'id' => $i + 1,
        'login' => $login,
        'status' => 1,
        'created_at' => '2018-03-28 21:00:13',
    ];
}

return $result;
