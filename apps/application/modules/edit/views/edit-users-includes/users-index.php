<?php
if (!defined('BASEPATH')) {
	exit('Cannot load script directly.');
}
?>
<div class="modal fade" id="app-modal" tabindex="-1" role="dialog" aria-labelledby="app-modal-dialog" aria-hidden="true" style="display:none;"></div>

<!-- Page DataTables plugins -->
<script src="<?= base_assets('vendor/datatables/jquery.dataTables.min.js');?>"></script>
<script src="<?= base_assets('vendor/datatables/dataTables.bootstrap4.min.js');?>"></script>
<script type="text/javascript">
<?php
if (isset($collect['app_userdatas'])) {
	?>
	//const app_userdatas = <?= json_encode($collect['app_userdatas']);?>;
	<?php
}
?>

</script>
<!-- Page App -->
<script type="text/javascript" src="<?= base_url('edit/users/scripts/edit-users-javascripts/users-index.js');?>"></script>