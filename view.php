<?php

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once(__DIR__ . '/lib.php');

// Define the context and page URL
$context = context_system::instance();
$pageurl = new moodle_url('/local/smartlibrary/view.php');
$heading = get_string('pluginname', 'local_smartlibrary');

// Set up the page
$PAGE->set_context($context);
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(format_string($heading));
$PAGE->set_heading($heading);
$PAGE->requires->css(new moodle_url('/local/smartlibrary/styles.css'));

// Render the page header
echo $OUTPUT->header();

// Get the course ID from the URL's query parameters
$courseid = optional_param('courseid', 0, PARAM_INT);
global $DB;

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
        'courseid' => $courseid, // Add this condition
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
        $newRecord->source = $source; // Set the source
        $newRecord->courseid = $courseid; // Set the course ID
        $DB->insert_record('smartlib_learning_resources', $newRecord);
    }
}

// Check if the course ID is valid
if ($courseid > 0) {
    // Get course
    $course = get_course($courseid);
    echo '<button onclick="goBackToCourse(' . $courseid . ')" style="color: #186cbc; font-weight: bold;">Go Back to Course</button>';  
    echo '<h1>Course Name: ' . format_string($course->fullname) . '</h1>';

    // Fetch input keywords for the current course
    echo '<h2><em>Input Keywords:</em></h2>';

    // Fetch input keywords for the current course
    $keywordRecords = $DB->get_records('smartlib_learning_resources', array('courseid' => $courseid, 'source' => 'input'));

    if (!empty($keywordRecords)) {
        foreach ($keywordRecords as $record) {
            $activityid = $record->activityid;

            // Retrieve and display the activity name
            $activityName = get_activity_name($activityid);

            echo '<h3 style="color: #0066cc; font-size: 18px;">' . $activityName . '</h3>';

            // Display keywords for the current activity
            $inputKeywords = $record->keywords;
            echo '<p>Keywords: ' . format_string($inputKeywords) . '</p>';

            // Edit Button
            echo '<button onclick="editKeywords(' . $courseid . ',' . $activityid . ', \'' . addslashes($inputKeywords) . '\')">Edit</button>';
            
            // Delete Button
            echo '<button onclick="deleteKeywords(' . $record->id . ',' . $courseid . ')">Delete</button>';
            echo '<hr>';
        }
    } else {
        echo '<p>No input keywords found for this course.</p>';
    }

    // Display Crawler-Extracted Keywords
    echo '<h2><em>Crawler-Extracted Keywords:</em></h2>';
    $keywordsArray = get_keywords($course->fullname . ' ' . $course->summary);

    if (!empty($keywordsArray)) {
        // Construct a WHERE clause for each keyword
        $whereClauses = [];
        foreach ($keywordsArray as $keyword) {
            $whereClauses[] = "name LIKE '%$keyword%'";
        }

        // Combine the WHERE clauses using OR
        $whereCondition = implode(' OR ', $whereClauses);

        // Your Moodle query
        $table_name = $CFG->prefix . 'smartlib_learning_resources';
        $sql = "SELECT id, name, link, keywords FROM {$table_name} WHERE $whereCondition AND source = 'crawler'";

        // Execute the query using Moodle's database API
        $entries = $DB->get_records_sql($sql);

        // Check if there are any matching entries
        if (!empty($entries)) {
            echo '<ul>';

            foreach ($entries as $entry) {
                // Assuming $entry is an object with properties id, name, and link
                $name = format_string($entry->name); // Ensuring HTML safety
                $link = format_string($entry->link); // Ensuring HTML safety

                // Output the HTML for each entry
                echo '<li><a href="' . $link . '">' . $name . '</a>';

                // Display existing keywords
                echo '<p>Keywords: ' . format_string($entry->keywords) . '</p>';

                echo '</li>';
            }

            echo '</ul>';
        } else {
            echo '<p>No matching entries found.</p>';
        }
    } else {
        echo '<p>No keywords found for this course.</p>';
    }

    // Check if the form was submitted
    if ($data = data_submitted()) {
        if (isset($data->submitbutton)) {
            // Form was submitted, handle the keywords
            $entryid = $data->entryid;
            $keywords = $data->keywords;

            // Save the entered keywords to the database
            save_keywords_to_database($entryid, $keywords);
        }
    }
} else {
    echo '<p>No course specified.</p>';
}

echo '<script>
    function editKeywords(courseId, activityId, keywords) {
        // Display a form to edit keywords
        var editedKeywords = prompt("Edit Keywords:", keywords);
        if (editedKeywords !== null && editedKeywords !== "") {
            // Use AJAX to update keywords without page reload
            $.ajax({
                url: "view.php", // Same page URL
                type: "POST",
                data: {
                    edit_keywords: 1,
                    courseid: courseId,
                    activityid: activityId,
                    edited_keywords: editedKeywords
                },
                success: function(response) {
                    // Handle success (e.g., refresh the page with the correct course id)
                    window.location.href = "view.php?courseid=" + courseId;
                },
                error: function(error) {
                    // Handle error
                    console.error("Error editing keywords: " + error);
                }
            });
        } else if (editedKeywords === "") {
            // Handle the case where the user entered an empty string
            alert("Keywords cannot be empty.");
        }
    }

    function goBackToCourse(courseId) {
        // Redirect back to the course using the course id
        window.location.href = "' . $CFG->wwwroot . '/course/view.php?id=" + courseId;
    }

    function deleteKeywords(entryId, courseId) {
        if (confirm("Are you sure you want to delete this entry?")) {
            
            $.ajax({
                url: "delete_keywords.php", // Replace with the actual delete script URL
                type: "POST",
                data: { entryId: entryId, courseId: courseId },
                success: function(response) {
                    // Handle success (e.g., refresh the page)
                    window.location.href = "view.php?courseid=" + courseId;
                },
                error: function(error) {
                    // Handle error
                    console.error("Error deleting entry: " + error);
                }
            });
        }
    }
</script>';

// Check if the form was submitted for keyword editing
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
        $newRecord->source = 'input'; // Set the source
        $newRecord->courseid = $courseid; // Set the course ID
        $DB->insert_record('smartlib_learning_resources', $newRecord);
    }

    // You can optionally send a response back to the AJAX request, e.g., echo 'success';
    // Note: If you send a response, make sure not to include any HTML or whitespace before it.
    exit;
}

echo $OUTPUT->footer();