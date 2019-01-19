<?php

// Real syntax parser should be implemented to check whole expression validity

define('INSIDE', true);
define('IN_ADMIN', true);

$_SetAccessLogPreFilename = 'admin/';
$_SetAccessLogPath = '../';
$_EnginePath = './../';

include($_EnginePath.'common.php');

if(!CheckAuth('programmer'))
{
    message($_Lang['sys_noalloaw'], $_Lang['sys_noaccess']);
}

includeLang('admin/alerts_filters');

$Now = time();

$FunctionsSyntaxes = array
(
    'userPresent' => array
    (
        'regexp' => 'userPresent\((.*?)\)',
        'replace' => 'in_array(\\1, $FiltersData[\'users\'])',
        'arg_regexp' => '[0-9]+',
        'badsyn' => '1',
        'arg_badsyn' => '1',
        'search' => 'USER',
        'info' => '1',
    ),
    'ipPresent' => array
    (
        'regexp' => 'ipPresent\((.*?)\)',
        'replace' => 'in_array(\\1, $FiltersData[\'ips\'])',
        'arg_regexp' => '[0-9]+',
        'badsyn' => '1',
        'arg_badsyn' => '1',
        'search' => 'IP',
        'info' => '2',
    ),
    'userIsSender' => array
    (
        'regexp' => 'userIsSender\((.*?)\)',
        'replace' => '\\1 == $FiltersData[\'sender\']',
        'arg_regexp' => '[0-9]+',
        'badsyn' => '1',
        'arg_badsyn' => '1',
        'search' => 'USER',
        'info' => '3',
    ),
    'userIsTarget' => array
    (
        'regexp' => 'userIsTarget\((.*?)\)',
        'replace' => '\\1 == $FiltersData[\'target\']',
        'arg_regexp' => '[0-9]+',
        'badsyn' => '1',
        'arg_badsyn' => '1',
        'search' => 'USER',
        'info' => '4',
    ),
    'logIPCount' => array
    (
        'regexp' => 'logIPCount\((.*?)(\, |\,){1}(.*?)(\, |\,){1}([0-9]{1,})\)',
        'replace' => '$FiltersData[\'logcount\'][\\3][\\1] <= \\5',
        'arg_regexp' => array(0 => '[0-9]+', 2 => '[0-9]+'),
        'badsyn' => '1',
        'arg_badsyn' => '1',
        'search' => array(0 => 'USER', 2 => 'IP'),
        'info' => '5',
    ),
    'inPlace' => array
    (
        'regexp' => 'inPlace\((.*?)\)',
        'replace' => '\\1 == $FiltersData[\'place\']',
        'arg_regexp' => '[0-9]+',
        'badsyn' => '1',
        'arg_badsyn' => '1',
        'search' => 'PLACE',
        'info' => '6',
    ),
    'AlertSenderIs' => array
    (
        'regexp' => 'AlertSenderIs\((.*?)\)',
        'replace' => '\\1 == $FiltersData[\'alertsender\']',
        'arg_regexp' => '[0-9]+',
        'badsyn' => '1',
        'arg_badsyn' => '1',
        'search' => 'ALERTSENDER',
        'info' => '7',
    ),
);

function prettySyntax($Code)
{
    global $FunctionsSyntaxes;

    foreach($FunctionsSyntaxes as $Function => $Data)
    {
        if(strstr($Code, $Function) !== FALSE)
        {
            $Code = preg_replace('#('.$Data['regexp'].')#si', '<b class="dash info'.$Data['info'].'">\\1</b>', $Code);
        }
    }

    return $Code;
}

// Deleting is here
if(isset($_GET['cmd']) && $_GET['cmd'] == 'del')
{
    $_GET['cmd'] = '';
    $DeleteID = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if($DeleteID > 0)
    {
        doquery(
            "DELETE FROM {{table}} WHERE `ID` = {$DeleteID} LIMIT 1;",
            'system_alerts_filters'
        );

        if(getDBLink()->affected_rows == 1)
        {
            $MSG = $_Lang['Info_FilterDeleted'];
            $MSGColor = 'lime';
        }
        else
        {
            $MSG = $_Lang['Error_NoSuchFilter'];
            $MSGColor = 'red';
        }
    }
    else
    {
        $MSG = $_Lang['Error_BadFilterID'];
        $MSGColor = 'red';
    }
}
else if(isset($_GET['cmd']) && $_GET['cmd'] == 'delpost')
{
    $_GET['cmd'] = '';
    $Filters2Delete = [];
    if(!empty($_POST['f']))
    {
        foreach($_POST['f'] as $FilterID => $Status)
        {
            if($Status == 'on')
            {
                $FilterID = intval($FilterID);
                if($FilterID > 0)
                {
                    $Filters2Delete[] = $FilterID;
                }
            }
        }
    }
    $DeleteCount = count($Filters2Delete);
    if($DeleteCount > 0)
    {
        doquery(
            "DELETE FROM {{table}} WHERE `ID` IN (".implode(', ', $Filters2Delete).");",
            'system_alerts_filters'
        );

        $AffectedRows = getDBLink()->affected_rows;
        if($AffectedRows > 0)
        {
            if($AffectedRows == $DeleteCount)
            {
                $MSG = $_Lang['Info_AllSelectsDeleted'];
                $MSGColor = 'lime';
            }
            else
            {
                $MSG = sprintf($_Lang['Warn_NotAllSelectsDeleted'], $AffectedRows, $DeleteCount);
                $MSGColor = 'orange';
            }
        }
        else
        {
            $MSG = $_Lang['Error_NothingDeleted'];
            $MSGColor = 'red';
        }
    }
    else
    {
        $MSG = $_Lang['Error_Nothing2Delete'];
        $MSGColor = 'red';
    }
}

if(isset($_GET['cmd']) && ($_GET['cmd'] == 'add' OR $_GET['cmd'] == 'edit'))
{
    $AllowProceed = false;
    $WhatAreWeDoint = $_GET['cmd'];
    if($_GET['cmd'] == 'edit')
    {
        $EditID = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if($EditID <= 0)
        {
            $_GET['cmd'] = '';
            $MSG = $_Lang['Error_NoIDGiven'];
            $MSGColor = 'red';
        }
        else
        {
            $SelectFilter = doquery("SELECT * FROM {{table}} WHERE `ID` = {$EditID} LIMIT 1;", 'system_alerts_filters', true);
            if($SelectFilter['ID'] != $EditID)
            {
                $_GET['cmd'] = '';
                $MSG = $_Lang['Error_NoSuchFilter'];
                $MSGColor = 'red';
            }
            else
            {
                $AllowProceed = true;
            }
        }
    }
    else
    {
        $AllowProceed = true;
    }

    if($AllowProceed === true)
    {
        $PageTPL = gettemplate('admin/alertsfilters_add');
        $Parse = $_Lang;
        $Parse['Rows'] = '';
        if($WhatAreWeDoint == 'edit')
        {
            $Parse['InsertOnEdit'] = '<input type="hidden" name="editid" value="'.$EditID.'"/>';
            $Parse['Filters_Add'] = $_Lang['Filters_Edit'];
            $Parse['AddFilter'] = $_Lang['EditFilter'];
            $Parse['AreYouSure_Add'] = $_Lang['AreYouSure_Edit'];
            $Parse['ThisFormAction'] = '?cmd=edit&id='.$EditID;
        }
        else
        {
            $Parse['ThisFormAction'] = '?cmd=add';
        }

        if($WhatAreWeDoint == 'add' OR ($WhatAreWeDoint == 'edit' && isset($_POST['editid']) && $_POST['editid'] == $EditID))
        {
            $Code = isset($_POST['code']) ? trim(stripslashes($_POST['code'])) : null;
            $Action = isset($_POST['action']) ? intval($_POST['action']) : 0;
            $Enabled = (isset($_POST['turnoff']) && $_POST['turnoff'] == 'on' ? '0' : '1');
            $AllowSave = true;
        }
        else
        {
            $Code = stripslashes($SelectFilter['HighCode']);
            $Action = $SelectFilter['ActionType'];
            $Enabled = $SelectFilter['Enabled'];
        }

        $Parse['CodePost'] = $Code;
        $Parse['ActionType_Select'.$Action] = 'selected';
        if($Enabled == '0')
        {
            $Parse['TurnOffChecked'] = 'checked';
        }

        if(isset($_POST['doWhat']) && $_POST['doWhat'] == 'check')
        {
            $BracketOpenCount = substr_count($Code, '(');
            $BracketCloseCount = substr_count($Code, ')');

            if($BracketOpenCount > $BracketCloseCount)
            {
                $Warnings[] = $_Lang['Warn_BadSyntax_BracketOpen'];
            }
            else if($BracketOpenCount < $BracketCloseCount)
            {
                $Warnings[] = $_Lang['Warn_BadSyntax_BracketClose'];
            }

            foreach($FunctionsSyntaxes as $Function => $Data)
            {
                if(strstr($Code, $Function) !== FALSE)
                {
                    $Matches = false;
                    $ThisFunctionCount = substr_count($Code, $Function);
                    preg_match_all('#'.$Data['regexp'].'#si', $Code, $Matches);
                    $ErrorsCount = $ThisFunctionCount - count($Matches[0]);
                    if($ErrorsCount > 0)
                    {
                        $Warnings[] = sprintf($_Lang['Warn_BadSyntax_'.$Data['badsyn']], $Function, $ErrorsCount);
                    }
                    $ErrorsCount = 0;

                    if(count($Matches) == 2)
                    {
                        $ArgumentRegExp = '/^'.$Data['arg_regexp'].'$/D';
                        foreach($Matches[1] as $Argument)
                        {
                            if(!preg_match($ArgumentRegExp, $Argument))
                            {
                                $ErrorsCount += 1;
                            }
                        }
                    }
                    else
                    {
                        foreach($Matches as $MatchID => $MatchArg)
                        {
                            if($MatchID == 0)
                            {
                                continue;
                            }
                            $MatchID -= 1;
                            if(!empty($Data['arg_regexp'][$MatchID]))
                            {
                                if(!preg_match('/^'.$Data['arg_regexp'][$MatchID].'$/D', $MatchArg[0]))
                                {
                                    $ErrorsCount += 1;
                                }
                            }
                        }
                    }
                    if($ErrorsCount > 0)
                    {
                        $Warnings[] = sprintf($_Lang['Warn_ArgBadSyntax_'.$Data['arg_badsyn']], $Function, $ErrorsCount);
                    }
                }
            }

            if(!empty($Warnings))
            {
                $Parse['System_MSG'] = '<tr><td class="red pad5 c" colspan="2">'.implode('<br/>', $Warnings).'</td></tr><tr class="inv"><td></td></tr>';
            }
            else
            {
                $Parse['System_MSG'] = '<tr><td class="c pad5 lime" colspan="8">'.$_Lang['Info_CodeLooksFine'].'</td></tr><tr class="inv"><td></td></tr>';
            }
        }
        else if(isset($_POST['doWhat']) && $_POST['doWhat'] == 'save')
        {
            if($WhatAreWeDoint == 'edit' AND $AllowSave === false)
            {
                $Code = '';
                $Errors[] = $_Lang['Error_IDIsMalformed'];
            }

            if(empty($Code))
            {
                $Errors[] = $_Lang['Error_CodeEmpty'];
            }
            if($Action <= 0)
            {
                $Errors[] = $_Lang['Error_BadAction'];
            }

            if(!empty($Code))
            {
                foreach($FunctionsSyntaxes as $Function => $Data)
                {
                    if(strstr($Code, $Function) !== FALSE)
                    {
                        $Matches = false;
                        $ThisFunctionCount = substr_count($Code, $Function);
                        preg_match_all('#'.$Data['regexp'].'#si', $Code, $Matches);
                        $ErrorsCount = $ThisFunctionCount - count($Matches[0]);
                        if($ErrorsCount > 0)
                        {
                            $Errors[] = sprintf($_Lang['Warn_BadSyntax_'.$Data['badsyn']], $Function, $ErrorsCount);
                        }
                        $ErrorsCount = 0;
                        if(count($Matches) == 2)
                        {
                            $ArgumentRegExp = '/^'.$Data['arg_regexp'].'$/D';
                            foreach($Matches[1] as $Argument)
                            {
                                if(!preg_match($ArgumentRegExp, $Argument))
                                {
                                    $ErrorsCount += 1;
                                }
                                else
                                {
                                    if(empty($SearchData[$Data['search']]) OR !in_array($Argument, $SearchData[$Data['search']]))
                                    {
                                        $SearchData[$Data['search']][] = $Argument;
                                    }
                                }
                            }
                        }
                        else
                        {
                            foreach($Matches as $MatchID => $MatchArg)
                            {
                                if($MatchID == 0)
                                {
                                    continue;
                                }
                                $MatchID -= 1;
                                if(!empty($Data['arg_regexp'][$MatchID]))
                                {
                                    if(!preg_match('/^'.$Data['arg_regexp'][$MatchID].'$/D', $MatchArg[0]))
                                    {
                                        $ErrorsCount += 1;
                                    }
                                    else
                                    {
                                        if(empty($SearchData[$Data['search'][$MatchID]]) OR !in_array($MatchArg[0], $SearchData[$Data['search'][$MatchID]]))
                                        {
                                            $SearchData[$Data['search'][$MatchID]][] = $MatchArg[0];
                                        }
                                    }
                                }
                            }
                        }
                        if($ErrorsCount > 0)
                        {
                            $Errors[] = sprintf($_Lang['Warn_ArgBadSyntax_'.$Data['arg_badsyn']], $Function, $ErrorsCount);
                        }
                    }
                }
            }

            if(!empty($Errors))
            {
                $Parse['System_MSG'] = '<tr><td class="red pad5 c" colspan="2">'.implode('<br/>', $Errors).'</td></tr><tr class="inv"><td></td></tr>';
            }
            else
            {
                foreach($FunctionsSyntaxes as $Data)
                {
                    if(empty($Data['replace_callback']))
                    {
                        $Patterns[] = '#'.$Data['regexp'].'#si';
                        $Replaces[] = $Data['replace'];
                    }
                    else
                    {
                        $Patterns_Callback[] = '#'.$Data['regexp'].'#si';
                        $Replaces_Callback[] = $Data['replace_callback'];
                    }
                }
                $EvalCode = $Code;

                if(!empty($Patterns))
                {
                    foreach($Patterns as $PatternIndex => $PatternData)
                    {
                        $EvalCode = preg_replace($PatternData, $Replaces[$PatternIndex], $EvalCode);
                    }
                }
                if(!empty($Patterns_Callback))
                {
                    foreach($Patterns_Callback as $PatternIndex => $PatternData)
                    {
                        $EvalCode = preg_replace_callback($PatternData, $Replaces_Callback[$PatternIndex], $EvalCode);
                    }
                }
                $EvalCode = addslashes($EvalCode);
                $Code = addslashes($Code);

                $SearchDataArray = '';
                if(!empty($SearchData))
                {
                    foreach($SearchData as $Type => $Values)
                    {
                        foreach($Values as $Value)
                        {
                            $SearchDataArray .= "{{$Type}_{$Value}}";
                        }
                    }
                }

                if($WhatAreWeDoint == 'add')
                {
                    doquery("INSERT INTO {{table}} SET `Date` = UNIX_TIMESTAMP(), `Enabled` = {$Enabled}, `ActionType` = {$Action}, `SearchData` = '{$SearchDataArray}', `HighCode` = '{$Code}', `EvalCode` = '{$EvalCode}';", 'system_alerts_filters');
                    $Parse['System_MSG'] = '<tr><td class="lime pad5 c" colspan="2">'.$_Lang['Info_FilterAdded'].'</td></tr><tr class="inv"><td></td></tr>';
                }
                else
                {
                    doquery("UPDATE {{table}} SET `Enabled` = {$Enabled}, `ActionType` = {$Action}, `SearchData` = '{$SearchDataArray}', `HighCode` = '{$Code}', `EvalCode` = '{$EvalCode}' WHERE `ID` = {$EditID};", 'system_alerts_filters');
                    $Parse['System_MSG'] = '<tr><td class="lime pad5 c" colspan="2">'.$_Lang['Info_FilterEdited'].'</td></tr><tr class="inv"><td></td></tr>';
                }
            }
        }
    }
}

if(empty($_GET['cmd']))
{
    $_GET['cmd'] = 'list';
}

if($_GET['cmd'] == 'list')
{
    $PageTPL = gettemplate('admin/alertsfilters_body');
    $RowsTPL = gettemplate('admin/alertsfilters_rows');

    $CurrentPage = 0;
    if(!empty($_GET['page']))
    {
        $CurrentPage = intval($_GET['page']);
    }
    if($CurrentPage <= 0)
    {
        $CurrentPage = 1;
    }
    $_Lang['CurrentPage'] = $CurrentPage;

    $PerPage = 0;
    if(!empty($_COOKIE['alertsfilter_pp']))
    {
        $PerPage = intval($_COOKIE['alertsfilter_pp']);
    }
    if($PerPage <= 0)
    {
        $PerPage = 20;
    }
    if(isset($_GET['pp']) && $_GET['pp'] > 0 && $_GET['pp'] != $PerPage)
    {
        $TempPerPage = intval($_GET['pp']);
        if($TempPerPage > 0 AND $TempPerPage != $PerPage)
        {
            $PerPage = $TempPerPage;
            setcookie('alertsfilter_pp', $PerPage, $Now + TIME_YEAR);
        }
    }
    $_Lang['perpage_select_'.$PerPage] = 'selected';

    $GetStart = (string) ((($CurrentPage - 1) * $PerPage) + 0);

    $SQLResult_GetFilters = doquery(
        "SELECT * FROM {{table}} ORDER BY `Date` DESC LIMIT {$GetStart}, {$PerPage};",
        'system_alerts_filters'
    );

    $SQLResult_GetFiltersCount = doquery("SELECT COUNT(`ID`) AS `Count` FROM {{table}};", 'system_alerts_filters', true);
    $FiltersCount = $SQLResult_GetFiltersCount['Count'];

    $Parse = $_Lang;
    $Parse['Rows'] = '';
    if(!empty($MSG))
    {
        $Parse['System_MSG'] = '<tr><td class="c pad5" colspan="8" style="color: '.$MSGColor.'">'.$MSG.'</td></tr><tr class="inv"><td></td></tr>';
    }

    if($FiltersCount > 0)
    {
        include_once($_EnginePath.'includes/functions/Pagination.php');
        $Pagin = CreatePaginationArray($FiltersCount, $PerPage, $CurrentPage, 7);
        $PaginationTPL = '<input type="button" class="pagin {$Classes}" name="goto_{$Value}" value="{$ShowValue}"/>';
        $PaginationViewOpt = array('CurrentPage_Classes' => 'fatB orange', 'Breaker_View' => '...');
        $CreatePagination = implode(' ', ParsePaginationArray($Pagin, $CurrentPage, $PaginationTPL, $PaginationViewOpt));
        $Parse['Pagination'] = $CreatePagination;
    }
    else
    {
        $Parse['HidePaginRow'] = ' class="hide"';
    }

    if($SQLResult_GetFilters->num_rows > 0)
    {
        $Parse['BlankCellFix'] = 'hide';
        while($Filter = $SQLResult_GetFilters->fetch_assoc())
        {
            $Row = $_Lang;

            $Row['CheckBox'] = '<input type="checkbox" name="f['.$Filter['ID'].']" />';
            $Row['ID'] = $Filter['ID'];
            $Row['Date'] = date('d.m.Y', $Filter['Date']).'<br/>'.date('H:i:s', $Filter['Date']);
            $Row['Enabled'] = ($Filter['Enabled'] == 1 ? '<b class="true"></b>' : '<b class="false"></b>');
            $Row['ActionType'] = $_Lang['ActionTypes'][$Filter['ActionType']];
            $Row['Conditions'] = prettySyntax($Filter['HighCode']);
            $Row['UseCount'] = (string) ($Filter['UseCount'] + 0);

            $Parse['Rows'] .= parsetemplate( $RowsTPL, $Row );
        }
    }
    else
    {
        if($CurrentPage > 1 AND $FiltersCount > 0)
        {
            $ThisWarning = $_Lang['No_Filters_ThisPage'];
        }
        else
        {
            $ThisWarning = $_Lang['No_Filters'];
        }
        $Parse['Rows'] = '<tr><th class="c pad5 red" colspan="8">'.$ThisWarning.'</td></tr>';
        $Parse['HideSelectors'] = 'hide';
        $Parse['BlankCellFix'] = 'inv';
    }
}

$Page = parsetemplate($PageTPL, $Parse);
display($Page, $_Lang['PageTitle'], false, true);

?>
