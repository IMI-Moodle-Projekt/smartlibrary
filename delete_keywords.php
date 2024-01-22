<?php
// delete_keywords.php

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once(__DIR__ . '/lib.php');

$entryId = required_param('entryId', PARAM_INT);
$courseId = required_param('courseId', PARAM_INT);
$activityId = optional_param('activityId', 0, PARAM_INT); // Optional parameter for activityId

// Perform the deletion in the database
global $DB;
$DB->delete_records('smartlib_learning_resources', array('id' => $entryId));

// Check if activityId is provided and delete associated activity
if ($activityId > 0) {
    $DB->delete_records('your_activity_table', array('id' => $activityId));
}

// You can perform additional actions here if needed

// Send a success response
echo 'Success';
?>