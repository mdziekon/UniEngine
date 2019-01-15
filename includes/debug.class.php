<?php

if(!defined('INSIDE')){ die('Access Denied!');}

class debug
{
    var $NestingPrevention = 0;
    var $PreviousMessage = '';

    function error($message) {
        global $_DBLink, $_User, $_EnginePath;

        $this->NestingPrevention += 1;

        if ($this->NestingPrevention > 1) {
            trigger_error(
                (
                    '<b>ErrorNesting Prevention!</b><br/>' .
                    $this->PreviousMessage
                ),
                E_USER_ERROR
            );
        }
        define('IN_ERROR', true);

        if (LOCALHOST) {
            require($_EnginePath . 'config.localhost.php');
        } else if (TESTSERVER) {
            require($_EnginePath . 'config.testserver.php');
        } else {
            require($_EnginePath . 'config.php');
        }

        if (!$_DBLink) {
            trigger_error('DBDriver Connection Error #01<br/>', E_USER_ERROR);
        }
        if (empty($_User['id'])) {
            $_User['id'] = '0';
        }
        $Replace_Search = [
            '{{table}}',
            '{{prefix}}'
        ];
        $Replace_Replace = [
            $__ServerConnectionSettings['prefix'] . 'errors',
            $__ServerConnectionSettings['prefix']
        ];

        $EscapedMessage = $_DBLink->escape_string($message);

        $SQLQuery_InsertError = (
            "INSERT INTO {{table}} SET " .
            "`error_sender` = {$_User['id']}, " .
            "`error_time` = UNIX_TIMESTAMP(), " .
            "`error_text` = '{$EscapedMessage}' " .
            ";"
        );
        $SQLQuery_InsertError = str_replace(
            $Replace_Search,
            $Replace_Replace,
            $SQLQuery_InsertError
        );

        $_DBLink->query($SQLQuery_InsertError);

        if ($_DBLink->errno) {
            trigger_error(
                'DBDriver Fatal Error #01<br/>' . $_DBLink->error,
                E_USER_ERROR
            );
        }

        $ErrorID = $_DBLink->insert_id;

        $ErrorMsg = 'An Error occured!<br/>Error ID: <b>'. $ErrorID .'</b>';
        $this->PreviousMessage = $ErrorMsg;

        if (!function_exists('message')) {
            echo $ErrorMsg;
        } else {
            message($ErrorMsg, 'System Error!');
        }

        $this->NestingPrevention -= 1;

        $_DBLink->close();

        die();
    }
}

?>
