<?php

function GalaxyRowAlly($GalaxyRowUser, $MyAllyPacts)
{
    global $_Lang, $_User;

    static $TPL = false;
    if($TPL === false)
    {
        $TPL = gettemplate('galaxy_row_ally');
    }

    if(isset($GalaxyRowUser['ally_id']) && $GalaxyRowUser['ally_id'] > 0)
    {
        if(!empty($GalaxyRowUser['ally_web']) AND ($GalaxyRowUser['ally_web_reveal'] == 1 OR $GalaxyRowUser['ally_id'] == $_User['ally_id']))
        {
            $AllyWeb = $GalaxyRowUser['ally_web'];
        }

        $Parse = array
        (
            'AllyID'                => $GalaxyRowUser['ally_id'],
            'AllyClass'                => ($GalaxyRowUser['ally_id'] == $_User['ally_id'] ? 'lime' : (isset($MyAllyPacts[$GalaxyRowUser['ally_id']]) && $MyAllyPacts[$GalaxyRowUser['ally_id']] > 0 ? 'skyblue' : '')),
            'Lang_Ally'                => $_Lang['Alliance'],
            'AllyName'                => $GalaxyRowUser['ally_name'],
            'Lang_MemberCount'        => $_Lang['gl_allymembers'],
            'AllyMembers'            => prettyNumber($GalaxyRowUser['ally_members']),
            'Lang_Internal'            => $_Lang['gl_ally_internal'],
            'AllyPosition'            => (string) ($GalaxyRowUser['ally_total_rank'] + 0),
            'AllyPositionPretty'    => (string) ($GalaxyRowUser['ally_total_rank'] + 0),
            'Lang_Stats'            => $_Lang['gl_stats'],
            'AllyWeb'                => (isset($AllyWeb) ? $AllyWeb : ''),
            'Lang_Web'                => $_Lang['gl_ally_web'],
            'Hide_AllyWeb'            => ($GalaxyRowUser['ally_web_reveal'] != 1 ? ' class=hide' : ''),
            'AllyTag'                => $GalaxyRowUser['ally_tag'],
        );

        $Result = parsetemplate($TPL, $Parse);
    }
    else
    {
        $Result = '<th class="hiFnt">&nbsp;</th>';
    }

    return $Result;
}

?>
