<?php

function CreateSFBInfobox($SFBData, $AppearanceSettings)
{
    if($SFBData['EndTime'] > time())
    {
        global $_Vars_FleetMissions;
        $_Lang = includeLang('sfbInfos', true);

        if($AppearanceSettings['standAlone'] === true)
        {
            $TPL = gettemplate('sfb_body_standalone');
            $_Lang['_Width'] = $AppearanceSettings['Width'];
            $_Lang['_MarginBottom'] = $AppearanceSettings['MarginBottom'];
        }
        else
        {
            $TPL = gettemplate('sfb_body_part');
            $_Lang['_Colspan'] = $AppearanceSettings['Colspan'] - 1;
        }
        $_Lang['_AdminLink'] = $AppearanceSettings['AdminLink'];
        $_Lang['_Icon'] = (empty($AppearanceSettings['Icon']) ? 'warningIcon' : $AppearanceSettings['Icon']);

        if($SFBData['BlockMissions'] == '0')
        {
            $_Lang['_MissionsInfo'] = $_Lang['sfb_Mission_All'];
            if($SFBData['DontBlockIfIdle'] == 1)
            {
                $_Lang['_MissionsInfo'] .= $_Lang['sfb_Mission_AggresiveDontBlockIdle'];
            }
        }
        else
        {
            $ExplodeMissions = explode(',', $SFBData['BlockMissions']);
            $CivilCount = 0;
            $AggresiveCount = 0;
            foreach($ExplodeMissions as $MissionID)
            {
                if(!in_array($MissionID, $_Vars_FleetMissions['all']))
                {
                    continue;
                }
                $SFBData['Missions'][] = $_Lang['sfb_Mission__'.$MissionID];
                if(in_array($MissionID, $_Vars_FleetMissions['civil']))
                {
                    $CivilCount += 1;
                }
                else
                {
                    $AggresiveCount += 1;
                }
            }
            if($CivilCount == count($_Vars_FleetMissions['civil']) AND $AggresiveCount == 0)
            {
                $_Lang['_MissionsInfo'] = $_Lang['sfb_Mission_Civil'];
            }
            else if($AggresiveCount == count($_Vars_FleetMissions['military']) AND $CivilCount == 0)
            {
                $_Lang['_MissionsInfo'] = $_Lang['sfb_Mission_Aggresive'];
            }
            else
            {
                $_Lang['_MissionsInfo'] = sprintf($_Lang['sfb_Mission_Other'], implode(', ', $SFBData['Missions']));
            }
            if($SFBData['DontBlockIfIdle'] == 1)
            {
                if($CivilCount > 0)
                {
                    $_Lang['_MissionsInfo'] .= $_Lang['sfb_Mission_AggresiveDontBlockIdle'];
                }
                else
                {
                    $_Lang['_MissionsInfo'] .= $_Lang['sfb_Mission_DontBlockIdle'];
                }
            }
        }
        if(!empty($SFBData['Reason']))
        {
            global $_GameConfig, $_EnginePath;
            if($_GameConfig['enable_bbcode'] == 1)
            {
                include_once($_EnginePath.'includes/functions/BBcodeFunction.php');
                $SFBData['Reason'] = trim(nl2br(bbcode(image(strip_tags(str_replace("'", '&#39;', $SFBData['Reason']), '<br><br/>')))));
            }
            else
            {
                $SFBData['Reason'] = trim(nl2br(strip_tags($SFBData['Reason'], '<br><br/>')));
            }
            $SFBData['Reason'] = "\"{$SFBData['Reason']}\"";
        }
        else
        {
            $SFBData['Reason'] = $_Lang['sfb_NoReason'];
        }

        $_Lang['_Text'] = sprintf($_Lang['sfb_GlobalText'], prettyDate('d m Y', $SFBData['EndTime'], 1), date('H:i:s', $SFBData['EndTime']), $_Lang['_MissionsInfo'], $SFBData['Reason']);

        return parsetemplate($TPL, $_Lang);
    }
}

?>
