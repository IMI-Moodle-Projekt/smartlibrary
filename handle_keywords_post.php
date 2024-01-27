<?php
// Include Moodle configuration
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once(__DIR__ . '/lib.php');

// Retrieve the courseid from the query parameters
$courseid = optional_param('courseid', 0, PARAM_INT);

// Check if this is a POST request with the expected parameters
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['keywords'], $_POST['activityid'], $_POST['courseid'])) {
    // Sanitize and retrieve the form data
    $activityId = required_param('activityid', PARAM_INT);
    $newKeywords = required_param('keywords', PARAM_TEXT);
    $courseid = required_param('courseid', PARAM_INT);
    $newKeywords = clean_param($newKeywords, PARAM_TEXT);

    // Determine the source of keywords (input or crawler)
    $source = 'input';

    // Fetch existing record for the activity, source, and course
    $existingRecord = $DB->get_record('smartlib_learning_resources', array(
        'activityid' => $activityId,
        'source' => $source,
        'courseid' => $courseid,
    ));

    if ($existingRecord) {
        // Append new keywords to existing ones with a separator
        $updatedKeywords = $existingRecord->keywords . ", " . $newKeywords;
        // Remove duplicates
        $uniqueKeywords = implode(', ', array_unique(array_map('trim', explode(',', $updatedKeywords))));
        $existingRecord->keywords = $uniqueKeywords;
        $DB->update_record('smartlib_learning_resources', $existingRecord);
    } else {
        // Insert a new record for the activity, source, and course
        $newRecord = new stdClass();
        $newRecord->activityid = $activityId;
        $newRecord->keywords = $newKeywords;
        $newRecord->source = $source;
        $newRecord->courseid = $courseid;
        $DB->insert_record('smartlib_learning_resources', $newRecord);
    }
    $crawler_url = 'http://localhost/local/smartlibrary/crawler.php?courseid=' .  $courseid;
    redirect($crawler_url);
} else {
    // Handle invalid or missing parameters (if needed)
    echo 'Invalid request parameters';
}
