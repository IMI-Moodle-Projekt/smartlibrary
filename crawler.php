<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_login();

global $DB;

// Define the context and page URL
$context = context_system::instance();
$pageurl = new moodle_url('/local/smartlibrary/crawler.php');
$heading = get_string('pluginname', 'local_smartlibrary');

// Set up the page
$PAGE->set_context($context);
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(format_string($heading));
$PAGE->set_heading($heading);


// Check for view capability
if (!has_capability('local/smartlibrary:view', $context)) {
    print_error('nopermissions', 'error', '', 'view smartlibrary');
}

// Render the page header
//echo $OUTPUT->header();

//get course id from crawler url
$courseid = optional_param('courseid', 0, PARAM_INT);

// Define array of Links to be extracted and later parsed
$extractedLinks = [];

// Define array of valid keywords extracted from Moodle course summary
$extractedSummaryKeywords = [];
$extractedCourseNames = [];

// Get all courses of Moodle
$courses = get_courses();

// Iterate each Moodle course summary and extract valid keywords of it and add it to $extractedSummaryKeywords
foreach ($courses as $course) {
    if (!empty($course->summary)) {
        $validKeywords = get_keywords($course->summary);
        foreach ($validKeywords as $keyword) {
            if (!in_array($keyword, $extractedSummaryKeywords)) {
                array_push($extractedSummaryKeywords, $keyword);
            }
        }
    }
}

$sql = "SELECT keywords FROM {smartlib_learning_resources} WHERE source = ?";
$params = array('input');
$records = $DB->get_records_sql($sql, $params);

// Loop through the records to extract keywords
foreach ($records as $record) {
    // Split the keywords string by comma and trim each keyword
    $keywordsArray = array_map('trim', explode(',', $record->keywords));

    // Add each keyword to $extractedSummaryKeywords if not already present
    foreach ($keywordsArray as $keyword) {
        if (!in_array($keyword, $extractedSummaryKeywords)) {
            array_push($extractedSummaryKeywords, $keyword);
        }
    }
}

// if $extractedSummaryKeywords doesn't have valid keywords to crawl
if (empty($extractedSummaryKeywords)) {
    //echo "None of the courses have any summary with valid keywords to intial crawler! Add some and try again";
    $view_url = 'http://localhost/local/smartlibrary/view.php?courseid=' . $courseid;
    redirect($view_url);
} else { // if $extractedSummaryKeywords has valid keywords to crawl
    // 1.Coursera
    foreach ($extractedSummaryKeywords as $keyword) {
        $table_name = 'smartlib_learning_resources';
        $sql = "SELECT COUNT(*) FROM {" . $table_name . "} WHERE " . $DB->sql_compare_text('keywords') . " = ?";
        $params = array($keyword);
        $count = $DB->count_records_sql($sql, $params);

        if ($count > 0) {
            //echo $keyword . " already in the db and was not crawled<br>";
        } else {
            // Strip <p> tags from the course summary keyword
            $cleanKeyword = strip_tags($keyword);
            // Define the search-query-url by Coursera for the keyword 
            $queryURL = 'https://www.coursera.org/search?query=' . urlencode($cleanKeyword);
            // Create a new DOMDocument object $docs
            $docs = new DOMDocument();
            // Load the HTML into the DOMDocument object (makes it easier to navigate, query, and manipulate HTML structure)
            @$docs->loadHTML(file_get_contents($queryURL));
            // Create a new DOMXPath object $xpath to perform XPath queries on the DOMDocument object
            $xpath = new DOMXPath($docs);
            // Perform an XPath query to select 5 list items within a specific unordered list and store them in DOMNodeList object $listItems
            $listItems = $xpath->query('(//ul[contains(@class, "cds-9 css-18msmec cds-10")]//li)[position() <= 5]');
            // Check if the XPath query was successful or not
            if ($listItems === false) {
                die("Error in XPath query");
            }
            // Iterate each elemnt of the  DOMNodeList $listItems
            foreach ($listItems as $item) {

                $data = new stdClass();
                // Perform another XPath query to search for <a>-Tags within the <li>-Element and store each value in DOMNodeList object $links
                $links = $xpath->query('.//a', $item);
                // Extract the href from the only element (first) in $links and assign it to $href
                $href = $links->item(0)->getAttribute('href');
                $courseLink = "https://www.coursera.org" . $href;
                $data->link = $courseLink;
                // Perform another XPath query to search for <h3>-Tags within the <li>-Element and store each value in DOMNodeList object $h3Tags
                $h3Tags = $xpath->query('.//h3', $item);
                // Extract the text content from the only element (first) in $h3Tags and assign it to $courseName
                $courseName = $h3Tags->item(0)->textContent;

                $data->name = $courseName;

                $data->keywords = $cleanKeyword;

                $table_name = 'smartlib_learning_resources';

                $sql = "SELECT * FROM {" . $table_name . "} WHERE " . $DB->sql_compare_text('link') . " = ?";
                $params = array($courseLink);

                if (!$DB->record_exists_sql($sql, $params)) {
                    // Link does not exist, insert the new record
                    $newid = $DB->insert_record($table_name, $data);
                    //echo "- New learning material $courseName inserted into database with id $newid.";
                } else {
                    // Link already exists, skip insertion
                    // echo "- Learning material with link $courseLink already exists in the database.";
                }
            }
        }
    }
    // 2.Code Cademy
    foreach ($extractedSummaryKeywords as $keyword) {
        $table_name = 'smartlib_learning_resources';
        $sql = "SELECT COUNT(*) FROM {" . $table_name . "} WHERE " . $DB->sql_compare_text('keywords') . " = ?";
        $params = array($keyword);
        $count = $DB->count_records_sql($sql, $params);

        if ($count > 5) {
            //echo $keyword . " already in the db and was not crawled<br>";
        } else {
            // Strip <p> tags from the course summary keyword
            $cleanKeyword = strip_tags($keyword);
            // Define the search-query-url by CodeCademy for the keyword 
            $queryURL = 'https://www.codecademy.com/search?query=' . urlencode($cleanKeyword);
            // Create a new DOMDocument object $docs
            $docs = new DOMDocument();
            // Load the HTML into the DOMDocument object (makes it easier to navigate, query, and manipulate HTML structure)
            @$docs->loadHTML(file_get_contents($queryURL));
            // Create a new DOMXPath object $xpath to perform XPath queries on the DOMDocument object
            $xpath = new DOMXPath($docs);
            // Perform an XPath query to select 5 list items within a specific unordered list and store them in DOMNodeList object $listItems
            $listItems = $xpath->query('(//ol[contains(@class, "gamut-1yv0cql-Box ebnwbv90")]//li) [position() <= 5]');
            // Check if the XPath query was successful or not
            if ($listItems === false) {
                die("Error in XPath query");
            }
            // Iterate each elemnt of the  DOMNodeList $listItems
            foreach ($listItems as $item) {

                $data = new stdClass();
                // Perform another XPath query to search for <a>-Tags within the <li>-Element and store each value in DOMNodeList object $links
                $links = $xpath->query('.//a', $item);
                // Extract the href from the only element (first) in $links and assign it to $href
                $href = $links->item(0)->getAttribute('href');
                $courseLink = "https://www.codecademy.com" . $href;
                $data->link = $courseLink;
                // Perform another XPath query to search for <h3>-Tags within the <li>-Element and store each value in DOMNodeList object $h3Tags
                $h3Tags = $xpath->query('.//h3', $item);
                // Extract the text content from the only element (first) in $h3Tags and assign it to $courseName
                $courseName = $h3Tags->item(0)->textContent;

                $data->name = $courseName;

                $data->keywords = $cleanKeyword;

                $table_name = 'smartlib_learning_resources';

                $sql = "SELECT * FROM {" . $table_name . "} WHERE " . $DB->sql_compare_text('link') . " = ?";
                $params = array($courseLink);

                if (!$DB->record_exists_sql($sql, $params)) {
                    // Link does not exist, insert the new record
                    $newid = $DB->insert_record($table_name, $data);
                    //echo "- New learning material $courseName inserted into database with id $newid.";
                } else {
                    // Link already exists, skip insertion
                    //echo "- Learning material with link $courseLink already exists in the database.";
                }
            }
        }
    }
    $view_url = 'http://localhost/local/smartlibrary/view.php?courseid=' . $courseid;
    redirect($view_url);
    /*
    //This is only for testing to display extracted links
    foreach ($extractedLinks as $linksss) {
        echo $linksss;
    }
    foreach ($extractedCourseNames as $namesss) {
        echo $namesss;
    }*/
}

//echo $OUTPUT->footer();
