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
    jQuery(document).ready(function ($) {

        date_changed();
        table_changed();

        $('[id$="-item"]').click(
<?php js_build_activity_click() ?>
        );

        $('select[id^="ts_table_"]').each(function () {
            $(this).val($(this).children('[selected="selected"]').val());
        });
    });

<?php js_build_update_entry() ?>

    function date_changed() {
        jQuery("#ts_table_year").change(function () {
            update_table();
        });

        jQuery("#ts_table_week").change(function () {
            update_table();
        });
    }

    function update_subtotal(day) {
        let sum = 0;
        jQuery('[data-day="' + day + '"]').each(function () {
            sum += Number(jQuery(this).text());
        });
        jQuery("#ts_subtot_" + day).text(mk_round(sum, 2));
    }

    function update_total() {
        let sum = 0;
        jQuery('[id^="ts_subtot_"]').each(function () {
            sum += Number(jQuery(this).text());

        });
        jQuery("#ts_tot").text(mk_round(sum, 2));
    }

<?php js_build_table_changed() ?>


    function update_table() {
        let year = jQuery("#ts_table_year").val();
        let week = jQuery("#ts_table_week").val();
        y = jQuery('#ts_header_select');
        y.html(mk_get_spinner('w3-text-white mk-jumbo'));
        jQuery.ajax({
            type: 'POST',
            data: {action: 'ajax_build_timesheet',
                year: year,
                week: week},
            url: '<?php echo admin_url('admin-ajax.php') ?>',
            success: function (data) {
                jQuery('table').html(data);
                date_changed();
                table_changed();
            },
            error: function (textStatus, errorThrown, jqXHR) {
                y.html('ERROR');
            }
        });
    }
</script>
<?php

