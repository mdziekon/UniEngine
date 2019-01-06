<?php

function InsertGalaxyScripts($CurrentPlanet)
{
    $TPL = gettemplate('galaxy_scripts');
    $Lang = includeLang('galaxy_ajax', true);
    foreach($Lang as $Key => $Value)
    {
        if(strstr($Key, 'ajax_send_') !== false)
        {
            $Code = str_replace('ajax_send_', '', $Key);
            $Lang['Insert_ReponseCodes'] .= "RespCodes['{$Code}'] = '{$Value}';\n";
        }
    }

    return parsetemplate($TPL, $Lang);
}

?>
