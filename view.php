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

// Render the page header
echo $OUTPUT->header();

// Get the course ID from the URL's query parameters
$courseid = optional_param('courseid', 0, PARAM_INT);
global $DB;

// Check if the course ID is valid
if ($courseid > 0) {

    // Get course 
    $course = get_course($courseid);

    // Print the course name
    echo '<h1>Course Name: ' . format_string($course->fullname) . '</h1>';

    $keywordsArray = get_keywords($course->fullname . ' ' . $course->summary);

    if (!empty($keywordsArray)) {
        // Construct a WHERE clause for each keyword
        $whereClauses = [];
        foreach ($keywordsArray as $keyword) {
            $whereClauses[] = "keywords LIKE '%$keyword%'";
        }

        // Combine the WHERE clauses using OR
        $whereCondition = implode(' OR ', $whereClauses);

        // Your Moodle query
        $table_name = $CFG->prefix . 'smartlib_learning_resources';
        $sql = "SELECT id, name, link FROM {$table_name} WHERE $whereCondition";

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
                echo '<li><a href="' . $link . '">' . $name . '</a></li>';
            }

            echo '</ul>';
        } else {
            echo '<p>No matching entries found.</p>';
        }
    } else {
        echo '<p>No keywords found for this course.</p>';
    }
} else {
    echo '<p>No course specified.</p>';
}

echo $OUTPUT->footer();

?>