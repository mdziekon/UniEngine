<?php

function Filter_Users($String = '', $Type = '', $Flags = array())
{
    // Sanitization
    if(!in_array($Type, array('', 'uid', 'uname', 'aid', 'astring', 'aname', 'atag', 'ipid', 'ipstring', 'umail')))
    {
        // Bad search type
        return false;
    }
    $String = explode('|', $String);
    foreach($String as $Value)
    {
        $Value = trim($Value);
        if(!empty($Value))
        {
            $Search[] = $Value;
        }
    }
    if(!empty($Type) && empty($Search))
    {
        // Type is given, but Search is empty
        return false;
    }

    // Type recognition
    if(!empty($Search))
    {
        // We need to make WHERE statements
        if($Type === 'uid')
        {
            foreach($Search as $Value)
            {
                $Value = intval($Value);
                if($Value > 0)
                {
                    $Query['whereData'][] = $Value;
                }
            }
            $Query['whereJoin'] = function($Array){ return implode(', ', $Array); };
            $Query['querySelect'] = 'id';
            $Query['querySelectArray'][] = 'id';
            $Query['queryString'] = "FROM {{table}} WHERE `id` IN ({whereJoin}) {whereAddition}";
            $Query['queryTable'] = 'users';
        }
        else if($Type === 'uname')
        {
            $ThisRegexp = ($Flags['strict'] === true ? REGEXP_USERNAME_ABSOLUTE : REGEXP_USERNAME);
            foreach($Search as $Value)
            {
                if(preg_match($ThisRegexp, $Value))
                {
                    $Query['whereData'][] = $Value;
                }
            }
            if($Flags['strict'] === true)
            {
                $Query['whereJoin'] = function($Array)
                {
                    foreach($Array as $Value)
                    {
                        $Return[] = "`username` = '{$Value}'";
                    }
                    return implode(' OR ', $Return);
                };
            }
            else
            {
                $Query['whereJoin'] = function($Array)
                {
                    foreach($Array as $Value)
                    {
                        $Value = preg_replace(REGEXP_SANITIZELIKE_SEARCH, REGEXP_SANITIZELIKE_REPLACE, $Value);
                        $Return[] = "`username` LIKE '%{$Value}%'";
                    }
                    return implode(' OR ', $Return);
                };
            }
            $Query['querySelect'] = 'id';
            $Query['querySelectArray'][] = 'id';
            $Query['queryString'] = "FROM {{table}} WHERE ({whereJoin}) {whereAddition}";
            $Query['queryTable'] = 'users';
        }
        else if($Type === 'aid')
        {
            foreach($Search as $Value)
            {
                $Value = intval($Value);
                if($Value > 0)
                {
                    $Query['whereData'][] = $Value;
                }
            }
            $Query['whereJoin'] = function($Array){ return implode(', ', $Array); };
            $Query['querySelect'] = 'id';
            $Query['querySelectArray'][] = 'id';
            if($Flags['allyRequest'] === true)
            {
                $ThisWhere = "(`ally_request` IN ({whereJoin}))";
            }
            else if($Flags['allyRequest'] === false)
            {
                $ThisWhere = "(`ally_id` IN ({whereJoin}))";
            }
            else
            {
                $ThisWhere = "(`ally_id` IN ({whereJoin}) OR `ally_request` IN ({whereJoin}))";
            }

            $Query['queryString'] = "FROM {{table}} WHERE {$ThisWhere} {whereAddition}";
            $Query['queryTable'] = 'users';
        }
        else if($Type === 'astring')
        {
            foreach($Search as $Value)
            {
                if(preg_match(REGEXP_ALLYNAMEANDTAG, $Value))
                {
                    $Query['whereData'][] = $Value;
                }
            }
            $Query['whereJoin'] = function($Array)
            {
                foreach($Array as $Value)
                {
                    $Value = preg_replace(REGEXP_SANITIZELIKE_SEARCH, REGEXP_SANITIZELIKE_REPLACE, $Value);
                    $Return[] = "`ally`.`ally_name` LIKE '%{$Value}%' OR `ally`.`ally_tag` LIKE '%{$Value}%'";
                }
                return implode(' OR ', $Return);
            };
            $Query['querySelect'] = '`users`.`id`';
            $Query['querySelectArray'][] = 'id';
            if($Flags['allyRequest'] === true)
            {
                $ThisJoin = "JOIN `{{prefix}}users` AS `users` ON `ally`.`id` = `users`.`ally_request`";
            }
            else if($Flags['allyRequest'] === false)
            {
                $ThisJoin = "JOIN `{{prefix}}users` AS `users` ON `ally`.`id` = `users`.`ally_id`";
            }
            else
            {
                $Query['querySelect'] = '`users1`.`id` AS `id1`, `users2`.`id` AS `id2`';
                $Query['querySelectArray'] = array('id1', 'id2');
                $ThisJoin  = "LEFT JOIN `{{prefix}}users` AS `users1` ON `ally`.`id` = `users1`.`ally_id` ";
                $ThisJoin .= "LEFT JOIN `{{prefix}}users` AS `users2` ON `ally`.`id` = `users2`.`ally_request` ";
            }
            $Query['queryString'] = "FROM {{table}} AS `ally` {$ThisJoin} WHERE ({whereJoin}) {whereAddition}";
            $Query['queryTable'] = 'alliance';
            $Query['whereAdditionAlias'] = 'users';
        }
        else if($Type === 'aname')
        {
            $ThisRegexp = ($Flags['strict'] === true ? REGEXP_ALLYNAME_ABSOLUTE : REGEXP_ALLYNAMEANDTAG);
            foreach($Search as $Value)
            {
                if(preg_match($ThisRegexp, $Value))
                {
                    $Query['whereData'][] = $Value;
                }
            }
            if($Flags['strict'] === true)
            {
                $Query['whereJoin'] = function($Array)
                {
                    foreach($Array as $Value)
                    {
                        $Return[] = "`ally`.`ally_name` = '{$Value}'";
                    }
                    return implode(' OR ', $Return);
                };
            }
            else
            {
                $Query['whereJoin'] = function($Array)
                {
                    foreach($Array as $Value)
                    {
                        $Value = preg_replace(REGEXP_SANITIZELIKE_SEARCH, REGEXP_SANITIZELIKE_REPLACE, $Value);
                        $Return[] = "`ally`.`ally_name` LIKE '%{$Value}%'";
                    }
                    return implode(' OR ', $Return);
                };
            }
            $Query['querySelect'] = '`users`.`id`';
            $Query['querySelectArray'][] = 'id';
            if($Flags['allyRequest'] === true)
            {
                $ThisJoin = "JOIN `{{prefix}}users` AS `users` ON `ally`.`id` = `users`.`ally_request`";
            }
            else if($Flags['allyRequest'] === false)
            {
                $ThisJoin = "JOIN `{{prefix}}users` AS `users` ON `ally`.`id` = `users`.`ally_id`";
            }
            else
            {
                $Query['querySelect'] = '`users1`.`id` AS `id1`, `users2`.`id` AS `id2`';
                $Query['querySelectArray'] = array('id1', 'id2');
                $ThisJoin  = "LEFT JOIN `{{prefix}}users` AS `users1` ON `ally`.`id` = `users1`.`ally_id` ";
                $ThisJoin .= "LEFT JOIN `{{prefix}}users` AS `users2` ON `ally`.`id` = `users2`.`ally_request` ";
            }
            $Query['queryString'] = "FROM {{table}} AS `ally` {$ThisJoin} WHERE ({whereJoin}) {whereAddition}";
            $Query['queryTable'] = 'alliance';
            $Query['whereAdditionAlias'] = 'users';
        }
        else if($Type === 'atag')
        {
            $ThisRegexp = ($Flags['strict'] === true ? REGEXP_ALLYTAG_ABSOLUTE : REGEXP_ALLYTAG);
            foreach($Search as $Value)
            {
                if(preg_match($ThisRegexp, $Value))
                {
                    $Query['whereData'][] = $Value;
                }
            }
            if($Flags['strict'] === true)
            {
                $Query['whereJoin'] = function($Array)
                {
                    foreach($Array as $Value)
                    {
                        $Return[] = "`ally`.`ally_tag` = '{$Value}'";
                    }
                    return implode(' OR ', $Return);
                };
            }
            else
            {
                $Query['whereJoin'] = function($Array)
                {
                    foreach($Array as $Value)
                    {
                        $Value = preg_replace(REGEXP_SANITIZELIKE_SEARCH, REGEXP_SANITIZELIKE_REPLACE, $Value);
                        $Return[] = "`ally`.`ally_tag` LIKE '%{$Value}%'";
                    }
                    return implode(' OR ', $Return);
                };
            }
            $Query['querySelect'] = '`users`.`id`';
            $Query['querySelectArray'][] = 'id';
            if($Flags['allyRequest'] === true)
            {
                $ThisJoin = "JOIN `{{prefix}}users` AS `users` ON `ally`.`id` = `users`.`ally_request`";
            }
            else if($Flags['allyRequest'] === false)
            {
                $ThisJoin = "JOIN `{{prefix}}users` AS `users` ON `ally`.`id` = `users`.`ally_id`";
            }
            else
            {
                $Query['querySelect'] = '`users1`.`id` AS `id1`, `users2`.`id` AS `id2`';
                $Query['querySelectArray'] = array('id1', 'id2');
                $ThisJoin  = "LEFT JOIN `{{prefix}}users` AS `users1` ON `ally`.`id` = `users1`.`ally_id` ";
                $ThisJoin .= "LEFT JOIN `{{prefix}}users` AS `users2` ON `ally`.`id` = `users2`.`ally_request` ";
            }
            $Query['queryString'] = "FROM {{table}} AS `ally` {$ThisJoin} WHERE ({whereJoin}) {whereAddition}";
            $Query['queryTable'] = 'alliance';
            $Query['whereAdditionAlias'] = 'users';
        }
        else if($Type === 'ipid')
        {
            foreach($Search as $Value)
            {
                $Value = round($Value);
                if($Value > 0)
                {
                    $Query['whereData'][] = $Value;
                }
            }
            $Query['whereJoin'] = function($Array){ return implode(', ', $Array); };
            $Query['querySelect'] = '`users`.`id`';
            $Query['querySelectArray'][] = 'id';
            $Query['queryString'] = "FROM {{table}} AS `enterlog` JOIN `{{prefix}}users` AS `users` ON `users`.`id` = `enterlog`.`User_ID` WHERE `enterlog`.`IP_ID` IN ({whereJoin}) {whereAddition}";
            $Query['queryTable'] = 'user_enterlog';
            $Query['whereAdditionAlias'] = 'users';
        }
        else if($Type === 'ipstring')
        {
            foreach($Search as $Value)
            {
                if(preg_match(REGEXP_IP, $Value))
                {
                    $Query['whereData'][] = $Value;
                }
            }
            if($Flags['utableOnly'] === true)
            {
                if($Flags['strict'] === true)
                {
                    $Query['whereJoin'] = function($Array)
                    {
                        foreach($Array as $Value)
                        {
                            $Return[] = "`user_lastip` = '{$Value}' OR `ip_at_reg` = '{$Value}'";
                        }
                        return implode(' OR ', $Return);
                    };
                }
                else
                {
                    $Query['whereJoin'] = function($Array)
                    {
                        foreach($Array as $Value)
                        {
                            $Value = preg_replace(REGEXP_SANITIZELIKE_SEARCH, REGEXP_SANITIZELIKE_REPLACE, $Value);
                            $Return[] = "`user_lastip` LIKE '%{$Value}%' OR `ip_at_reg` LIKE '%{$Value}%'";
                        }
                        return implode(' OR ', $Return);
                    };
                }
                $Query['querySelect'] = 'id';
                $Query['querySelectArray'][] = 'id';
                $Query['queryString'] = "FROM {{table}} WHERE ({whereJoin}) {whereAddition}";
                $Query['queryTable'] = 'users';
            }
            else
            {
                if($Flags['strict'] === true)
                {
                    $Query['whereJoin'] = function($Array)
                    {
                        foreach($Array as $Value)
                        {
                            $Value = md5($Value);
                            $Return[] = "`iptable`.`ValueHash` = '{$Value}'";
                        }
                        return implode(' OR ', $Return);
                    };
                }
                else
                {
                    $Query['whereJoin'] = function($Array)
                    {
                        foreach($Array as $Value)
                        {
                            $Value = preg_replace(REGEXP_SANITIZELIKE_SEARCH, REGEXP_SANITIZELIKE_REPLACE, $Value);
                            $Return[] = "`iptable`.`Value` LIKE '%{$Value}%'";
                        }
                        return implode(' OR ', $Return);
                    };
                }
                $Query['querySelect'] = '`users`.`id`';
                $Query['querySelectArray'][] = 'id';
                $Query['queryString'] = "FROM {{table}} AS `iptable` LEFT JOIN `{{prefix}}user_enterlog` AS `enterlog` ON `enterlog`.`IP_ID` = `iptable`.`ID` JOIN `{{prefix}}users` AS `users` ON `enterlog`.`User_ID` = `users`.`id` WHERE (`iptable`.`Type` = 'ip' AND ({whereJoin})) {whereAddition} GROUP BY `User_ID`";
                $Query['queryTable'] = 'used_ip_and_ua';
                $Query['whereAdditionAlias'] = 'users';
            }
        }
        else if($Type === 'umail')
        {
            $ThisRegexp = ($Flags['strict'] === true ? REGEXP_EMAIL_SIGNS : REGEXP_EMAIL_SIGNS);
            foreach($Search as $Value)
            {
                if(preg_match($ThisRegexp, $Value))
                {
                    $Query['whereData'][] = $Value;
                }
            }
            if($Flags['strict'] === true)
            {
                $Query['whereJoin'] = function($Array)
                {
                    foreach($Array as $Value)
                    {
                        $Return[] = "`email` = '{$Value}' OR `email_2` = '{$Value}'";
                    }
                    return implode(' OR ', $Return);
                };
            }
            else
            {
                $Query['whereJoin'] = function($Array)
                {
                    foreach($Array as $Value)
                    {
                        $Value = preg_replace(REGEXP_SANITIZELIKE_SEARCH, REGEXP_SANITIZELIKE_REPLACE, $Value);
                        $Return[] = "`email` LIKE '%{$Value}%' OR `email_2` LIKE '%{$Value}%'";
                    }
                    return implode(' OR ', $Return);
                };
            }
            $Query['querySelect'] = 'id';
            $Query['querySelectArray'][] = 'id';
            $Query['queryString'] = "FROM {{table}} WHERE ({whereJoin}) {whereAddition}";
            $Query['queryTable'] = 'users';
        }
    }
    else
    {
        // WHERE Statement is unnecessary
        $Query['querySelect'] = 'id';
        $Query['querySelectArray'][] = 'id';
        $Query['queryString'] = "FROM {{table}} {whereAddition}";
        $Query['queryTable'] = 'users';
    }

    // Check, if we can go further
    if(!empty($Search) && empty($Query['whereData']))
    {
        // All given data was rejected
        return false;
    }

    // Additional Flags
    // > user is an Artificial Intelligence (AI)
    if($Flags['isAI'] === true)
    {
        $Query['whereAddition'][] = '`isAI` = 1';
    }
    else if($Flags['isAI'] === false)
    {
        $Query['whereAddition'][] = '`isAI` = 0';
    }
    // > user is On Vacation
    if($Flags['onVacation'] === true)
    {
        $Query['whereAddition'][] = '`is_onvacation` = 1';
    }
    else if($Flags['onVacation'] === false)
    {
        $Query['whereAddition'][] = '`is_onvacation` = 0';
    }
    // > user is Deleting his Account
    if($Flags['inDeletion'] === true)
    {
        $Query['whereAddition'][] = '`is_ondeletion` = 1';
    }
    else if($Flags['inDeletion'] === false)
    {
        $Query['whereAddition'][] = '`is_ondeletion` = 0';
    }
    // > user is Banned
    if($Flags['isBanned'] === true)
    {
        $Query['whereAddition'][] = '`is_banned` = 1';
    }
    else if($Flags['isBanned'] === false)
    {
        $Query['whereAddition'][] = '`is_banned` = 0';
    }
    // > user has Activated his Account
    if($Flags['isActive'] === true)
    {
        $Query['whereAddition'][] = '`activation_code` = \'\'';
    }
    else if($Flags['isActive'] === false)
    {
        $Query['whereAddition'][] = '`activation_code` != \'\'';
    }
    // > user is in Any Ally
    if(!in_array($Type, array('aid', 'astring', 'aname', 'atag')))
    {
        if($Flags['inAlly'] === true)
        {
            $Query['whereAddition'][] = '(`ally_id` > 0 OR `ally_request` > 0)';
        }
        elseif($Flags['inAlly'] === false)
        {
            $Query['whereAddition'][] = '(`ally_id` = 0 AND `ally_request` = 0)';
        }
    }
    // > user is Online (Active in last TIME_ONLINE seconds)
    if($Flags['isOnline'] === true)
    {
        $Query['whereAddition'][] = '`onlinetime` >= (UNIX_TIMESTAMP() - '.TIME_ONLINE.')';
    }
    else if($Flags['isOnline'] === false)
    {
        $Query['whereAddition'][] = '`onlinetime` < (UNIX_TIMESTAMP() - '.TIME_ONLINE.')';
    }

    // Prepare SQL Statement
    if(empty($Query['whereAddition']) AND empty($Query['whereJoin']) AND empty($Query['whereData']))
    {
        // Don't load any data here, we don't want to load whole table
        return true;
    }

    if(!empty($Query['whereAddition']))
    {
        if(!empty($Query['whereAdditionAlias']))
        {
            foreach($Query['whereAddition'] as &$Value)
            {
                $Value = preg_replace('#\`(.*?)\`#si', "`{$Query['whereAdditionAlias']}`.`$1`", $Value);
            }
        }
        $Replacement = '('.implode(' AND ', $Query['whereAddition']).')';
        if(!empty($Query['whereJoin']) OR !empty($Query['whereData']))
        {
            $Replacement = ' AND '.$Replacement;
        }
        else
        {
            $Replacement = ' WHERE '.$Replacement;
        }
        $Query['queryString'] = str_replace('{whereAddition}', $Replacement, $Query['queryString']);
    }
    else
    {
        $Query['queryString'] = str_replace('{whereAddition}', '', $Query['queryString']);
    }
    if(!empty($Query['whereJoin']))
    {
        $Query['queryString'] = str_replace('{whereJoin}', $Query['whereJoin']($Query['whereData']), $Query['queryString']);
    }
    else
    {
        $Query['queryString'] = str_replace('{whereData}', (isset($Query['whereData']) ? $Query['whereData'] : null), $Query['queryString']);
    }

    $Query['queryString'] = "SELECT {$Query['querySelect']} {$Query['queryString']}; -- FilterQuery";

    $Result = doquery($Query['queryString'], $Query['queryTable']);

    if($Result->num_rows > 0)
    {
        $SelectArrayCount = count($Query['querySelectArray']);
        $NeedValueCheck = ($SelectArrayCount > 1 ? true : false);
        while($Data = $Result->fetch_assoc())
        {
            foreach($Query['querySelectArray'] AS $FieldName)
            {
                if($NeedValueCheck === true)
                {
                    if($Data[$FieldName] <= 0)
                    {
                        continue;
                    }
                }
                $Return[] = $Data[$FieldName];
            }
        }
        if($NeedValueCheck === true)
        {
            $Return = array_unique($Return);
        }
        return $Return;
    }
    // Nothing found
    return null;
}

?>
