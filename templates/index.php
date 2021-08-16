<?php
/**
 * @author Patrick Greyson
 *
 * Postmag - Postfix mail alias generator for Nextcloud
 * Copyright (C) 2021
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

style('postmag', 'style');
script('postmag', 'aliasListHandler')
?>

<div id="app">
	<div id="app-navigation">
        <div class="app-navigation-new">
            <button id="postmagNewAlias" type="button" class="icon-add">
                <?php p($l->t('New alias')); ?>
            </button>
        </div>

        <ul class="with-icon">
            <li><a id="postmagAliasFilterAll" href="#" class="icon-user svg active"><?php p($l->t('All aliases')); ?></a></li>
            <li><a id="postmagAliasFilterEnabled" href="#" class="icon-checkmark svg"><?php p($l->t('Enabled')); ?></a></li>
            <li><a id="postmagAliasFilterDisabled" href="#" class="icon-close svg"><?php p($l->t('Disabled')); ?></a></li>
        </ul>
	</div>

	<div id="app-content">
        <div class="section">
            <h2><?php p($l->t('Welcome to Postmag!')); ?></h2>
            <h3><?php p($l->t('Your postfix mail alias generator.')); ?></h3>
            <p><?php p($l->t('Loading...')); ?></p>
        </div>
	</div>
</div>

