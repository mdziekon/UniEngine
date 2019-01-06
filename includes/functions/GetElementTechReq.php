<?php

function GetElementTechReq($TheUser, $ThePlanet, $ElementID, $OnlyDivs = false)
{
    global $_Vars_Requirements, $_Vars_GameElements, $_Vars_ElementCategories, $_Lang, $_SkinPath;
    static $TPL;

    if(isset($_Vars_Requirements[$ElementID]))
    {
        foreach($_Vars_Requirements[$ElementID] as $RequiredElementID => $RequiredLevel)
        {
            if(in_array($RequiredElementID, $_Vars_ElementCategories['tech']))
            {
                $TempArray = array
                (
                    $RequiredLevel,
                    (isset($TheUser[$_Vars_GameElements[$RequiredElementID]]) ? $TheUser[$_Vars_GameElements[$RequiredElementID]] : 0)
                );

                if($RequiredLevel == 0 || (isset($TheUser[$_Vars_GameElements[$RequiredElementID]]) && $TheUser[$_Vars_GameElements[$RequiredElementID]] >= $RequiredLevel))
                {
                    $RequiredDone[$RequiredElementID] = $TempArray;
                }
                else
                {
                    $Required[$RequiredElementID] = $TempArray;
                }
            }
            else
            {
                $TempArray = array
                (
                    $RequiredLevel,
                    (isset($ThePlanet[$_Vars_GameElements[$RequiredElementID]]) ? $ThePlanet[$_Vars_GameElements[$RequiredElementID]] : 0)
                );

                if($RequiredLevel == 0 || (isset($ThePlanet[$_Vars_GameElements[$RequiredElementID]]) && $ThePlanet[$_Vars_GameElements[$RequiredElementID]] >= $RequiredLevel))
                {
                    $RequiredDone[$RequiredElementID] = $TempArray;
                }
                else
                {
                    $Required[$RequiredElementID] = $TempArray;
                }
            }
        }
    }

    if(!empty($Required))
    {
        if(!empty($RequiredDone))
        {
            foreach($RequiredDone as $Key => $Data)
            {
                $Required[$Key] = $Data;
            }
            asort($Required);
        }
        if(empty($TPL))
        {
            $TPL['main'] = gettemplate('_function_getelementtechreq_main');
            $TPL['divs'] = gettemplate('_function_getelementtechreq_divs');
        }

        if(!isset($_Lang['Insert_TechReqDivs']))
        {
            $_Lang['Insert_TechReqDivs'] = '';
        }
        foreach($Required as $ElementID => $Data)
        {
            if($Data[1] >= $Data[0])
            {
                $Color = 'lime';
            }
            else
            {
                $Color = 'red';
            }
            $_Lang['Insert_TechReqDivs'] .= parsetemplate($TPL['divs'], array('ID' => $ElementID, 'Name' => $_Lang['tech'][$ElementID], 'skinpath' => $_SkinPath, 'Color' => $Color, 'CLevel' => $Data[1], 'NLevel' => $Data[0]));
        }
        if($OnlyDivs)
        {
            $ToReturn = $_Lang['Insert_TechReqDivs'];
            $_Lang['Insert_TechReqDivs'] = '';
            return $ToReturn;
        }

        $Return = parsetemplate($TPL['main'], $_Lang);
        $_Lang['Insert_TechReqDivs'] = '';
    }
    return $Return;
}

?>
