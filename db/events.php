<?php

$observers = array(
    array(
        'eventname'   => '\core\event\course_updated',
        'callback'    => 'local_smartlibrary\local_smartlibrary_observer::course_updated',
    ),
    array(
        'eventname' => '\core\event\course_viewed',
        'callback'  => 'local_smartlibrary\local_smartlibrary_observer::course_viewed',
    ),
);
