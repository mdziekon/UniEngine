<?php

namespace UniEngine\Engine\Modules\Messages\Utils;

/**
 * Creates new subject by increasing reply counter on the previous subject.
 *
 * @param array $params
 * @param string $params['previousSubject']
 * @param number $params['replyCounter']
 */
function createReplyMessageSubject($params) {
    global $_Lang;

    $previousSubject = $params['previousSubject'];
    $replyCounter = $params['replyCounter'];

    $cleanSubject = preg_replace(
        '#' . $_Lang['mess_answer_prefix'] . '\[[0-9]{1,}\]\: #si',
        '',
        $previousSubject
    );
    $newReplySubject = "{$_Lang['mess_answer_prefix']} [{$replyCounter}]: {$cleanSubject}";

    return $newReplySubject;
}

?>
