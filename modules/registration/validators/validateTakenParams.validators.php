<?php

namespace UniEngine\Engine\Modules\Registration\Validators;

//  Arguments
//      - $params (Object)
//          - username (String)
//          - email (String)
//
function validateTakenParams($params) {
    $selectExistingParamsQuery = (
        "SELECT " .
        "`username`, `email` " .
        "FROM {{table}} " .
        "WHERE " .
        "`username` = '{$params['username']}' OR " .
        "`email` = '{$params['email']}' " .
        "LIMIT 2 " .
        ";"
    );
    $selectExistingParamsResult = doquery($selectExistingParamsQuery, 'users');

    $validationResults = [
        'isUsernameTaken' => false,
        'isEmailTaken' => false,
    ];

    if ($selectExistingParamsResult->num_rows <= 0) {
        return $validationResults;
    }

    while ($searchRow = $selectExistingParamsResult->fetch_assoc()) {
        if (strtolower($searchRow['username']) == strtolower($params['username'])) {
            $validationResults['isUsernameTaken'] = true;
        }
        if (strtolower($searchRow['email']) == strtolower($params['email'])) {
            $validationResults['isEmailTaken'] = true;
        }
    }

    return $validationResults;
}

?>
