<?php

function GalaxyLegendPopup()
{
    global $_Lang, $_GameConfig;

    $TPL = gettemplate('galaxy_legendpopup');
    $Parse = array
    (
        'Legend'                    => $_Lang['Legend'],
        'User_NonActivatedText'        => $_Lang['User_NonActivatedText'],
        'User_NonActivated'            => $_Lang['User_NonActivated'],
        'FleetBlockadeUser'            => $_Lang['FleetBlockadeUser'],
        'Fleet_Blockade_Protected'    => $_Lang['Fleet_Blockade_Protected'],
        'Strong_player'                => $_Lang['Strong_player'],
        'strong_player_shortcut'    => $_Lang['strong_player_shortcut'],
        'Weak_player'                => $_Lang['Weak_player'],
        'weak_player_shortcut'        => $_Lang['weak_player_shortcut'],
        'New_player'                => sprintf($_Lang['New_player'], prettyNumber($_GameConfig['Protection_NewPlayerTime'] / 3600)),
        'new_player_shortcut'        => $_Lang['new_player_shortcut'],
        'Way_vacation'                => $_Lang['Way_vacation'],
        'vacation_shortcut'            => $_Lang['vacation_shortcut'],
        'Pendent_user'                => $_Lang['Pendent_user'],
        'banned_shortcut'            => $_Lang['banned_shortcut'],
        'Inactive_7_days'            => $_Lang['Inactive_7_days'],
        'inactif_7_shortcut'        => $_Lang['inactif_7_shortcut'],
        'Inactive_28_days'            => $_Lang['Inactive_28_days'],
        'inactif_28_shortcut'        => $_Lang['inactif_28_shortcut'],
    );
    return parsetemplate($TPL, $Parse);
}

?>
