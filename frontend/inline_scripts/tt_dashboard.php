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
?>
<script>

    var select_identifier = 'tsr_select_';
    var xhrQueue = [];
    var xhrCount = 0;
    var xhrObj = {
        ajaxobject: null,
        chartobjects: [null]
    };

    jQuery(document).ready(function ($) {
        $('input:checkbox').prop("checked", false);
        let a = $('input#after');
        a.val(a.prop('min'));
        let b = $('input#before');
        b.val(b.prop('max'));

        update_selects();
        refresh_report();
    });

    function request_data(xhrObj) {
        let data = {};
        jQuery('input[name^="' + select_identifier + '"]:checked').each(function () {
            let x = jQuery(this).attr('name').replace(select_identifier, '');
            let y = jQuery(this).data("key");
            if (x in data) {
                data[x].push(y);
            } else {
                data[x] = [y];
            }
        });
        let w = typeof xhrObj.lastclickedobj ? xhrObj.lastclickedobj.replace(select_identifier, '') : '';

        let a = jQuery('#after');
        let b = jQuery('#before');
        if (a.val() !== a.prop('min'))
            data['after'] = a.val();
        if (b.val() !== b.prop('max'))
            data['before'] = b.val();

        let z = JSON.stringify(data);

        xhrObj.ajaxobject = jQuery.ajax({
            type: 'POST',
            data: {action: 'ajax_build_report', data_out: z, current_group: w},
            dataType: 'json',
            url: '<?php echo admin_url('admin-ajax.php') ?>',
            success: function (data) {
                jQuery('#tsr_selectors').html(data.selectors);
                update_selects();
                xhrObj.chartobjects[0] = build_graph(data.chartsdata[0]);
                xhrObj.chartobjects[1] = build_graph2(data.chartsdata[1]);
            },
            error: function (textStatus, errorThrown, jqXHR) {
                //console.log('textStatus');
                //console.log(textStatus);
            }
        });
    }

    function build_graph(data_in) {
        const ctx = document.getElementById('myChart').getContext('2d');
        return [
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data_in['date_rec'],
                    datasets: [{
                            label: '<?php echo ts_get_column_prop('time_units', 'description') ?>',
                            data: data_in['sum_time_units'],
                            fill: false,
                            borderColor: 'rgb(75, 192, 192)',
                            tension: 0.1
                        }]
                },
                options: {
                    plugins: {legend: false},
                    responsive: true,
                    aspectRatio: 3
                }
            })
        ];
    }

    function build_graph2(data_in) {
        const ctx = document.getElementById('myChart2').getContext('2d');

        let x = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: data_in['activity_id'],
                datasets: [{
                        label: '<?php echo ts_get_column_prop('time_units', 'description') ?>',
                        data: data_in['sum_time_units'],
                        fill: false,
                        //borderColor: 'rgb(75, 192, 192)',
                        tension: 0.1,
                        backgroundColor: get_random_rgb(data_in['activity_id'].length),
                        cutout: "50%"
                    }]
            },
            options: {
                plugins: {legend: false},
                //maintainAspectRatio: false,
                responsive: true,
                aspectRatio: 1

            }
        });

        return [
            x
        ];
    }

    function update_selects() {
        jQuery('input[name^="' + select_identifier + '"]').change(refresh_report);
        jQuery('input#after').change(refresh_report);
        jQuery('input#before').change(refresh_report);

        jQuery('div[name="reset"]').click(function () {
            let a = jQuery(this).siblings('div[name="block"]');
            a.children('input:checkbox').prop("checked", false);
            let c = a.children('input#after');
            c.val(c.prop('min'));
            let b = a.children('input#before');
            b.val(b.prop('max'));
            jQuery(this).hide();
            refresh_report();
        });
    }

    function refresh_report(e) {

        xhrQueue.push(xhrCount);
        setTimeout(function () {
            xhrCount = ++xhrCount;
            if (xhrCount === xhrQueue.length) {
                if (xhrObj.ajaxobject !== null) {
                    xhrObj.ajaxobject.abort();
                }
                xhrObj.chartobjects.forEach(function (value, index, chart) {
                    if (value !== null) {
                        chart[index][0].destroy();
                    }
                });
                xhrObj = {
                    ajaxobject: null,
                    chartobjects: [null],
                    lastclickedobj: e ? e.target.name : ''
                };

                request_data(xhrObj);

                xhrQueue = [];
                xhrCount = 0;
            }
        }, 2000);

    }

    function toggle(x) {
        jQuery('[id^="tsr_select_"]').not(x).next('div').hide();
        jQuery(x).next('div').toggle();
    }


</script>

<?php
