<?php

function SpyTarget($TargetArray, $Mode, $TitleString, $UserInfo = array())
{
    global $_Lang, $_Vars_GameElements, $_Vars_ElementCategories;

    static $LangIncluded = false;
    if(!$LangIncluded)
    {
        includeLang('spyReport');
        $LangIncluded = true;
    }

    $ReturnArray = false;
    $SimData = array();

    $LookAtLoop = true;
    $AddToSim = false;
    if($Mode == 0)
    {
        // Show TargetInfo + Resources
        if($UserInfo['isEmptyReport'] === true)
        {
            if($UserInfo['uid'] > 0)
            {
                $ReturnData[] = '{SpyRes_Msg_empty}';
            }
            else
            {
                $ReturnData[] = '{SpyRes_Msg_ab_empty}';
            }
        }
        else
        {
            if($UserInfo['uid'] > 0)
            {
                $ReturnData[] = '{SpyRes_Msg}';
            }
            else
            {
                $ReturnData[] = '{SpyRes_Msg_ab}';
            }
        }
        $ReturnData[] = ($TargetArray['planet_type'] == 1 ? $_Lang['Spy_OnPlanet'] : $_Lang['Spy_OnMoon']);
        $ReturnData[] = $TargetArray['name'];
        $ReturnData[] = $TargetArray['galaxy'];
        $ReturnData[] = $TargetArray['system'];
        $ReturnData[] = $TargetArray['galaxy'];
        $ReturnData[] = $TargetArray['system'];
        $ReturnData[] = $TargetArray['planet'];
        if($UserInfo['uid'] > 0)
        {
            $ReturnData[] = $UserInfo['uid'];
            $ReturnData[] = $UserInfo['username'];
        }
        else
        {
            $ReturnData[] = $_Lang['Spy_Abandoned_'.$TargetArray['planet_type']];
        }
        if($UserInfo['isEmptyReport'] === true)
        {
            $ReturnData[] = $_Lang['Spy_Morale_EmptyReport'];
        }
        else
        {
            $ReturnData[] = prettyNumber($TargetArray['metal']);
            $ReturnData[] = prettyNumber($TargetArray['crystal']);
            $ReturnData[] = prettyNumber($TargetArray['deuterium']);
            $ReturnData[] = prettyNumber($TargetArray['energy_max']);
        }
        $ReturnArray[] = $ReturnData;
        $LookAtLoop = false;
    }
    else if($Mode == 1)
    {
        // Show Ships
        $ElementArrays[] = $_Vars_ElementCategories['fleet'];
        $AddToSim = true;
    }
    else if($Mode == 2)
    {
        $ElementArrays[] = $_Vars_ElementCategories['defense'];
        $AddToSim = true;
    }
    else if($Mode == 3)
    {
        $ElementArrays[] = $_Vars_ElementCategories['build'];
    }
    else if($Mode == 4)
    {
        $ElementArrays[] = $_Vars_ElementCategories['tech'];
        $AddToSim = array(109, 110, 111, 120, 121, 122, 125, 126, 199);
    }
    else if($Mode == 5)
    {
        // Show Morale Info
        if(MORALE_ENABLED)
        {
            Morale_ReCalculate($TargetArray, $UserInfo['SpyTime']);
            $TargetMorale = $TargetArray['morale_level'];
            $TargetMoraleStatus = $_Lang['SpyRes_Morale_Mood_Neutral'];
            if($TargetArray['morale_droptime'] > $UserInfo['SpyTime'])
            {
                if($TargetMorale > 0)
                {
                    $TargetMoraleStatus = $_Lang['SpyRes_Morale_Mood_Positive'];
                }
                else if($TargetMorale < 0)
                {
                    $TargetMoraleStatus = $_Lang['SpyRes_Morale_Mood_Negative'];
                }
            }

            $ReturnArray[] = array('{SpyRes_Mor}', $TargetMorale, $TargetMoraleStatus, prettyNumber($TargetArray['morale_points']));
        }
        else
        {
            $ReturnArray[] = array('{SpyRes_Emp}');
        }
        $LookAtLoop = false;
    }

    $Count = 0;
    if($LookAtLoop == true)
    {
        $ReturnArray[] = array('{SpyRes_Oth}', ((2 * SPY_REPORT_ROW) + (SPY_REPORT_ROW - 1)), $TitleString);

        $ArrayString = null;
        if(!empty($ElementArrays))
        {
            foreach($ElementArrays as $ThisArray)
            {
                $row = 0;
                foreach($ThisArray as $ElementID)
                {
                    if(isset($TargetArray[$_Vars_GameElements[$ElementID]]) && $TargetArray[$_Vars_GameElements[$ElementID]] > 0)
                    {
                        if($row == 0)
                        {
                            $ArrayString .= '<tr>';
                        }
                        $ArrayString .= '{s_tdl}{tech}['.$ElementID.']</td>{s_tdr}'.prettyNumber($TargetArray[$_Vars_GameElements[$ElementID]]).'</td>';
                        if($row < SPY_REPORT_ROW - 1)
                        {
                            $ArrayString .= '{s_tdn}';
                        }
                        $Count += $TargetArray[$_Vars_GameElements[$ElementID]];
                        $row += 1;
                        if($row == SPY_REPORT_ROW)
                        {
                            $ArrayString .= '</tr>';
                            $row = 0;
                        }
                        if($AddToSim === TRUE OR ((array)$AddToSim === $AddToSim AND in_array($ElementID, $AddToSim, true)))
                        {
                            $SimData[$ElementID] = $TargetArray[$_Vars_GameElements[$ElementID]];
                        }
                    }
                }
                while($row != 0)
                {
                    $ArrayString .= '{s_tdn}{s_tdn}';
                    $row += 1;
                    if($row == SPY_REPORT_ROW)
                    {
                        $ArrayString .= '</tr>';
                        $row= 0;
                    }
                }
            }
        }

        $ReturnArray[] = $ArrayString;
    }
    $ReturnArray[] = '</table>';

    $return['Array'] = $ReturnArray;
    $return['Count'] = $Count;
    $return['Sim'] = $SimData;
    return $return;
}

?>
