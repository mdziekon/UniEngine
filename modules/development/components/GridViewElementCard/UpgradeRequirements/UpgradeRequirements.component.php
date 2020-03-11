<?php

namespace UniEngine\Engine\Modules\Development\Components\GridViewElementCard\UpgradeRequirements;

use UniEngine\Engine\Includes\Helpers\World\Elements;

//  Arguments
//      - $props (Object)
//          - elementID (String)
//          - user (Object)
//          - planet (Object)
//          - isQueueActive (Boolean)
//          - elementDetails (Object)
//              - currentState (Number)
//              - queueLevelModifier (Number)
//              - hasTechnologyRequirementMet (Boolean)
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_EnginePath, $_Lang;

    include_once($_EnginePath . 'includes/functions/GetElementTechReq.php');

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $tplBodyCache = [
        'body' => $localTemplateLoader('body'),
        'headline_resourcesonly' => $localTemplateLoader('headline_resourcesonly'),
        'headline_resourcesandtech' => $localTemplateLoader('headline_resourcesandtech'),
    ];

    $elementID = $props['elementID'];
    $planet = $props['planet'];
    $user = $props['user'];
    $isQueueActive = $props['isQueueActive'];
    $elementDetails = $props['elementDetails'];

    $elementCurrentState = $elementDetails['currentState'];
    $elementQueueLevelModifier = $elementDetails['queueLevelModifier'];
    $hasTechnologyRequirementMet = $elementDetails['hasTechnologyRequirementMet'];

    $elementQueuedLevel = ($elementCurrentState + $elementQueueLevelModifier);
    $elementNextLevelToQueue = ($elementQueuedLevel + 1);
    $isElementUpgradeableType = Elements\isUpgradeable($elementID);

    // Render subcomponents
    $subcomponentHeadlineHTML = '';
    $subcomponentResources = ResourcesList\render([
        'elementID' => $elementID,
        'user' => $user,
        'planet' => $planet,
        'isQueueActive' => $isQueueActive,
    ]);
    $subcomponentResourcesHTML = $subcomponentResources['componentHTML'];
    $subcomponentTechRequirementsHTML = (
        !$hasTechnologyRequirementMet ?
        GetElementTechReq($user, $planet, $elementID, true) :
        ''
    );

    $subcomponentUpgradeTemplate = (
        $hasTechnologyRequirementMet ?
        $tplBodyCache['headline_resourcesonly'] :
        $tplBodyCache['headline_resourcesandtech']
    );

    $subcomponentHeadlineHTML = parsetemplate(
        $subcomponentUpgradeTemplate,
        [
            'InfoBox_ResRequirements' => (
                $isElementUpgradeableType ?
                $_Lang['InfoBox_ResRequirements'] :
                $_Lang['InfoBox_ResRequirementsShip']
            ),
            'InfoBox_RequirementsFor' => (
                $isElementUpgradeableType ?
                $_Lang['InfoBox_RequirementsFor'] :
                $_Lang['InfoBox_RequirementsForShip']
            ),
            'InfoBox_Requirements_Res' => $_Lang['InfoBox_Requirements_Res'],
            'InfoBox_Requirements_Tech' => $_Lang['InfoBox_Requirements_Tech'],

            'BuildLevel' => (
                $isElementUpgradeableType ?
                prettyNumber($elementNextLevelToQueue) :
                ''
            ),
        ]
    );


    $componentHTML = parsetemplate(
        $tplBodyCache['body'],
        [
            'ElementRequirementsHeadline' => $subcomponentHeadlineHTML,
            'HideResReqDiv' => classNames([
                'hide' => (!$hasTechnologyRequirementMet),
            ]),
            'ElementPriceDiv' => $subcomponentResourcesHTML,
            'ElementTechDiv' => $subcomponentTechRequirementsHTML,
        ]
    );

    return [
        'componentHTML' => $componentHTML
    ];
}

?>
