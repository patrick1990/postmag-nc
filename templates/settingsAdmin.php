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

style('postmag', 'settingsAdmin');
script('postmag', 'settingsAdmin');
?>

<div class="section" id="postmag">
	<h2><?php p($l->t('Postmag settings')); ?></h2>
	<p class="settings-hint"><?php p($l->t('Set your target mail domain and alias id lengths.')); ?></p>
	
	<label for="postmagDomain"><?php p($l->t('Target mail domain')); ?></label><br/>
	<input id="postmagDomain"
		type="text"
		value="<?php p($_['domain']); ?>"
		pattern="<?php p($_['regexDomain'])?>"
        required/><br/>
	
	<label for="postmagUserAliasIdLen"><?php p($l->t('Length of user alias ids (only for new users)')); ?></label><br/>
	<input id="postmagUserAliasIdLen"
		type="number"
		value="<?php p($_['userAliasIdLen']); ?>"
		min="<?php p($_['userAliasIdLenMin']); ?>"
		max="<?php p($_['userAliasIdLenMax']); ?>"
        required/><br/>
	
	<label for="postmagAliasIdLen"><?php p($l->t('Length of alias ids (only for new aliases)')); ?></label><br/>
	<input id="postmagAliasIdLen"
		type="number"
		value="<?php p($_['aliasIdLen']); ?>"
		min="<?php p($_['aliasIdLenMin']); ?>"
		max="<?php p($_['aliasIdLenMax']); ?>"
        required/><br/>

    <label for="postmagReadyTime"><?php p($l->t('Time until alias changes are valid (ready time in seconds)')); ?></label><br/>
    <input id="postmagReadyTime"
       type="number"
       value="<?php p($_['readyTime']); ?>"
       min="<?php p($_['readyTimeMin']); ?>"
       required/><br/>
</div>
