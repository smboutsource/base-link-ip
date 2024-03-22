<?php
if (!defined("BASEPATH")) {
	exit("Cannot load script directly.");
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="SMB Landingpage" />
    <meta name="author" content="smb@augipt.com" />
	<meta name="robots" content="noindex,nofollow" />
	<meta name="googlebot" content="noindex" />
    <title><?= (isset($page_title) ? $page_title : 'Page Dashboard');?></title>
    <!-- Custom styles for this template-->
	<script type="text/javascript">
		const base_url = function(path) {
			let BASEURL = '<?= base_url();?>';
			return BASEURL.concat(path);
		}
		<?php
		if (isset($userdata->user_id)) {
			?>
			const logged_userdata = <?= json_encode($userdata);?>;
			<?php
		}
		?>
	</script>
	<!-- Custom fonts for this template-->
    <link href="<?= base_assets('vendor/fontawesome-free/css/all.min.css');?>" rel="stylesheet" type="text/css" />
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet" />
	<link href="<?= base_assets('css/sb-admin-2.min.css');?>" rel="stylesheet" type="text/css">
	<link href="<?= base_assets('vendor/datatables/dataTables.bootstrap4.min.css');?>" rel="stylesheet" type="text/css">
</head>
<body id="page-top">