<?php
if (!defined("BASEPATH")) {
	exit("Cannot load script directly.");
}
?>
<div class="modal-dialog" role="document">
	<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="app-modal-dialog">
				Add Userdata
			</h5>
			<button class="close" type="button" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">X</span>
			</button>
		</div>
		<div class="modal-body">
			<div id="error-userdata-messages"></div>
			<for id="frm-edit-userdata" method="post" action="<?= base_url("edit/users/add/action");?>">
				<div class="form-body">
					<div class="form-group">
						<label for="inp-user-email">User Email</label>
						<input id="inp-user-email" class="form-control" type="text" value="" placeholder="User email" />
					</div>
					<div class="form-group">
						<label for="inp-user-role">User Role</label>
						<select id="inp-user-role" class="form-control">
							<?php
							if (isset($collect['app_userdaata_roles']) && is_array($collect['app_userdaata_roles'])) {
								foreach ($collect['app_userdaata_roles'] as $role) {
									?>
									<option value="<?=$role['value'];?>"><?=$role['name'];?></option>
									<?php
								}
							}
							?>
						</select>
					</div>
					<div class="form-group">
						<label>User Active</label>
						<div class="custom-control custom-switch">
							<input type="checkbox" class="custom-control-input" id="inp-user-active">
							<label class="custom-control-label" for="inp-user-active">Active Status</label>
						</div>
					</div>
				</div>
			</form>
		</div>
		<div class="modal-footer">
			<button class="btn btn-sm btn-secondary" type="button" data-dismiss="modal">Cancel</button>
			<button id="btn-add-userdata" class="btn btn-sm btn-primary" type="button">Add</button>
		</div>
	</div>
</div>