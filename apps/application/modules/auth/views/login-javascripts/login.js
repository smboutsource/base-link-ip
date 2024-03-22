
$(document).ready(function() {
	
	
	
	$(document).on('click', '#btn-login-dashboard', function(f) {
		let error_display = '';
		let error = false, error_msg = [];
		f.preventDefault();
		const login_params = {
			'account_email'		: $('#inp-account-email').val(),
			'account_password'	: $('#inp-account-password').val()
		};
		if (!error) {
			if (!login_params.account_email) {
				error = true;
				error_msg.push('Empty account email.');
			}
			if (!login_params.account_password) {
				error = true;
				error_msg.push('Empty account password.');
			}
		}
		if (!error) {
			if (typeof login_params.account_email != 'string') {
				error = true;
				error_msg.push('Account email should be in string datatype.');
			}
			if (typeof login_params.account_password != 'string') {
				error = true;
				error_msg.push('Account password should be in string datatype.');
			}
		}
		if (!error) {
			$.ajax({
				'type'			: 'POST',
				'url'			: base_url('auth/login/index'),
				'data'			: {
					'account_email'		: login_params.account_email.toString(),
					'account_password'	: login_params.account_password.toString()
				},
				'success'		: function(response) {
					if ('status' in response) {
						if (response.status === true) {
							// Success Login
							$('#error-messages').hide();
							const redirect_url = base_url('dashboard');
							window.location.assign(redirect_url);
						} else {
							error = true;
							error_msg.push('Login failed.');
							
							if ('errors' in response) {
								if ((typeof response.errors == 'object') && (response.errors.length > 0)) {
									for (var i = 0; i < response.errors.length; i++) {
										error_msg.push(response.errors[i]);
									}
								}
							}
						}
					} else {
						error = true;
						error_msg.push('Not have status from response login.');
					}
				}
			}).then(function(result) {
				if ((error === true) && (error_msg.length > 0)) {
					error_display += '<ul class="list-group">';
					for (emsg of error_msg) {
						if (typeof emsg == 'string') {
							error_display += '<li class="list-group-item list-danger"><span class="small text-danger">' + emsg + '</span></li>';
						}
					}
					error_display += '</ul>';
					
					$('#error-messages').html(error_display);
					$('#error-messages').show();
				}	
			});
		} else {
			if ((error === true) && (error_msg.length > 0)) {
				error_display += '<ul class="list-group">';
				for (emsg of error_msg) {
					if (typeof emsg == 'string') {
						error_display += '<li class="list-group-item list-danger"><span class="small text-danger">' + emsg + '</span></li>';
					}
				}
				error_display += '</ul>';
				
				$('#error-messages').html(error_display);
				$('#error-messages').show();
			}
		}
	});
	
	
	$("#inp-account-email").keypress(function (e) {
		if (e.keyCode === 13) {
			$("#btn-login-dashboard").click();
		}
	});
	$("#inp-account-password").keypress(function (e) {
		if (e.keyCode === 13) {
			$("#btn-login-dashboard").click();
		}
	});
});