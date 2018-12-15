<?php

function GalaxyRowPos($Galaxy, $System, $Planet)
{
    static $TPL = false;

    if($TPL === false)
    {
        $TPL = gettemplate('galaxy_row_pos');
    }

    $Parse = array
    (
        'Galaxy' => $Galaxy,
        'System' => $System,
        'Planet' => $Planet,
    );

    return parsetemplate($TPL, $Parse);
}

?>
