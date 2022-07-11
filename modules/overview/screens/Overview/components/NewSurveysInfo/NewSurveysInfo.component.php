<?php

namespace UniEngine\Engine\Modules\Overview\Screens\Overview\Components\NewSurveysInfo;

/**
 * @param array $props
 * @param number $props['userId']
 */
function render($props) {
    global $_Lang;

    $userId = $props['userId'];

    $getSurveysDataQuery = (
        "SELECT " .
        "`surveys`.`id`, `votes`.`id` AS `vote_id` " .
        "FROM {{table}} AS `surveys` " .
        "LEFT JOIN {{prefix}}poll_votes AS `votes` " .
        "ON " .
        "`votes`.`poll_id` = `surveys`.`id` AND " .
        "`votes`.`user_id` = {$userId} " .
        "WHERE " .
        "`surveys`.`open` = 1 " .
        "ORDER BY `surveys`.`time` DESC " .
        ";"
    );
    $surveysData = doquery($getSurveysDataQuery, 'polls');

    $surveysWithoutVotes = mapQueryResults($surveysData, function ($surveyEntry) {
        $hasUserVoted = ($surveyEntry['vote_id'] > 0);

        return (
            $hasUserVoted ?
                0 :
                1
        );
    });
    $surveysWithoutVotesCount = array_sum($surveysWithoutVotes);

    if ($surveysWithoutVotesCount == 0) {
        return [
            'componentHTML' => '',
        ];
    }

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    /**
     * TODO: Use a better translation system to support more languages.
     */
    $content = vsprintf(
        $_Lang['PollBox_You_can_vote_in_new_polls'],
        (
            $surveysWithoutVotesCount > 1 ?
                $_Lang['PollBox_More'] :
                $_Lang['PollBox_One']
        )
    );

    $tplBodyParams = [
        'content' => $content,
    ];

    $componentHTML = parsetemplate(
        $localTemplateLoader('body'),
        $tplBodyParams
    );

    return [
        'componentHTML' => $componentHTML,
    ];
}

?>
