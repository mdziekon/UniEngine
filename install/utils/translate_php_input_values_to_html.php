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

    foreach ($normalizedInputs as $key => $value) {
        $htmlValue = $value;

        if (
            !isset($originalInput['set_' . $key]) ||
            $originalInput['set_' . $key] == ''
        ) {
            $htmlValue = '';

            $htmlValues[$key] = $htmlValue;
            continue;
        }

        if (in_array($key, $checkboxes)) {
            $htmlValue = ($value ? "checked" : "");
        }

        $htmlValues[$key] = $htmlValue;
    }

    return $htmlValues;
}

?>
