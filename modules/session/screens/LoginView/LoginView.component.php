<?php

namespace UniEngine\Engine\Modules\Session\Screens\LoginView;

//  Arguments
//      - $props (Object)
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    includeLang('login');

    // Handle user input
    // $cmdResult = Session\Input\UserCommands\handleCommands(
    //     $input,
    //     []
    // );
    // if ($cmdResult['isSuccess']) {
    //     // TODO: do something
    // } else {
    //     // TODO: collect errors to display
    // }

    $viewProps = [];
    $viewComponent = Components\LoginForm\render($viewProps);

    return [
        'componentHTML' => $viewComponent['componentHTML'],
    ];
}

?>
