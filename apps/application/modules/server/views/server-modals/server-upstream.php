<?php
if (!defined("BASEPATH")) {
	exit("Cannot load script directly.");
}
?>
<div class="modal-dialog modal-lg modal-xl" role="document" style="width:78%;">
	<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="app-modal-dialog">
				<?= (isset($collect['validation_data']->server_site_purpose) ? $collect['validation_data']->server_site_purpose : '');?>
			</h5>
			<button class="close" type="button" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">X</span>
			</button>
		</div>
		<div class="modal-body">
			<div id="error-serverdata-messages"></div>
			<for id="frm-edit-serverdata" method="post" action="<?= base_url("server/upstream/set-upstream/action/{$collect['validation_data']->server_site_purpose}");?>" data-serverdata-id="<?= (isset($collect['validation_data']->server_site_purpose) ? $collect['validation_data']->server_site_purpose : '');?>">
				<div class="form-body">
					
					<div class="form-group">
						<label for="inp-serverdata-content">Data Content (Must HTTPS)</label>
						<input type="text" id="inp-serverdata-content" class="form-control" value="https://<?= trim($collect['validation_data']->server_site_content);?>" />
					</div>
				</div>
			</form>
		</div>
		<div class="modal-footer">
			<button class="btn btn-sm btn-secondary" type="button" data-dismiss="modal">Cancel</button>
			<button id="btn-edit-serverdata" class="btn btn-sm btn-primary" type="button" data-serverdata-id="<?= (isset($collect['validation_data']->server_site_purpose) ? $collect['validation_data']->server_site_purpose : 'validation');?>">Edit</button>
		</div>
	</div>
</div>