<?php

/*
 * Copyright (C) 2022 Giovanni Cascione <ing.cascione@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

//Helpers
function ts_get_column_prop($tags, $prop = 'name', $target = array(), $key_out_type = 'name') {
    if (is_array($tags)) {
        return MADkitchen\Modules\Handler::get_table_column_prop_array_by_key('TimeTracker', 'timetable', $tags, $prop, $key_out_type, $target);
    } else {
        return MADkitchen\Modules\Handler::get_table_column_prop_by_key('TimeTracker', 'timetable', $tags, $prop);
    }
}

function ts_query_items($arg = [], $table = 'timetable') {
    return MADkitchen\Modules\Handler::$active_modules['TimeTracker']['class']->query($table, $arg)->items;
}

function ts_update_items($arg, $table = 'timetable') {
    return MADkitchen\Modules\Handler::$active_modules['TimeTracker']['class']->query($table)->update_item($arg);
}

function ts_add_items($arg, $table = 'timetable') {
    return MADkitchen\Modules\Handler::$active_modules['TimeTracker']['class']->query($table)->add_item($arg);
}

function ts_resolve_relation($column, $id, $target = null, $table = 'timetable') {
    return MADkitchen\Modules\Handler::resolve_internal_relation('TimeTracker', $table, $column, $id, $target);
}

//TODO: generalize the following 3
function ts_get_activity_name($arg) {
    $items = ts_query_items(['activity_id' => $activity_id, 'groupby' => ['activity_name']]);
    $retval = isset($items[0]['activity_name']) ? $items[0]['activity_name'] : '';
    return $retval;
}

function ts_get_activity_group($activity_id) {
    $items = ts_query_items(['activity_id' => $activity_id, 'groupby' => ['activity_group']]);
    $retval = isset($items[0]['activity_group']) ? $items[0]['activity_group'] : '';
    return $retval;
}

function ts_get_activities() {
    $items = ts_query_items(['count' => true, 'groupby' => ['activity_group', 'activity_id', 'activity_id_name']], 'activity_id');
    $retval = [];

    foreach ($items as $item) {
        $activity_id = $item['activity_id'];//ts_resolve_relation('activity_id', $item['activity_id'], null, 'activity_id');
        $activity_id_name = $item['activity_id_name'];//ts_resolve_relation('activity_id_name', $item['activity_id_name'], null, 'activity_id');
        $activity_group = ts_resolve_relation('activity_group', $item['activity_group'], null, 'activity_id');
        $retval[$activity_group][$activity_id] = $activity_id_name;
    }
    return $retval;
}
