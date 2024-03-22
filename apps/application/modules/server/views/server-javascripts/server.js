

const isInputUrlMustHttpValid = function(str) {
	try {
		const newUrl = new URL(str);
		return newUrl.protocol === 'http:' || newUrl.protocol === 'https:';
	} catch (err) {
		return false;
	}
}
const serverdata_params = {
	'pageLength'			: $('#tbl-app-serverdata').attr('data-page-perpage'),
	'lengthMenu'			: [[1, 10, 25,50, 100, 500, -1], [1, 10, 25, 50, 100, 500, "All"]],
	'ajax'					: {
		'url'		: base_url('server/upstream/get-serverdata'),
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
				var render_response = '';
				if (full.server_site_purpose != 'redirect') {
					render_response += 'https://';
				}
				render_response += full.server_site_content.toString().replace(/\n/g, "<br />");
				return render_response;
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
$(document).ready(function() {
	let user_active_checked = 'N';
	var tb_serverdata = $("#tbl-app-serverdata").DataTable(serverdata_params);
	
	

	// Edit
	$(document).on('click', '.edit-app-serverdata', function(e) {
		e.preventDefault();
		const server_purpose = $(this).attr('data-serverdata-id').toString().toLowerCase();
		const url_serverdata_modal = base_url('server/upstream/server-purposes') + '/' + server_purpose;
		$.ajax({
			'type'				: 'GET',
			'url'					: url_serverdata_modal,
			'success'			: function(response) {
				$('#app-modal').html(response).modal({
					'show'			: true,
					'backdrop'	: 'static'
				}).show();
			}
		});
	});
	$(document).on('click', '#btn-edit-serverdata', function(f) {
		let error_display = '';
		f.preventDefault();
		let errors = {
			'code'		: 0,
			'msg'			: []
		};
		let edit_params = {
			'server_site_purpose'		: $(this).attr('data-serverdata-id'),
			'server_site_content'		: $('#inp-serverdata-content').val(),
		};
		if (typeof(edit_params.server_site_purpose) != 'string') {
			errors.code += 1;
			errors.msg.push('Given server_site_purpose text should be in string datatype.');
		}
		if (typeof(edit_params.server_site_content) != 'string') {
			errors.code += 1;
			errors.msg.push('Given server_site_content text should be in string datatype.');
		}
		if (!edit_params.server_site_purpose.length) {
			errors.code += 1;
			errors.msg.push('Empty server_site_purpose text given.');
		}
		if (!edit_params.server_site_content.length) {
			errors.code += 1;
			errors.msg.push('Empty server_site_content text given.');
		}
		if (errors.code == 0) {
			if (['mapped', 'redirect', 'upstream'].includes(edit_params.server_site_purpose) !== true) {
				errors.code += 1;
				errors.msg.push('Not allowed server-side-purpose.');
			}
		}
		// Check Input Url
		if (errors.code == 0) {
			edit_params.server_site_content = edit_params.server_site_content.toString().toLowerCase().trim();
			if (isInputUrlMustHttpValid(edit_params.server_site_content) != true) {
				errors.code += 1;
				errors.msg.push('Please using valid website-url with scheme[protocol] as https.');
			}
		}
		// is it https?
		if (errors.code == 0) {
			let url_params = new URL(edit_params.server_site_content.toString());
			if (url_params.protocol != 'https:') {
				errors.code += 1;
				errors.msg.push('Only allow HTTPS for scheme on url address content.');
			}
		}
		
		if (errors.code > 0) {
			error_display += '<ul class="list-group">';
			if (errors.msg.length) {
				for (const errmsg of errors.msg) {
					if (typeof errmsg == 'string') {
						error_display += '<li class="list-group-item small"><span class="text-danger">' + errmsg.toString() + '</span></li>';
					}
				}
			}
			error_display += '</ul>';
			if (error_display.length > 0) {
				$('#app-modal').find('#error-serverdata-messages').each(function(i, el) {
					$(el).html(error_display);
				});
			}
		} else {
			$.ajax({
				'type'			: 'POST',
				'url'				: base_url('server/upstream/set-upstream/action') + '/' + edit_params.server_site_purpose,
				'data'			: edit_params,
				'dataType'	: 'json',
				'success'		: function(response) {
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
		}
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