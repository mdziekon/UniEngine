<?php

namespace UniEngine\Engine\Includes\Helpers\Common\Navigation;

function getPageURL($pageType, $pageParams) {
    switch ($pageType) {
        case "login":
            return "login.php";
        case "registration":
            return "reg_mainpage.php";
        default:
            throw new \Exception("UniEngine::getPageURL(): cannot navigate to '{$pageType}'");
    }
}

?>
