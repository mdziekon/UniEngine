<?php

function normalize_config_inputs($inputs, $options) {
    $normalized = [
        // Text values
        'dbconfig_host' => $inputs['set_dbconfig_host'],
        'dbconfig_user' => $inputs['set_dbconfig_user'],
        'dbconfig_pass' => $inputs['set_dbconfig_pass'],
        'dbconfig_name' => $inputs['set_dbconfig_name'],
        'dbconfig_prefix' => $inputs['set_dbconfig_prefix'],
        'const_uniid' => $inputs['set_const_uniid'],
        'uni_gamename' => $inputs['set_uni_gamename'],
        'uni_gamedefaultlang' => (
            !empty($inputs['set_uni_gamedefaultlang']) ?
            $inputs['set_uni_gamedefaultlang'] :
            $options['installerLang']
        ),
        'const_domain' => $inputs['set_const_domain'],
        'const_subdomain' => $inputs['set_const_subdomain'],
        'admin_username' => $inputs['set_admin_username'],
        'admin_password' => $inputs['set_admin_password'],
        'admin_email' => $inputs['set_admin_email'],
        'const_recaptcha_public' => $inputs['set_const_recaptcha_public'],
        'const_recaptcha_private' => $inputs['set_const_recaptcha_private'],
        'uni_autotoolpass_gziplog' => $inputs['set_uni_autotoolpass_gziplog'],
        'uni_autotoolpass_statbuilder' => $inputs['set_uni_autotoolpass_statbuilder'],
        'uni_autotoolpass_gc' => $inputs['set_uni_autotoolpass_gc'],

        // Numerical values
        'uni_gamespeed' => floatval($inputs['set_uni_gamespeed']),
        'uni_fleetspeed' => floatval($inputs['set_uni_fleetspeed']),
        'uni_resourcespeed' => round($inputs['set_uni_resourcespeed']),
        'uni_motherfields' => round($inputs['set_uni_motherfields']),
        'uni_fleetdebris' => round($inputs['set_uni_fleetdebris']),
        'uni_defensedebris' => round($inputs['set_uni_defensedebris']),
        'uni_missiledebris' => round($inputs['set_uni_missiledebris']),
        'uni_noobprt_basictime' => round($inputs['set_uni_noobprt_basictime']),
        'uni_noobprt_basicmultiplier' => floatval($inputs['set_uni_noobprt_basicmultiplier']),
        'uni_noobprt_remove' => round($inputs['set_uni_noobprt_remove']),
        'uni_noobprt_idledays' => round($inputs['set_uni_noobprt_idledays']),
        'uni_noobprt_firstlogin' => round($inputs['set_uni_noobprt_firstlogin']),
        'uni_antifarmratio' => floatval($inputs['set_uni_antifarmratio']),
        'uni_antifarmtotalcount' => round($inputs['set_uni_antifarmtotalcount']),
        'uni_antifarmplanetcount' => round($inputs['set_uni_antifarmplanetcount']),
        'uni_antibashinterval' => round($inputs['set_uni_antibashinterval']),
        'uni_antibashtotalcount' => round($inputs['set_uni_antibashtotalcount']),
        'uni_antibashplanetcount' => round($inputs['set_uni_antibashplanetcount']),

        // Flag (boolean) values
        'const_recaptcha_enable' => ($inputs['set_const_recaptcha_enable'] == 'on'),
        'const_recaptcha_serverip_as_hostname' => ($inputs['set_const_recaptcha_serverip_as_hostname'] == 'on'),
        'uni_mailactivationneeded' => ($inputs['set_uni_mailactivationneeded'] == 'on'),
        'uni_telemetryenable' => ($inputs['set_uni_telemetryenable'] == 'on'),
        'uni_noobprt_enable' => ($inputs['set_uni_noobprt_enable'] == 'on'),
        'uni_antifarmenable' => ($inputs['set_uni_antifarmenable'] == 'on'),
        'uni_antibashenable' => ($inputs['set_uni_antibashenable'] == 'on'),
    ];

    return $normalized;
}

?>
