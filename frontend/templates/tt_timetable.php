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

<div class="w3-container">
    <div id="ts_modal_newrow" class="w3-modal w3-card">
        <div class="w3-modal-content">
            <div class="w3-button w3-red w3-ripple w3-block w3-xlarge"" onclick="jQuery('#ts_modal_newrow').hide()" style="padding:0; margin:0">&times;</div>
            <div class="w3-row-padding w3-red w3-center"" style="padding:0; margin:0">
                <select id="ts_modal_select_job_tag" name="ts_modal_select_job_tag" class="w3-button w3-ripple w3-block mk-large w3-padding-8" style="padding:0; margin:0">
                    <?php get_job_tags() ?>
                </select>
            </div><div><input type="search" placeholder="Search.." id="ts_modal_search" onkeyup="filter(jQuery(this).val())" class="w3-white w3-input" style="background-image: url('<?php
                global $mk_plugin_url; //TODO: cleanup
                echo $mk_plugin_url . join('/', array('assets', 'images', 'searchicon.png'));
                ?>'); background-position: 14px 12px; background-repeat: no-repeat; padding: 14px 20px 12px 45px;"></div>
                              <?php echo fill_activitylist(); ?>
        </div>
    </div>
</div>

<div class="w3-container">
    <div id="ts_modal_newentry" class="w3-modal">
        <div class="w3-modal-content w3-red w3-animate-opacity w3-card-4">
            <div class="w3-container w3-padding-large">
                <div class="w3-container w3-cell w3-half">
                    <input type="number" placeholder="Hours" id="ts_modal_entry" class="w3-input w3-red w3-border w3-center mk-xxlarge w3-padding-32" min="0" max="24" step="0.01">
                </div>
                <div class="w3-container w3-cell w3-half">
                    <div id="ts_modal_entry_submit" onclick="update_entry()" class="w3-button w3-red w3-ripple w3-cell w3-third mk-xxlarge">&plus;</div>
                    <div id="ts_modal_entry_remove" onclick="update_entry(true)" class="w3-button w3-red w3-ripple w3-cell w3-third mk-xxlarge">&minus;</div>
                    <div id="ts_modal_entry_close" onclick="jQuery('#ts_modal_newentry').hide()" class="w3-button w3-red w3-ripple w3-cell w3-third mk-xxlarge">&times;</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="w3-responsive">
    <table class="w3-table-all w3-card w3-center mk-medium">
        <?php build_timesheet() ?>
    </table>
</div>

<?php
