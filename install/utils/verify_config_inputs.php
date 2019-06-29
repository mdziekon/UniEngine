<?php

function verify_config_inputs($normalizedInputs) {
    $isValid = true;
    $tests = [
        'uni_gamespeed' => true,
        'uni_fleetspeed' => true,
        'uni_resourcespeed' => true,
        'uni_motherfields' => true,
        'uni_fleetdebris' => true,
        'uni_defensedebris' => true,
        'uni_missiledebris' => true,
        'uni_noobprt_basictime' => true,
        'uni_noobprt_basicmultiplier' => true,
        'uni_noobprt_remove' => true,
        'uni_noobprt_idledays' => true,
        'uni_noobprt_firstlogin' => true,
        'uni_antifarmratio' => true,
        'uni_antifarmtotalcount' => true,
        'uni_antifarmplanetcount' => true,
        'uni_antibashinterval' => true,
        'uni_antibashtotalcount' => true,
        'uni_antibashplanetcount' => true,
    ];

    if ($normalizedInputs['uni_gamespeed'] <= 0) {
        $isValid = false;
        $tests['uni_gamespeed'] = false;
    }
    if ($normalizedInputs['uni_fleetspeed'] <= 0) {
        $isValid = false;
        $tests['uni_fleetspeed'] = false;
    }
    if ($normalizedInputs['uni_resourcespeed'] <= 0) {
        $isValid = false;
        $tests['uni_resourcespeed'] = false;
    }
    if ($normalizedInputs['uni_motherfields'] <= 0) {
        $isValid = false;
        $tests['uni_motherfields'] = false;
    }
    if (
        $normalizedInputs['uni_fleetdebris'] < 0 ||
        $normalizedInputs['uni_fleetdebris'] > 100
    ) {
        $isValid = false;
        $tests['uni_fleetdebris'] = false;
    }
    if (
        $normalizedInputs['uni_defensedebris'] < 0 ||
        $normalizedInputs['uni_defensedebris'] > 100
    ) {
        $isValid = false;
        $tests['uni_defensedebris'] = false;
    }
    if (
        $normalizedInputs['uni_missiledebris'] < 0 ||
        $normalizedInputs['uni_missiledebris'] > 100
    ) {
        $isValid = false;
        $tests['uni_missiledebris'] = false;
    }

    if ($normalizedInputs['uni_noobprt_enable']) {
        if ($normalizedInputs['uni_noobprt_basictime'] <= 0) {
            $isValid = false;
            $tests['uni_noobprt_basictime'] = false;
        }
        if ($normalizedInputs['uni_noobprt_basicmultiplier'] <= 0) {
            $isValid = false;
            $tests['uni_noobprt_basicmultiplier'] = false;
        }
        if ($normalizedInputs['uni_noobprt_remove'] <= 0) {
            $isValid = false;
            $tests['uni_noobprt_remove'] = false;
        }
        if ($normalizedInputs['uni_noobprt_idledays'] <= 0) {
            $isValid = false;
            $tests['uni_noobprt_idledays'] = false;
        }
        if ($normalizedInputs['uni_noobprt_firstlogin'] < 0) {
            $isValid = false;
            $tests['uni_noobprt_firstlogin'] = false;
        }
    }
    if ($normalizedInputs['uni_antifarmenable']) {
        if ($normalizedInputs['uni_antifarmratio'] <= 0) {
            $isValid = false;
            $tests['uni_antifarmratio'] = false;
        }
        if ($normalizedInputs['uni_antifarmtotalcount'] <= 0) {
            $isValid = false;
            $tests['uni_antifarmtotalcount'] = false;
        }
        if ($normalizedInputs['uni_antifarmplanetcount'] <= 0) {
            $isValid = false;
            $tests['uni_antifarmplanetcount'] = false;
        }
    }
    if ($normalizedInputs['uni_antibashenable']) {
        if ($normalizedInputs['uni_antibashinterval'] <= 0) {
            $isValid = false;
            $tests['uni_antibashinterval'] = false;
        }
        if ($normalizedInputs['uni_antibashtotalcount'] <= 0) {
            $isValid = false;
            $tests['uni_antibashtotalcount'] = false;
        }
        if ($normalizedInputs['uni_antibashplanetcount'] <= 0) {
            $isValid = false;
            $tests['uni_antibashplanetcount'] = false;
        }
    }

    return [
        'isValid' => $isValid,
        'tests' => $tests
    ];
}

?>
