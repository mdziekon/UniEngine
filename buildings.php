<?php

define('INSIDE', true);

$_AllowInVacationMode = true;

$_EnginePath = './';
include($_EnginePath.'common.php');

loggedCheck();
includeLang('buildings');

if(isset($_POST['chgView']) && $_POST['chgView'] == '1')
{
    if($_POST['mode'] == '0' OR $_POST['mode'] == '1')
    {
        $Option = $_POST['mode'];
        $_User['settings_DevelopmentOld'] = $Option;
        doquery("UPDATE {{table}} SET `settings_DevelopmentOld` = '{$Option}' WHERE `id` = {$_User['id']};", 'users');
    }
}

GlobalTemplate_AppendToAfterBody(gettemplate('buildings_compact_viewselector_files'));

$ViewSelectorPHP = array
(
    'PHP_ViewMode' => $_User['settings_DevelopmentOld'],
    'PHP_ViewText' => $_Lang['ViewSel_ViewText'],
    'PHP_Mode0' => $_Lang['ViewSel_Mode0'],
    'PHP_Mode1' => $_Lang['ViewSel_Mode1'],
    'PHP_ChangeView' => $_Lang['ViewSel_ChangeView']
);
GlobalTemplate_AppendToBottomMenuInjection(parsetemplate(gettemplate('buildings_compact_viewselector_body'), $ViewSelectorPHP));

if(!isset($_Planet))
{
    $_Planet = array();
}
HandleFullUserUpdate($_User, $_Planet, $GetLabPlanet);
if(isset($_User['techQueue_EndTime']) && $_User['techQueue_EndTime'] > 0)
{
    $InResearch = true;
    $ResearchPlanet = &$GetLabPlanet;
}
else
{
    $InResearch = false;
    $ResearchPlanet = false;
}

$OldViewMode = ($_User['settings_DevelopmentOld'] == 1 ? true : false);

if(!isset($_GET['mode']))
{
    $_GET['mode'] = '';
}

include($_EnginePath . 'modules/development/_includes.php');

switch($_GET['mode'])
{
    case 'fleet':
        if($OldViewMode)
        {
            include($_EnginePath.'includes/functions/FleetBuildingPage.php');
            FleetBuildingPage($_Planet, $_User);
        }
        else
        {
            include($_EnginePath.'includes/functions/ShipyardPage.php');
            ShipyardPage($_Planet, $_User);
        }
        break;
    case 'research':
        if($OldViewMode)
        {
            include($_EnginePath.'includes/functions/ResearchBuildingPage.php');
            ResearchBuildingPage($_Planet, $_User, $ResearchPlanet);
        }
        else
        {
            include($_EnginePath.'includes/functions/LaboratoryPage.php');
            LaboratoryPage($_Planet, $_User, $InResearch, $ResearchPlanet);
        }
        break;
    case 'defense':
        if($OldViewMode)
        {
            include($_EnginePath.'includes/functions/DefensesBuildingPage.php');
            DefensesBuildingPage($_Planet, $_User);
        }
        else
        {
            include($_EnginePath.'includes/functions/ShipyardPage.php');
            ShipyardPage($_Planet, $_User, 'defense');
        }
        break;
    default:
        if($OldViewMode)
        {
            include($_EnginePath.'includes/functions/BatimentBuildingPage.php');
            BatimentBuildingPage($_Planet, $_User);
        }
        else
        {
            include($_EnginePath.'includes/functions/StructuresBuildingPage.php');
            StructuresBuildingPage($_Planet, $_User);
        }
        break;
}

?>
