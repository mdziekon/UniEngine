<?php

function IsTechnologieAccessible($TheUser, $ThePlanet, $ElementID)
{
    global $_Vars_Requirements, $_Vars_GameElements, $_Vars_ElementCategories;

    if(isset($_Vars_Requirements[$ElementID]))
    {
        foreach($_Vars_Requirements[$ElementID] as $RequiredElementID => $RequiredLevel)
        {
            if(in_array($RequiredElementID, $_Vars_ElementCategories['tech']))
            {
                if(!($RequiredLevel == 0 || (isset($TheUser[$_Vars_GameElements[$RequiredElementID]]) && $TheUser[$_Vars_GameElements[$RequiredElementID]] >= $RequiredLevel)))
                {
                    return false;
                }
            }
            else
            {
                if(!($RequiredLevel == 0 || (isset($ThePlanet[$_Vars_GameElements[$RequiredElementID]]) && $ThePlanet[$_Vars_GameElements[$RequiredElementID]] >= $RequiredLevel)))
                {
                    return false;
                }
            }
        }
    }

    return true;
}

?>
