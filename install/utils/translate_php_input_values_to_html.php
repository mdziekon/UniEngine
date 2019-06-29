<?php

function translate_php_input_values_to_html($normalizedInputs, $originalInput) {
    $htmlValues = [];

    $checkboxes = [
        'const_recaptcha_enable',
        'const_recaptcha_serverip_as_hostname',
        'uni_mailactivationneeded',
        'uni_telemetryenable',
        'uni_noobprt_enable',
        'uni_antifarmenable',
        'uni_antibashenable',
    ];

    $selectInputs = [
        'uni_gamedefaultlang'
    ];

    foreach ($normalizedInputs as $key => $value) {
        $htmlValue = $value;

        if (
            !in_array($key, $selectInputs) &&
            (
                !isset($originalInput['set_' . $key]) ||
                $originalInput['set_' . $key] == ''
            )
        ) {
            $htmlValue = '';

            $htmlValues[$key] = $htmlValue;
            continue;
        }

        if (in_array($key, $checkboxes)) {
            $htmlValue = ($value ? "checked" : "");
        }

        if (in_array($key, $selectInputs)) {
            $htmlValue = 'selected';
            $key = $key . '_' . $value;
        }

        $htmlValues[$key] = $htmlValue;
    }

    return $htmlValues;
}

?>
