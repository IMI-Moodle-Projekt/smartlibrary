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
 * Upgrades the database if a new plugin version is installed.
 *
 * @package     local_smartlibrary
 * @copyright   2023 
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrades the database if a new plugin version is installed.
 *
 * @param int $oldversion
 * 
 */

function xmldb_local_smartlibrary_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2024010300) {

    // Define field activityid to be added to smartlib_learning_resources.
    $table = new xmldb_table('smartlib_learning_resources');
    //$field = new xmldb_field('activityid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, '0', 'keywords'); // Default value set to '0'
    $field = new xmldb_field('activityid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'keywords'); // Default value set to '0'
        
    // Conditionally launch add field activityid.
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }

    // Smartlibrary savepoint reached.
    upgrade_plugin_savepoint(true, 2024010300, 'local', 'smartlibrary');
    }

    if ($oldversion < 2024010300) {

    // Define field manual_keywords to be dropped from smartlib_learning_resources.
    $table = new xmldb_table('smartlib_learning_resources');
    $field = new xmldb_field('manual_keywords');

    // Conditionally launch drop field activityid.
    if ($dbman->field_exists($table, $field)) {
        $dbman->drop_field($table, $field);
    }

    // Smartlibrary savepoint reached.
    upgrade_plugin_savepoint(true, 2024010300, 'local', 'smartlibrary');
    }

    if ($oldversion < 2024011300) {

        // Define field source to be added to smartlib_learning_resources.
        $table = new xmldb_table('smartlib_learning_resources');
        $field = new xmldb_field('source', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, 'crawler', 'activityid');

        // Conditionally launch add field source.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Smartlibrary savepoint reached.
        upgrade_plugin_savepoint(true, 2024011300, 'local', 'smartlibrary');
    }

    if ($oldversion < 2024011400) {

        // Define field courseid to be added to smartlib_learning_resources.
        $table = new xmldb_table('smartlib_learning_resources');
        $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'source');

        // Conditionally launch add field courseid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Smartlibrary savepoint reached.
        upgrade_plugin_savepoint(true, 2024011400, 'local', 'smartlibrary');
    }
    
    // Everything has succeeded to here. Return true.
    return true;
}