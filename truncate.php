<?php

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot.'/lib/adminlib.php');

// Define the context
$context = context_system::instance();

require_login();
require_capability('moodle/site:config', $context);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/smartlibrary/truncate.php'));
$PAGE->set_title("Truncate Database Table");
$PAGE->set_heading("Truncate Table");

echo $OUTPUT->header();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    truncate_table($DB);
    //echo "Database table truncated successfully.";
}


echo '<form method="post">';
echo '    <button type="submit" name="truncateTable">Truncate Database</button>';
echo '</form>';

echo $OUTPUT->footer();

/*function truncate_table($DB) {
    $table_name = 'smartlib_learning_resources'; // Adjust the table name
    $DB->execute("TRUNCATE TABLE {" . $table_name . "}");
}*/

function truncate_table($DB) {
    $table_name = 'smartlib_learning_resources'; // Adjust the table name

    // Check if the table is already empty
    $count = $DB->count_records($table_name);

    if ($count > 0) {
        // Table is not empty, proceed with truncating
        $DB->execute("TRUNCATE TABLE {" . $table_name . "}");
        echo "<p>The table 'Learning-Materials' was truncated successfully!</p>";
    } else {
        // Table is empty, no need to truncate
        echo "<p>The table 'Learning-Materials' is already empty; no truncating was performed!</p>";
    }
}


?>
