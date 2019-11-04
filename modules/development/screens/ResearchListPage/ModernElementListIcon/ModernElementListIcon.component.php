<?php

namespace UniEngine\Engine\Modules\Development\Screens\ResearchListPage\ModernElementListIcon;

//  Arguments
//      - $props (Object)
//          - elementID (String)
//          - elementCurrentLevel (Number)
//          - elementQueueLevelModifier (Number)
//          - isInQueue (Boolean)
//          - canStartUpgrade (Boolean)
//          - canQueueUpgrade (Boolean)
//          - upgradeBlockersList (Array<String>)
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_Lang, $_SkinPath;

    $disabledClassTPLBody = gettemplate('buildings_compact_list_disabled');
    $partiallyDisabledElementOverlay = parsetemplate($disabledClassTPLBody, [ 'AddOpacity' => 'dPart' ]);
    $fullyDisabledElementOverlay = parsetemplate($disabledClassTPLBody, [ 'AddOpacity' => 'dPart' ]);

    $tplBodyCache = [
        'list_element' => gettemplate('buildings_compact_list_element_lab'),
        'list_levelmodif' => gettemplate('buildings_compact_list_levelmodif')
    ];

    $elementID = $props['elementID'];
    $elementCurrentLevel = $props['elementCurrentLevel'];
    $elementQueueLevelModifier = $props['elementQueueLevelModifier'];
    $isInQueue = $props['isInQueue'];
    $canStartUpgrade = $props['canStartUpgrade'];
    $canQueueUpgrade = $props['canQueueUpgrade'];
    $upgradeBlockersList = $props['upgradeBlockersList'];

    $elementName = $_Lang['tech'][$elementID];

    $isElementDisabledOverlay = (
        $canStartUpgrade ?
        '' :
        (
            $canQueueUpgrade ?
            $partiallyDisabledElementOverlay :
            $fullyDisabledElementOverlay
        )
    );

    $buildButtonColorClass = (
        $canStartUpgrade ?
        'buildDo_Green' :
        (
            $canQueueUpgrade ?
            'buildDo_Orange' :
            'buildDo_Gray'
        )
    );
    $hideQuickBuildButtonClass = (
        !$canQueueUpgrade ?
        'hide' :
        ''
    );

    $elementLevelModifierComponentHTML = '';

    if ($isInQueue) {
        $elementLevelModifierTPLData = [
            'modColor' => null,
            'modText' => null
        ];

        if ($elementQueueLevelModifier < 0) {
            $elementLevelModifierTPLData['modColor'] = 'red';
            $elementLevelModifierTPLData['modText'] = prettyNumber($elementQueueLevelModifier);
        } else if ($elementQueueLevelModifier == 0) {
            $elementLevelModifierTPLData['modColor'] = 'orange';
            $elementLevelModifierTPLData['modText'] = '0';
        } else {
            $elementLevelModifierTPLData['modColor'] = 'lime';
            $elementLevelModifierTPLData['modText'] = '+' . prettyNumber($elementQueueLevelModifier);
        }

        $elementLevelModifierComponentHTML = parsetemplate(
            $tplBodyCache['list_levelmodif'],
            $elementLevelModifierTPLData
        );
    }

    $componentTPLData = [
        'SkinPath'              => $_SkinPath,

        'ElementID'             => $elementID,
        'ElementName'           => $elementName,
        'ElementDisabled'       => $isElementDisabledOverlay,
        'ElementDisableReason'  => end($upgradeBlockersList),
        'BuildButtonColor'      => $buildButtonColorClass,
        'HideQuickBuildButton'  => $hideQuickBuildButtonClass,
        'ElementRealLevel'      => prettyNumber($elementCurrentLevel),
        'ElementLevelModif'     => $elementLevelModifierComponentHTML
    ];

    $componentHTML = parsetemplate($tplBodyCache['list_element'], $componentTPLData);

    return [
        'componentHTML' => $componentHTML
    ];
}

?>
