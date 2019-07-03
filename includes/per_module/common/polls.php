<?php

function fetchObligatoryPollsCount ($userID) {
    $Query_SelectPolls = '';
    $Query_SelectPolls .= "SELECT COUNT(`polls`.`id`) AS `polls_with_no_vote` FROM {{table}} AS `polls` ";
    $Query_SelectPolls .= "LEFT JOIN `{{prefix}}poll_votes` AS `votes` ";
    $Query_SelectPolls .= "  ON `votes`.`poll_id` = `polls`.`id` AND `votes`.`user_id` = {$userID} ";
    $Query_SelectPolls .= "WHERE `polls`.`open` = 1 AND `polls`.`obligatory` = 1 AND `votes`.`id` IS NULL;";

    $SelectObligatoryPolls = doquery($Query_SelectPolls, 'polls', true);

    return intval($SelectObligatoryPolls['polls_with_no_vote'], 10);

    // if ($SelectObligatoryPolls->num_rows == 0) {
    //     return 0;
    // }

    // if($SelectObligatoryPolls->num_rows > 0)
    // {
    //     $PollsCount = 0;
    //     while($SelectObligatoryPollsData = $SelectObligatoryPolls->fetch_assoc())
    //     {
    //         if($SelectObligatoryPollsData['vote_id'] <= 0)
    //         {
    //             $PollsCount += 1;
    //         }
    //     }
    //     if($PollsCount > 0)
    //     {
    //         message(sprintf($_Lang['YouHaveToVoteInSurveys'], $PollsCount), $_Lang['SystemInfo'], 'polls.php', 10);
    //     }
    // }
}

?>
