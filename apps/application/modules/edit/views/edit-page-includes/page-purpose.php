<?php
if (!defined('BASEPATH')) {
	exit('Cannot load script directly.');
}
?>
<style type="text/css" media="screen">
	#inp-page-content {
		height:800px;
	}
</style>
<script src="https://cdn.jsdelivr.net/npm/ace-builds@1.24.1/src-min-noconflict/ace.min.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
	$(document).ready(function() {
		const editoraces = ace.edit("inp-page-content");
		editoraces.setTheme("ace/theme/chrome");
		editoraces.session.setMode("ace/mode/html");
		
		const form_data = $('#frm-edit-page-purpose');
		form_data.submit(function(f) {
			f.preventDefault();
			const data_value = editoraces.getValue();
			
			$.ajax({
				'type'		: 'POST',
				'url'		: base_url('edit/page/edit/action') + '/' + $(this).attr('data-page-id'),
				'data'		: {
					'page_content'		: data_value.toString()
				},
				'success'	: function(response) {
					window.location.reload();
				}
			});
		});
	});
</script>

