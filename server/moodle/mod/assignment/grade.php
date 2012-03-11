<?php

require_once("../../config.php");

$id   = required_param('id', PARAM_INT);          // Course module ID
$userid = optional_param('userid', 0, PARAM_INT); // Graded user ID (optional)

$PAGE->set_url('/mod/assignment/grade.php', array('id'=>$id));
if (! $cm = get_coursemodule_from_id('assignment', $id)) {
    print_error('invalidcoursemodule');
}

if (! $assignment = $DB->get_record("assignment", array("id"=>$cm->instance))) {
    print_error('invalidid', 'assignment');
}

if (! $course = $DB->get_record("course", array("id"=>$assignment->course))) {
    print_error('coursemisconf', 'assignment');
}

require_login($course, false, $cm);

if (has_capability('mod/assignment:grade', get_context_instance(CONTEXT_MODULE, $cm->id))) {
    if ($userid) {
        redirect('submissions.php?id='.$cm->id.'&userid='.$userid.'&mode=single&filter=0&offset=0');
    } else {
        redirect('submissions.php?id='.$cm->id);
    }
} else {
    // user will view his own submission, parameter $userid is ignored
    redirect('view.php?id='.$cm->id);
}