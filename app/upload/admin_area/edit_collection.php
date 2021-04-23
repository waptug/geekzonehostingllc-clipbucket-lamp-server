<?php
	/*
	 ****************************************************************************************************
	 | Copyright (c) 2007-2010 Clip-Bucket.com. All rights reserved.											|
	 | @ Author 	: ArslanHassan																		|
	 | @ Software 	: ClipBucket , © PHPBucket.com														|
	 ****************************************************************************************************
	*/

	require_once '../includes/admin_config.php';
	$userquery->admin_login_check();
	$userquery->login_check('video_moderation');
	$pages->page_redir();

	$id = mysql_clean($_GET['collection']);
	$c = $cbcollection->get_collection($id);

	/* Generating breadcrumb */
	global $breadcrumb;
	$breadcrumb[0] = array('title' => 'Collections', 'url' => '');
	$breadcrumb[1] = array('title' => 'Manage Collections', 'url' => ADMIN_BASEURL.'/collection_manager.php');
	$breadcrumb[2] = array('title' => 'Editing : '.display_clean($c['collection_name']), 'url' => ADMIN_BASEURL.'/edit_collection.php?collection='.display_clean($id));

	if(isset($_POST['update_collection']))
	{
		$cbcollection->update_collection();
	}

	if(isset($_POST['delete_preview']))
	{
		$id = mysql_clean($_POST['delete_preview']);
		$cbcollection->delete_thumbs($id);
	}

	//Performing Actions
	if($_GET['mode']!='')
	{
		$cbcollection->collection_actions($_GET['mode'],$id);
	}

	switch($c['type'])
	{
		case "videos":
		case "v":
			$items = $cbvideo->collection->get_collection_items_with_details($c['collection_id'],NULL,4);
			break;

		case "photos":
		case "p":
			$items = $cbphoto->collection->get_collection_items_with_details($c['collection_id'],NULL,4);
			break;
	}

	if(!empty($items))
		assign('objects',$items);
	assign('data',$c);

	$get_limit = create_query_limit($page,5);
	$FlaggedPhotos = $cbvid->action->get_flagged_objects($get_limit);
	Assign('flaggedPhoto', $FlaggedPhotos);

	$comments = getComments($comment_cond);
	assign("comments",$comments);

	assign('randon_number', rand(-5000, 5000));
	subtitle("Edit Collection");
	template_files('edit_collection.html');
	display_it();
