<?php

namespace UniEngine\Engine\Modules\Development\Components\GridViewElementCard\UpgradeProductionChange;

//  Arguments
//      - $props (Object)
//          - elementID (String)
//          - user (Object)
//          - planet (Object)
//          - timestamp (Number)
//          - elementDetails (Object)
//              - currentLevel (Number)
//              - queueLevelModifier (Number)
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_Lang;

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $tplBodyCache = [
        'production_change_row' => $localTemplateLoader('production_change_row'),
    ];

    $elementID = $props['elementID'];
    $user = $props['user'];
    $planet = $props['planet'];
    $timestamp = $props['timestamp'];
    $elementDetails = $props['elementDetails'];

    $elementCurrentLevel = $elementDetails['currentLevel'];
    $elementQueueLevelModifier = $elementDetails['queueLevelModifier'];

    $elementQueuedLevel = ($elementCurrentLevel + $elementQueueLevelModifier);
    $elementNextLevelToQueue = ($elementQueuedLevel + 1);

    $resourceLabels = [
        'metal'         => $_Lang['Metal'],
        'crystal'       => $_Lang['Crystal'],
        'deuterium'     => $_Lang['Deuterium'],
        'energy'        => $_Lang['Energy'],
        'energy_max'    => $_Lang['Energy'],
        'darkEnergy'    => $_Lang['DarkEnergy']
    ];

    $elementProductionChangeRows = [];

    // Calculate theoretical production increase
    $thisLevelProduction = getElementProduction(
        $elementID,
        $planet,
        $user,
        [
            'useCurrentBoosters' => true,
            'currentTimestamp' => $timestamp,
            'customLevel' => $elementQueuedLevel,
            'customProductionFactor' => 10
        ]
    );
    $nextLevelProduction = getElementProduction(
        $elementID,
        $planet,
        $user,
        [
            'useCurrentBoosters' => true,
            'currentTimestamp' => $timestamp,
            'customLevel' => $elementNextLevelToQueue,
            'customProductionFactor' => 10
        ]
    );

    foreach ($nextLevelProduction as $resourceKey => $nextLevelResourceProduction) {
        $difference = ($nextLevelResourceProduction - $thisLevelProduction[$resourceKey]);

        if ($difference == 0) {
            continue;
        }

        $differenceFormatted = prettyNumber($difference);
        $label = $resourceLabels[$resourceKey];

        $elementProductionChangeRows[] = parsetemplate(
            $tplBodyCache['production_change_row'],
            [
                'Label' => $label,
                'ValueClasses' => classNames([
                    'lime' => ($difference >= 0),
                    'red' => ($difference < 0),
                ]),
                'Value' => (
                    $difference >= 0 ?
                    ('+' . $differenceFormatted) :
                    $differenceFormatted
                )
            ]
        );
    }

    $componentHTML = implode('', $elementProductionChangeRows);

    return [
        'componentHTML' => $componentHTML
    ];
}

?>
