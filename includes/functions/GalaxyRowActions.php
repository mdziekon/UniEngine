<?php

function GalaxyRowActions($GalaxyRowPlanet, $GalaxyRowPlayer, $Galaxy, $System, $Planet, $MyBuddies)
{
    global $_User, $_SkinPath, $CurrentMIP, $CurrentSystem, $CurrentGalaxy;
    static $TPL = false;
    if($TPL === false)
    {
        $TPL = gettemplate('galaxy_row_action');
    }

    if(isset($GalaxyRowPlanet['id']) && $GalaxyRowPlanet['id'] > 0 && $GalaxyRowPlayer['id'] != $_User['id'])
    {
        $HiddenOptions = $OptionsCount = 4;

        $Parse = array
        (
            'Hide_Spy'            => ' hide',
            'Hide_Msg'            => ' hide',
            'Hide_Buddy'        => ' hide',
            'Hide_Rocket'        => ' hide',
            'Galaxy'            => $Galaxy,
            'System'            => $System,
            'Planet'            => $Planet,
            'SkinPath'            => $_SkinPath,
            'UserID'            => $GalaxyRowPlayer['id'],
            'Current'            => $_User['current_planet'],
        );

        if($_User['settings_mis'] == 1)
        {
            if($CurrentMIP > 0)
            {
                if($GalaxyRowPlanet['galaxy'] == $CurrentGalaxy)
                {
                    $MiRange = GetMissileRange();
                    $SystemLimitMin = $CurrentSystem - $MiRange;
                    if($SystemLimitMin < 1)
                    {
                        $SystemLimitMin = 1;
                    }
                    $SystemLimitMax = $CurrentSystem + $MiRange;
                    if($System <= $SystemLimitMax AND $System >= $SystemLimitMin)
                    {
                        --$HiddenOptions;
                        $Parse['Hide_Rocket'] = '';
                    }
                }
            }
        }
        if($_User['settings_esp'] == 1)
        {
            --$HiddenOptions;
            $Parse['Hide_Spy'] = '';
        }
        if($_User['settings_wri'] == 1 AND $GalaxyRowPlanet['id_owner'] > 0)
        {
            --$HiddenOptions;
            $Parse['Hide_Msg'] = '';
        }
        if($_User['settings_bud'] == 1 AND $GalaxyRowPlanet['id_owner'] > 0)
        {
            if(!in_array($GalaxyRowPlayer['id'], $MyBuddies))
            {
                --$HiddenOptions;
                $Parse['Hide_Buddy'] = '';
            }
        }

        if($OptionsCount == $HiddenOptions)
        {
            $Result = '<th class="hiFnt">&nbsp;</th>';
        }
        else
        {
            $Result = parsetemplate($TPL, $Parse);
        }
    }
    else
    {
        $Result = '<th class="hiFnt">&nbsp;</th>';
    }

    return $Result;
}

?>
