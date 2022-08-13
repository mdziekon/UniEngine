<?php

namespace UniEngine\Engine\Modules\Info\Components\QuantumGateState;

/**
 * @param array $props
 * @param arrayRef $props['planet']
 * @param number $props['currentTimestamp']
 */
function render($props) {
    global $_EnginePath, $_Lang;

    $planet = &$props['planet'];
    $currentTimestamp = $props['currentTimestamp'];

    include_once("{$_EnginePath}/includes/functions/InsertJavaScriptChronoApplet.php");

    $nextPossibleUseTimestamp = ($planet['quantumgate_lastuse'] + (QUANTUMGATE_INTERVAL_HOURS * TIME_HOUR)) - $currentTimestamp;
    $nextPossibleUseTimestamp = keepInRange($nextPossibleUseTimestamp, 0, PHP_INT_MAX);

    $content = [];

    if ($nextPossibleUseTimestamp == 0) {
        $content[] = '<span class="lime">' . $_Lang['GateReadyToUse'] . '</span>';
    } else {
        $chronoAppletId = 'quantum';

        $content[] = InsertJavaScriptChronoApplet($chronoAppletId, '', $nextPossibleUseTimestamp);
        $content[] = '<span class="orange">' . $_Lang['GateReadyToUseIn'] . ':</span>';
        $content[] = '<br/>';
        $content[] = '<span id="bxx' . $chronoAppletId . '">' . pretty_time($nextPossibleUseTimestamp, true) . '</span>';
    }

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $componentHTML = parsetemplate(
        $localTemplateLoader('body'),
        [
            'content' => implode('', $content),
        ]
    );

    return [
        'componentHTML' => $componentHTML,
    ];
}

?>
