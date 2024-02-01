<?php

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once(__DIR__ . '/lib.php');

// Include the file for handling keywords POST requests
require_once(__DIR__ . '/handle_keywords_post.php');

// Include the file for handling edit keywords POST requests
require_once(__DIR__ . '/handle_edit_keywords_post.php');

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

// Include the CSS stylesheet
echo '<link rel="stylesheet" type="text/css" href="' . $CFG->wwwroot . '/local/smartlibrary/css/styles.css">';
// Include the JavaScript file
echo '<script src="' . $CFG->wwwroot . '/local/smartlibrary/scripts.js"></script>';


// Get the course ID from the URL's query parameters
$courseid = optional_param('courseid', 0, PARAM_INT);
global $DB;

// Check if the course ID is valid
if ($courseid > 0) {

    require_once(__DIR__ . '/handle_keywords_post.php');
    require_once(__DIR__ . '/handle_edit_keywords_post.php');
    // Get course
    $course = get_course($courseid);

    echo '<div style="display: flex; justify-content: space-between; align-items: center;">';
    // Logo
    echo '<img src="' . $CFG->wwwroot . '/local/smartlibrary/images/logo.png" alt="SmartLibrary Logo" class="logo" style="max-width: 250px; margin-right: 10px;" />';
    // "Go Back to Course" Button
    echo '<button id="backToCourse" onclick="goBackToCourse(' . $courseid . ')">Back To Course</button>';
    // "Course name with expand all & collapse all buttons"
    echo '</div>';
    echo '<h2>' . format_string($course->fullname) . '</h2>';

    echo '<div style="display: flex; justify-content: flex-end; align-items: right;">'; 
    echo '<button id="expandAllButton" onclick="toggleKeywordsNew(0, \'expand\')">Expand All</button>';
    echo '<button id="collapseAllButton" style="display:none;" onclick="toggleKeywordsNew(0, \'collapse\')">Collapse All</button>';
    echo '</div>';

    // Fetch input keywords for the current course
    echo '<h4>Input Keywords:</h4>';
    $keywordRecords = $DB->get_records('smartlib_learning_resources', array('courseid' => $courseid, 'source' => 'input'));

    if (!empty($keywordRecords)) {
        foreach ($keywordRecords as $record) {
            $activityid = $record->activityid;

            // Retrieve and display the activity name
            $activityName = get_activity_name($activityid);
            echo '<hr>';

            // Flexbox für horizontalen Container
            echo '<div style="display: flex; align-items: center;">'; 
            // Display the toggle arrow first
            echo '<span class="toggle-arrow" onclick="toggleKeywords(' . $activityid . ')">&#x25B6;</span>';
            // Display the activity name
            echo '<h6 style="color: #0066cc; font-size: 18px; margin-left: 10px;">' . $activityName . '</h6>';
            echo '</div>';

            // Display keywords for the current activity
            $inputKeywords = $record->keywords;

            // Use a div with an ID to easily toggle its display
            echo '<div id="keywords_' . $activityid . '" style="display: block;">';
            echo '<div style="display: flex; align-items: center;">';
            echo '<p class="keyword-group" style="color: #f98012; flex: 1; margin-right: 10px;">' . format_string($inputKeywords) . '</p>';

            // Edit and Delete Buttons with a class (visible only for professors)
            if (has_capability('local/smartlibrary:edit', context_course::instance($courseid))) {
                echo '<div class="edit-delete-buttons_' . $activityid . '" style="display: none;">';
                echo '<button class="delete-button" onclick="deleteKeywords(' . $record->id . ',' . $courseid . ')">Delete</button>';
                echo '<button class="edit-button" onclick="editKeywords(' . $courseid . ',' . $activityid . ', \'' . addslashes($inputKeywords) . '\')">Modify</button>';
                echo '</div>';
            }
            echo '</div>';

            //here where I started
            $inputKeywordsArray = get_keywords($inputKeywords);
            foreach ($inputKeywordsArray as $keyword) {
                echo '<h5 class="toggle-arrow" onclick="toggleCourseSummaryKeyword(this)">&#x25B6; ' . $keyword . '</h5>';
                echo '<div class="keyword-group" style="display: none;">'; // Start with hidden keywords
                $whereCondition = "CONCAT(' ', name, ' ') LIKE '% " . $keyword . " %' AND source = 'crawler'";
                $table_name = $CFG->prefix . 'smartlib_learning_resources';
                $sql = "SELECT id, name, link, keywords FROM {$table_name} WHERE $whereCondition";
                $entries = $DB->get_records_sql($sql);
                if (!empty($entries)) {
                    echo '<ul>';

                    foreach ($entries as $entry) {
                        // Assuming $entry is an object with properties id, name, and link
                        $name = format_string($entry->name); 
                        $link = format_string($entry->link); 

                        // Determine which logo to display based on the link
                        $logo = '';
                        if (stripos($link, 'coursera') !== false) {
                            $logo = '<img src="/local/smartlibrary/images/coursera.svg.png" alt="Coursera Logo" class="logo-icon" />';
                        } elseif (stripos($link, 'codecademy') !== false) {
                            $logo = '<img src="/local/smartlibrary/images/codecademy.svg.png" alt="CodeCademy Logo" class="logo-icon" />';
                        }

                        // Output the HTML for each entry with the logo
                        echo '<li>';
                        echo '<a href="' . $link . '">' . $name . '</a>';
                        echo $logo; // Display the logo
                        echo '</li>';
                    }

                    echo '</ul>';
                } else {
                    echo '<p style="color: #fd3d41;">No matching entries found for this keyword.</p>';
                }
                echo '</div>';
            }


            echo '</div>';
        }
    } else {
        echo '<p style="color: #fd3d41;">No input keywords found for this course.</p>';
    }
    echo '<hr>';

    // Display Crawler-Extracted Keywords
    echo '<h4>Course Summary Keywords:</h4>';

    $keywordsArray = get_keywords($course->summary);
    echo '<div id="courseSummaryKeywords" class="keywords-container expanded">';
    if (!empty($keywordsArray)) {
        // Iterate through each keyword
        foreach ($keywordsArray as $keyword) {
            // Display the keyword outside the border
            echo '<h5 class="toggle-arrow" onclick="toggleCourseSummaryKeyword(this)">&#x25B6; ' . $keyword . '</h5>';
            echo '<div class="keyword-group" style="display: none;">'; 
            // Construct a WHERE clause for the current keyword
            $whereCondition = "CONCAT(' ', name, ' ') LIKE '% " . $keyword . " %' AND source = 'crawler'";
            // Your Moodle query
            $table_name = $CFG->prefix . 'smartlib_learning_resources';
            $sql = "SELECT id, name, link, keywords FROM {$table_name} WHERE $whereCondition";

            // Execute the query using Moodle's database API
            $entries = $DB->get_records_sql($sql);

            // Check if there are any matching entries
            if (!empty($entries)) {
                echo '<ul>';

                foreach ($entries as $entry) {
                    // Assuming $entry is an object with properties id, name, and link
                    $name = format_string($entry->name); 
                    $link = format_string($entry->link); 

                    // Determine which logo to display based on the link
                    $logo = '';
                    if (stripos($link, 'coursera') !== false) {
                        $logo = '<img src="/local/smartlibrary/images/coursera.svg.png" alt="Coursera Logo" class="logo-icon" />';
                    } elseif (stripos($link, 'codecademy') !== false) {
                        $logo = '<img src="/local/smartlibrary/images/codecademy.svg.png" alt="CodeCademy Logo" class="logo-icon" />';
                    }

                    // Output the HTML for each entry with the logo
                    echo '<li>';
                    echo '<a href="' . $link . '">' . $name . '</a>';
                    echo $logo; // Display the logo
                    echo '</li>';
                }

                echo '</ul>';
            } else {
                echo '<p style="color: #fd3d41;">No matching entries found for this keyword.</p>';
            }

            echo '</div>'; 
        }
    } else {
        echo '<p style="color: #fd3d41;">No course summary keywords found for this course.</p>';
    }

    echo '</div>'; 

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
    echo '<p style="color: #fd3d41;">No course specified.</p>';
}

//JS Script
echo '<script>
function goBackToCourse(courseId) {
    window.location.href = "' . $CFG->wwwroot . '/course/view.php?id=" + courseId;
}
</script>';


echo $OUTPUT->footer();
