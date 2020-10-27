<?php

namespace UniEngine\Engine\Modules\Messages\Utils;

function parseThreadedMessages($params) {
    global $_Lang;

    $displayedCategoryId = $params['displayedCategoryId'];
    $messageDetails = &$params['messageDetails'];
    $threadLengthsByThreadId = $params['threadLengthsByThreadId'];

    $messageTplBody = gettemplate('message_mailbox_body');
    $threadContainerTplBody = gettemplate('message_mailbox_threaded');

    $threadId = $messageDetails['Thread_ID'];
    $threadLength = $threadLengthsByThreadId[$threadId];

    $fetchedMessagesInThread = (
        1 +
        count($messageDetails['inThreadMessages'])
    );

    $hasFetchedMessagesToShow = ($fetchedMessagesInThread > 1);
    $hasMessagesYetToShow = ($fetchedMessagesInThread < $threadLength);

    if (
        !$hasFetchedMessagesToShow &&
        !$hasMessagesYetToShow
    ) {
        return [
            'contentHTML' => '',
        ];
    }

    $fetchedThreadMessagesIds = array_map(
        function ($additionaMessageData) {
            return $additionaMessageData['id'];
        },
        $messageDetails['inThreadMessages']
    );
    $fetchedThreadMessagesRows = array_map(
        function ($additionaMessageData) use ($messageTplBody) {
            return parsetemplate($messageTplBody, $additionaMessageData);
        },
        $messageDetails['inThreadMessages']
    );

    $threadContainerTplData = [
        'Lang_Expand' => $_Lang['Action_Expand'],
        'Lang_Collapse' => $_Lang['Action_Collapse'],
        'Lang_Loading' => $_Lang['Action_ThreadLoading'],
        'Insert_ThreadID' => $threadId,
        'Insert_MaxMsgID' => $messageDetails['CurrMSG_ID'],
        'Insert_CatID' => $displayedCategoryId,
        'Insert_ExcludeIDs' => implode('_', $fetchedThreadMessagesIds),
        'Insert_Msgs' => implode('', $fetchedThreadMessagesRows),
        'Insert_HideParsed' => (
            !$hasFetchedMessagesToShow ?
                'hide' :
                ''
        ),
        'Insert_HideExpand' => (
            !$hasMessagesYetToShow ?
                'hide' :
                ''
        ),
        'Insert_Count' => prettyNumber($threadLength),
    ];

    return [
        'contentHTML' => parsetemplate($threadContainerTplBody, $threadContainerTplData),
    ];
}

?>
