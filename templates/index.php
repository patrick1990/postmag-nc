<?php
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
            <li><a id="postmagAliasFilterAll" href="#" class="icon-user active"><?php p($l->t('All aliases')); ?></a></li>
            <li><a id="postmagAliasFilterEnabled" href="#" class="icon-checkmark"><?php p($l->t('Enabled')); ?></a></li>
            <li><a id="postmagAliasFilterDisabled" href="#" class="icon-close"><?php p($l->t('Disabled')); ?></a></li>
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

