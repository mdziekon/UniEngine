<?php

function CheckPlanetUsedFields(&$planet)
{
    global $_Vars_GameElements, $_Vars_ElementCategories;

    $TotalUsedFields = 0;
    foreach($_Vars_ElementCategories['buildOn'][$planet['planet_type']] as $ElementID)
    {
        $TotalUsedFields += $planet[$_Vars_GameElements[$ElementID]];
    }

    if($planet['field_current'] != $TotalUsedFields)
    {
        $planet['field_current'] = $TotalUsedFields;
        doquery("UPDATE {{table}} SET `field_current` = {$TotalUsedFields} WHERE `id`= {$planet['id']};", 'planets');
    }
}

?>
