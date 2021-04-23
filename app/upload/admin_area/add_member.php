<?php
	require_once '../includes/admin_config.php';
	$userquery->admin_login_check();
	$userquery->login_check('member_moderation');
	$pages->page_redir();

	/* Generating breadcrumb */
	global $breadcrumb;
	$breadcrumb[0] = array('title' => lang('users'), 'url' => '');
	$breadcrumb[1] = array('title' => 'Add Member', 'url' => ADMIN_BASEURL.'/add_member.php');

	if(isset($_POST['add_member'])) {
		if($userquery->signup_user($_POST)) {
			e(lang("new_mem_added"),"m");
			$_POST = '';
		}
	}

	subtitle("Add New Member");
	template_files('add_members.html');
	display_it();
