<?php
if (!defined('BASEPATH')) {
	exit('Cannot load script directly.');
}
?>
<div class="modal fade" id="app-modal" tabindex="-1" role="dialog" aria-labelledby="app-modal-dialog" aria-hidden="true" style="display:none;"></div>

<!-- Page DataTables Plugins -->
<script src="<?= base_assets('vendor/datatables/jquery.dataTables.min.js');?>"></script>
<script src="<?= base_assets('vendor/datatables/dataTables.bootstrap4.min.js');?>"></script>
<!-- Toast Plugins -->
<link href="<?= base_assets('vendor/toastr/toastr.min.css');?>" rel="stylesheet" />
<script type="text/javascript" src="<?= base_assets('vendor/toastr/toastr.min.js');?>"></script>
<script type="text/javascript">
	const app_serverdata = {};
</script>
<!-- App -->
<script type="text/javascript" src="<?= base_url('edit/server/scripts/edit-cert-javascripts/cert-edit.js');?>"></script>


