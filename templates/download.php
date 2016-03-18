<?php extract($atts); ?>

<div id="ss-downloads">
	<h3>Your download is ready &raquo;</h3>
	<div class="btn-ss-downloads">
		<a target="_blank" href="<?php echo SSD_PLUGIN_URL; ?>/services/getfile.php?file=<?php echo $file; ?>" download><?php echo $title; ?></a>
	</div>
</div>