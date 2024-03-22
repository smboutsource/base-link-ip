<?php
if (!defined('BASEPATH')) {
	exit('Basepath not yet defined.');
}



if (isset($collect['landingpage_data']->page_content)) {
	echo $collect['landingpage_data']->page_content;
}