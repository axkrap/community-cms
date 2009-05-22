<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

/**
 * Add a user's vote to a poll
 * @global class $db
 * @global class $page
 * @param int $question ID of the question that was responded to
 * @param int $response ID of the answer choice chosen
 * @param string $ip IP of the user that voted
 * @return void
 */
function poll_vote($question,$response,$ip) {
    $question = (int)$question;
    $response = (int)$response;
    if (!eregi('^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$',$ip)) {
        return;
    }
    if ($question == 0 || $response == 0) {
        return;
    }
    $ip = ip2long($ip);
    global $db;
    global $page;
    $vote_query = 'INSERT INTO ' . POLL_RESPONSE_TABLE . '
        (question_id ,answer_id ,value ,ip_addr) VALUES ('.$question.',
        '.$response.', NULL, \''.$ip.'\')';
    $vote_handle = $db->sql_query($vote_query);
    if ($db->error[$vote_handle] === 1) {
        $page->notification .= 'Failed to record your vote.<br />';
    } else {
        $page->notification .= 'Thank you for voting.<br />';
    }
}
?>
