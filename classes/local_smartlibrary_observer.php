<?php

namespace local_smartlibrary;

defined('MOODLE_INTERNAL') || die();

class local_smartlibrary_observer {

    public static function course_updated(\core\event\course_updated $event) {
        $courseid = $event->courseid;
        $crawler_url = 'http://localhost/local/smartlibrary/crawler.php?courseid=' .  $courseid;
        redirect($crawler_url); // Note: Redirecting within an event observer might not be the best approach
        // file_get_contents($crawler_url); // Uncomment if you want to use this instead 
    }

    public static function course_viewed(\core\event\course_viewed $event) {
        error_log('Course viewed event observed in course ID: ' . $event->courseid);
    }
}
