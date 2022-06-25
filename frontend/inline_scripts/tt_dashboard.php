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
        toggle_open_modal(false);
        xhrObj.ajaxobject = jQuery.ajax({
            type: 'POST',
            data: {action: 'ajax_build_report', data_out: z, current_group: w},
            dataType: 'json',
            url: '<?php echo admin_url('admin-ajax.php') ?>',
            success: function (data) {
                jQuery('#tsr_selectors').html(data.selectors);
                update_selects();
                toggle_open_modal();
                xhrObj.chartobjects[0] = build_graph(data.chartsdata[0]);
                xhrObj.chartobjects[1] = build_graph2(data.chartsdata[1]);
                xhrObj.chartobjects[2] = build_graph3(data.chartsdata[2]);
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
                            data: data_in['sum_time_units']
                        }]
                },
                options: {
                    plugins: {
                        legend: false,
                        datalabels: false,
                        title: {
                            display: true,
                            text: '<?php echo ts_get_column_prop('time_units', 'description'). ' per ' . ts_get_column_prop('date_rec', 'description')?>'
                        }
                    },
                    responsive: true,
                    aspectRatio: 3,
                    elements: {
                        line: {
                            fill: false,
                            borderColor: get_random_rgb(data_in['sum_time_units'].length),
                            tension: 0.1,
                            borderWidth: 1
                        },
                        point: {
                            borderColor: 'rgb(0,0,0,0)',
                            backgroundColor: 'rgb(0,0,0,0)'
                        }
                    }
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
                        //fill: false,
                        //borderColor: 'rgb(75, 192, 192)',
                        //tension: 0.1,
                        backgroundColor: get_random_rgb(data_in['sum_time_units'].length),
                        //cutout: "50%",
                        hoverOffset: 10
                    }]
            },
            options: {
                plugins: {
                    legend: false,
                    datalabels: {
                        anchor: 'end',
                        align: 'end',
                        offset: 20,
                        //clamp: true,
                        /*display: function (context) {
                         const res = context.chart.data.datasets[0].data[context.dataIndex] > context.chart.data.datasets[0].data[context.dataIndex - 1] ? 'auto' : true;
                         return res; // display labels with an odd index
                         },*/
                        display: 'auto',
                        formatter: function (value, context) {
                            const res = context.chart.data.labels[context.dataIndex];
                            return res.lengt < 20 ? res : res.substring(0, 20) + '...';
                        },
                        borderWidth: 1
                    },
                        title: {
                            display: true,
                            text: '<?php echo ts_get_column_prop('time_units', 'description'). ' per ' . ts_get_column_prop('activity_id', 'description')?>'
                        }
                },
                layout: {
                    padding: {
                        left: 150,
                        right: 150,
                        top: 20,
                        bottom: 20
                    }

                },
                //maintainAspectRatio: false,
                responsive: true,
                aspectRatio: 1

            }
        });
        return [
            x
        ];
    }

    function build_graph3(data_in) {
        const ctx = document.getElementById('myChart3').getContext('2d');
        return [
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data_in['activity_group'],
                    datasets: [{
                            label: '<?php echo ts_get_column_prop('time_units', 'description') ?>',
                            data: data_in['sum_time_units'],
                            backgroundColor: get_random_rgb(data_in['sum_time_units'].length),
                            hoverOffset: 10
                        }]
                },
                options: {
                    scales: {
                        x: {
                            ticks: {
                                callback: function (val, index) {
                                    const res = this.getLabelForValue(val);
                                    return res.lengt < 20 ? res : res.substring(0, 20) + '...';
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: false,
                        datalabels: false,
                        title: {
                            display: true,
                            text: '<?php echo ts_get_column_prop('time_units', 'description'). ' per ' . ts_get_column_prop('activity_group', 'description')?>'
                        }
                    },
                    responsive: true,
                    aspectRatio: 1
                }
            })
        ];
    }

    function toggle_open_modal(show = true) {
        const x = jQuery('#tsr_selectors_open_modal');
        if (show === true) {
            x.addClass(['w3-red', 'w3-button']);
            x.html('&plus;');
            x.click(function () {
                jQuery('#ts_modal_selectors').show();
            });
        } else {
            x.removeClass(['w3-red', 'w3-button']);
            x.html(mk_get_spinner('w3-text-red mk-jumbo'));
            x.off();
    }
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
