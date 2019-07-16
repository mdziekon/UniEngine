<?php

define('INSIDE', true);
define('IN_ADMIN', true);

$_SetAccessLogPreFilename = 'admin/';
$_SetAccessLogPath = '../';
$_EnginePath = './../';

$DisableTelemetry = true;

include($_EnginePath.'common.php');

$Hide = 'hide';

if(!CheckAuth('programmer'))
{
    message($_Lang['sys_noalloaw'], $_Lang['sys_noaccess']);
}

includeLang('admin/telemetry');
$_Lang['Places'] = '';

$Title = $_Lang['Page_Title'];

$_Lang['Hide_MessageBox'] = $_Lang['Hide_Headers'] = $Hide;
$_Lang['MessageBox_Color'] = 'red';

$PlaceID = 0;
if(!empty($_GET['pid']))
{
    $_Lang['MessageBox_Text'] = $_Lang['Tele_Msg_BadPID'];
    $PID = intval($_GET['pid']);
    if($PID > 0)
    {
        $Place = doquery("SELECT `places`.*, COUNT(`data`.`DataID`) AS `PointsCount` FROM {{table}} AS `places` LEFT JOIN {{prefix}}telemetry_data AS `data` ON `data`.`PlaceID` = `places`.`ID` WHERE `places`.`ID` = {$PID} GROUP BY `PlaceID` LIMIT 1;", 'telemetry_pages', true);
        if($Place['ID'] == $PID)
        {
            $PlaceID = $PID;
        }
    }
}

if($PlaceID > 0)
{
    $BodyTPL = gettemplate('admin/telemetry_graph_body');
    $ModeColors = array('black', 'red', 'orange', 'green', 'purple', 'blue');
    $ModeNameTranslation = array('_t', '_c', '_c_maininc', '_d', '_f0', '_f1');
    foreach($ModeNameTranslation as $Value)
    {
        $ModeNameTranslation[$Value] = $_Lang['Tele_NameTranslation_'.$Value];
    }

    $_Lang['ThisPID'] = $PlaceID;

    if(!empty($Place['Get']))
    {
        $_Lang['CombinePlace'] = "{$Place['Page']}?{$Place['Get']}";
    }
    else
    {
        $_Lang['CombinePlace'] = $Place['Page'];
    }

    $_Lang['Insert_Filter_Where_Val'] = '';
    $_Lang['Insert_Filter_Order_Val'] = '`TimeStamp`';
    $_Lang['Insert_Filter_Limit_Val'] = '100';
    $_Lang['Insert_Filter_Jumps_On'] = '';
    $_Lang['Insert_Filter_JumpsMin_Val'] = '5';

    if(!empty($_POST['filter_where']))
    {
        $SelectDataWhere = getDBLink()->escape_string($_POST['filter_where']);
    }
    if(!empty($_POST['filter_order']))
    {
        $SelectDataOrder = getDBLink()->escape_string($_POST['filter_order']);
    }
    if(!empty($_POST['filter_limit']))
    {
        $SelectDataLimit = preg_replace('#[^0-9\ \,]#si', '', $_POST['filter_limit']);
    }
    if(isset($_POST['filter_jumps']) && $_POST['filter_jumps'] == 'on')
    {
        $_POST['filter_jumps_min'] = str_replace(',', '.', $_POST['filter_jumps_min']);
        $MinimalDiff = floatval($_POST['filter_jumps_min']);
        if($MinimalDiff > 1)
        {
            $DeleteScoreJumps = true;
            $_Lang['Insert_Filter_Jumps_On'] = 'checked';
            $_Lang['Insert_Filter_JumpsMin_Val'] = $MinimalDiff;
        }
    }

    $InsertWhere = '';
    $InsertOrder = '';
    $InsertLimit = '';
    if(!empty($SelectDataWhere))
    {
        $InsertWhere = " AND {$SelectDataWhere}";
        $_Lang['Insert_Filter_Where_Val'] = $SelectDataWhere;
    }
    if(!empty($SelectDataOrder))
    {
        $SelectDataOrder = str_ireplace(array('asc', 'desc'), array('', ''), $SelectDataOrder);
        $InsertOrder = "ORDER BY {$SelectDataOrder} DESC";
        $_Lang['Insert_Filter_Order_Val'] = $SelectDataOrder;
    }
    else
    {
        $InsertOrder = "ORDER BY `TimeStamp` DESC";
    }
    if(!empty($SelectDataLimit))
    {
        $InsertLimit = "LIMIT {$SelectDataLimit}";
        $_Lang['Insert_Filter_Limit_Val'] = $SelectDataLimit;
    }
    else
    {
        $InsertLimit = "LIMIT 100";
    }

    $SQLResult_SelectData = doquery(
        "SELECT `DataID`, `UserID`, `TimeStamp`, `Data` FROM {{table}} WHERE `PlaceID` = {$PlaceID} {$InsertWhere} {$InsertOrder} {$InsertLimit};",
        'telemetry_data'
    );

    if($SQLResult_SelectData->num_rows > 0)
    {
        $ModesI = 0;
        $ModesAdded = array();
        while($DataPoint = $SQLResult_SelectData->fetch_assoc())
        {
            $DataPoint['Data'] = json_decode($DataPoint['Data'], true);
            foreach($DataPoint['Data'] as $DataKey => $DataValue)
            {
                if(!in_array($DataKey, $ModesAdded))
                {
                    if(empty($ModeNameTranslation[$DataKey]))
                    {
                        $ModeName = $DataKey;
                    }
                    else
                    {
                        $ModeName = $ModeNameTranslation[$DataKey];
                    }
                    $Modes[$ModesI] = array
                    (
                        'id' => $ModesI,
                        'vendor_id' => 1,
                        'mode' => '',
                        'name' => $ModeName,
                        'color' => $ModeColors[$ModesI]
                    );
                    $ModesAdded[] = $DataKey;
                    $ModesMap[$DataKey] = $ModesI;
                    $ModesI += 1;
                }
                $TimeValue = $DataValue * 1000;

                $CreateStamp = $DataPoint['TimeStamp'].str_pad($DataPoint['DataID'], 20, '0', STR_PAD_LEFT).str_pad($ModesMap[$DataKey], 5, '0', STR_PAD_LEFT);
                $Scores[] = array
                (
                    'run_id' => $DataPoint['DataID'],
                    'user_id' => $DataPoint['UserID'],
                    'mode_id' => $ModesMap[$DataKey],
                    'score' => $TimeValue,
                    'orgstamp' => $DataPoint['TimeStamp'],
                    'stamp' => $CreateStamp
                );
                $GetUsernames[$DataPoint['UserID']] = $DataPoint['UserID'];

                if(!isset($AvgScores[$ModesMap[$DataKey]]))
                {
                    $AvgScores[$ModesMap[$DataKey]] = 0;
                }
                if(!isset($AvgCounts[$ModesMap[$DataKey]]))
                {
                    $AvgCounts[$ModesMap[$DataKey]] = 0;
                }
                $AvgScores[$ModesMap[$DataKey]] += $TimeValue;
                $AvgCounts[$ModesMap[$DataKey]] += 1;
            }
        }

        foreach($Scores as $ScoreKey => $ScoreData)
        {
            $ScoreStamps[$ScoreKey][] = $ScoreData['stamp'];
        }
        array_multisort($Scores, $ScoreStamps, SORT_ASC);
        $ScoreIndexes = 0;
        $ScoreLastDataID = $Scores[0]['run_id'];
        foreach($Scores as $ScoreData)
        {
            if($ScoreData['run_id'] != $ScoreLastDataID)
            {
                $ScoreIndexes += 1;
                $ScoreLastDataID = $ScoreData['run_id'];
            }
            $UserLinking[$ScoreData['mode_id']][$ScoreIndexes] = array
            (
                'user_id' => $ScoreData['user_id'],
                'stamp' => $ScoreData['orgstamp']
            );
        }

        foreach($AvgScores as $ModeID => &$Value)
        {
            $Value = $Value/$AvgCounts[$ModeID];
            $Modes[$ModeID]['avg'] = sprintf($_Lang['Tele_Legend_Avg'], sprintf('%0.3f', $Value));
        }

        if(isset($DeleteScoreJumps))
        {
            foreach($Scores as &$ScoreData)
            {
                if($ScoreData['score'] > ($AvgScores[$ScoreData['mode_id']] * $MinimalDiff))
                {
                    $ScoreData['score'] = 'null';
                }
            }
        }

        if(!empty($GetUsernames))
        {
            $SQLResult_GetUsernames = doquery(
                "SELECT `id`, `username` FROM {{table}} WHERE `id` IN (".implode(', ', $GetUsernames).");",
                'users'
            );

            while($Username = $SQLResult_GetUsernames->fetch_assoc())
            {
                $Usernames[$Username['id']] = $Username['username'];
            }
        }

        foreach($UserLinking as $ModeID => $Linkings)
        {
            foreach($Linkings as $ThisIndex => $ThisData)
            {
                $ThisIndex = (string)($ThisIndex + 0);
                if(empty($Usernames[$ThisData['user_id']]))
                {
                    $Usernames[$ThisData['user_id']] = $_Lang['Tele_UsernameEmpty'];
                }
                $LinkUsers[$ModeID][$ThisIndex] = array('id' => $ThisData['user_id'], 'name' => $Usernames[$ThisData['user_id']]);
            }
        }
        $LinkUsers = json_encode($LinkUsers);

        include($_EnginePath.'includes/functions/MakeGraphs.php');
        $Scores['data'] = array
        (
            'fullstamp' => '%d.%m.%Y, %H:%M:%S',
            'shortstamp' => '%d.%m<br>%H:%M:%S',
            'otherdate' => '%Y_%m_%d',
            'units' => $_Lang['Tele_TimeUnits'],
            'tooltipGenFunction' => $_Lang['Tele_TooltipGenFunction']
        );
        $Result = MakeGraphs
        (
            $Modes,
            array(0 => $Scores),
            array('x' => 700, 'y' => 400),
            'if(UserLinking[modeNo][x] !== undefined){ var UserID = UserLinking[modeNo][x][\'id\']; var Username = UserLinking[modeNo][x][\'name\']; } else { var UserID = 0; var Username = \'none\'; } var OtherDate = graph.runs[x].otherDate;'
        );

        $_Lang['InsertUserLinkings'] = $LinkUsers;
        $_Lang['InsertScripts'] = $Result['includes'];
        $_Lang['InsertGraph'] = $Result['graphs'][0];
        $_Lang['InsertLegend'] = $Result['legend'];
    }
    else
    {
        $_Lang['MessageBox_Text'] = $_Lang['Tele_Msg_NoDataPoints'];
    }
}
else
{
    $BodyTPL = gettemplate('admin/telemetry_select_body');

    $SQLResult_SelectPlaces = doquery(
        "SELECT `places`.*, COUNT(*) AS `PointsCount` FROM {{table}} AS `places` JOIN {{prefix}}telemetry_data AS `data` ON `data`.`PlaceID` = `places`.`ID` GROUP BY `PlaceID` ORDER BY `places`.`Page` ASC;",
        'telemetry_pages'
    );

    if($SQLResult_SelectPlaces->num_rows > 0)
    {
        $_Lang['Hide_Headers'] = '';
        $PlacesTPL = gettemplate('admin/telemetry_select_row');
        while($Place = $SQLResult_SelectPlaces->fetch_assoc())
        {
            if(!empty($Place['Get']))
            {
                $Place['CombinePlace'] = "{$Place['Page']}?{$Place['Get']}";
            }
            else
            {
                $Place['CombinePlace'] = $Place['Page'];
            }
            $Place['HasPost'] = $_Lang['Tele_HasPost'][(string)($Place['HasPost'] + 0)];
            $Place['HasPostColor'] = $Place['HasPost']['color'];
            $Place['HasPost'] = $Place['HasPost']['txt'];
            $Place['PointsCount'] = prettyNumber($Place['PointsCount']);

            $_Lang['Places'] .= parsetemplate($PlacesTPL, $Place);
        }
    }
    else
    {
        $_Lang['MessageBox_Text'] = $_Lang['Tele_Msg_NoPlaces'];
    }
}

if(!empty($_Lang['MessageBox_Text']))
{
    $_Lang['Hide_MessageBox'] = '';
}

$Page = parsetemplate($BodyTPL, $_Lang);
display($Page, $Title, false, true);

?>
