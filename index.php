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

// Define the context and page URL
$context = context_system::instance();
$pageurl = new moodle_url('/local/smartlibrary/index.php');
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
// CSS-Styles für die Tabelle
echo '<style>';
echo 'table { width: 100%; border-collapse: collapse; }';
echo 'th, td { border: 1px solid black; padding: 8px; text-align: left; }';
echo '</style>';

// Tabelle für die Anzeige der Daten
echo '<table>';
echo '<tr><th>Spaltenname</th></tr>'; // Ersetzen Sie 'Spaltenname' durch den tatsächlichen Spaltennamen
foreach ($records as $record) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($record->course_id) . '</td>';
    echo '<td>' . htmlspecialchars($record->course_name) . '</td>'; // Ersetzen Sie 'fieldname' mit dem tatsächlichen Feldnamen
    echo '</tr>';
}
echo '</table>';
echo $OUTPUT->footer();
?>
