<?php

// TODO: Migrate to IIFE once PHP 5 support is removed
call_user_func(function () {
    global $_EnginePath;

    $includePath = $_EnginePath . 'modules/messages/';

    include($includePath . './commands/batchDeleteMessagesByID.commands.php');
    include($includePath . './commands/batchDeleteMessagesOlderThan.commands.php');
    include($includePath . './commands/batchMarkMessagesAsRead.commands.php');
    include($includePath . './input/batchActions.userCommands.php');
    include($includePath . './input/sendMessage.userCommands.php');
    include($includePath . './screens/CategoriesListView/CategoriesListView.component.php');
    include($includePath . './screens/CategoriesListView/utils/fetchMessagesCounters.utils.php');
    include($includePath . './utils/batchDeleteMessages.utils.php');
    include($includePath . './utils/batchMessageUpdates.utils.php');
    include($includePath . './utils/buildMessageDetails.utils.php');
    include($includePath . './utils/createReplyMessageSubject.utils.php');
    include($includePath . './utils/fetchFormDataForReply.utils.php');
    include($includePath . './utils/fetchOriginalMessagesForRefSystem.utils.php');
    include($includePath . './utils/fetchRecipientDataByUserId.utils.php');
    include($includePath . './utils/fetchRecipientDataByUsername.utils.php');
    include($includePath . './utils/fetchUserMessages.utils.php');
    include($includePath . './utils/fetchUserMessagesCount.utils.php');
    include($includePath . './utils/fetchUserThreads.utils.php');
    include($includePath . './utils/formatUserMessageDetails.utils.php');
    include($includePath . './utils/getConstants.utils.php');
    include($includePath . './utils/getMessageCopyId.utils.php');
    include($includePath . './utils/getUnreadMessageIds.utils.php');
    include($includePath . './utils/messageObject.utils.php');
    include($includePath . './utils/normalizeFormData.utils.php');
    include($includePath . './utils/parseThreadedMessages.utils.php');
    include($includePath . './utils/sendMessage.utils.php');
    include($includePath . './utils/updateMessagesReadStatus.utils.php');
    include($includePath . './validators/validateWithIgnoreSystem.validators.php');

});

?>
