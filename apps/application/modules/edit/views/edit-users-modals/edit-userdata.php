<?php
if (!defined("BASEPATH")) {
	exit("Cannot load script directly.");
}
?>
<div class="modal-dialog" role="document">
	<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="app-modal-dialog">
				<?= (isset($collect['app_userdata']->user_email) ? $collect['app_userdata']->user_email : '');?>
			</h5>
			<button class="close" type="button" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">X</span>
			</button>
		</div>
		<div class="modal-body">
			<div id="error-userdata-messages"></div>
			<for id="frm-edit-userdata" method="post" action="<?= base_url("edit/users/edit/{$collect['app_userdata']->user_id}");?>" data-edit-userdata-id="<?= (isset($collect['app_userdata']->user_id) ? $collect['app_userdata']->user_id : '');?>">
				<div class="form-body">
					<div class="form-group">
						<label for="inp-user-role">User Role</label>
						<select id="inp-user-role" class="form-control">
							<?php
							if (isset($collect['app_userdata']->app_user_roles) && is_array($collect['app_userdata']->app_user_roles)) {
								foreach ($collect['app_userdata']->app_user_roles as $role) {
									if ($role['value'] == $collectData['collect']['app_userdata']->user_role) {
										$role_options = [
											'selected'		=> 'selected="selected"',
											'class'			=> 'active',
										];
									} else {
										$role_options = [
											'selected'		=> '',
											'class'			=> '',
										];
									}
									?>
									<option value="<?=$role['value'];?>" <?=$role_options['selected'];?>><?=$role['name'];?></option>
									<?php
								}
							}
							?>
						</select>
					</div>
					<div class="form-group">
						<label>User Active</label>
						<div class="custom-control custom-switch">
							<input type="checkbox" class="custom-control-input" id="inp-user-active" <?= (strtoupper($collect['app_userdata']->user_active) === 'Y') ? 'checked="checked"' : '';?>>
							<label class="custom-control-label" for="inp-user-active">Active Status</label>
						</div>
					</div>
				</div>
			</form>
		</div>
		<div class="modal-footer">
			<button id="btn-delete-userdata" class="btn btn-sm btn-danger" type="button" data-userdata-userid="<?= (isset($collect['app_userdata']->user_id) ? $collect['app_userdata']->user_id : '');?>">Delete</button>
			<button class="btn btn-sm btn-secondary" type="button" data-dismiss="modal">Cancel</button>
			<button id="btn-edit-userdata" class="btn btn-sm btn-primary" type="button" data-userdata-userid="<?= (isset($collect['app_userdata']->user_id) ? $collect['app_userdata']->user_id : '');?>">Edit</button>
		</div>
	</div>
</div>