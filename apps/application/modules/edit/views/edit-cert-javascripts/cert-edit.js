
$(document).ready(function() {
	let user_active_checked = 'N';
	const serverdata_params = {
		'pageLength'			: $('#tbl-app-serverdata').attr('data-page-perpage'),
		'lengthMenu'			: [[1, 10, 25,50, 100, 500, -1], [1, 10, 25, 50, 100, 500, "All"]],
		'ajax'					: {
			'url'		: base_url('edit/server/get'),
			'method'	: 'POST'
		},
		'processing'	: true,
		'serverSide'	: true,
		'columns'		: [
			{ 
				'data'		: 'server_site_purpose',
				'name'		: 'server_site_purpose',
				'render'	: function(data, display, full, meta) {
					return (meta.row + 1);
				}
			},
			{ 
				'data'		: 'server_site_purpose',
				'name'		: 'server_site_purpose',
				'render'	: function(data, display, full, meta) {
					return '<span class="text-primary">' + full.server_site_purpose + '</span>';
				}				
			},
			{ 
				'data'		: 'server_site_content',
				'name'		: 'server_site_content',
				'render'	: function(data, display, full, meta) {
					return full.server_site_content.toString().replace(/\n/g, "<br />");
				}
			},
			{ 
				'data'		: 'server_site_datetime',
				'name'		: 'server_site_datetime',
				'render'	: function(data, display, full, meta) {
					if (full.server_site_datetime != null) {
						return full.server_site_datetime;
					} else {
						return '-';
					}
				}
			},
			{ 
				'data'		: 'server_site_purpose',
				'name'		: 'server_site_purpose',
				'render'	: function(data, display, full, meta) {
					return '<button class="btn btn-sm btn-warning edit-app-serverdata" class="btn btn-warning btn-xs margin-r5" title="Edit data" data-serverdata-id="' + full.server_site_purpose + '"><i class="fas fa-pencil"></i> Edit</button>';
				}
			}

		],
		'processing'				: true,
		'language'					: {
			'processing'					: '<span class="text-primary"><i class="fa fa-spinner fa-lg fa-xl"></i> Loading....</span>'
		}
	};
	var tb_serverdata = $("#tbl-app-serverdata").DataTable(serverdata_params);
	
	
	//
	// User Server Action
	//
	$('#app-modal').on('show.bs.modal', function(e) {

	});
	// Edit
	$(document).on('click', '.edit-app-serverdata', function(e) {
		e.preventDefault();
		const server_purpose = $(this).attr('data-serverdata-id').toString().toLowerCase();
		if (server_purpose === 'certificate') {
			var url_serverdata_modal = base_url('edit/server/pem/form');
		} else {
			var url_serverdata_modal = base_url('edit/server/server-cert') + '/' + server_purpose;
		}
		$.ajax({
			'type'		: 'GET',
			'url'		: url_serverdata_modal,
			'success'	: function(response) {
				$('#app-modal').html(response).modal({
					'show'		: true,
					'backdrop'	: 'static'
				}).show();
			}
		});
	});
	$(document).on('click', '#btn-edit-serverdata', function(f) {
		f.preventDefault();
		let edit_params = {
			'server_site_purpose'		: $(this).attr('data-serverdata-id'),
			'server_site_content'		: $('#inp-serverdata-content').val(),
		};
		$.ajax({
			'type'		: 'POST',
			'url'		: base_url('edit/server/cert/action') + '/' + edit_params.server_site_purpose,
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
					$('#app-modal').find('#error-serverdata-messages').each(function(i, el) {
						$(el).html(error_display);
					});
				}
			}
		});
	});
	// PEM Upload
	$(document).on('click', '#btn-upload-app-serverdata', function(f) {
		f.preventDefault();
		$.ajax({
			'type'		: 'GET',
			'url'		: base_url('edit/server/pem/form'),
			'success'	: function(response) {
				$('#app-modal').html(response).modal({
					'show'		: true,
					'backdrop'	: 'static'
				}).show();
			}
		});
	});
	$(document).on('click', '#btn-upload-serverdata-certificate', function(f) {
		f.preventDefault();
		let formData = new FormData();
		const input_files = {
			'fullchain'		: document.getElementById('inp-serverdata-pem-fullchain'),
			'private'		: document.getElementById('inp-serverdata-pem-private'),
		};
		if (input_files.fullchain.files.length) {
			formData.append('pem_fullchain', input_files['fullchain'].files[0]);
		}
		if (input_files.fullchain.files.length) {
			formData.append('pem_private',  input_files['private'].files[0])
		}
		fetch(base_url('edit/server/pem/action'), {
			'method'		: 'POST',
			'headers'		: {
				'Accept'			: 'application/json, application/xml, text/plain, text/html, *.*',
				// 'Content-Type'		: 'multipart/form-data'
			},
			'body'			: formData
		}).then(res => res.json()).then(response => {
			let error_display = '';
			if ('status' in response) {
				if (response.status !== true) {
					if ('errors' in response) {
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
				} else {
					window.location.assign(base_url('edit/server/cert'));
				}
			}
			if (error_display.length > 0) {
				$('#app-modal').find('#error-serverdata-messages').each(function(i, el) {
					$(el).html(error_display);
				});
			}
		}).catch(err => {
			console.log(err);
		});
		
	});
	// Restart Server
	$(document).on('click', '#btn-restart-server-service', function(f) {
		f.preventDefault();
		$.ajax({
			'type'		: 'GET',
			'url'		: base_url('edit/server/restart/machine'),
			'dataType'	: 'json',
			'success'	: function(response) {
				if ('status' in response) {
					var response_msgs = {
						'type'		: 'info',
						'msg'		: ''
					}
					if (response.status != true) {
						response_msgs.type = 'danger';
						response_msgs['msg'] += 'Error while restart server service.';
					} else {
						response_msgs.type = 'success';
						response_msgs['msg'] += 'Success while restart server service.';
					}
					toastr[response_msgs.type](response_msgs['msg']);
				}
			}
		});
		
	});
});