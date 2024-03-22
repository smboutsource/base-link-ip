<?php
if (!defined("BASEPATH")) {
	exit("Cannot load script directly.");
}
?>
<div class="modal-dialog modal-lg modal-xl" role="document" style="width:78%;">
	<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="app-modal-dialog">
				<?= (isset($collect['pem_paths']['basepath']) ? 'Upload File Certificate' : 'Modal Title');?>
			</h5>
			<button class="close" type="button" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">X</span>
			</button>
		</div>
		<div class="modal-body">
			<div id="error-serverdata-messages"></div>
			<for id="frm-upload-certificate" method="post" action="<?= base_url("edit/server/pem/action");?>" type="multipart/form-data">
				<div class="form-body">
					
					<div class="form-group">
						<label for="inp-serverdata-pem-fullchain">Fullchain Certificate</label>
						<input id="inp-serverdata-pem-fullchain" type="file" class="form-control" data-serverdata-pem="fullchain" />
					</div>
					<div class="form-group">
						<label for="inp-serverdata-pem-private">Private Key</label>
						<input id="inp-serverdata-pem-private" type="file" class="form-control" data-serverdata-pem="private" />
					</div>
				</div>
			</form>
		</div>
		<div class="modal-footer">
			<button class="btn btn-sm btn-secondary" type="button" data-dismiss="modal">Cancel</button>
			<button id="btn-upload-serverdata-certificate" class="btn btn-sm btn-primary" type="button">Upload</button>
		</div>
	</div>
</div>