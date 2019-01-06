<?php

function InsertJavaScriptChronoApplet($Type, $Ref, $Value, $FixedTime = false, $Reverse = false, $CallbackFunction = false)
{
    static $Created, $TPL_Instance;
    global $_EnginePath;

    if($Created !== true)
    {
        global $_Lang;
        GlobalTemplate_AppendToAfterBody(parsetemplate(gettemplate('_JSTimer_script'), array('FilePath' => $_EnginePath, 'ServerTimestamp' => time(), 'Lang_day1' => $_Lang['Chrono_Day1'], 'Lang_dayM' => $_Lang['Chrono_DayM'])));
        $TPL_Instance = gettemplate('_JSTimer_instance');
        $Created = true;
        if($Type === false)
        {
            return true;
        }
    }

    $ReverseChrono = '';
    $InsertCallback = '';

    if($FixedTime)
    {
        $InsertTime = $Value;
    }
    else
    {
        $InsertTime = time() + $Value;
    }
    if($Reverse !== false)
    {
        $ReverseChrono = ', true';
    }

    if($CallbackFunction !== false)
    {
        if($Reverse === false)
        {
            $ReverseChrono = ', false';
        }
        $InsertCallback = ', '.$CallbackFunction;
    }

    return parsetemplate($TPL_Instance, array
    (
        'Type' => $Type, 'Ref' => $Ref, 'InsertTime' => $InsertTime, 'ReverseChrono' => $ReverseChrono, 'InsertCallback' => $InsertCallback,
    ));
}

?>
