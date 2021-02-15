<?php

define('INSIDE', true);
define('UEC_INLOGIN', true);

$_DontShowMenus = true;

$_EnginePath = './';
include($_EnginePath . 'common.php');
include_once($_EnginePath . 'modules/session/_includes.php');

use UniEngine\Engine\Modules\Session;

if (!LOGINPAGE_ALLOW_LOGINPHP) {
    Session\Utils\Redirects\permaRedirectToMainDomain();

    die();
}

$pageView = Session\Screens\LoginView\render([
    'currentTimestamp' => time(),
]);

includeLang('login');

display($pageView['componentHTML'], $_Lang['Page_Title']);

?>
