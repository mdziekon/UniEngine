<?php

namespace UniEngine\Engine\Modules\Development\Components\ListViewElementRow\UpgradeProductionChange;

//  Arguments
//      - $props (Object)
//          - elementID (String)
//          - user (Object)
//          - planet (Object)
//          - timestamp (Number)
//          - elementDetails (Object)
//              - currentState (Number)
//              - queueLevelModifier (Number)
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_Lang;

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $tplBodyCache = [
        'resource_production_change' => $localTemplateLoader('resource_production_change'),
    ];

    $elementID = $props['elementID'];
    $user = $props['user'];
    $planet = $props['planet'];
    $timestamp = $props['timestamp'];
    $elementDetails = $props['elementDetails'];

    $elementCurrentState = $elementDetails['currentState'];
    $elementQueueLevelModifier = $elementDetails['queueLevelModifier'];
    $elementQueuedLevel = ($elementCurrentState + $elementQueueLevelModifier);
    $elementNextLevelToQueue = ($elementQueuedLevel + 1);

    $resourceLabels = [
        'metal'         => $_Lang['Metal'],
        'crystal'       => $_Lang['Crystal'],
        'deuterium'     => $_Lang['Deuterium'],
        'energy'        => $_Lang['Energy'],
        'energy_max'    => $_Lang['Energy'],
        'darkEnergy'    => $_Lang['DarkEnergy']
    ];


    $resourceChangesListHTMLs = [];

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

        $resourceProductionChangeDiffFormatted = prettyNumber($difference);

        $resourceProductionChangeTPLData = [
            'ResourceName'              => $resourceLabels[$resourceKey],
            'ResourceStateColorClass'   => classNames([
                'lime' => ($difference >= 0),
                'red' => ($difference < 0),
            ]),
            'ResourceCurrentState'      => (
                $difference >= 0 ?
                ('+' . $resourceProductionChangeDiffFormatted) :
                $resourceProductionChangeDiffFormatted
            )
        ];

        $resourceChangesListHTMLs[] = parsetemplate(
            $tplBodyCache['resource_production_change'],
            $resourceProductionChangeTPLData
        );
    }

    return [
        'componentHTML' => implode('', $resourceChangesListHTMLs)
    ];
}

?>
