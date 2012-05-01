<?php

function calendar_get_events_by_course($courseid) {
    global $DB;

    $whereclause = 'courseid = '.$courseid;

    $events = $DB->get_records_select('event', $whereclause, null, 'timestart');
    if ($events === false) {
        $events = array();
    }
    return $events;
}

function forum_get_discussion_byname($name, $forumid, $userid) {
    global $DB;
    
    $discussion = $DB->get_record('forum_discussions', array('name'=>$name, 'forum'=>$forumid, 'userid'=>$userid), '*');
    if(is_null($discussion)){
        return 0;
    } else {
        return $discussion;
    }
}
