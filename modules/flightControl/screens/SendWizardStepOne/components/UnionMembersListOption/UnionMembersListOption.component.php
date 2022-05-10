<?php

namespace UniEngine\Engine\Modules\FlightControl\Components\UnionMembersListOption;

use UniEngine\Engine\Includes\Helpers\Common\Collections;

//  Arguments
//      - $props (Object)
//          - memberId (String)
//          - memberDetails (Object)
//
//  Returns: Object
//      - listOptionType (String enum: 'UsersInvited' | 'Users2Invite')
//      - componentHTML (String)
//
function render($props) {
    global $_Lang;

    $memberId = $props['memberId'];
    $memberDetails = $props['memberDetails'];

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);
    $tplBodyCache = [
        'body' => $localTemplateLoader('body'),
    ];

    $canBeMovedOnList = (
        $memberDetails['canmove'] &&
        $memberDetails['status'] != $_Lang['fl_acs_joined']
    );

    $userLabelParts = Collections\compact([
        $memberDetails['name'],
        (
            !empty($memberDetails['status']) ?
                " ({$memberDetails['status']})" :
                null
        ),
    ]);
    $userLabel = implode(' ', $userLabelParts);

    $componentTPLData = [
        'userId' => $memberId,
        'isDisabledProp' => (
            !$canBeMovedOnList ?
                ' disabled' :
                ''
        ),
        'userLabel' => $userLabel,
    ];

    return [
        'listOptionType' => (
            $memberDetails['place'] == 1 ?
                'UsersInvited' :
                'Users2Invite'
        ),
        'componentHTML' => parsetemplate($tplBodyCache['body'], $componentTPLData),
    ];
}

?>
