<?php

define('INSIDE', true);

$_EnginePath = './';

include($_EnginePath.'common.php');

includeLang('reg_mainpage');
$_DontShowMenus = true;

$RecommendedUni = '1';

$UniData = array
(
    '1' => array
    (
        // If you want to place your Universum on another domain (or subdomain),
        // regscripturl is absolute path (with http://) to your Universum reg_ajax.php file
        'regscripturl' => './reg_ajax.php',
        'gamespeed'    => 1,
        'resspeed'     => 1,
        'fleetspeed'   => 1,
        'start'        => 0,
        'galaxycount'  => 9,
        'acs'          => true,
        'rapid'        => true,
        'fleetdeb'     => 0,
        'defdeb'       => '0',
        'mother'       => 1,
    ),
);

// Create registry form
$parse = $_Lang;

$TPL_UniInfoBox             = gettemplate('reg_uniinfo_box');
$TPL_UniSelector            = gettemplate('reg_uniselector');
$TPLInfo_UniInfoBoxWidth    = 700;

$UniIterator = 0;
foreach($UniData as $UniNo => $This)
{
    $ThisInfobox = $_Lang;
    $ThisInfobox['Insert_UniID']                 = $UniNo;
    $ThisInfobox['Insert_GameSpeed']             = prettyNumber($This['gamespeed']);
    $ThisInfobox['Insert_ResSpeed']              = prettyNumber($This['resspeed']);
    $ThisInfobox['Insert_FleetSpeed']            = prettyNumber($This['fleetspeed']);
    $ThisInfobox['Insert_UniStart']              = prettyDate('d m Y - H:i', $This['start'], 1);
    $ThisInfobox['Insert_WorldSize']             = $This['galaxycount'];
    $ThisInfobox['Insert_GameACS']               = ($This['acs'] === true ? $_Lang['UniOpt_Active'] : $_Lang['UniOpt_InActive']);
    $ThisInfobox['Insert_GameACS_Color']         = ($This['acs'] === true ? 'lime' : 'red');
    $ThisInfobox['Insert_GameRapidFire']         = ($This['rapid'] === true ? $_Lang['UniOpt_Active'] : $_Lang['UniOpt_InActive']);
    $ThisInfobox['Insert_GameRapidFire_Color']   = ($This['rapid'] === true ? 'lime' : 'red');
    $ThisInfobox['Insert_GameFleetDebris']       = $This['fleetdeb'];
    $ThisInfobox['Insert_GameDefenseDebris']     = $This['defdeb'];
    $ThisInfobox['Insert_MotherSize']            = prettyNumber($This['mother']);

    $ThisInfobox['Insert_UniVal']                = $This['regscripturl'];
    if($UniNo == $RecommendedUni)
    {
        $ThisInfobox['Insert_Selected']          = 'selected';
        $parse['Insert_UniInfo_Holder_LeftPos']  = -($UniIterator * $TPLInfo_UniInfoBoxWidth);
    }
    if(empty($This['name']))
    {
        $ThisInfobox['Insert_UniName']           = $UniNo;
    }

    $parse['Insert_UniInfo_Boxes'] = parsetemplate($TPL_UniInfoBox, $ThisInfobox);
    $parse['Insert_UniSelectors'] = parsetemplate($TPL_UniSelector, $ThisInfobox);

    $UniIterator += 1;
}

$parse['GameURL'] = GAMEURL_STRICT;
$parse['GameName'] = $_GameConfig['game_name'];
$parse['Insert_JSLang'] = json_encode($_Lang['JSLang']);

if(REGISTER_RECAPTCHA_ENABLE)
{
    $RecaptchaJSSetupTpl = gettemplate('registry_form_recaptcha_jssetup');

    $parse['PHPInject_RecaptchaJSSetup'] = parsetemplate(
        $RecaptchaJSSetupTpl,
        [
            'Recaptcha_Sitekey' => REGISTER_RECAPTCHA_PUBLICKEY,
            'Recaptcha_Lang' => 'pl'
        ]
    );
}
$page = parsetemplate(gettemplate('registry_form'), $parse);

display($page, $_Lang['Title'], false);

?>
