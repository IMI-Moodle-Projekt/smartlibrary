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
if ($hassiteconfig) { // needs this condition or there is error on login page
    $settings = new admin_settingpage('local_smartlibrary', get_string('pluginname', 'local_smartlibrary'));
    $ADMIN->add('localplugins', $settings);

    // Add settings here
    $settings->add(new admin_setting_configcheckbox('local_smartlibrary/enable', 
        get_string('enable', 'local_smartlibrary'), 
        get_string('enable_desc', 'local_smartlibrary'), 0));
}
