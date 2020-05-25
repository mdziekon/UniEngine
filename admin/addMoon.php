<?php

define('INSIDE', true);
define('IN_ADMIN', true);

$_SetAccessLogPreFilename = 'admin/';
$_SetAccessLogPath = '../';
$_EnginePath = './../';

include($_EnginePath . 'common.php');

if (!CheckAuth('supportadmin')) {
    AdminMessage($_Lang['sys_noalloaw'], $_Lang['sys_noaccess']);
}

include($_EnginePath . 'modules/admin/_includes.php');

use UniEngine\Engine\Modules\Admin\Screens\MoonCreationView;

$pageView = MoonCreationView\render([
    'input' => $_POST,
]);

display($pageView['componentHTML'], $_Lang['AddMoon_Title'], false, true);

?>
