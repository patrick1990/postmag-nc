<?php
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
		pattern="<?php p($_['regexDomain'])?>"/><br/>
	
	<label for="postmagUserAliasIdLen"><?php p($l->t('Length of user alias ids (only for new users)')); ?></label><br/>
	<input id="postmagUserAliasIdLen"
		type="number"
		value="<?php p($_['userAliasIdLen']); ?>"
		min="<?php p($_['userAliasIdLenMin']); ?>"
		max="<?php p($_['userAliasIdLenMax']); ?>"/><br/>
	
	<label for="postmagAliasIdLen"><?php p($l->t('Length of alias ids (only for new aliases)')); ?></label><br/>
	<input id="postmagAliasIdLen"
		type="number"
		value="<?php p($_['aliasIdLen']); ?>"
		min="<?php p($_['aliasIdLenMin']); ?>"
		max="<?php p($_['aliasIdLenMax']); ?>"/><br/>
</div>
