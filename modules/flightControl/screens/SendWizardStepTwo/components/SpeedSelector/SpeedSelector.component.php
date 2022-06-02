<?php

namespace UniEngine\Engine\Modules\FlightControl\Screens\SendWizardStepTwo\Components\SpeedSelector;

//  Arguments
//      - $props (Object)
//          - speedOptions (object)
//          - selectedOption (String)
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    $speedOptions = $props['speedOptions'];
    $selectedOption = $props['selectedOption'];

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);
    $tplBodyCache = [
        'optionBody' => trim($localTemplateLoader('optionBody')),
        'optionsSeparator' => trim($localTemplateLoader('optionsSeparator')),
    ];

    $optionsListElements = array_map_withkeys($speedOptions, function ($speedOption) use ($selectedOption, &$tplBodyCache) {
        $label = $speedOption * 10;
        $isSpeedSelected = ($selectedOption == $speedOption);

        return parsetemplate($tplBodyCache['optionBody'], [
            'isSelectedOptionClasses' => (
                $isSpeedSelected ?
                    'setSpeed_Selected setSpeed_Current' :
                    ''
            ),
            'optionValue' => $speedOption,
            'optionLabel' => $label,
        ]);
    });

    $elementsListHTML = implode($tplBodyCache['optionsSeparator'], $optionsListElements);

    return [
        'componentHTML' => $elementsListHTML,
    ];
}

?>
