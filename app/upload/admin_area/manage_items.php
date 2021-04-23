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
	$breadcrumb[1] = array('title' => 'Manage Collections', 'url' => ADMIN_BASEURL.'/flagged_collections.php');
	$breadcrumb[2] = array('title' => 'Editing : '.display_clean($c['collection_name']), 'url' => ADMIN_BASEURL.'/edit_collection.php?collection='.display_clean($id));
	$breadcrumb[3] = array('title' => 'Manage Collection Items', 'url' => ADMIN_BASEURL.'/manage_items.php?collection='.display_clean($id).'&type=videos');

	$type = mysql_clean($_GET['type']);
	$data = $cbcollection->get_collection_items($id);

	switch($type)
	{
		case "photos":
			if(isset($_POST['remove_selected']))
			{
				$total = count($_POST['check_obj']);
				for($i=0;$i<$total;$i++)
				{
					$cbphoto->collection->remove_item($_POST['check_obj'][$i],$id);
					$cbphoto->make_photo_orphan($id,$_POST['check_obj'][$i]);
				}
				$eh->flush();
				e($total." photos have been removed.","m");
			}

			if(isset($_POST['move_selected']))
			{
				$total = count($_POST['check_obj']);
				$new = mysql_clean($_POST['collection_id']);
				for($i=0;$i<$total;$i++)
				{
					$cbphoto->collection->change_collection($new,$_POST['check_obj'][$i],$id);
					$db->update(tbl('photos'),array('collection_id'),array($new)," collection_id = $id AND photo_id = ".$_POST['check_obj'][$i]."");
				}
				$eh->flush();
				e($total." photo(s) have been moved to '<strong>".get_collection_field($new,'collection_name')."</strong>'","m");
			}

			$items = $cbphoto->collection->get_collection_items_with_details($id);
			$collection = $cbphoto->collection->get_collections(array("type"=>"photos","user"=>$data['userid']));
			break;

		case "videos":
			if(isset($_POST['remove_selected']))
			{
				$total = count($_POST['check_obj']);
				for($i=0;$i<$total;$i++)
				{
					$cbvideo->collection->remove_item($_POST['check_obj'][$i],$id);
				}
			}

			if(isset($_POST['move_selected']))
			{
				$total = count($_POST['check_obj']);
				$new = mysql_clean($_POST['collection_id']);
				for($i=0;$i<$total;$i++)
				{
					$cbvideo->collection->change_collection($new,$_POST['check_obj'][$i],$id);
				}
				$eh->flush();
				e($total." video(s) have been moved to '<strong>".get_collection_field($new,'collection_name')."</strong>'","m");
			}

			$items = $cbvideo->collection->get_collection_items_with_details($id);
			$collection = $cbvideo->collection->get_collections(array("type"=>"videos","user"=>$data['userid']));
			break;
	}

	assign('data',$data);
	assign('obj',$items);
	assign('type',$type);
	assign('c',$collection);

	subtitle("Manage Items");
	template_files('manage_items.html');
	display_it();
