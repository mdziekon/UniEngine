<?php

function InsertGalaxyScripts($additionalTemplateData) {
    $template = gettemplate('galaxy_scripts');
    $templateData = [];
    $jsAJAXCodeMsgs = [];

    $galaxyAJAXLang = includeLang('galaxy_ajax', true);

    foreach ($galaxyAJAXLang['ajax_code_msgs'] as $codeID => $codeMsg) {
        $jsAJAXCodeMsgs[] = "RespCodes['{$codeID}'] = '{$codeMsg}';";
    }

    $templateData = array_merge(
        $templateData,
        [
            'Insert_ReponseCodes' => implode("\n", $jsAJAXCodeMsgs)
        ],
        $galaxyAJAXLang,
        $additionalTemplateData
    );

    return parsetemplate($template, $templateData);
}

?>
