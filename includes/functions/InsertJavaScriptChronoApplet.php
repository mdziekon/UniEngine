<?php

function InsertJavaScriptChronoApplet($Type, $Ref, $Value, $FixedTime = false, $Reverse = false, $CallbackFunction = false, $params = [])
{
    static $Created, $TPL_Instance;
    global $_EnginePath, $_Lang;

    if ($Created !== true) {
        $TPL_Script = gettemplate('_JSTimer_script');
        $TPL_Instance = gettemplate('_JSTimer_instance');

        $scriptHTML = parsetemplate(
            $TPL_Script,
            [
                'FilePath' => $_EnginePath,
                'ServerTimestamp' => time(),
                'LANG_daysFullJSFunction' => $_Lang['Chrono_PrettyTime']['chronoFormat']['daysFullJSFunction']
            ]
        );

        GlobalTemplate_AppendToAfterBody($scriptHTML);

        $Created = true;

        if ($Type === false) {
            return true;
        }
    }

    $isReverse = 'false';
    $reverseEndTimestamp = 'Infinity';

    if ($FixedTime) {
        $endTimestamp = $Value;
    } else {
        $endTimestamp = time() + $Value;
    }
    if ($Reverse !== false) {
        $isReverse = 'true';

        if (isset($params['reverseEndTimestamp'])) {
            $reverseEndTimestamp = $params['reverseEndTimestamp'];
        }
    }

    return parsetemplate(
        $TPL_Instance,
        [
            'Type' => $Type,
            'Ref' => $Ref,
            'endTimestamp' => $endTimestamp,
            'isReverse' => $isReverse,
            'reverseEndTimestamp' => $reverseEndTimestamp,
            'onEndCallback' => !empty($CallbackFunction) ? $CallbackFunction : "undefined"
        ]
    );
}

?>
