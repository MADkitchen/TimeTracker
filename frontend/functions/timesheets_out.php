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

$ajax_functions = ['ajax_build_report'];

foreach ($ajax_functions as $item) {
    add_action('wp_ajax_' . $item, $item);
    add_action('wp_ajax_nopriv_' . $item, $item);
}

function populate_selectors($filter = [], $date_range = []) {

    if (!$date_range) {
        $date_range = get_filter_date_range();
    }

    $data = get_selectors_data($filter, $date_range);

    $button_wrapper = '<div style="position:relative">'
            . '<div name="reset" class="w3-button w3-red w3-display-topleft"%1$s>&#9745;</div>'
            . '%2$s'
            . '</div>';

    //Populate elements inner except date range
    $html = [];
    foreach ($data as $key => $item) {
        $inner = '';
        $is_filtered = array_key_exists($key, $filter);
        $is_alone = count($item) == 1;
        foreach ($item as $value) {
            $checked = $is_filtered && in_array($value, $filter[$key]);
            $disabled = $is_alone && !$checked;
            $inner .= sprintf('<input name="tsr_select_%2$s" type="checkbox" value="%1$s"' . checked($checked, true, false) . disabled($disabled, true, false) . '> <label>%1$s</label><br>', $value, $key);
        }
        $html[$key] = '<div id="tsr_select_' . $key . '" class="w3-button w3-red w3-ripple w3-block" onclick="toggle(this)">'
                . ts_get_column_prop($key, 'description')
                . '</div><div name="block" style="display:none" class="w3-border w3-border-red w3-hover-border-gray w3-padding">' . $inner . '</div>';
    }
    $html = array_map(function ($a) use ($button_wrapper) {
        $display = str_contains($a, checked(true, true, false)) ? '' : ' style="display:none"';
        return sprintf($button_wrapper, $display, $a);
    }, $html);

    //Populate date range
    $min = '';
    $max = '';
    $after = '';
    $before = '';

    if (isset($date_range['range'])) {
        $min = !isset($date_range['range']['min']) ?: $date_range['range']['min'];
        $max = !isset($date_range['range']['max']) ?: $date_range['range']['max'];
        $after = isset($date_range['range']['after']) ? $date_range['range']['after'] : $date_range['range']['min'];
        $before = isset($date_range['range']['before']) ? $date_range['range']['before'] : $date_range['range']['max'];
    }

    $html['date_range'] = '<div id="tsr_select_range" class="w3-button w3-red w3-ripple w3-block" onclick="toggle(this)">' . __('Date range') . '</div>'
            . '<div name="block" style="display:none" class="w3-border w3-border-red w3-hover-border-gray w3-padding">'
            . '<input type="date" id="after" value="' . $after . '" min="' . $min . '" max="' . $max . '"> <label>' . __('After') . '</label><br>'
            . '<input type="date" id="before" value="' . $before . '" min="' . $min . '" max="' . $max . '"> <label>' . __('Before') . '</label><br>'
            . '</div>';
    $display = ($before !== $max) || ($after !== $min) ? '' : ' style="display:none"';
    $html['date_range'] = sprintf($button_wrapper, $display, $html['date_range']);

    //Paginate elements in columns
    $output = '';
    $col_no = 3;
    $items_per_column = ceil(count($html) / $col_no);
    $col_html = '<div class="w3-cell w3-third">%s</div>';
    $col = '';
    $count = 0;
    foreach ($html as $item) {
        $col .= $item;
        $count++;
        if ($count == $items_per_column) {
            $output .= sprintf($col_html, $col);
            $col = '';
            $count = 0;
        }
    }

    //Add residual elements to last column if any
    if ($col !== '') {
        $output .= sprintf($col_html, $col);
    }

    return $output;
}

function get_report_vars() {
    return [
        'user_name',
        'user_group',
        'user_role',
        'job_no',
        'job_wbs',
        'job_tag',
        'activity_group',
        'activity_id'
    ];
}

function get_filter_date_range(&$filter = []) {
    //Check if date range is requested and remove from filters to treat separately
    //$start = 0;
    //$end = 0;
    $date_range = [];
    $date_query = [];
    if (isset($filter['after'])) {
        //$start = $filter['start'];
        $date_range['after'] = $filter['after']; //strtotime($start) - 1;
        unset($filter['after']);
    } else if (isset($filter['before'])) {
        //$end = $filter['end'];
        $date_range['before'] = $filter['before']; //strtotime($end) + 1;
        unset($filter['before']);
    }

    //Prepare date query if needed
    if ($date_range) {
        $date_query = ['date_query' => [
                'column' => ts_get_column_prop('date_rec'),
                $date_range
            ]
        ];
    }

    //Check max range excluding date filters
    $date_check = ts_query_items(
            array_merge($filter, [
        'min' => [
            'date_rec'
        ],
        'max' => [
            'date_rec'
        ],
            ])
    );

    $date_range['min'] = $date_check[0]['min_date_rec'];
    $date_range['max'] = $date_check[0]['max_date_rec'];

    return ['range' => $date_range, 'query' => $date_query];
}

function get_selectors_data($filter = [], $date_range = []) {

    $data_cols = ts_get_column_prop(get_report_vars());

    //$date_range = get_filter_date_range($filter);

    $data_tot = [];
    foreach ($data_cols as $a) {

        $default_args = [
            'count' => true,
            'groupby' => [
                $a,
            ]
        ];

        $x = ts_query_items(
                array_merge($default_args, $filter, isset($date_range['query']) ? $date_range['query'] : []),
        );

        $data_tot[$a] = [];
        foreach ($x as $item) {
            $data_tot[$a][] = ts_resolve_relation($a,$item[$a]);
        }
    }

    return $data_tot;
}

function ajax_build_report() {
    $filters = [];

    if (isset($_POST['data_out'])) {
        $z = $_POST['data_out'];
        $y = json_decode(html_entity_decode(stripslashes($z)), true);
        if ($y) {
            $filters = ts_get_column_prop(get_report_vars(), 'name', $y);
            $date_range = get_filter_date_range($y);
        }
    }

    $w['selectors'] = populate_selectors($filters, $date_range);
    $w['chartsdata'][] = chart1_get_data($filters, $date_range);

    $v = json_encode($w);

    echo $v;

    wp_die();
}

function chart1_get_data($args = [], $date_range = []) {

    if (!isset($date_range)) {
        $date_range = get_filter_date_range();
    }


    $default_args = ['sum' => [
            'time_units'
        ],
        'groupby' => [
            'date_rec'],
    ];

    $x = ts_query_items(
            array_merge($default_args, $args, isset($date_range['query']) ? $date_range['query'] : [])
    );

    $w = [];
    foreach ($x as $z) {
        foreach ($z as $key => $value) {
            $w[$key][] = ts_resolve_relation($key,$value);
        }
    }

    return $w;
}
