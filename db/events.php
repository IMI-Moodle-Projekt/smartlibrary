<?php
$observers = array(
    array(
        'eventname'   => '\core\event\course_viewed',
        'callback'    => 'local_smartlibrary_observer::course_viewed',
    ),
);
?>