<?php

function determine_required_fields($normalizedInputs) {
    $requiredFields = [
        'dbconfig_host',
        'dbconfig_user',
        'dbconfig_pass',
        'dbconfig_name',
        'const_domain',
        'admin_username',
        'admin_password',
        'admin_email',
        'uni_gamename',
        'uni_gamespeed',
        'uni_fleetspeed',
        'uni_resourcespeed',
        'uni_motherfields',
        'uni_fleetdebris',
        'uni_defensedebris',
        'uni_missiledebris',
    ];

    if (function_exists('apc_fetch')) {
        $requiredFields[] = 'const_uniid';
    }
    if ($normalizedInputs['const_recaptcha_enable']) {
        $requiredFields[] = 'const_recaptcha_public';
        $requiredFields[] = 'const_recaptcha_private';
    }
    if ($normalizedInputs['uni_noobprt_enable']) {
        $requiredFields[] = 'uni_noobprt_basictime';
        $requiredFields[] = 'uni_noobprt_basicmultiplier';
        $requiredFields[] = 'uni_noobprt_remove';
        $requiredFields[] = 'uni_noobprt_idledays';
        $requiredFields[] = 'uni_noobprt_firstlogin';
    }
    if ($normalizedInputs['uni_antifarmenable']) {
        $requiredFields[] = 'uni_antifarmratio';
        $requiredFields[] = 'uni_antifarmtotalcount';
        $requiredFields[] = 'uni_antifarmplanetcount';
    }
    if ($normalizedInputs['uni_antibashenable']) {
        $requiredFields[] = 'uni_antibashinterval';
        $requiredFields[] = 'uni_antibashtotalcount';
        $requiredFields[] = 'uni_antibashplanetcount';
    }

    return $requiredFields;
}

?>
