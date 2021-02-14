<?php

namespace UniEngine\Engine\Modules\Session\Utils\Redirects;

function redirectToOverview() {
    header("Location: ./overview.php");
}

function permaRedirectToMainDomain() {
    header("HTTP/1.1 301 Moved Permanently");
    header('Location: ' . GAMEURL_STRICT);
}

?>
