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
    <div id="ts_modal_selectors" class="w3-modal w3-card">
        <div class="w3-modal-content  w3-red">
            <div class="w3-button w3-red w3-ripple w3-block w3-xlarge" onclick="jQuery('#ts_modal_selectors').hide()" style="padding:0; margin:0">&times;</div>
            <div id="tsr_selectors" class="w3-cell-row">
                <?php //echo populate_selectors() ?>
            </div>
        </div>
    </div>
</div>

<div id="tsr_selectors_spinner" class="w3-block mk-jumbo w3-text-red"></div>
<div id="tsr_selectors_open_modal_block" class="w3-row w3-margin-bottom" style="display:none">
    <div id="tsr_selectors_open_modal" class="w3-xlarge w3-red" style="position:relative">
        <div id="tsr_selectors_open_modal_reset" class="w3-button w3-display-topleft" style="display:none">&#9745;</div>
        <div id="tsr_selectors_open_modal_label" class="w3-ripple w3-button w3-col l8 m6 s12" onclick="jQuery('#ts_modal_selectors').show()">&plus;</div>
    </div>
    <div id="totals_widget" class="w3-col w3-border w3-border-red w3-text-red l4 m6 s12 w3-white" style="position: relative;  margin: auto">
        <div class="w3-text-red mk-small" style="float: right;"><?php echo ts_get_column_prop('time_units', 'description') ?></div>
        <div id="total_time" class="mk-xxlarge" style="float:right"></div>
    </div>
</div>

<div class="w3-row">
    <div class="w3-padding w3-col l6 m12 s12" style="position: relative;  margin: auto;">
        <canvas id="myChart2"></canvas>
    </div>
    <div class="w3-padding w3-col l6 m12 s12" style="position: relative;  margin: auto;">
        <canvas id="myChart3"></canvas>
    </div>
</div>

<div class="w3-padding w3-block" style="position: relative;  margin: auto;">
    <canvas id="myChart"></canvas>
</div>

<?php


