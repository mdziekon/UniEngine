<?php

namespace UniEngine\Engine\Modules\Overview\Screens\Overview\Components\VacationInfoBox;

/**
 * @param array $props
 * @param arrayRef $props['user']
 */
function render($props) {
    global $_Lang;

    if (!isOnVacation($props['user'])) {
        return [
            'componentHTML' => '',
        ];
    }

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $componentHTML = parsetemplate(
        $localTemplateLoader('body'),
        $_Lang
    );

    return [
        'componentHTML' => $componentHTML,
    ];
}

?>
