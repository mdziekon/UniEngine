<?php

namespace UniEngine\Engine\Modules\Overview\Screens\Overview\Components\GiftItemsInfoBox;

/**
 * @param array $props
 * @param number $props['userId']
 */
function render($props) {
    global $_Lang;

    $userId = $props['userId'];

    $getGiftItemsDataQuery = (
        "SELECT " .
        "COUNT(`ID`) as `Count` " .
        "FROM {{table}} " .
        "WHERE " .
        "`UserID` = {$userId} AND " .
        "`Used` = false " .
        ";"
    );
    $giftItemsData = doquery($getGiftItemsDataQuery, 'premium_free', true);

    if ($giftItemsData['Count'] == 0) {
        return [
            'componentHTML' => '',
        ];
    }

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $content = sprintf(
        $_Lang['FreePremItem_Text'],
        $giftItemsData['Count']
    );

    $componentHTML = parsetemplate(
        $localTemplateLoader('body'),
        [
            'content' => $content,
        ]
    );

    return [
        'componentHTML' => $componentHTML,
    ];
}

?>
