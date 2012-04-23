<ul id="AccountNavigation" class="AccountNavigation">
<?php
	foreach($itemsBefore as $item) {
?>
	<li class="nohover"><?= $item ?></li>
<?php
	}

	if (!$isAnon) {

	// Piggyback hack
	if ( isset( $piggyback ) ):
?>
	<li>
		<a href="<?= $piggybackUrl ?>">
			<?= $piggybackTargetUserAvatar ?>
			<img class="chevron" src="<?= $wg->BlankImgUrl; ?>">
		</a>
		<?= $piggybackDropdown?></li>
	</li>
<?php
	// End of the Piggyback hack
	endif;
?>
	<li>
		<a accesskey="." href="<?= $profileLink ?>">
			<?= $profileAvatar ?>
			<?= $username ?>
			<img class="chevron" src="<?= $wg->BlankImgUrl; ?>">
		</a>
		<ul class="subnav WikiaMenuElement">
<?php
		foreach($dropdown as $link) {
?>
			<li><?= $link ?></li>
<?php
		}
?>
		</ul>
	</li>
<?php
	} else {
?>
	<li>
		<?= $loginLink ?>
		<?= $loginDropdown ?>
	</li>
	<li>
		<?= $registerLink ?>
	</li>
<?php
	}
?>
</ul>
