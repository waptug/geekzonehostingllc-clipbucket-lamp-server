<?php
/* 
 ******************************************************************
 | Copyright (c) 2007-2009 Clip-Bucket.com. All rights reserved.	
 | @ Author : ArslanHassan											
 | @ Software : ClipBucket , © PHPBucket.com						
 *******************************************************************
*/

define("THIS_PAGE",'view_item');
define("PARENT_PAGE",'collections');

require 'includes/config.inc.php';

$item = (string) mysql_clean($_GET['item']);	
$type = (string) mysql_clean($_GET['type']);
$cid  = (int) mysql_clean($_GET['collection']);
$order = tbl("collection_items").".ci_id DESC";

if($cbcollection->is_viewable($cid))
{
	if(empty($item))
		header('location:'.BASEURL);
	else
	{
		if(empty($type))
			header('location:'.BASEURL);
		else
		{
			assign('type',$type);
			$param = array("type"=>$type,"cid"=>$cid);
			$cdetails = $cbcollection->get_collections($param);
			$collect = $cdetails[0];
			switch($type)
			{
				case "videos":
				case "v":
				{
					global $cbvideo;
					$video = $cbvideo->get_video($item);
					
					if(video_playable($video))
					{
						//Getting list of collection items
						$page = mysql_clean($_GET['page']);
						$get_limit = create_query_limit($page,20);
						$order = tbl("collection_items").".ci_id DESC";

						$items = $cbvideo->collection->get_collection_items_with_details($cid,$order,$get_limit);
						assign('items',$items);
						
						assign('open_collection','yes');
						$info = $cbvideo->collection->get_collection_item_fields($cid,$video['videoid'],'ci_id,collection_id');
						if($info)
						{
							$video = array_merge($video,$info[0]);						
							increment_views($video['videoid'],'video');
							
							assign('object',$video);
							assign('user',$userquery->get_user_details($video['userid']));
							assign('c',$collect);						
							
							subtitle($video['title']);
							
						} else {
							e(lang("item_not_exist"));
							$Cbucket->show_page = false;
						}
					} else {
						e(lang("item_not_exist"));
						$Cbucket->show_page = false;	
					}
					
					
				}
				break;
				
				case "photos":
				case "p":
				{
					global $cbphoto;
					if (isSectionEnabled('photos')) {
						$photo = $cbphoto->get_photo($item);
						if($photo)
						{
							$info = $cbphoto->collection->get_collection_item_fields($cid,$photo['photo_id'],'ci_id');
							if($info)
							{
								$photo = array_merge($photo,$info[0]);							
								increment_views($photo['photo_id'],'photo');
								
								assign('object',$photo);
								assign('user',$userquery->get_user_details($photo['userid']));
								assign('c',$collect);
								
								subtitle($photo['photo_title'].' &laquo; '.$collect['collection_name']);
							} else {
								e(lang("item_not_exist"));
								$Cbucket->show_page = false;	
							}
						} else {
							e(lang("item_not_exist"));
							$Cbucket->show_page = false;	
						}
					} else {
						$Cbucket->show_page = false;
					}
				}
				break;
			}
	
		}		
	}
} else 
	$Cbucket->show_page = false;



//Getting Collection Lists
$page = mysql_clean($_GET['page']);
$get_limit = create_query_limit($page,COLLPP);
$clist = $cond;
$clist['active'] 	=	"yes";
$clist['limit'] 	=	$get_limit;
$collections 		=	$cbcollection->get_collections($clist);

Assign('collections', $collections);

//Getting Photo List
$page = mysql_clean($_GET['page']);
$get_limit = create_query_limit($page,MAINPLIST);
$clist = $cond;
$clist['limit'] = $get_limit;
$clist ['order']  = " last_viewed DESC";
if(isset($photo['photo_id']))
{
	$clist['exclude'] = $photo['photo_id']; 	
}
$photos = get_photos($clist);

Assign('photos', $photos);

/* CREATING LIMIT1 */
///////////////////////////////
$page = mysql_clean($_GET['page']);
$get_limit = create_query_limit($page,RESULTS);

$carray['limit'] = $get_limit;
if(!empty($carray['order']))
    $carray['order'] = $carray['order']." DESC";
else
    $carray['order'] = "DESC";

$collections = $cbcollection->get_collections($carray);
assign('co',$collections);     //previously assign('c',$collections);
////////////////////////////////

template_files('view_item.html');
display_it();
?>