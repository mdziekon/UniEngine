<?php

namespace UniEngine\Engine\Modules\Admin\Screens\MoonCreationView;

use UniEngine\Engine\Includes\Helpers\Common\Collections;

//  Arguments
//      - $props (Object)
//          - input (Object)
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_Lang;

    $input = $props['input'];

    includeLang('admin/addMoon');

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $tplBodyCache = [
        'pageBody' => $localTemplateLoader('page_body'),
    ];

    $componentTplData = &$_Lang;

    $componentTplData['PHP_Ins_PlanetID'] = $input['planetID'];
    $componentTplData['PHP_Ins_Name'] = $input['name'];
    $componentTplData['PHP_Ins_Diameter'] = $input['diameter'];

    // Handle user input
    $cmdResult = Utils\handleCommands($input);

    if ($cmdResult['isSuccess'] === null) {
        $componentTplData['PHP_InfoBox_Hide'] = 'display: none;';
    } else if ($cmdResult['isSuccess'] === false) {
        $errorMessage = null;

        if (Collections\get($cmdResult['error'], [ 'input', 'planetID', 'isEmpty' ])) {
            $errorMessage = $_Lang['AddMoon_Fail_BadID'];
        } else if (Collections\get($cmdResult['error'], [ 'input', 'planetID', 'isInvalid' ])) {
            $errorMessage = $_Lang['AddMoon_Fail_BadID'];
        } else if (Collections\get($cmdResult['error'], [ 'input', 'name', 'isInvalid' ])) {
            $errorMessage = $_Lang['AddMoon_Fail_NameBadSigns'];
        } else if (Collections\get($cmdResult['error'], [ 'planet', 'isInvalid' ])) {
            $errorMessage = $_Lang['AddMoon_Fail_NoPlanet'];
        } else if (Collections\get($cmdResult['error'], [ 'moon', 'alreadyExists' ])) {
            $errorMessage = $_Lang['AddMoon_Fail_MoonExists'];
        }

        $componentTplData['PHP_InfoBox_Text'] = $errorMessage;
        $componentTplData['PHP_InfoBox_Color'] = 'red';
    } else if ($cmdResult['isSuccess'] === true) {
        $componentTplData['PHP_InfoBox_Text'] = $_Lang['AddMoon_Success'];
        $componentTplData['PHP_InfoBox_Color'] = 'lime';
    }

    $componentHTML = parsetemplate($tplBodyCache['pageBody'], $componentTplData);

    return [
        'componentHTML' => $componentHTML,
    ];
}

?>
