<?php
if (!defined("BASEPATH")) {
	exit("Cannot load script directly.");
}
?>
<div class="modal-dialog" role="document">
	<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title">
				Page Error
			</h5>
			<button class="close" type="button" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">X</span>
			</button>
		</div>
		<div class="modal-body">
			<?php
			if (isset($json_response['errors']) && is_array($json_response['errors'])) {
				?>
				<ul class="list-group-item">
					<?php
					foreach ($json_response['errors'] as $error_msg) {
						if (is_string($error_msg)) {
							?><li class="list-group-item text-danger"><?=$error_msg;?></li><?php
						}
					}
					?>
				</ul>
				<?php
			}
			?>
		</div>
		<div class="modal-footer">
			<button class="btn btn-sm btn-secondary" type="button" data-dismiss="modal">Cancel</button>
		</div>
	</div>
</div>