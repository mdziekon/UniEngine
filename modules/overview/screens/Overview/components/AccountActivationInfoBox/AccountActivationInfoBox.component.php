<?php

namespace UniEngine\Engine\Modules\Overview\Screens\Overview\Components\AccountActivationInfoBox;

/**
 * @param array $props
 * @param arrayRef $props['user']
 */
function render($props) {
    global $_Lang;

    if (isUserAccountActivated($props['user'])) {
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
