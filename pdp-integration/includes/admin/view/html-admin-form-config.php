<?php
/**
*/
?>
<div class="wrap">
	<div class="icon32" id="icon-options-general"><br /></div>
	<h2><?php _e('PDP Integration','pdpinteg') ?></h2>
	<form method="post" action="options.php">
		<?php
			settings_fields( 'pdpinteg_settings' );
			do_settings_sections( 'pdpinteg_settings' );
			submit_button();
		?>
	</form>
</div>