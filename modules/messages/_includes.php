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
    include($includePath . './utils/batchDeleteMessages.utils.php');
    include($includePath . './utils/batchMessageUpdates.utils.php');
    include($includePath . './utils/buildMessageDetails.utils.php');
    include($includePath . './utils/createReplyMessageSubject.utils.php');
    include($includePath . './utils/fetchFormDataForReply.utils.php');
    include($includePath . './utils/fetchRecipientDataByUserId.utils.php');
    include($includePath . './utils/fetchRecipientDataByUsername.utils.php');
    include($includePath . './utils/getMessageCopyId.utils.php');
    include($includePath . './utils/normalizeFormData.utils.php');
    include($includePath . './utils/sendMessage.utils.php');
    include($includePath . './validators/validateWithIgnoreSystem.validators.php');

});

?>
