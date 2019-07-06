<?php

function doquery($query, $table, $fetch = false) {
    global $_DBLink, $_EnginePath, $_User;
    static $__ServerConnectionSettings, $dbErrorHandler = NULL;

    if ($dbErrorHandler === NULL) {
        include($_EnginePath . 'includes/debug.class.php');
        $dbErrorHandler = new DBErrorHandler();
    }

    if (empty($__ServerConnectionSettings)) {
        require($_EnginePath . 'config.php');
    }

    if (!$_DBLink) {
        $_DBLink = new mysqli(
            $__ServerConnectionSettings['server'],
            $__ServerConnectionSettings['user'],
            $__ServerConnectionSettings['pass']
        );

        if ($_DBLink->connect_errno) {
            $dbErrorHandler->error($_DBLink->error . '<br/>' . $query);
        }

        $_DBLink->select_db($__ServerConnectionSettings['name']);

        if ($_DBLink->errno) {
            $dbErrorHandler->error($_DBLink->error . '<br/>' . $query);
        }

        $_DBLink->query("SET NAMES 'UTF8';");
    }

    $Replace_Search = [
        '{{table}}',
        '{{prefix}}',
        'DROP'
    ];
    $Replace_Replace = [
        $__ServerConnectionSettings['prefix'].$table,
        $__ServerConnectionSettings['prefix'],
        ''
    ];

    $SQLQuery_Final = str_replace($Replace_Search, $Replace_Replace, $query);

    if (isset($_User['id']) && $_User['id'] > 1) {
        $SQLQuery_Final = str_replace(
            'TRUNCATE',
            '',
            $SQLQuery_Final
        );
    }

    $SQLResult = $_DBLink->query($SQLQuery_Final);

    if ($_DBLink->errno) {
        $dbErrorHandler->error(
            $_DBLink->error .
            '<br/>' . $SQLQuery_Final .
            '<br/>File: ' . $_SERVER['REQUEST_URI'] .
            '<br/>User: ' . $_User['username'] . '[' . $_User['id'] . ']<br/>'
        );
    }

    if ($fetch) {
        return $SQLResult->fetch_assoc();
    }

    return $SQLResult;
}

function getDBLink() {
    global $_DBLink;

    return $_DBLink;
}

function closeDBLink() {
    global $_DBLink;

    if (!$_DBLink) {
        return;
    }

    $_DBLink->close();
}

?>
