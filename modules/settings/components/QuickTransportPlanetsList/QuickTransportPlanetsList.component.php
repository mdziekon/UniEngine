<?php

namespace UniEngine\Engine\Modules\Settings\Components\QuickTransportPlanetsList;

/**
 * @param object $props
 * @param number $props['userId']
 * @param number $props['currentMainPlanetId']
 *
 * @return object $result
 * @return string $result['componentHTML']
 */
function render($props) {
    $userId = $props['userId'];
    $currentMainPlanetId = $props['currentMainPlanetId'];

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $tplBodyCache = [
        'optionBody' => $localTemplateLoader('optionBody'),
    ];

    $getPlanetsQuery = (
        "SELECT " .
        "`id`, `name`, `galaxy`, `system`, `planet` " .
        "FROM {{table}} " .
        "WHERE " .
        "`id_owner` = {$userId} AND " .
        "`planet_type` = 1 " .
        ";"
    );
    $getPlanetsResult = doquery($getPlanetsQuery, 'planets');

    $optionElementsHTML = mapQueryResults($getPlanetsResult, function ($planet) use (&$tplBodyCache, $currentMainPlanetId) {
        $componentTplData = [
            'planetData_id' => $planet['id'],
            'planetData_isSelectedAttr' => (
                $planet['id'] == $currentMainPlanetId ?
                    'selected' :
                    ''
            ),
            'planetData_name' => $planet['name'],
            'planetData_galaxy' => $planet['galaxy'],
            'planetData_system' => $planet['system'],
            'planetData_planetPos' => $planet['planet'],
        ];

        $componentHTML = parsetemplate(
            $tplBodyCache['optionBody'],
            $componentTplData
        );

        return $componentHTML;
    });

    $componentHTML = implode('', $optionElementsHTML);

    return [
        'componentHTML' => $componentHTML
    ];
}

?>
