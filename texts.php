<?php

define('INSIDE', true);

$_DontShowMenus = true;
$_DontCheckPolls = true;
$_DontForceRulesAcceptance = true;
$_AllowInVacationMode = true;

$_EnginePath = './';
include($_EnginePath.'common.php');

includeLang('texts');
$_Lang['skinpath'] = $_SkinPath;

$TPL = '<style>{Style}</style><br/><table width="800"><tr><td class="b pad5">{Text}</td></tr></table>';

$TextID = (isset($_GET['id']) ? intval($_GET['id']) : 0);
if($TextID > 0)
{
    if(!empty($_Lang['Texts'][$TextID]))
    {
        $_Lang['Title'] = $_Lang['Texts'][$TextID]['title'];
        $_Lang['Text'] = $_Lang['Texts'][$TextID]['text'];
        $_Lang['Style'] = $_Lang['Texts'][$TextID]['style'];
    }
    else
    {
        message($_Lang['Error_TextNoExist'], $_Lang['PageTitleError']);
    }
}
else
{
    message($_Lang['Error_BadID'], $_Lang['PageTitleError']);
}

$page = parsetemplate($TPL, $_Lang);
display($page, $_Lang['PageTitle'].$_Lang['Title'], false);

?>
