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
			<for id="frm-edit-serverdata" method="post" action="<?= base_url("edit/server/cert/action/{$collect['validation_data']->server_site_purpose}");?>" data-serverdata-id="<?= (isset($collect['validation_data']->server_site_purpose) ? $collect['validation_data']->server_site_purpose : '');?>">
				<div class="form-body">
					
					<div class="form-group">
						<label for="inp-serverdata-content">Data Content</label>
						<textarea id="inp-serverdata-content" class="form-control" cols="16" rows="16"><?= $collect['validation_data']->server_site_content;?></textarea>
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