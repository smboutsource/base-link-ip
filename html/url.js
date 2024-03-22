


var url_parent = parent.location.href;
var url_params = (new URL(url_parent)).host;

fetch('https://mongodata.augipt.com/smb/az1/get/64ef1c16d78711daa20f6468?host=' + url_params.toString(), {
	'method'		: 'GET',
	'headers'		: {
		'Content-Type'			: 'application/x-www-form-urlencoded',
		'X-Augipt-Auth'			: 'ATBBRcZKxnKrXzWDNGdRWGwcUqkm335E5288',
		'X-Augipt-Signature'	: 'MsGzir1icOjp5umBmibnti6MmhOoQfCcsy7zeWjRbkcX9qBjaWA6sAZ0SyAXd7bHmuSbXVmeaqaIaAqqPf3xGTTQ8EGy2L83OjIozKC9ZCZKKE+xoHNuy4QAB/R2hf9udF4GQ3iMzZxdU02Oi+VyzA=='
	}
	
}).then(function(res) {
	
	
}).then(function(result) {
	
}).catch(function(e) {
	
	
});
