<?php

namespace UniEngine\Engine\Modules\Overview\Screens\Overview\Components\Morale\Utils;

/**
 * @param array $props
 * @param arrayRef $props['user']
 * @param number $props['currentTimestamp']
 */
function getMoraleStatusData($props) {
    global $_Lang;

    $user = &$props['user'];
    $currentTimestamp = $props['currentTimestamp'];

    $userMoraleLevel = $user['morale_level'];

    if ($userMoraleLevel == 0) {
        return [
            'text' => $_Lang['Box_Morale_NoChanges'],
            'globalJS' => null,
        ];
    }

    $moraleSentiment = (
        $userMoraleLevel > 0 ?
            'Pos' :
            'Neg'
    );
    $moraleDroptimeRemaining = $user['morale_droptime'] - $currentTimestamp;

    if ($moraleDroptimeRemaining > 0) {
        return [
            'text' => sprintf(
                $_Lang["Box_Morale_DropStartIn_{$moraleSentiment}"],
                pretty_time(
                    $moraleDroptimeRemaining,
                    true,
                    'D'
                )
            ),
            'globalJS' => InsertJavaScriptChronoApplet(
                'morale',
                '',
                $user['morale_droptime'],
                true
            ),
        ];
    }

    $moraleDropInterval = (
        $userMoraleLevel > 0 ?
            MORALE_DROPINTERVAL_POSITIVE :
            MORALE_DROPINTERVAL_NEGATIVE
    );
    $moraleDropNextTimepoint = (
        ($user['morale_lastupdate'] == 0) ?
            ($user['morale_droptime'] + $moraleDropInterval) :
            ($user['morale_lastupdate'] + $moraleDropInterval)
    );

    return [
        'text' => sprintf(
            $_Lang["Box_Morale_Dropping_{$moraleSentiment}"],
            pretty_time(
                $moraleDropNextTimepoint - $currentTimestamp,
                true,
                'D'
            )
        ),
        'globalJS' => InsertJavaScriptChronoApplet(
            'morale',
            '',
            $moraleDropNextTimepoint,
            true
        ),
    ];
}

?>
