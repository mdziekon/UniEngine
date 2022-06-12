<?php

namespace UniEngine\Engine\Modules\Settings\Utils\Validators;

function _isKnownOrderedResourceType($resourceType) {
    $knownResourceTypes = [
        'met',
        'cry',
        'deu',
    ];

    return in_array($resourceType, $knownResourceTypes);
}

/**
 * @param array $params
 * @param array $params['input']
 * @param string $params['input']['orderedResourceTypesString']
 */
function validateResourcesOrdering($params) {
    $executor = function ($input, $resultHelpers) {
        $orderedResourceTypesString = $input['orderedResourceTypesString'];

        $orderedResourceTypes = explode(',', $orderedResourceTypesString);

        if (count($orderedResourceTypes) !== 3) {
            return $resultHelpers['createFailure']([
                'code' => 'INVALID_STRING',
            ]);
        }

        $hasUnknownResourceType = array_any(
            $orderedResourceTypes,
            function ($resourceType) {
                return !_isKnownOrderedResourceType($resourceType);
            }
        );

        if ($hasUnknownResourceType) {
            return $resultHelpers['createFailure']([
                'code' => 'INVALID_RESOURCE_TYPE_FOUND',
            ]);
        }

        return $resultHelpers['createSuccess']([]);
    };

    return createFuncWithResultHelpers($executor)($params['input']);
}

?>
