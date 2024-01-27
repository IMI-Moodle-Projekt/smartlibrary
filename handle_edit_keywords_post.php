<?php
// Include Moodle configuration
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once(__DIR__ . '/lib.php');

// Retrieve the courseid from the query parameters
$courseid = optional_param('courseid', 0, PARAM_INT);

// Check if this is a POST request with the expected parameters
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_keywords'])) {
    $courseid = required_param('courseid', PARAM_INT);
    $activityid = required_param('activityid', PARAM_INT);
    $editedKeywords = required_param('edited_keywords', PARAM_TEXT);

    // Fetch existing record for the activity, source, and course
    $existingRecord = $DB->get_record('smartlib_learning_resources', array(
        'activityid' => $activityid,
        'source' => 'input',
        'courseid' => $courseid,
    ));

    if ($existingRecord) {
        // Update the keywords in the database
        $existingRecord->keywords = $editedKeywords;
        $DB->update_record('smartlib_learning_resources', $existingRecord);
    } else {
        // Insert a new record if it doesn't exist
        $newRecord = new stdClass();
        $newRecord->activityid = $activityid;
        $newRecord->keywords = $editedKeywords;
        $newRecord->source = 'input';
        $newRecord->courseid = $courseid;
        $DB->insert_record('smartlib_learning_resources', $newRecord);
    }
    $crawler_url = 'http://localhost/local/smartlibrary/crawler.php?courseid=' .  $courseid;
    redirect($crawler_url);
} else {
    // Handle invalid or missing parameters (if needed)
    echo 'Invalid request parameters';
}
