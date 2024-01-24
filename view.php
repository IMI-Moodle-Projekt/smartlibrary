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


echo '<style>
.local_smartlibrary_course_table {
    width: 100%;
    border-collapse: collapse;
}

.local_smartlibrary_course_table th, 
.local_smartlibrary_course_table td {
    border: 1px solid black;
    padding: 8px;
    text-align: left;
}

.local_smartlibrary_course_table a {
    color: #d9b702; /* Ändere die Farbe des Links */
}

/* Ändere die Farbe der Einträge in der Tabelle der Crawler-Extracted Keywords */
.local_smartlibrary_course_table td {
    color: #131f34; /* Ändere die Textfarbe der Zellen in der Tabelle */
}

body {
    background-image: url("https://www.ponto.io/_ipx/w_1536,f_webp,fit_cover/https://backend.talentine.io/storage/media/companies/190/wilhelminenhof-bibliothek-htw-berlin-maria-schramm-mitcopyright-1630409495mCQkI.jpg");
    background-size: cover;
    background-repeat: no-repeat;
}

.pagelayout-standard #page.drawers .main-inner,
body.limitedwidth #page.drawers .main-inner {
    max-width: 1000px;
    opacity: 0.95; 
    text-align: left;
    padding-left: 40px;
    padding-right: 40px;

}

/* Additional styles from view.php */
h1 {
    color: #f98012;
    text-align: center;
    margin-bottom: 40px;
    margin-top: 30px;
    font-weight: bold;
}

button {
    color: #186cbc;
    font-weight: bold;
   
    margin: 8px 8px 15px 0; /* Standard Margin mit einem größeren Abstand unten */
    border: none; /* Entferne den Standard-Rand */
    border-radius: 10px; /* Füge abgerundete Ecken hinzu */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Füge einen leichten Schatten hinzu */
}

/* Färbe den "Edit" Button grün */
button.edit-button {
    color: #fff;
    background-color:#76b900; /* Grüne Hintergrundfarbe */
    box-shadow: 0 2px 4px rgba(76, 175, 80, 0.4); /* Grüner Schatten */
    padding: 2px 16px; /* Standard Padding */
}

/* Färbe den "Delete" Button rot */
button.delete-button {
    color: #fff;
    background-color: #fd3d41; /* Rote Hintergrundfarbe */
  /*  box-shadow: 0 2px 4px rgba(255, 0, 0, 0.274); /* Roter Schatten */
    padding: 2px 8px; /* Standard Padding */
}

button:hover {
    background-color: #f98012; /* Ändere die Hintergrundfarbe beim Hovern */
    /*border: #f98012;*/
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Ändere den Schatten beim Hovern */
}

#expandAllButton,
#collapseAllButton {
    float: right;
    margin-right: 10px; /* Adjust the margin as needed */
    
}
/* Style the buttons */
#expandAllButton,
#collapseAllButton {
    color: #fff;
    background-color: #186cbc;
    padding: 5px 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

#expandAllButton:hover,
#collapseAllButton:hover {
    background-color: #f98012;
}

#collapseAllButton {
    display: none; /* Initially hide the Collapse All button */
}



/* Füge mehr Padding für die Keywords-Liste hinzu */
p {
    padding-bottom: 5px; /* Füge mehr Padding am unteren Rand hinzu */
    margin-bottom: 5px; /* Füge mehr Margin zum nächsten Element hinzu */
}

h4 {
    color: #131f34; /* Ändere die Farbe für "Input Keywords" und "Crawler-Extracted Keywords" */
    margin-bottom: 20px; /* Erhöhe den Abstand am unteren Rand */
}

h6 {
    color: #0066cc;
    font-size: 18px;
    margin-bottom: 10px; /* Erhöhe den Abstand am unteren Rand */
}
hr {
    border: 1px solid #ccc; /* Ändere die Farbe und Dicke der Trennlinie */
    margin: 15px 0; /* Füge mehr Margin oberhalb und unterhalb der Trennlinie hinzu */
}
/* Add a class to style the toggle button */
.toggle-arrow {
    cursor: pointer;
    color: black;
    font-weight: bold;
    margin-right: 5px;
    margin-bottom: 10px;
}

/* Hide the keywords by default */
.keywords-container {
    display: none;
}

/* Style for the expanded keywords */
.keywords-container.expanded {
    display: block;
}
</style>
';
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

    // Redirect to prevent form resubmission
    //redirect(new moodle_url('/local/smartlibrary/view.php', ['courseid' => $courseid]), 'Keywords saved successfully', 1);
}

// Check if the course ID is valid
if ($courseid > 0) {
    // Get course
    $course = get_course($courseid);

    

    // Print the course name

    //echo '<button onclick="goBackToCourse(' . $courseid . ')">Go Back to Course</button>';
  //  echo '<button onclick="goBackToCourse(' . $courseid . ')" style="color: #186cbc; font-weight: bold;">Go Back to Course</button>';


    

    echo '<h1>' . format_string($course->fullname) . '</h1>';


    echo '<button id="expandAllButton" onclick="toggleKeywordsNew(0, \'expand\')">Expand All</button>';
    echo '<button id="collapseAllButton" style="display:none;" onclick="toggleKeywordsNew(0, \'collapse\')">Collapse All</button>';

    

    // Fetch input keywords for the current course
    echo '<h4>Input Keywords:</h4>';

   
    // Fetch input keywords for the current course

    // Fetch input keywords for the current course
    $keywordRecords = $DB->get_records('smartlib_learning_resources', array('courseid' => $courseid, 'source' => 'input'));

    if (!empty($keywordRecords)) {
        foreach ($keywordRecords as $record) {
            $activityid = $record->activityid;

            // Retrieve and display the activity name
            $activityName = get_activity_name($activityid);
            echo '<hr>';
            
            echo '<div style="display: flex; align-items: center;">'; // Flexbox für horizontalen Container

            // Display the toggle arrow first
            echo '<span class="toggle-arrow" onclick="toggleKeywords(' . $activityid . ')">&#x25B6;</span>';
        
            // Display the activity name
            echo '<h6 style="color: #0066cc; font-size: 18px; margin-left: 10px;">' . $activityName . '</h6>';
        
            echo '</div>';
        


            // Display keywords for the current activity
        $inputKeywords = $record->keywords;

        // Use a div with an ID to easily toggle its display
        echo '<div id="keywords_' . $activityid . '" style="display: block;">';
        echo '<p>' . format_string($inputKeywords) . '</p>';

        // Edit and Delete Buttons with a class (visible only for professors)
        if (has_capability('local/smartlibrary:edit', context_course::instance($courseid))) {
            echo '<div class="edit-delete-buttons_' . $activityid . '" style="display: none;">';
            echo '<button class="edit-button" onclick="editKeywords(' . $courseid . ',' . $activityid . ', \'' . addslashes($inputKeywords) . '\')">Edit</button>';
            echo '<button class="delete-button" onclick="deleteKeywords(' . $record->id . ',' . $courseid . ')">Delete</button>';
            echo '</div>';
        }

        echo '</div>';
    }
            
    } else {
        echo '<p>No input keywords found for this course.</p>';
    }
    echo '<hr>';

    // Display Crawler-Extracted Keywords
    echo '<h4>Course Summary Keywords:</h4>';
    $keywordsArray = get_keywords($course->fullname . ' ' . $course->summary);
    echo '<div id="courseSummaryKeywords" class="keywords-container expanded">'; // Setze die Klasse "expanded" für sichtbare Anzeige

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


    function toggleKeywords(activityId) {
        var keywordsElement = document.getElementById("keywords_" + activityId);
        var editDeleteButtons = document.getElementsByClassName("edit-delete-buttons_" + activityId);
        var arrowElement = document.querySelector(".toggle-arrow");

        if (keywordsElement.style.display === "none" || keywordsElement.style.display === "") {
            keywordsElement.style.display = "block";
            for (var i = 0; i < editDeleteButtons.length; i++) {
                editDeleteButtons[i].style.display = "inline-block";
            }
            arrowElement.innerHTML = "&#x25BC;"; // Pfeil nach unten
        } else {
            keywordsElement.style.display = "none";
            for (var i = 0; i < editDeleteButtons.length; i++) {
                editDeleteButtons[i].style.display = "none";
            }
            arrowElement.innerHTML = "&#x25B6;"; // Pfeil nach rechts
        }
    }


    // Show keywords and buttons on page load
    document.addEventListener("DOMContentLoaded", function() {
        var allKeywordElements = document.querySelectorAll("[id^=\'keywords_\']");
        var allButtonsElements = document.querySelectorAll("[class^=\'edit-delete-buttons_\']");

        for (var i = 0; i < allKeywordElements.length; i++) {
            allKeywordElements[i].style.display = "block";
        }

        for (var i = 0; i < allButtonsElements.length; i++) {
            allButtonsElements[i].style.display = "inline-block";
        }
    });



    
 function toggleKeywordsNew(activityId, action) {
    var keywordsElements = document.querySelectorAll("[id^=\'keywords_\']");
    var editDeleteButtons = document.querySelectorAll("[class^=\'edit-delete-buttons_\']");
    var expandAllButton = document.getElementById("expandAllButton");
    var collapseAllButton = document.getElementById("collapseAllButton");

    for (var i = 0; i < keywordsElements.length; i++) {
        if (action === "collapse") {
            keywordsElements[i].style.display = "none";
            for (var j = 0; j < editDeleteButtons.length; j++) {
                editDeleteButtons[j].style.display = "none";
            }
            expandAllButton.style.display = "block";
            collapseAllButton.style.display = "none";
        } else {
            keywordsElements[i].style.display = "block";
            for (var j = 0; j < editDeleteButtons.length; j++) {
                editDeleteButtons[j].style.display = "inline-block";
            }
            expandAllButton.style.display = "none";
            collapseAllButton.style.display = "block";
        }
    }

    // Zusätzlich für Course Summary Keywords
    var courseSummaryKeywordsElement = document.getElementById("courseSummaryKeywords");
    if (courseSummaryKeywordsElement) {
        if (action === "collapse") {
            courseSummaryKeywordsElement.style.display = "none";
            courseSummaryKeywordsElement.classList.remove("expanded"); // Entferne die Klasse "expanded"
        } else {
            courseSummaryKeywordsElement.style.display = "block";
            courseSummaryKeywordsElement.classList.add("expanded"); // Füge die Klasse "expanded" hinzu
        }
    }
}

// Funktion zum Anzeigen von Keywords und Buttons beim Laden der Seite
document.addEventListener("DOMContentLoaded", function() {
    var allKeywordElements = document.querySelectorAll("[id^=\'keywords_\']");
    var allButtonsElements = document.querySelectorAll("[class^=\'edit-delete-buttons_\']");
    var courseSummaryKeywordsElement = document.getElementById("courseSummaryKeywords");

    for (var i = 0; i < allKeywordElements.length; i++) {
        allKeywordElements[i].style.display = "block";
    }

    for (var i = 0; i < allButtonsElements.length; i++) {
        allButtonsElements[i].style.display = "inline-block";
    }

    // Beim Laden der Seite "Collapse All" ausführen
    toggleKeywordsNew(0, "collapse");

    // Zusätzlich für Course Summary Keywords
    if (courseSummaryKeywordsElement) {
        courseSummaryKeywordsElement.style.display = "none";
        courseSummaryKeywordsElement.classList.remove("expanded"); // Entferne die Klasse "expanded"
    }
});

// Funktion zum Anzeigen von Keywords und Buttons beim Laden der Seite
document.addEventListener("DOMContentLoaded", function() {
    var allKeywordElements = document.querySelectorAll("[id^=\'keywords_\']");
    var allButtonsElements = document.querySelectorAll("[class^=\'edit-delete-buttons_\']");
    var courseSummaryKeywordsElement = document.getElementById("courseSummaryKeywords");

    for (var i = 0; i < allKeywordElements.length; i++) {
        allKeywordElements[i].style.display = "block";
    }

    for (var i = 0; i < allButtonsElements.length; i++) {
        allButtonsElements[i].style.display = "inline-block";
    }

    // Beim Laden der Seite "Collapse All" ausführen
    toggleKeywordsNew(0, "collapse");

    // Zusätzlich für Course Summary Keywords
    if (courseSummaryKeywordsElement) {
        courseSummaryKeywordsElement.style.display = "none";
        courseSummaryKeywordsElement.classList.remove("expanded"); // Entferne die Klasse "expanded"
    }
});

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