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

$ajax_functions = ['ajax_send_to_db', 'ajax_build_row', 'ajax_build_timesheet'];

foreach ($ajax_functions as $item) {
    add_action('wp_ajax_' . $item, $item);
    add_action('wp_ajax_nopriv_' . $item, $item);
}

function build_header($year = null, $week = null) {

    $retval_th = '<th id="ts_header_select" style="width:30%%; padding:0">%s</th>';
    $retval_select = '<select id="ts_table_%1$s" name="ts_table_%1$s" class="w3-button w3-ripple w3-large w3-block" style="padding:0; margin:0">%2$s</select>';
    $retval_option = '<option value="%1$s" %2$s>%3$s</option>';

    $retval1 = '';
    foreach (MADkitchen\Helpers\Time::get_years($year) as $no => $item) {
        $retval1 .= sprintf($retval_option, $item['year'], selected($item['selected'], true, false), $item['year']);
    }
    $retval2 = '';
    foreach (MADkitchen\Helpers\Time::get_weeks($year, $week) as $no => $item) {
        $retval2 .= sprintf($retval_option, $no, selected($item['selected'], true, false), $item['range']);
    }

    $buffer = sprintf($retval_th, sprintf($retval_select, 'year', $retval1) . sprintf($retval_select, 'week', $retval2));

    foreach (MADkitchen\Helpers\Time::get_days($year, $week) as $no => $item) {
        $buffer .= '<th id="ts_weekday_' . $no . '" data-date="' . $item['date'] . '" class="w3-padding-16" style="width:10%">';
        $buffer .= '<div class="w3-block">' . $item['name'] . '</div>';
        $buffer .= '<div class="w3-block w3-large">' . $item['number'] . '</div>';
        $buffer .= '</th>';
    }

    echo '<tr id="ts_header" class="w3-red">' . $buffer . '</tr>';
}

function build_table($year = null, $week = null) {
    global $current_user;
    $days = MADkitchen\Helpers\Time::get_days($year, $week);

    $x = ts_query_items([
        'user_name' => $current_user->user_login,
        'sum' => [
            'time_units'
        ],
        'groupby' => ts_get_column_prop(array_merge(get_timesheet_vars(), ['date_rec', 'id'])),
        'date_query' => [
            'column' => ts_get_column_prop('date_rec'),
            [
                'before' => strtotime($days[6]['date']) + 1,
                'after' => strtotime($days[0]['date']) - 1,
            ]
        ]
            ,
    ]);

    $rows = [];
    $table = [];
    $subtotals = [];
    $total = 0;

    for ($i = 0;
            $i < 7;
            $i++) {
        $subtotals[$i] = 0;

        $extra_cols = array_diff(get_timesheet_vars(), get_row_label_id());

        $tot_cols = get_timesheet_vars();

        foreach ($x as $item) {
            if ($item['date_rec'] == $days[$i]['date']) {

                ts_add_external_columns_to_query_res($extra_cols, get_row_label_id(), $item);

                $label = get_row_label_id($item);
                $label_array = ts_get_column_prop($tot_cols, 'name', $item);
                if (!array_key_exists($label, $rows)) {
                    $rows[$label] = $label_array;
                }
                $table[$label][$i] = [
                    'value' => $item['sum_' . ts_get_column_prop('time_units')],
                    'key' => $item[ts_get_column_prop('id')]
                ];

                $subtotals[$i] += $table[$label][$i]['value'];
                $total += $table[$label][$i]['value'];
            }
        }
    }

    build_subtotals_row($subtotals);

    foreach ($table as $key => $value) {
        build_row(
                $rows[$key],
                $value,
        );
    }

    build_last_row($total);
}

function get_timesheet_vars() {
    return [
        'activity_id',
        'activity_id_name',
        'job_no',
        'job_wbs',
        'job_tag'
    ];
}

function build_subtotals_row($subtotals = array()) {
    $retval = '<tr class="w3-center">';
    $retval .= '<td class="w3-center">' . __('Sub totals') . '</td>';
    for ($i = 0;
            $i < 7;
            $i++) {
        $value = isset($subtotals[$i]) ? $subtotals[$i] : '0';
        $retval .= '<td id="ts_subtot_' . $i . '" class="w3-center">' . round($value, 2) . '</td>';
    }
    $retval .= '</tr>';
    echo $retval;
}

function build_last_row($total = 0) {

    $retval = '<td id="ts_table_new" class="w3-xlarge" style="padding:0">';
    $retval .= '<div onclick="filter(); jQuery(\'#ts_modal_newrow\').show()" class="w3-button w3-red w3-ripple w3-block">&plus;</div>';
    $retval .= '</td>';
    for ($i = 0;
            $i < 5;
            $i++) {
        $retval .= '<td></td>';
    }
    $retval .= '<td class="w3-center">' . __('Total') . '</td>';
    $retval .= '<td id="ts_tot" class="w3-center">' . round($total, 2) . '</td>';

    echo sprintf('<tr>%s</tr>', $retval);
}

function get_job_tags() { //filter query by user later
    $tags = ts_get_column_prop(['job_no', 'job_wbs', 'job_tag']);
    $w = [];
    foreach ($tags as $tag) {
        $x = ts_query_items(
                [
                    //'count' => true,
                    'groupby' => [
                        'id', //TODO: generalize id
                    ],
                    'orderby' => [
                        $tag,
                    ],
                    'order' => 'ASC',
                ],
                ts_get_table_source($tag)
        );

        foreach ($x as $row) {
            //optimize conversion to entries
            $w[$tag][] = ts_get_entry_by_row($tag, $row);
        }
    }

    foreach ($w['job_tag'] as $item) {
        $label = get_label($w, $item, 'job_tag');
        echo "<option value=\"{$item->value}\" data-key=\"{$item->key}\">$label</option>";
    }
}

function get_row_label_id($target = null) {
    $array = [
        'job_tag',
        'activity_id'
    ];

    if (is_array($target)) {
        return implode('_', array_map(function ($x)use ($target) {
                    return $target[$x];
                }, $array));
    } else if ($target === 'print_js') {
        return implode('+"_"+', ts_get_column_prop($array));
    } else if (is_null($target)) {
        return ts_get_column_prop($array);
    }
}

function build_row($data, $values = array()) {

    $labels = ts_get_column_prop(get_timesheet_vars());
    $row_id = get_row_label_id($data);

    $retval = sprintf('<tr id="ts_row_%1$s" data-%2$s="%3$s" data-%4$s="%5$s">',
            $row_id,
            $labels['job_tag'],
            $data[$labels['job_tag']],
            $labels['activity_id'],
            $data[$labels['activity_id']],
    );

    //Resolve
    if (!empty($data['job_tag'])) {
        $data['job_no'] = ts_get_column_value_by_id('job_no', $data['job_no']);
        $data['job_wbs'] = ts_get_column_value_by_id('job_wbs', $data['job_wbs']);
        $data['job_tag'] = ts_get_column_value_by_id('job_tag', $data['job_tag']);
    }

    if (!empty($data['activity_id'])) {
        $data['activity_id_name'] = ts_get_column_value_by_id('activity_id_name', $data['activity_id_name']);
        $data['activity_id'] = ts_get_column_value_by_id('activity_id', $data['activity_id']);
    }

    $retval .= '<td class="w3-center">';
    $retval .= '<div class="w3-row-padding w3-center">';
    $retval .= '<div class="w3-third">' . $data[$labels['job_no']] . '</div>';
    $retval .= '<div class="w3-third">' . $data[$labels['job_wbs']] . '</div>';
    $retval .= '<div class="w3-third">' . $data[$labels['job_tag']] . '</div>';
    $retval .= '</div>';
    $retval .= '<div class="w3-block w3-padding-16 w3-large">' . $data[$labels['activity_id']] . ' - ' . $data[$labels['activity_id_name']] . '</div>';
    $retval .= '</td>';

    for ($i = 0;
            $i < 7;
            $i++) {
        $key = isset($values[$i]) ? $values[$i]['key'] : '';
        $value = isset($values[$i]) ? $values[$i]['value'] : '';
        $retval .= '<td name="ts_' . $i . '_entry" data-day="' . $i . '" data-key="' . $key . '" class="w3-button w3-center w3-xlarge" style="vertical-align: middle;">' . $value . '</td>';
    }

    $retval .= '</tr>';

    echo $retval;
}

function build_timesheet($year = null, $week = null) {
    build_header($year, $week);
    build_table($year, $week);
}

function fill_activitylist() {
    $lookup_activities = ts_get_activities();

    $retval = '';

    foreach ($lookup_activities as $key => $value) {
        $retval .= "<div onclick=\"jQuery('#ts_modal-$key-block').toggleClass('w3-hide')\" class=\"w3-button w3-large w3-block w3-left-align w3-red\" id=\"ts_modal-$key-group\">$key - " . $lookup_activities[$key]['name'] . "</div>";
        $retval .= "<div id=\"ts_modal-$key-block\" class=\"w3-container w3-hide\">";
        foreach ($value as $subkey => $subvalue) {
            if ($subkey == 'name') {
                continue;
            }
            $retval .= "<div class=\"w3-button w3-block w3-large w3-left-align\" id=\"ts_modal-item\" data-key=\"$subkey\">{$subvalue['no']} - {$subvalue['name']}</div>";
        }
        $retval .= '</div>';
    }

    return $retval;
}

function ajax_send_to_db() {
//TODO: improve sanitization

    global $current_user;

    if (isset($_POST['date_rec']) &&
            isset($_POST['activity_id']) &&
            isset($_POST['job_tag'])) {

        $retval = null;

        if (isset($_POST['key']) && $_POST['key']) {
            if ($_POST['time_units']) {
                if (ts_update_item($_POST['key'], ['time_units' => $_POST['time_units'],])) {
                    $retval = $_POST['key'];
                }
            } else {
                if (ts_remove_item($_POST['key'])) {
                    $retval = '';
                }
            }
        } else {

            $retval = ts_add_items([
                'activity_id' => $_POST['activity_id'],
                'date_rec' => $_POST['date_rec'],
                'user_group' => 'y', //TODO: add wp option
                'time_units' => $_POST['time_units'],
                'user_name' => $current_user->user_login, //TODO: check if better $current_user->display_name
                'user_role' => 'x', //TODO: add wp option
                'job_tag' => $_POST['job_tag'],
            ]);
        }
    }

    $retval = is_null($retval) ? 'FAILED' : $retval;

    echo $retval;

    wp_die(); // this is required to terminate immediately and return a proper response
}

function ajax_build_row() {
    if (isset($_POST['job_tag']) &&
            isset($_POST['activity_id'])) {

        $extra_cols = array_diff(get_timesheet_vars(), get_row_label_id());
        $item = ts_get_column_prop(get_row_label_id(), 'name', $_POST);
        ts_add_external_columns_to_query_res($extra_cols, get_row_label_id(), $item);

        build_row($item);
    } else {
        return false;
    }
    wp_die();
}

function ajax_build_timesheet() {
    if (isset($_POST['year']) &&
            isset($_POST['week'])) {


        build_timesheet($_POST['year'], $_POST['week']);
    } else {
        return false;
    }
    wp_die();
}

function js_build_activity_click() {
    $retval = "function () {\n"
            . "    let y = jQuery('#ts_table_new div');\n";
    $array = get_row_label_id();
    unset($array['activity_id']);
    foreach ($array as $item) {
        $item = ts_get_column_prop($item);
        $retval .= "    const $item = $('#ts_modal_select_$item option:selected').attr('data-key');\n";
    }
    $item = ts_get_column_prop('activity_id');

    $retval .= "    const $item = $(this).attr('data-key');\n";

    $rule = get_row_label_id('print_js');

    $retval .= "    const id_no = $rule;\n"
            . "        if (!$('#ts_row_' + id_no).length) {\n"
            . "            y.html(get_spinner('w3-text-white w3-jumbo'));\n"
            . "            $.ajax({\n"
            . "                type: 'POST',\n"
            . "                data: {action: 'ajax_build_row',\n";

    $output = [];
    foreach (get_row_label_id() as $item) {
        $item = ts_get_column_prop($item);
        $output[] = "$item: $item";
    }
    $retval .= implode(",\n", $output) . "\n"
            . "                },\n"
            . "            url: '" . admin_url('admin-ajax.php') . "',\n"
            . "            success: function (data) {\n"
            . "                $(data).insertBefore($('#ts_table_new').parent());\n"
            . "                table_changed();\n"
            . "                y.html('&plus;');\n"
            . "            },\n"
            . "            error: function (textStatus, errorThrown, jqXHR) {\n"
            . "                y.html(textStatus);\n"
            . "            }\n"
            . "        });\n"
            . "        $('#ts_modal_newrow').hide();\n"
            . "    }\n"
            . "}\n";

    echo $retval;
}

function js_build_update_entry() {
    $retval = " function update_entry(reset = false) {\n"
            . "        let x = jQuery('#ts_modal_entry');\n";
    foreach (get_row_label_id() as $item) {
        $item = ts_get_column_prop($item);
        $retval .= "let $item = x.data('$item');\n";
    }
    $retval .= "        let day = x.data('day');\n"
            . "        let key = x.data('key');\n"
            . "        let date_rec = jQuery('#ts_weekday_' + x.data('day')).data('date');\n"
            . "        let val = reset ? 0 : x.val();\n";
    $rule = get_row_label_id('print_js');
    $retval .= "        const id_no = $rule;\n"
            . "        let y = jQuery('#ts_row_' + id_no + ' [data-day=\"' + day + '\"]');\n"
            . "        y.html(get_spinner('w3-text-red w3-jumbo'));\n"
            . "        jQuery.ajax({\n"
            . "            type: 'POST',\n"
            . "            data: {action: 'ajax_send_to_db',\n";

    foreach (array_merge(['date_rec'], get_row_label_id()) as $item) {
        $item = ts_get_column_prop($item);
        $retval .= "$item: $item,\n";
    }

    $item = ts_get_column_prop('time_units');
    $retval .= "$item: val,\n"
            . "                key: key\n"
            . "            },\n"
            . "            url: '" . admin_url('admin-ajax.php') . "',\n"
            . "            success: function (data) {\n"
            . "                //$('#ts_table_week').html(data);\n"
            . "                if (data === '') {\n"
            . "                    y.text('');\n"
            . "                    y.data('key', '');\n"
            . "                } else if (data !== 'FAILED') { //TODO: finalize\n"
            . "                    y.text(round(val,2));\n"
            . "                    y.data('key', data);\n"
            . "                } else { //TODO: finalize\n"
            . "                }\n"
            . "                update_subtotal(day);\n"
            . "                update_total();\n"
            . "            },\n"
            . "            error: function (textStatus, errorThrown, jqXHR) {\n"
            . "                y.html('ERROR');\n"
            . "            }\n"
            . "        });\n"
            . "        //encodeURIComponent\n"
            . "        jQuery('#ts_modal_newentry').hide();\n"
            . "    }\n";

    echo $retval;
}

function js_build_table_changed() {

    $retval = "    function table_changed() {\n"
            . "        jQuery(\"[name$='_entry']\").click(function () {\n"
            . "            jQuery('#ts_modal_newentry').show();\n"
            . "            const x = jQuery('#ts_modal_entry');\n"
            . "            x.val(jQuery(this).text());\n"
            . "\n";

    foreach (get_row_label_id() as $item) {
        $item = ts_get_column_prop($item);
        $retval .= "x.data('$item', jQuery(this).parent().data('$item'));\n";
    }

    $retval .= "            x.data('day', jQuery(this).data('day'));\n"
            . "            x.data('key', jQuery(this).data('key'));\n"
            . "        });\n"
            . "    }\n";

    echo $retval;
}
