
$(document).ready(function() {
	let user_active_checked = 'N';
	var tb_userdata = $("#tbl-app-userdata").DataTable({
		'pageLength'			: $('#tbl-app-userdata').attr('data-page-perpage'),
		'lengthMenu'			: [[1, 10, 25,50, 100, 500, -1], [1, 10, 25, 50, 100, 500, "All"]],
		'ajax'					: {
			'url'		: base_url('edit/users/get'),
			'method'	: 'POST'
		},
		'processing'	: true,
		'serverSide'	: true,
		'columns'		: [
			{ 
				'data'		: 'user_id',
				'name'		: 'user_id',
				'render'	: function(data, display, full, meta) {
					return (meta.row + 1);
				}
			},
			{ 
				'data'		: 'user_email',
				'name'		: 'user_email',
				'render'	: function(data, display, full, meta) {
					return '<span class="text-primary">' + full.user_email + '</span>';
				}				
			},
			{ 
				'data'		: 'user_active',
				'name'		: 'user_active',
				'render'	: function(data, display, full, meta) {
					return ((full.user_active.toString().toUpperCase() === 'Y') ? '<span class="text-success">Yes</span>' : '<span class="text-danger">No</span>');
				}
			},
			{ 
				'data'		: 'user_role',
				'name'		: 'user_role',
				'render'	: function(data, display, full, meta) {
					return ((parseInt(full.user_role) > 0) ? 'Admin' : 'User');
				}
			},
			{ 
				'data'		: 'user_dt_add',
				'name'		: 'user_dt_add',
				'render'	: function(data, display, full, meta) {
					if (full.user_dt_add != null) {
						return full.user_dt_add;
					} else {
						return '-';
					}
				}
			},
			{ 
				'data'		: 'user_dt_edit',
				'name'		: 'user_dt_edit',
				'render'	: function(data, display, full, meta) {
					if (full.user_dt_edit != null) {
						return full.user_dt_edit;
					} else {
						return '-';
					}
				}
			},
			{ 
				'data'		: 'user_id',
				'name'		: 'user_id',
				'render'	: function(data, display, full, meta) {
					return '<button class="btn btn-sm btn-warning edit-app-userdata" class="btn btn-warning btn-xs margin-r5" title="Edit data" data-userdata-id="' + full.user_id + '"><i class="fas fa-pencil"></i> Edit</button>';
				}
			}

		],
		'processing'				: true,
		'language'					: {
			'processing'					: '<span class="text-primary"><i class="fa fa-spinner fa-lg fa-xl"></i> Loading....</span>'
		}
	});
	
	// Form Edit Userdata
	$(document).on('click', '.edit-app-userdata', function(e) {
		e.preventDefault();
		$.ajax({
			'type'		: 'GET',
			'url'		: base_url('edit/users/get-single-userdata') + '/' + $(this).attr('data-userdata-id'),
			'success'	: function(response) {
				$('#app-modal').html(response).modal({
					'show'		: true,
					'backdrop'	: 'static'
				}).show();
			}
		});
	});
	// Form Add Userdata
	$(document).on('click', '#btn-add-app-userdata', function(e) {
		e.preventDefault();
		$.ajax({
			'type'		: 'GET',
			'url'		: base_url('edit/users/add'),
			'success'	: function(response) {
				$('#app-modal').html(response).modal({
					'show'		: true,
					'backdrop'	: 'static'
				}).show();
			}
		});
	});
	
	
	//
	// User Edit Action
	//
	$('#app-modal').on('show.bs.modal', function(e) {
		$(document).find('#inp-user-active').each(function(i, e) {
			const is_checked = $(e).prop('checked');
			if (is_checked === true) {
				user_active_checked = 'Y';
			} else {
				user_active_checked = 'N';
			}
		});
		$(document).find('#frm-edit-userdata').each(function(i, el) {
			if ($(el).data('edit-userdata-id') == logged_userdata.user_id) {
				$('#btn-delete-userdata').hide();
				$('#btn-edit-userdata').hide();
			}
		});
	});
	$(document).on('change', '#inp-user-active', function(e) {
		e.preventDefault();
		const is_checked = $(this).prop('checked');
		if (is_checked === true) {
			user_active_checked = 'Y';
		} else {
			user_active_checked = 'N';
		}
	});
	// Delete
	$(document).on('click', '#btn-delete-userdata', function(f) {
		f.preventDefault();
		let edit_params = {
			'user_id'		: $(this).attr('data-userdata-userid'),
			'user_role'		: $('#inp-user-role').val(),
			'user_active'	: user_active_checked
		};
		$.ajax({
			'type'		: 'POST',
			'url'		: base_url('edit/users/edit') + '/delete/' + edit_params.user_id,
			'data'		: edit_params,
			'dataType'	: 'json',
			'success'	: function(response) {
				let error_display = '';
				if ('status' in response) {
					if (response.status === true) {
						window.location.reload();
					} else {
						error_display += '<ul class="list-group">';
						if (response.errors.length) {
							for (var msg of response.errors) {
								if (typeof msg == 'string') {
									error_display += '<li class="list-group-item small"><span class="text-danger">' + msg.toString() + '</span></li>';
								}
							}
						}
						error_display += '</ul>';
					}
				}
				
				if (error_display.length > 0) {
					$('#app-modal').find('#error-userdata-messages').each(function(i, el) {
						$(el).html(error_display);
					});
				}
			}
		});
	});
	// Edit
	$(document).on('click', '#btn-edit-userdata', function(f) {
		f.preventDefault();
		let edit_params = {
			'user_id'		: $(this).attr('data-userdata-userid'),
			'user_role'		: $('#inp-user-role').val(),
			'user_active'	: user_active_checked
		};
		$.ajax({
			'type'		: 'POST',
			'url'		: base_url('edit/users/edit') + '/change/' + edit_params.user_id,
			'data'		: edit_params,
			'dataType'	: 'json',
			'success'	: function(response) {
				let error_display = '';
				if ('status' in response) {
					if (response.status === true) {
						window.location.reload();
					} else {
						error_display += '<ul class="list-group">';
						if (response.errors.length) {
							for (var msg of response.errors) {
								if (typeof msg == 'string') {
									error_display += '<li class="list-group-item small"><span class="text-danger">' + msg.toString() + '</span></li>';
								}
							}
						}
						error_display += '</ul>';
					}
				}
				
				if (error_display.length > 0) {
					$('#app-modal').find('#error-userdata-messages').each(function(i, el) {
						$(el).html(error_display);
					});
				}
			}
		});
	});
	// Add
	$(document).on('click', '#btn-add-userdata', function(f) {
		f.preventDefault();
		let add_params = {
			'user_email'	: $('#inp-user-email').val(),
			'user_role'		: $('#inp-user-role').val(),
			'user_active'	: user_active_checked
		};
		$.ajax({
			'type'		: 'POST',
			'url'		: base_url('edit/users/add/action'),
			'data'		: add_params,
			'dataType'	: 'json',
			'success'	: function(response) {
				let error_display = '';
				if ('status' in response) {
					if (response.status === true) {
						window.location.reload();
					} else {
						error_display += '<ul class="list-group">';
						if (response.errors.length) {
							for (var msg of response.errors) {
								if (typeof msg == 'string') {
									error_display += '<li class="list-group-item small"><span class="text-danger">' + msg.toString() + '</span></li>';
								}
							}
						}
						error_display += '</ul>';
					}
				}
				
				if (error_display.length > 0) {
					$('#app-modal').find('#error-userdata-messages').each(function(i, el) {
						$(el).html(error_display);
					});
				}
			}
		});
	});
	
	
	
	
	
	
	
	
	
	
});
