<?php

namespace UniEngine\Engine\Modules\Info\Components\BuildingDestructionSection;

use UniEngine\Engine\Includes\Helpers\World;

/**
 * @param array $props
 * @param string $props['elementId']
 * @param arrayRef $props['planet']
 * @param arrayRef $props['user']
 */
function render($props) {
    global $_Lang, $_Vars_GameElements;

    $elementId = $props['elementId'];
    $planet = &$props['planet'];
    $user = &$props['user'];

    if (!World\Elements\isDowngradeable($elementId)) {
        return [
            'componentHTML' => '',
        ];
    }

    $elementLevel = World\Elements\getElementCurrentLevel($elementId, $planet, $user);

    if ($elementLevel <= 0) {
        return [
            'componentHTML' => '',
        ];
    }

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $destroyResourceRequirements = GetBuildingPrice($user, $planet, $elementId, true, true);
    $destroyTime = GetBuildingTime($user, $planet, $elementId) / 2;

    $tplProps = [
        'destroyurl'    => "buildings.php?cmd=destroy&building={$elementId}",
        'levelvalue'    => $elementLevel,
        'destroytime'   => pretty_time($destroyTime),
        'nfo_metal'     => $_Lang['Metal'],
        'nfo_crysta'    => $_Lang['Crystal'],
        'nfo_deuter'    => $_Lang['Deuterium'],
        'metal'         => prettyNumber($destroyResourceRequirements['metal']),
        'crystal'       => prettyNumber($destroyResourceRequirements['crystal']),
        'deuterium'     => prettyNumber($destroyResourceRequirements['deuterium']),
        'Met_Color'     => (
            ($destroyResourceRequirements['metal'] > $planet['metal']) ?
                'red' :
                'lime'
        ),
        'Cry_Color'     => (
            ($destroyResourceRequirements['crystal'] > $planet['crystal']) ?
                'red' :
                'lime'
        ),
        'Deu_Color'     => (
            ($destroyResourceRequirements['deuterium'] > $planet['deuterium']) ?
                'red' :
                'lime'
        ),
    ];

    return [
        'componentHTML' => parsetemplate(
            $localTemplateLoader('body'),
            array_merge($_Lang, $tplProps)
        ),
    ];
}

?>
