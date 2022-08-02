<?php

namespace UniEngine\Engine\Modules\Info\Components\TeleportTargetMoonsList;

/**
 * @param array $props
 * @param arrayRef $props['planet']
 * @param arrayRef $props['user']
 */
function render($props) {
    global $_Vars_GameElements;

    $planet = &$props['planet'];
    $user = &$props['user'];

    $TELEPORT_ELEMENT_ID = 43;
    $TELEPORT_ELEMENT_KEY = $_Vars_GameElements[$TELEPORT_ELEMENT_ID];

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);
    $targetOptionTpl = $localTemplateLoader('targetOption');

    $getOtherMoonsQuery = (
        "SELECT " .
        "`id`, `galaxy`, `system`, `planet`, `name`, " .
        "`{$TELEPORT_ELEMENT_KEY}`, `last_jump_time` " .
        "FROM {{table}} " .
        "WHERE " .
        "`id_owner` = {$user['id']} AND " .
        "`id` != {$planet['id']} AND " .
        "`planet_type` = 3 " .
        ";"
    );
    $otherMoonsResult = doquery($getOtherMoonsQuery, 'planets');
    $otherMoons = mapQueryResults($otherMoonsResult, function ($moonEntry) {
        return $moonEntry;
    });
    $otherMoons = array_filter($otherMoons, function ($moonEntry) use ($TELEPORT_ELEMENT_KEY) {
        return $moonEntry[$TELEPORT_ELEMENT_KEY] > 0;
    });

    if (empty($otherMoons)) {
        return [
            'componentHTML' => '',
        ];
    }

    $otherMoonOptions = array_map_withkeys(
        $otherMoons,
        function ($moonEntry) use (&$targetOptionTpl) {
            $nextJumpWaitTime = GetNextJumpWaitTime($moonEntry);

            if (!empty($nextJumpWaitTime['string'])) {
                $nextJumpWaitTime['string'] = trim($nextJumpWaitTime['string']);
                $nextJumpWaitTime['string'] = " ({$nextJumpWaitTime['string']})";
            }

            return parsetemplate($targetOptionTpl, [
                'MoonID' => $moonEntry['id'],
                'Galaxy' => $moonEntry['galaxy'],
                'System' => $moonEntry['system'],
                'Planet' => $moonEntry['planet'],
                'Name' => $moonEntry['name'],
                'TimeString' => $nextJumpWaitTime['string']
            ]);
        }
    );

    return [
        'componentHTML' => implode('', $otherMoonOptions),
    ];
}

?>
