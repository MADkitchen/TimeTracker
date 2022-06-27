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
        return MADkitchen\Database\Handler::get_table_column_prop_array_by_key('TimeTracker', 'timetable', $tags, $prop, $key_out_type, $target);
    } else {
        return MADkitchen\Database\Handler::get_table_column_prop_by_key('TimeTracker', 'timetable', $tags, $prop);
    }
}

function ts_query_items($arg = [], $table = 'timetable') { //TODO:disable LIMIT=100 (number=false, query var)
    return MADkitchen\Modules\Handler::$active_modules['TimeTracker']['class']->query($table, $arg)->items;
}

function ts_update_item($key, $arg, $table = 'timetable') {
    return MADkitchen\Modules\Handler::$active_modules['TimeTracker']['class']->query($table)->update_item($key, $arg);
}

function ts_remove_item($key, $table = 'timetable') {
    return MADkitchen\Modules\Handler::$active_modules['TimeTracker']['class']->query($table)->delete_item($key);
}

function ts_add_items($arg, $table = 'timetable') {
    return MADkitchen\Modules\Handler::$active_modules['TimeTracker']['class']->query($table)->add_item($arg);
}

//function ts_resolve_relation($column_source, $id_source, $column_target = null, $table_source = 'timetable') {
function ts_get_column_value_by_id($column, $id, $get_row = false) {
    $entry = new MADkitchen\Database\Entry('TimeTracker', $column);
    $row = $entry->set_key($id);
    if (!$get_row) {
        return $entry->value ?? null;
    } else {
        return $row ?? null;
    }
}

function ts_get_entry_by_id($column, $id) {
    $entry = new MADkitchen\Database\Entry('TimeTracker', $column);
    $row = $entry->set_key($id);
    return $entry;
}

function ts_get_entry_by_value($column, $value) {
    $entry = new MADkitchen\Database\Entry('TimeTracker', $column);
    $row = $entry->set_value($value);
    return $entry;
}

function ts_get_entry_by_row($column, $row) {
    $entry = new MADkitchen\Database\Entry('TimeTracker', $column);
    $entry->set_row($row);
    return $entry;
}

function ts_get_id_by_column_value($column, $value, $get_row = false) {
    $entry = new MADkitchen\Database\Entry('TimeTracker', $column);
    $row = $entry->set_value($value);
    if (!$get_row) {
        return $entry->key ?? null;
    } else {
        return $row ?? null;
    }
}

function ts_is_lookup_table($table) {
    return !empty(MADkitchen\Database\Handler::get_tables_data('TimeTracker', $table)['lookup_table']);
}

function ts_get_table_source($column) {
    $entry = new MADkitchen\Database\Entry('TimeTracker', $column);
    return $entry->get_source_table($column);
}

function ts_get_lookup_columns($this_column, $this_table = 'timetable') {
    return MADkitchen\Database\Handler::get_lookup_columns('TimeTracker', $this_column, $this_table);
}

function ts_get_activities() {

    $items = ts_get_lookup_table_data(ts_get_table_source('activity_id'));

    $retval = [];

    foreach ($items as $item) {
        $activity_id_id = $item->id; //TODO: generalize 'id'
        $activity_id = $item->activity_id; //ts_resolve_relation('activity_id', $item['activity_id'], null, 'activity_id');
        $activity_id_name = $item->activity_id_name; //ts_resolve_relation('activity_id_name', $item['activity_id_name'], null, 'activity_id');
        $activity_group = ts_get_column_value_by_id('activity_group', $item->activity_group);
        $retval[$activity_group][$activity_id_id]['no'] = $activity_id;
        $retval[$activity_group][$activity_id_id]['name'] = $activity_id_name;
        $retval[$activity_group]['name'] = ts_get_column_value_by_id('activity_group_name', $item->activity_group);
    }
    return $retval;
}

function filter_args_out($data_cols, $query = [], $base_table = null) {
    $data_cols = array_values($data_cols); //TODO: check if associative arrays are really needed upstream
    $data_buffer = $data_cols;
    $data_tot = [];
    $watchdog = 0;
    do {
        $i = false;
        foreach ($data_buffer as $a) {
            $lookup_columns = array_intersect($data_cols, ts_get_lookup_columns($a));
            $found = null;
            if (empty($lookup_columns)) {
                $watchdog = 0;
                $a = ts_get_column_prop($a);

                $default_args = [
                    //'count' => true,
                    'groupby' => [
                        ts_is_lookup_table($base_table ?? ts_get_table_source($a)) ? 'id' : $a, //TODO: generalize 'id'
                    ],
                    'orderby' => [
                        $a,
                    ],
                ];

                $x = ts_query_items(
                        array_merge($default_args, $query),
                        $base_table ?? ts_get_table_source($a),
                );

                foreach ($x as $item) {
                    if (ts_get_table_source($a) === ($base_table ?? ts_get_table_source($a))) { //will not be used eventually...
                        $found = ts_get_entry_by_row($a, $item);
                    } else {
                        $found = ts_get_entry_by_id($a, $item->$a);
                    }
                }
                $data_buffer = array_diff($data_buffer, [$a]);
            } else {
                $i = true;
                $lookup_column = reset($lookup_columns);
                if (!empty($data_tot[$lookup_column])) { //first match only
                    $search_table = ts_get_table_source($lookup_column);

                    if (MADkitchen\Database\Handler::is_column_external('TimeTracker', $search_table, $a)) {
                        $x = ts_query_items(
                                ['id' => array_map(fn($y) => $y->row->$a, //TODO generalize 'id'
                                            $data_tot[$lookup_column]),
                                    'orderby' => [
                                        $a,
                                    ],
                                ],
                                ts_get_table_source($a)
                        );
                        foreach ($x as $item) {
                            $found = ts_get_entry_by_row($a, $item); //ts_get_entry_by_id($a, $item->$a);
                        }
                    } else {
                        foreach ($data_tot[$lookup_column] as $lookup_entry) {
                            $found = ts_get_entry_by_row($a, $lookup_entry->row);
                        }
                    }
                    $data_buffer = array_diff($data_buffer, [$a]);
                }
            }
            if (!empty($found)) {
                $data_tot[$a][] = $found;
            }
        }
        if ($watchdog++ > 3)
            break;
    } while ($i);

    return MADkitchen\Helpers\Common::ksort_by_array($data_tot, $data_cols);
}

function filter_args_in($args) {
    $watchdog = 0;
    do {
        $i = false;
        foreach ($args as $ak => $a) {

            $lookup_columns = array_intersect(get_report_vars(), ts_get_lookup_columns($ak));

            if (!empty($lookup_columns)) {
                $watchdog = 0;
                $i = true;
                $x = ts_query_items(
                        [$ak => $a],
                        ts_get_table_source(reset($lookup_columns)) //first match only
                );

                $filtered = array_map(fn($y) => $y->id, $x);

                if (!empty($args[reset($lookup_columns)])) {
                    $args[reset($lookup_columns)] = array_intersect($args[reset($lookup_columns)], $filtered); //TODO: generalize 'id'
                } else {
                    $args[reset($lookup_columns)] = $filtered;
                }

                unset($args[$ak]);
            }
        }
        if ($watchdog++ > 3)
            break;
    } while ($i);

    return $args; //check
}

function ts_add_external_columns_to_query_res($external_columns, $ref_columns, &$ref_query_res, $base_table = null) {

    $queries = [];
    foreach ($ref_columns as $column) {
        if (!empty($ref_query_res[$column])) {
            $queries[$column] = $ref_query_res[$column];
        }
    }

    $extra_entries = [];
    if (empty($base_table)) {

        foreach ($queries as $key => $query) {
            $extra_entries = array_merge($extra_entries, filter_args_out(array_merge($external_columns, [$key]), ['id' => $query])); //TODO: generalize 'id'
        }
    } else {
        $extra_entries = filter_args_out(array_merge($external_columns, $ref_columns), $queries, 'timetable');
    }

    foreach ($extra_entries as $key => $entry) {
        if (in_array($key, $external_columns)) {
            $ref_query_res[$key] = reset($entry)->key; //first item only
        }
    }
}

//check
function get_data_row_by_id($data_column, $id) {
    foreach ($data_column as $item) {
        if ($item->key == $id) {
            return $item->row;
        }
    }
}

function get_label($dataset_entries, $this_entry, $this_column) {
    $retval = [];
    switch ($this_column) {
        case 'job_wbs':
            $retval[] = get_data_row_by_id($dataset_entries['job_no'], $this_entry->row->job_no)->job_no; //TODO: generalize 'id'
            break;
        case 'job_tag':
            $wbs_row = get_data_row_by_id($dataset_entries['job_wbs'], $this_entry->row->job_wbs);
            $retval[] = get_data_row_by_id($dataset_entries['job_no'], $wbs_row->job_no)->job_no; //TODO: generalize 'id'
            $retval[] = $wbs_row->job_wbs;
            break;
    }
    $retval[] = $this_entry->value;
    $retval[] = $this_entry->row->{"{$this_column}_name"} ?? null;

    return join(" ", $retval);
}

function ts_get_current_user() {
    global $current_user;

    return reset(ts_query_items(['wp_id' => [$current_user->id]], 'user_name')) ?? null;
}

//$hierarchy is an array of items hierarchically linked and ordered from top to bottom, at caller's responsibility

function ts_get_hierarchical_structure($hierarchy) {
    $retval = [];
    $x = ts_query_items([
        'groupby' => [
            $hierarchy
        ],
            ], ts_get_table_source(end($hierarchy))
    );

    $hierarchy = array_reverse($hierarchy);

    foreach ($x as $x_row) {

        $levels = [];
        $item_tag = reset($hierarchy);
//TODO: generalize 'id'
        do {
            $levels[$item_tag] = !empty($prev_tag) ? ts_get_column_value_by_id($item_tag, $levels[$prev_tag]->$item_tag, true) : $x_row;
            if (empty($prev_tag)) {
                $retval[$item_tag]['ids'][$levels[$item_tag]->id] = [];
                $retval[$item_tag]['count'][$levels[$item_tag]->id] = 1;
            } else {
                $retval[$item_tag]['ids'][$levels[$item_tag]->id][$levels[$prev_tag]->id] = $retval[$prev_tag]['ids'][$levels[$prev_tag]->id];
                $retval[$item_tag]['count'][$levels[$item_tag]->id] = ($retval[$item_tag]['count'][$levels[$item_tag]->id] ?? 0) + 1;
            }
            //$retval[$item_tag]['count'][$levels[$item_tag]->id]--; //table is assumed aligned hence row is shared between columns
            $retval[$item_tag]['rows'][$levels[$item_tag]->id] = $levels[$item_tag];

            $prev_tag = $item_tag;
        } while (($item_tag = next($hierarchy)) !== false);

        unset($prev_tag);
    }

    return ['data' => array_reverse($retval), 'count' => count($x)];
}

function ts_get_table_inner_by_hierarchical_structure($hierarchical_structure) {
    $table_row = '<tr class="w3-center">%s</tr>';
    $table_cell = '<td rowspan="%1$s" class="w3-button mk-cell w3-padding-16" data-key="%3$s" data-type="%4$s">%2$s</td>';
    $cells = [];
    $this_span = 0;
    $list = array_keys($hierarchical_structure['data']);
    foreach ($list as $item) {
        reset($hierarchical_structure['data'][$item]['count']);
        for ($i = 0; $i < $hierarchical_structure['count']; $i++) {
            if (!$this_span) {
                $this_span = current($hierarchical_structure['data'][$item]['count']);
                $this_key = key($hierarchical_structure['data'][$item]['count']);
                $this_value = $hierarchical_structure['data'][$item]['rows'][$this_key]->$item . ' - ' . $hierarchical_structure['data'][$item]['rows'][$this_key]->{"{$item}_name"};
                $cells[$item][$i] = sprintf($table_cell, $this_span, $this_value, $this_key, $item);
                next($hierarchical_structure['data'][$item]['count']);
            } else {
                $cells[$item][$i] = '';
            }
            $this_span--;
        }
    }
    $table_inner = '';
    for ($i = 0; $i < $hierarchical_structure['count']; $i++) {
        $row_inner = '';
        foreach ($list as $item) {
            $row_inner .= $cells[$item][$i];
        }
        $table_inner .= sprintf($table_row, $row_inner);
    }

    return $table_inner;
}

function ts_build_hierarchical_table(array $ordered_list) {

    $table = '<table class="w3-table-all w3-card w3-center mk-medium">%s</table>';

    $header_row = '<tr class="w3-red">%s</tr>';
    $footer_row = '<tr class="mk-xlarge">%s</tr>';
    $header_cell = '<th class="mk-large w3-padding-16 w3-center">%1$s</th>';
    $footer_cell = '<td class="w3-button w3-red w3-ripple w3-block w3-center">%1$s</td>';

    $column_titles = array_map(fn($a) => sprintf($header_cell, $a),
            array_map(fn($a) => ts_get_column_prop($a, 'description'), $ordered_list)
    );
    $retval = sprintf($header_row, join('', $column_titles));

    $retval .= ts_get_table_inner_by_hierarchical_structure(ts_get_hierarchical_structure($ordered_list));

    $btm_buttons = array_map(fn($a) => sprintf($footer_cell, $a),
            array_map(fn($a) => '&plus;', $ordered_list),
            []
    );

    $retval .= sprintf($footer_row, join('', $btm_buttons));

    echo sprintf($table, $retval);
}

//TODO: rationalize this mind of functions
function ts_build_users_table() {

    $ordered_list = [
        'user_name',
        'user_group',
        'user_role',
        'job_settings'
    ];

    $table = '<table class="w3-table-all w3-card w3-center mk-medium">%s</table>';

    $header_row = '<tr class="w3-red">%s</tr>';
    $header_cell = '<th class="mk-large w3-padding-16 w3-center">%1$s</th>';

    $column_titles = array_map(fn($a) => sprintf($header_cell, $a),
            array_map(fn($a) => ts_get_column_prop($a, 'description'), $ordered_list)
    );
    $retval = sprintf($header_row, join('', $column_titles));

    $table_row = '<tr class="w3-center">%s</tr>';
    $table_cell = '<td rowspan="%1$s" class="w3-button mk-cell w3-padding-16" data-key="%3$s" data-type="%4$s">%2$s</td>';

    $x = ts_get_lookup_table_data('user_name');

    //TODO:generalize!

    foreach ($x as $item) {
        $inner = '';
        $inner .= sprintf($table_cell, '1', $item->user_name, $item->id, 'user_name');
        $inner .= sprintf($table_cell, '1', ts_get_column_value_by_id('user_group', $item->user_group) . ' - ' . ts_get_column_value_by_id('user_group_name', $item->user_group), $item->user_group, 'user_group');
        $inner .= sprintf($table_cell, '1', ts_get_column_value_by_id('user_role', $item->user_role) . ' - ' . ts_get_column_value_by_id('user_role_name', $item->user_role), $item->user_group, 'user_group');
        $inner .= sprintf($table_cell, '1', '&plus;', $item->user_name, 'job_settings'); //develop!
        $retval .= sprintf($table_row, $inner);
    }


    echo sprintf($table, $retval);
}

//TODO: generalize better
function ts_get_search_field($item_sel = 'jQuery([])', $grp_swc_sel = 'jQuery([])', $grp_blk_sel = 'jQuery([])', $val = 'jQuery(this).val()', $close_on_exit = 'false', $open_on_exit = 'false') {
    global $mk_plugin_url; //TODO: cleanup
    return '<div><input type="search" placeholder="Search..." id="ts_modal_search" onkeyup="'
            . get_one_word_find_args($item_sel, $grp_swc_sel, $grp_blk_sel, $val, $close_on_exit, $open_on_exit)
            . '" class="w3-white w3-input" style="background-image: url(\''
            . $mk_plugin_url . join('/', array('assets', 'images', 'searchicon.png'))
            . '\'); background-position: 14px 12px; background-repeat: no-repeat; padding: 14px 20px 12px 45px;"></div>';
}

//TODO: generalize better
function get_one_word_find_args($item_sel = "jQuery([])", $grp_swc_sel = "jQuery([])", $grp_blk_sel = "jQuery([])", $val = 'jQuery(this).val()', $close_on_exit = 'false', $open_on_exit = 'false') {
    return htmlspecialchars("one_word_find($item_sel,$grp_swc_sel,$grp_blk_sel,$val,$close_on_exit,$open_on_exit);");
}

function ts_get_lookup_table_data($table) {
    return \MADkitchen\Database\Handler::maybe_get_lookup_table('TimeTracker', $table);
}
