<?php

function ShowGalaxyMISelector($Galaxy, $System, $Planet, $AvailableMissiles, $HideSelector = false)
{
    global $_Lang, $_Vars_ElementCategories;

    $TPL = gettemplate('galaxy_selector_missiles');
    $Parse = array
    (
        'Galaxy'                => $Galaxy,
        'System'                => $System,
        'Planet'                => $Planet,
        'ThisPos'                => "{$Galaxy}:{$System}:{$Planet}",
        'MSelector_Title'        => $_Lang['MSelector_Title'],
        'MSelector_MCount'        => $_Lang['MSelector_MCount'],
        'MSelector_Target'        => $_Lang['MSelector_Target'],
        'MSelector_TargetAll'    => $_Lang['MSelector_TargetAll'],
        'MSelector_AvailableM'    => $_Lang['MSelector_AvailableM'],
        'MSelector_Submit'        => $_Lang['MSelector_Submit'],
        'MSelector_Close'        => $_Lang['MSelector_Close'],
        'Input_MissileCount'    => prettyNumber($AvailableMissiles),
        'Input_HideMissileForm'    => ($HideSelector === true ? 'class="hide"' : ''),
        'Input_Targets'            => ''
    );
    foreach($_Vars_ElementCategories['defense'] as $ElementID)
    {
        if(in_array($ElementID, $_Vars_ElementCategories['rockets']))
        {
            continue;
        }
        $Parse['Input_Targets'] .= "<option value=\"".(int)($ElementID - 400)."\">{$_Lang['tech'][$ElementID]}</option>";
    }

    return parsetemplate($TPL, $Parse);
}

?>
