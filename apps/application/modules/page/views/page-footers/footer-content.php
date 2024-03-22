<?php
if (!defined("BASEPATH")) {
	exit("Cannot load script directly.");
}

?>
	</div>
	<!-- End of Main Content -->

	<!-- Footer -->
	<footer class="sticky-footer bg-white">
		<div class="container my-auto">
			<div class="copyright text-center my-auto">
				<span><?= Instance_config::$env_apc['env']['copyright.' . Instance_config::$env_group['env_env'] . '.string'];?></span>
			</div>
		</div>
	</footer>
	<!-- End of Footer -->
</div>
<!-- End of Content Wrapper -->