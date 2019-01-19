<?php

if(!defined('IN_UPDATER'))
{
    die();
}

// --- Update /includes/constants.php
// > Add REGISTER_RECAPTCHA_SERVERIP_AS_HOSTNAME constant
function update2_add_newconstants() {
    $filePath = "../includes/constants.php";

    $fileLines = file($filePath, FILE_IGNORE_NEW_LINES);

    // Check if constants.php does not already contain this definition
    foreach ($fileLines as $lineIdx => $lineValue) {
        if (strpos($lineValue, "REGISTER_RECAPTCHA_SERVERIP_AS_HOSTNAME") !== false) {
            return;
        }
    }

    // Find position to inject new constant
    $insertAfter = -1;

    foreach ($fileLines as $lineIdx => $lineValue) {
        if (strpos($lineValue, "REGISTER_RECAPTCHA_ENABLE") === false) {
            continue;
        }

        $insertAfter = $lineIdx;
        break;
    }

    if ($insertAfter === -1) {
        throw new Exception(
            "update2_add_newconstants: " .
            "could not find the position to inject \"REGISTER_RECAPTCHA_SERVERIP_AS_HOSTNAME\" constant! " .
            "\"REGISTER_RECAPTCHA_ENABLE\" constant not found!"
        );
    }

    array_splice(
        $fileLines,
        $insertAfter + 1,
        0,
        [
            "define('REGISTER_RECAPTCHA_SERVERIP_AS_HOSTNAME', true);"
        ]
    );

    $writeResult = file_put_contents(
        $filePath,
        implode("\n", $fileLines),
        LOCK_EX
    );

    if ($writeResult === false) {
        throw new Exception(
            "update2_add_newconstants: " .
            "could not write to file \"{$filePath}\"!"
        );
    }
}

update2_add_newconstants();

?>
