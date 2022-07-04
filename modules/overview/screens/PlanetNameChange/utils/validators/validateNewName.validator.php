<?php

namespace UniEngine\Engine\Modules\Overview\Screens\PlanetNameChange\Utils\Validators;

/**
 * @param array $params
 * @param array $params['input']
 * @param string $params['input']['newName']
 * @param arrayRef $params['planet']
 */
function validateNewName($params) {
    $planet = &$params['planet'];

    $executor = function ($input, $resultHelpers) use (&$planet) {
        $newName = $input['newName'];

        $currentName = $planet['name'];

        $MIN_LENGTH = 3;
        $MAX_LENGTH = 20;

        if (empty($newName)) {
            return $resultHelpers['createFailure']([
                'code' => 'NEW_NAME_EMPTY',
            ]);
        }
        if ($newName == $currentName) {
            return $resultHelpers['createFailure']([
                'code' => 'NEW_NAME_SAME_AS_OLD',
            ]);
        }

        $newNameLength = strlen($newName);

        if ($newNameLength < $MIN_LENGTH) {
            return $resultHelpers['createFailure']([
                'code' => 'NEW_NAME_TOO_SHORT',
                'params' => [
                    'minLength' => $MIN_LENGTH
                ],
            ]);
        }
        if ($newNameLength > $MAX_LENGTH) {
            return $resultHelpers['createFailure']([
                'code' => 'NEW_NAME_TOO_LONG',
                'params' => [
                    'maxLength' => $MAX_LENGTH
                ],
            ]);
        }
        if (!preg_match(REGEXP_PLANETNAME_ABSOLUTE, $newName)) {
            return $resultHelpers['createFailure']([
                'code' => 'NEW_NAME_INVALID',
            ]);
        }

        return $resultHelpers['createSuccess']([]);
    };

    return createFuncWithResultHelpers($executor)($params['input']);
}

?>
