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
    <div id="ts_modal" class="w3-modal w3-card">
        <div class="w3-modal-content">
            <div id="ts_close_modal" class="w3-button w3-red w3-ripple w3-block w3-xlarge"" onclick="jQuery('#ts_modal').hide();" style="padding:0; margin:0">&times;</div>
            <div class="w3-large w3-padding-large w3-center">
            Buttons are disabled in this test.
            </div>
        </div>
    </div>
</div>

<div class="w3-responsive">
        <?php ts_build_users_table() ?>
</div>

<script>
    jQuery('.w3-button').not('#ts_close_modal').click(function () {
        jQuery('#ts_modal').show();
    });
</script>