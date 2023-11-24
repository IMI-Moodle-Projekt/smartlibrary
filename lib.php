<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Plugin functions for the local_smartlibrary plugin.
 *
 * @package   local_smartlibrary
 * @copyright 2023 Your Name <your@email.address>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function local_smartlibrary_extend_settings_navigation($settingsnav, $context) {
    global $CFG, $PAGE;

    // Only add this settings item on non-site course pages.
    if (!$PAGE->course or $PAGE->course->id == 1) {
        return;
    }

    // Only let users with the appropriate capability see this settings item.
    if (!has_capability('local/smartlibrary:view', context_course::instance($PAGE->course->id))) {
        return;
    }

    if ($settingnode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE)) {
        $nodename = get_string('nodename', 'local_smartlibrary'); // Use a string identifier 'nodename' in your language file
        $url = new moodle_url('/local/smartlibrary/yourfile.php', array('id' => $PAGE->course->id));
        $customnode = navigation_node::create(
            $nodename,
            $url,
            navigation_node::NODETYPE_LEAF,
            'smartlibrary',
            'smartlibrary',
            new pix_icon('t/addcontact', $nodename)
        );
        if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
            $customnode->make_active();
        }
        $settingnode->add_node($customnode);
    }
}

function smartlibrary_display_table() {
    $table = new html_table();
    $table->head = array('Keyword', 'Resource');
    $table->data = array(
        array('', ''),
        array('', ''),
        array('', ''),
        array('', ''),
        array('', '')
    );
    return html_writer::table($table);
}


