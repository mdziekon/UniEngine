<?php

function verify_requirements($params) {
    $hasPassed = true;
    $tests = [
        'PHPVersion' => false,
        'PHPNoticesOff' => false,
        'ConfigDirectoryWritable' => false,
        'ConfigDirectoryMigrationEntryDoesNotExist' => false,
        'ConfigWritable' => false,
        'ConstantsWritable' => false,
        'RegisterWritable' => false,
        'ActionLogsWritable' => false,
        'AdminActionLogsWritable' => false,
    ];

    if (version_compare(PHP_VERSION, '5.4.0') < 0) {
        $hasPassed = false;
        $tests['PHPVersion'] = true;
    }

    if (error_reporting() & E_NOTICE) {
        $hasPassed = false;
        $tests['PHPNoticesOff'] = true;
    }

    if (!is_writable('../'.$params['configDirectory'].'/')) {
        $hasPassed = false;
        $tests['ConfigDirectoryWritable'] = true;
    }

    if (file_exists('../'.$params['configDirectory'].'/latest-applied-migration')) {
        $hasPassed = false;
        $tests['ConfigDirectoryMigrationEntryDoesNotExist'] = true;
    }

    if (!is_writable('../'.$params['configFile'].'.php')) {
        $hasPassed = false;
        $tests['ConfigWritable'] = true;
    }

    if (!is_writable('../includes/constants.php')) {
        $hasPassed = false;
        $tests['ConstantsWritable'] = true;
    }

    if (!is_writable('../js/register.js')) {
        $hasPassed = false;
        $tests['RegisterWritable'] = true;
    }

    if (!is_writable('../action_logs')) {
        $hasPassed = false;
        $tests['ActionLogsWritable'] = true;
    }

    if (!is_writable('../admin/action_logs')) {
        $hasPassed = false;
        $tests['AdminActionLogsWritable'] = true;
    }

    return [
        'hasPassed' => $hasPassed,
        'tests' => $tests
    ];
}

?>
