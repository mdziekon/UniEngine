<?php

function doquery($query, $table, $fetch = false)
{
    global $_DBLink, $_EnginePath, $_User;
    static $__ServerConnectionSettings, $debug = NULL;

    if($debug === NULL)
    {
        include($_EnginePath.'includes/debug.class.php');
        $debug = new debug();
    }

    if(empty($__ServerConnectionSettings))
    {
        if(LOCALHOST)
        {
            require($_EnginePath.'config.localhost.php');
        }
        else if(TESTSERVER)
        {
            require($_EnginePath.'config.testserver.php');
        }
        else
        {
            require($_EnginePath.'config.php');
        }
    }

    if(!$_DBLink)
    {
        $_DBLink = mysqli_connect(
            $__ServerConnectionSettings['server'],
            $__ServerConnectionSettings['user'],
            $__ServerConnectionSettings['pass']
        );

        if ($_DBLink->connect_errno) {
            $debug->error(mysqli_error($_DBLink).'<br/>'.$query);
        }

        $_DBLink->select_db($__ServerConnectionSettings['name']);

        if ($_DBLink->errno) {
            $debug->error(mysqli_error($_DBLink).'<br/>'.$query);
        }

        $_DBLink->query("SET NAMES 'UTF8';");
    }
    $Replace_Search = array('{{table}}', '{{prefix}}', 'DROP');
    $Replace_Replace = array($__ServerConnectionSettings['prefix'].$table, $__ServerConnectionSettings['prefix'], '');
    $sql = str_replace($Replace_Search, $Replace_Replace, $query);
    if(isset($_User['id']) && $_User['id'] > 1)
    {
        $sql = str_replace('TRUNCATE', '', $sql);
    }

    $sqlquery = $_DBLink->query($sql);

    if ($_DBLink->errno) {
        $debug->error(
            mysqli_error($_DBLink) .
            '<br/>' . $sql .
            '<br/>File: ' . $_SERVER['REQUEST_URI'] .
            '<br/>User: ' . $_User['username'] . '[' . $_User['id'] . ']<br/>'
        );
    }

    if($fetch)
    {
        $sqlrow = $sqlquery->fetch_array(MYSQLI_ASSOC);

        return $sqlrow;
    }
    else
    {
        return $sqlquery;
    }
}

function getDBLink()
{
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
