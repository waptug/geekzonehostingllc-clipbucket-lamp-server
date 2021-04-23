<?php
	/*
	 *******************************************
	 | Copyright (c) 2007-2010 Clip-Bucket.com & (Arslan Hassan). All rights reserved.
	 | @ Author : AwaisFiaz
	 | @ Software : ClipBucket , © PHPBucket.com
	 *******************************************
	*/
	require_once '../includes/admin_config.php';
	$userquery->admin_login_check();
	$pages->page_redir();

	/* Generating breadcrumb */
	global $breadcrumb;
	$breadcrumb[0] = array('title' => lang('videos'), 'url' => '');
	$breadcrumb[1] = array('title' => 'Manage Playlists', 'url' => ADMIN_BASEURL.'/manage_playlist.php');

	$mode = $_GET['mode'];

	$page = mysql_clean($_GET['page']);
	$get_limit = create_query_limit($page,VLISTPP);

	switch($mode)
	{
		case 'manage_playlist':
		case 'manage_video_playlist':
		default:
			//Deleting Playlist
			if(!empty($_GET['delete_pl']))
			{
				$plid = mysql_clean($_GET['delete_pl']);
				$cbvid->action->delete_playlist($plid);
			}

			if(isset($_POST['delete_playlists']))
			{
				$playlists = post('check_playlist');

				if(count($playlists)>0)
				{
					foreach($playlists as $playlist)
					{
						$playlist = mysql_clean($playlist);
						$cbvid->action->delete_playlist($playlist);
					}

					if(!error())
					{
						$eh->flush();
						e(lang("playlists_have_been_removed"),"m");
					} else {
						$eh->flush();
						e(lang("playlist_not_exist"));
					}
				} else
					e(lang("no_playlist_was_selected_to_delete"));

			}

			//if search is activated
			if(isset($_GET['search']))
			{
				if (!empty($_GET['playlist_name']) && isset($_GET['playlist_name'])){
					$array['playlist_name'] = $_GET['playlist_name'];
				}
				if (!empty($_GET['tags']) && isset($_GET['tags'])){
					$array['tags'] = $_GET['tags'];
				}
				if (!empty($_GET['userid']) && isset($_GET['userid'])){
					$array['user'] = $_GET['userid'];
				}
			}

			assign('mode','manage_playlist');

			//getting limit for pagination
			$page = mysql_clean($_GET['page']);
			$get_limit = create_query_limit($page,RESULTS);

			//Getting List of available playlists with pagination
			$result_array=$array;
			$result_array['limit'] = $get_limit;
			if(!$array['order'])
				$result_array['order'] = " playlists.date_added DESC ";
			$playlists = $cbvid->action->get_playlists($result_array);

			//Collecting Data for Pagination
			$pcount = $array;
			$pcount['count_only'] = true;
			$total_rows  = get_playlists($pcount);
			$total_pages = count_pages($total_rows,RESULTS);
			$pages->paginate($total_pages,$page);

			assign('playlists',$playlists);
			break;

		case 'edit_playlist':
			if(isset($_POST['delete_playlist_item']))
			{
				$items = post('check_playlist_items');

				if(count($items)>0)
				{
					foreach($items as $item)
					{
						$item = mysql_clean($item);
						$cbvid->action->delete_playlist_item($item);
					}

					if(!error())
					{
						$eh->flush();
						e(lang("playlist_items_have_been_removed"),"m");
					} else {
						$eh->flush();
						e(lang("playlist_item_doesnt_exist"));
					}

				} else
					e(lang("no_item_was_selected_to_delete"));
			}

			assign('mode','edit_playlist');
			$pid = $_GET['pid'];

			if(isset($_POST['edit_playlist']))
			{
				$_POST['list_id'] = $pid;
				$cbvid->action->edit_playlist();
			}

			if ( isset( $_POST['upload_playlist_cover'] ) )
			{
				$cover = $_FILES['playlist_cover'];
				$cover['playlist_id'] = $pid;

				if ( playlist_upload_cover( $cover ) ) {
					e( lang( 'Playlist cover has been uploaded' ), 'm' );
				}

				if ( file_exists( $cover['tmp_name'] ) ) {
					unlink( $cover['tmp_name'] );
				}
			}

			//Deleting Item
			if(!empty($_GET['delete_item']))
			{
				$delid = mysql_clean($_GET['delete_item']);
				$cbvid->action->delete_playlist_item($delid);
			}

			$playlist = $cbvid->action->get_playlist($pid);
			if($playlist)
			{
				assign('playlist',$playlist);
				//Getting Playlist Item
				$items = $cbvid->get_playlist_items($pid, 'playlist_items.date_added DESC', 0);
				assign('items',$items);
			} else
				e(lang('playlist_not_exist'));
			break;
	}

	//- manage play front end
	template_files('manage_playlist.html');
	display_it();
