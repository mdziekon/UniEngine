<?php

define('INSIDE', true);

$_AllowInVacationMode = true;

$_EnginePath = './';
include($_EnginePath.'common.php');

loggedCheck();

$page = '';
$HeadTpl = gettemplate('techtree_head');
$RowTpl = gettemplate('techtree_row');
foreach($_Lang['tech'] as $Element => $ElementName)
{
    $parse = array();
    $parse['id'] = $Element;
    $parse['skinpath'] = $_SkinPath;
    $parse['tt_name'] = $ElementName;
    if(!isset($_Vars_GameElements[$Element]))
    {
        $parse['Requirements'] = $_Lang['Requirements'];
        $page .= parsetemplate($HeadTpl, $parse);
    }
    else
    {
        if(isset($_Vars_Requirements[$Element]))
        {
            $parse['required_list'] = '';
            foreach($_Vars_Requirements[$Element] as $ResClass => $Level)
            {
                $SetCurrentLevel = '';
                if(in_array($ResClass, $_Vars_ElementCategories['tech']))
                {
                    if($_User[$_Vars_GameElements[$ResClass]] >= $Level)
                    {
                        $SetColor = 'lime';
                    }
                    else
                    {
                        $SetColor = 'red';
                        $SetCurrentLevel = ($_User[$_Vars_GameElements[$ResClass]] > 0 ? $_User[$_Vars_GameElements[$ResClass]] : '0').'/';
                    }
                }
                else
                {
                    if($_Planet[$_Vars_GameElements[$ResClass]] >= $Level)
                    {
                        $SetColor = 'lime';
                    }
                    else
                    {
                        $SetColor = 'red';
                        $SetCurrentLevel = ($_Planet[$_Vars_GameElements[$ResClass]] > 0 ? $_Planet[$_Vars_GameElements[$ResClass]] : '0').'/';
                    }
                }
                $parse['required_list'] .= "<a class=\"{$SetColor}\" href=\"#el{$ResClass}\">{$_Lang['tech'][$ResClass]} ({$_Lang['level']} {$SetCurrentLevel}{$Level})</a><br/>";
            }
        }
        else
        {
            $parse['required_list'] = '&nbsp;';
            $parse['tt_detail'] = '';
        }
        $parse['tt_info'] = $Element;
        $page .= parsetemplate($RowTpl, $parse);
    }
}

$parse['techtree_list'] = $page;
$parse['Tech0'] = $_Lang['tech'][0];
$parse['Tech100'] = $_Lang['tech'][100];
$parse['Tech200'] = $_Lang['tech'][200];
$parse['Tech400'] = $_Lang['tech'][400];
$page = parsetemplate(gettemplate('techtree_body'), $parse);

display($page, $_Lang['Tech'], false);

?>
