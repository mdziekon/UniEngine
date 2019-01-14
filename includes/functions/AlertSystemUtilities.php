<?php

function AlertUtils_IPIntersect($FirstUserID, $SecondUserID, $ExcludeRules = array())
{
    $Query_SelectIPs = '';
    $Query_SelectIPs .= "SELECT `User_ID`, `IP_ID`, (`Count` - `FailCount`) AS `SumCount`, `LastTime` ";
    $Query_SelectIPs .= "FROM {{table}} WHERE ";
    $Query_SelectIPs .= "`User_ID` IN ({$FirstUserID}, {$SecondUserID}) AND ";
    $Query_SelectIPs .= "`Count` > `FailCount`;";
    $Result_SelectIPs = doquery($Query_SelectIPs, 'user_enterlog');

    if($Result_SelectIPs->num_rows == 0)
    {
        return false;
    }

    $ForIntersection = array();
    $IPLogData = array();
    while($FetchData = $Result_SelectIPs->fetch_assoc())
    {
        if(!isset($IPLogData[$FetchData['User_ID']][$FetchData['IP_ID']]['Count']))
        {
            $IPLogData[$FetchData['User_ID']][$FetchData['IP_ID']]['Count'] = 0;
        }

        $ForIntersection[$FetchData['User_ID']][$FetchData['IP_ID']] = $FetchData['IP_ID'];
        $IPLogData[$FetchData['User_ID']][$FetchData['IP_ID']]['Count'] += $FetchData['SumCount'];
        if(!isset($IPLogData[$FetchData['User_ID']][$FetchData['IP_ID']]['LastTime']) || $IPLogData[$FetchData['User_ID']][$FetchData['IP_ID']]['LastTime'] < $FetchData['LastTime'])
        {
            $IPLogData[$FetchData['User_ID']][$FetchData['IP_ID']]['LastTime'] = $FetchData['LastTime'];
        }
    }

    if(empty($ForIntersection[$FirstUserID]) OR empty($ForIntersection[$SecondUserID]))
    {
        return false;
    }

    $Intersection = array_intersect($ForIntersection[$FirstUserID], $ForIntersection[$SecondUserID]);
    if(empty($Intersection))
    {
        return false;
    }

    if(!empty($ExcludeRules))
    {
        if($ExcludeRules['LastTimeDiff'] > 0)
        {
            foreach($Intersection as $IPID)
            {
                $LastTimeDiff = $IPLogData[$FirstUserID][$IPID]['LastTime'] - $IPLogData[$SecondUserID][$IPID]['LastTime'];
                if($LastTimeDiff < 0)
                {
                    $LastTimeDiff *= -1;
                }
                if($LastTimeDiff >= $ExcludeRules['LastTimeDiff'])
                {
                    unset($Intersection[$IPID]);
                    unset($IPLogData[$FirstUserID][$IPID]);
                    unset($IPLogData[$SecondUserID][$IPID]);
                }
            }
        }
        if($ExcludeRules['ThisTimeDiff'] > 0)
        {
            $ThisTimeStamp = ($ExcludeRules['ThisTimeStamp'] > 0 ? $ExcludeRules['ThisTimeStamp'] : (time() - SERVER_MAINOPEN_TSTAMP));
            foreach($Intersection as $IPID)
            {
                $ThisTimeMax = ($IPLogData[$FirstUserID][$IPID]['LastTime'] > $IPLogData[$SecondUserID][$IPID]['LastTime'] ? $IPLogData[$FirstUserID][$IPID]['LastTime'] : $IPLogData[$SecondUserID][$IPID]['LastTime']);
                if(($ThisTimeStamp - $ThisTimeMax) >= $ExcludeRules['ThisTimeDiff'])
                {
                    unset($Intersection[$IPID]);
                    unset($IPLogData[$FirstUserID][$IPID]);
                    unset($IPLogData[$SecondUserID][$IPID]);
                }
            }
        }

        if(empty($Intersection))
        {
            return false;
        }
    }

    return array('Intersect' => $Intersection, 'IPLogData' => $IPLogData);
}

function AlertUtils_CheckFilters($FiltersData, $CacheSettings = array())
{
    static $_FiltersCache = array();

    if(isset($CacheSettings['DontLoad_OnlyIfCacheEmpty']) && $CacheSettings['DontLoad_OnlyIfCacheEmpty'] === true)
    {
        if(empty($_FiltersCache))
        {
            $CacheSettings['DontLoad'] = false;
        }
    }

    if(!isset($CacheSettings['DontLoad']) || $CacheSettings['DontLoad'] !== true)
    {
        foreach($FiltersData as $Type => $Value)
        {
            if($Type == 'users')
            {
                foreach($Value as $Data)
                {
                    $FilterSearch[] = '{USER_'.$Data.'}';
                }
            }
            else if($Type == 'ips')
            {
                foreach($Value as $Data)
                {
                    $FilterSearch[] = '{IP_'.$Data.'}';
                }
            }
            else if($Type == 'place')
            {
                $FilterSearch[] = '{PLACE_'.$Value.'}';
            }
            else if($Type == 'alertsender')
            {
                $FilterSearch[] = '{ALERTSENDER_'.$Value.'}';
            }
        }

        $Query_GetFilters = '';
        $Query_GetFilters .= "SELECT `ID`, `ActionType`, `EvalCode` FROM {{table}} WHERE `Enabled` = 1 AND ";
        $Query_GetFilters .= "(`SearchData` = ''";
        if(!empty($FilterSearch))
        {
            foreach($FilterSearch as $Key => $Data)
            {
                $FilterSearch[$Key] = "`SearchData` LIKE '%{$Data}%'";
            }
            $Query_GetFilters .= " OR ".implode(' OR ', $FilterSearch);
        }
        $Query_GetFilters .= ");";
        $Result_GetFilters = doquery($Query_GetFilters, 'system_alerts_filters');

        if($Result_GetFilters->num_rows > 0)
        {
            while($Filter = $Result_GetFilters->fetch_assoc())
            {
                $Filters[] = $Filter;
                if($CacheSettings['Save'] === true)
                {
                    $_FiltersCache[$Filter['ID']] = array
                    (
                        'ID' => $Filter['ID'],
                        'EvalCode' => $Filter['EvalCode'],
                        'ActionType' => $Filter['ActionType']
                    );
                }
            }
        }
    }
    if(isset($CacheSettings['UseCache']) && $CacheSettings['UseCache'] === true)
    {
        if(!empty($_FiltersCache))
        {
            foreach($_FiltersCache as $Filter)
            {
                $Filters[] = $Filter;
            }
        }
    }

    $Return = array('FilterUsed' => false, 'SendAlert' => true, 'ShowAlert' => true);

    if(!empty($Filters))
    {
        foreach($Filters as $Filter)
        {
            if(!empty($Filter['EvalCode']))
            {
                $EvalReturn = false;
                $EvalCode = 'if('.stripcslashes($Filter['EvalCode']).'){ $EvalReturn = true; };';
                eval($EvalCode);

                if($EvalReturn === true)
                {
                    $Return['FilterUsed'] = true;
                    if($Filter['ActionType'] == 1)
                    {
                        $Return['SendAlert'] = false;
                    }
                    elseif($Filter['ActionType'] == 2)
                    {
                        $Return['SendAlert'] = false;
                        $Return['ShowAlert'] = false;
                    }
                    doquery("UPDATE {{table}} SET `UseCount` = `UseCount` + 1 WHERE `ID` = {$Filter['ID']};", 'system_alerts_filters');
                    break;
                }
            }
        }
    }

    return $Return;
}

?>
