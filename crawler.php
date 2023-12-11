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
echo $OUTPUT->header();

// In the future the crawler can crawl through multiple pages.
// For now it only extracts keywords from one given page and uses the inserted link as course link.

// Check if the form is submitted
if ($data = data_submitted()) {
    // Get the website link from the form using optional_param
    $websiteLink = optional_param('websiteLink', '', PARAM_URL);

    // Validate the URL
    if (filter_var($websiteLink, FILTER_VALIDATE_URL)) {

        // Check if the domain is supported
        // we do this here so that we do not download the HTML but give an error 
        // message right away
        $allowedDomains = ['www.coursera.org']; // Add your supported domains here
        $parsedUrl = parse_url($websiteLink);
        $host = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';

        if (in_array($host, $allowedDomains)) {

            // Variables to store the extraction results
            $extractedKeywords = [];
            $materialName = "";

            // Download the HTML content
            $htmlContent = file_get_contents($websiteLink);

            if ($htmlContent !== false) {
                echo "<h2>Extracted keywords:</h2>";
                echo "<p>Link: $websiteLink</p>";

                // For debugging: 
                // echo '<pre>' . var_dump($htmlContent) . '</pre>';

                // Keyword extraction specific to coursera
                if ($host == 'www.coursera.org') {

                    // Create a DOMDocument object
                    $dom = new DOMDocument();

                    // Load the HTML content
                    libxml_use_internal_errors(true); // Disable warnings for malformed HTML
                    $dom->loadHTML($htmlContent);
                    libxml_clear_errors();

                    // Create a DOMXPath object
                    $xpath = new DOMXPath($dom);

                    // Coursera has a "Skill" section with links which have this attribute:
                    $attributeValue = 'seo_skills_link_tag';

                    // Use XPath query to find <a> elements with that attribute value
                    $query = "//a[@data-track-component='$attributeValue']";

                    // Execute the query
                    $matchingLinks = $xpath->query($query);

                    // Extract and display the text content of matching links
                    foreach ($matchingLinks as $link) {
                        $skillText = trim($link->textContent);
                        echo "- Extracted Keywords: $skillText<br>";
                        $extractedKeywords[] = $skillText;
                    }

                    // The course name is in the first h1 element
                    // Create XPath query to find the first <h1> element
                    $query = "//h1";
                    $h1Elements = $xpath->query($query);
                    if ($h1Elements->length > 0) {
                        $materialName = trim($h1Elements->item(0)->textContent);
                    }
                    echo "- Extracted learning material name: $materialName";

                } else {
                    echo "<p>Domain supported but no keyword extraction was imlemented.</p>";
                }

            } else {
                // Error downloading HTML
                echo "<p>Error downloading HTML from $websiteLink.</p>";
            }

            if (!empty($extractedKeywords) && !empty($materialName)) {
                $combinedKeywords = implode(", ", $extractedKeywords);
                // Define the data for the new record
                $data = new stdClass();
                $data->keywords = $combinedKeywords;
                $data->name = $materialName;
                $data->link = $websiteLink;

                // For debugging: 
                // echo '<pre>' . var_dump($data) . '</pre>';

                // Insert the record into the smartlib_resources table
                // $table_name = $CFG->prefix . 'smartlib_learning_resources';
                $table_name = 'smartlib_learning_resources';
                $newid = $DB->insert_record($table_name, $data);

                echo "- New learning material $materialName inserted into database with id $newid.";

            } else {
                echo "Error parsing the page html.";
            }

        } else {
            echo "<p>Domain $host not supported. Please enter a valid website URL.</p>";
        }
    } else {
        echo "<p>Please enter a valid website URL.</p>";
    }
} else {
    echo '<h2>Enter a Link to a course to extract:</h2>';
    echo '<form method="post">';
    echo '    <label for="websiteLink">Website Link:</label>';
    echo '    <input type="text" id="websiteLink" name="websiteLink" placeholder="https://www.coursera.org/specializations/swift-5-ios-app-developer" required>';
    echo '    <button type="submit">Go</button>';
    echo '</form>';
}

echo $OUTPUT->footer();
?>
