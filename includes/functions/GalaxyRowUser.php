<?php

function GalaxyRowUser($GalaxyRowPlanet, $GalaxyRowUser, $MyBuddies, $SFBStatus)
{
    global $_Lang, $_User, $_GameConfig, $Time;
    static $TPL = false, $TPL_MoraleBox = false;
    if($TPL === false)
    {
        if($_User['settings_Galaxy_ShowUserAvatars'] == 1)
        {
            $TPL = gettemplate('galaxy_row_user');
        }
        else
        {
            $TPL = gettemplate('galaxy_row_user_noav');
        }
        if(MORALE_ENABLED)
        {
            $TPL_MoraleBox = gettemplate('galaxy_row_user_morale');
        }
    }

    if(isset($GalaxyRowPlanet['id_owner']) && $GalaxyRowPlanet['id_owner'] > 0)
    {
        $NoobProt = $_GameConfig['noobprotection'];
        $NoobTime = $_GameConfig['noobprotectiontime'];
        $NoobMulti = $_GameConfig['noobprotectionmulti'];
        $noNoobProtect = $_GameConfig['no_noob_protect'];
        $noIdleProtect = $_GameConfig['no_idle_protect'];
        $Protections['idleTime'] = $_GameConfig['no_idle_protect'] * TIME_DAY;

        $UserPoints['total_points'] = $_User['total_points'];
        $UserPoints['total_rank'] = $_User['total_rank'];
        $User2Points['total_points'] = $GalaxyRowUser['total_points'];
        $User2Points['total_rank'] = $GalaxyRowUser['total_rank'];

        if($GalaxyRowUser['is_banned'] == 1 OR isOnVacation($GalaxyRowUser))
        {
            if(isOnVacation($GalaxyRowUser))
            {
                $Status[] = array('class' => 'vacation', 'sign' => $_Lang['vacation_shortcut']);
                $NameClasses[] = array('class' => 'vacation', 'importance' => 90);
            }
            if($GalaxyRowUser['is_banned'] == 1)
            {
                $Status[] = array('class' => 'banned', 'sign' => $_Lang['banned_shortcut']);
                $NameClasses[] = array('class' => 'red', 'importance' => 100);
            }
        }
        else if(!empty($GalaxyRowUser['activation_code']))
        {
            $Status[] = array('class' => 'nonactivated', 'sign' => $_Lang['User_NonActivated']);
            $NameClasses[] = array('class' => 'nonactivated', 'importance' => 50);
        }
        else if($GalaxyRowUser['first_login'] == 0 OR $GalaxyRowUser['NoobProtection_EndTime'] > $Time)
        {
            $Status[] = array('class' => 'newplayer', 'sign' => '<b>'.$_Lang['new_player_shortcut'].'</b>');
            $NameClasses[] = array('class' => 'newplayer', 'importance' => 60);
        }
        else if($GalaxyRowUser['onlinetime'] < ($Time - (TIME_DAY * 7)))
        {
            $Status[] = array('class' => 'inactive', 'sign' => $_Lang['inactif_7_shortcut']);
            $NameClasses[] = array('class' => 'inactive', 'importance' => 40);
            if($GalaxyRowUser['onlinetime'] < ($Time - (TIME_DAY * 28)))
            {
                $Status[] = array('class' => 'longinactive', 'sign' => $_Lang['inactif_28_shortcut']);
                $NameClasses[] = array('class' => 'longinactive', 'importance' => 41);
            }
        }
        else if($NoobProt)
        {
            if($GalaxyRowUser['id'] != $_User['id'])
            {
                if($GalaxyRowUser['onlinetime'] >= ($Time - (TIME_DAY * $noIdleProtect)))
                {
                    if($User2Points['total_points'] < ($NoobTime * 1000))
                    {
                        $Status[] = array('class' => 'noob', 'sign' => $_Lang['weak_player_shortcut']);
                        $NameClasses[] = array('class' => 'noob', 'importance' => 50);
                    }
                    else
                    {
                        if($UserPoints['total_points'] < ($noNoobProtect * 1000) OR $User2Points['total_points'] < ($noNoobProtect * 1000))
                        {
                            if(($UserPoints['total_points'] > ($User2Points['total_points'] * $NoobMulti)))
                            {
                                $Status[] = array('class' => 'noob', 'sign' => $_Lang['weak_player_shortcut']);
                                $NameClasses[] = array('class' => 'noob', 'importance' => 50);
                            }
                            elseif(($UserPoints['total_points'] * $NoobMulti) < $User2Points['total_points'])
                            {
                                $Status[] = array('class' => 'strong', 'sign' => $_Lang['strong_player_shortcut']);
                                $NameClasses[] = array('class' => 'strong', 'importance' => 50);
                            }
                        }
                    }
                }
            }
        }
        // Smart Fleet Blockade indicator
        if($SFBStatus['BlockMissions'] == '0')
        {
            $AllMissionsBlocked = true;
        }

        if($SFBStatus['ID'] > 0 AND
        (
            ($AllMissionsBlocked !== true AND $GalaxyRowUser['onlinetime'] > ($Time - $Protections['idleTime']) AND $GalaxyRowUser['onlinetime'] < $SFBStatus['StartTime'])
            OR
            ($AllMissionsBlocked === true AND $GalaxyRowUser['onlinetime'] > ($Time - $Protections['idleTime']) AND $GalaxyRowUser['onlinetime'] < $SFBStatus['EndTime'])
        ))
        {
            $Status[] = array('class' => 'orange', 'sign' => $_Lang['Fleet_Blockade_Protected']);
        }
        if($GalaxyRowUser['avatar'] != '')
        {
            $Avatar = "<img src=\"{$GalaxyRowUser['avatar']}\" class=\"imgS\"/>";
        }
        else
        {
            $Avatar = '&nbsp;';
        }
        if(CheckAuth('supportadmin', AUTHCHECK_NORMAL, $GalaxyRowUser))
        {
            $Status[] = array('class' => 'lime', 'sign' => "<blink>{$_Lang['User_isAdmin']}</blink>");
        }

        if($User2Points['total_rank'] > 0)
        {
            $UserStatPosition = $User2Points['total_rank'];
            $UserStatPositionTH = prettyNumber($User2Points['total_rank']);
        }
        else
        {
            $UserStatPosition = '0';
            $UserStatPositionTH = '&nbsp;';
        }

        if(!empty($NameClasses))
        {
            foreach($NameClasses as $Data)
            {
                $NameClassesImportance[] = $Data['importance'];
            }
            array_multisort($NameClasses, SORT_DESC, $NameClassesImportance);
        }
        if(!empty($Status))
        {
            foreach($Status as $Data)
            {
                $Statuses[] = "<span class=\"{$Data['class']}\">{$Data['sign']}</span>";
            }
        }

        if(MORALE_ENABLED AND $GalaxyRowUser['id'] != $_User['id'])
        {
            $Parse = array
            (
                'Lang_MoralePoints'            => $_Lang['gl_moralepoints'],
                'Lang_MoralePoints_Units'    => $_Lang['gl_moralepoints_units'],
                'MoralePoints'                => prettyNumber($GalaxyRowUser['morale_points'])
            );

            $MoraleBox = parsetemplate($TPL_MoraleBox, $Parse);
        }

        $Parse = array
        (
            'Avatar'            => $Avatar,
            'UserID'            => $GalaxyRowUser['id'],
            'Lang_User'            => $_Lang['gl_username'],
            'Username'            => $GalaxyRowUser['username'],
            'NameClass'            => (isset($NameClasses) ? $NameClasses[0]['class'] : ''),
            'AddOldUsername'    => ($GalaxyRowUser['old_username_expire'] > $Time ? ' <acronym class=point title=\"'.$_Lang['Old_username_is'].': '.$GalaxyRowUser['old_username'].'\">(?)</acronym>' : ''),
            'Lang_Profile'        => $_Lang['gl_showprofile'],
            'Lang_Message'        => $_Lang['gl_sendmess'],
            'Lang_Buddy'        => $_Lang['gl_buddyreq'],
            'Lang_AllyInvite'    => $_Lang['gl_allyinvite'],
            'StatStart'            => $UserStatPosition,
            'Lang_Stats'        => $_Lang['gl_stats'],
            'Position'            => prettyNumber($UserStatPosition),
            'PositionTH'        => $UserStatPositionTH,
            'Add_hiFntClass'    => ($UserStatPositionTH == '&nbsp;' ? 'class="hiFnt"' : ''),
            'Statuses'            => (!empty($Statuses) ? '('.implode(' ', $Statuses).')' : ''),
            'Hide_Message'        => ' class=hide',
            'Hide_Buddy'        => ' class=hide',
            'Hide_InviteToAlly' => ' class=hide',
            'Insert_MoraleBox'    => (isset($MoraleBox) ? $MoraleBox : '')
        );
        if($GalaxyRowUser['id'] != $_User['id'])
        {
            $Parse['Hide_Message'] = '';
            if(!in_array($GalaxyRowUser['id'], $MyBuddies))
            {
                $Parse['Hide_Buddy'] = '';
            }
            if($_User['ally_id'] > 0 AND $GalaxyRowUser['ally_id'] <= 0)
            {
                $Parse['Hide_InviteToAlly'] = '';
            }
        }

        $Result = parsetemplate($TPL, $Parse);
    }
    else
    {
        if($_User['settings_Galaxy_ShowUserAvatars'] == 1)
        {
            $Result = '<th class="hiFnt">&nbsp;</th><th class="hiFnt">&nbsp;</th><th class="hiFnt">&nbsp;</th>';
        }
        else
        {
            $Result = '<th class="hiFnt" colspan="2">&nbsp;</th><th class="hiFnt">&nbsp;</th>';
        }
    }

    return $Result;
}

?>
