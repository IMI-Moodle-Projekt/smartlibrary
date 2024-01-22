<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once(__DIR__ . '/lib.php');

// Retrieve parameters from the URL
$courseid = optional_param('courseid', 0, PARAM_INT);
$activityid = optional_param('activityid', 0, PARAM_INT);
$keywords = optional_param('keywords', '', PARAM_TEXT);

// Ensure that a course ID is provided
if (!$courseid) {
    print_error('invalidcourse', 'error');
}

// Set the context
$context = context_course::instance($courseid);
$PAGE->set_context($context);

// Set the page URL
$PAGE->set_url(new moodle_url('/local/smartlibrary/edit_keywords.php', array('courseid' => $courseid, 'activityid' => $activityid)));

// Require login
require_login($courseid);

// Check if the user has the required capability (Optional)
// require_capability('moodle/course:manageactivities', $context);

// Set page layout (Optional)
// $PAGE->set_pagelayout('standard');

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edited_keywords'])) {
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
        $newRecord->source = 'input'; // Set the source
        $newRecord->courseid = $courseid; // Set the course ID
        $DB->insert_record('smartlib_learning_resources', $newRecord);
    }

    // Redirect back to view.php
    redirect(new moodle_url('/local/smartlibrary/view.php', ['courseid' => $courseid]), 'Keywords edited successfully', 1);
}

// Render the page header
echo $OUTPUT->header();

// Display the form for editing keywords
echo '<h1>Edit Keywords</h1>';
echo '<form method="post" action="edit_keywords.php">';
echo '<input type="hidden" name="courseid" value="' . $courseid . '">';
echo '<input type="hidden" name="activityid" value="' . $activityid . '">';
echo '<textarea name="edited_keywords">' . $keywords . '</textarea>';
echo '<input type="submit" value="Save Keywords">';
echo '</form>';

// Render the page footer
echo $OUTPUT->footer();
?>
