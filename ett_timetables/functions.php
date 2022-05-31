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
        return MADkit\Modules\Handler::get_table_column_prop_array_by_key('ett_timetables', $tags, $prop, $key_out_type, $target);
    } else {
        return MADkit\Modules\Handler::get_table_column_prop_by_key('ett_timetables', $tags, $prop);
    }
}

function ts_query_items($arg=[]){
    return MADkit\Modules\Handler::$active_modules['ett_timetables']['class']->query($arg)->items;
}

function ts_update_items($arg){
    return MADkit\Modules\Handler::$active_modules['ett_timetables']['class']->query()->update_item($arg);
}

function ts_add_items($arg){
    return MADkit\Modules\Handler::$active_modules['ett_timetables']['class']->query()->add_item($arg);
}

function ts_get_activity_name($arg){
    return MADkit\Modules\Handler::$active_modules['ett_activities']['class']->get_activity_name($arg);
}

function ts_get_activity_group($arg){
    return MADkit\Modules\Handler::$active_modules['ett_activities']['class']->get_activity_group($arg);
}

function ts_get_activities(){
    return MADkit\Modules\Handler::$active_modules['ett_activities']['class']->get_activities();
}
