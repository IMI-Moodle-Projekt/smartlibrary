<?php

namespace local_smartlibrary;

defined('MOODLE_INTERNAL') || die();

class observer {
    public static function course_viewed($event) {
        global $DB, $PAGE;

        // Sicherstellen, dass der Code nur auf Kursseiten ausgeführt wird
        if ($PAGE->pagetype != 'course-view') {
            return;
        }

        // Daten aus der Datenbanktabelle 'course' abrufen
        $courses = $DB->get_records('moodle.course');

        // Starten der Ausgabe von HTML
        $html = '';
        $html .= '<div>';
        $html .= '<table>';
        $html .= '<tr><th>Kurs-ID</th><th>Kursname</th></tr>'; // Spaltenüberschriften

        // Durchlaufen der Daten und Erstellen der Tabellenzeilen
        foreach ($courses as $course) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($course->id) . '</td>';
            $html .= '<td>' . htmlspecialchars($course->name) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';
        $html .= '</div>';

        // Ausgabe des generierten HTML
        echo $html;
    }
}
?>