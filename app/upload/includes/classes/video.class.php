<?php
/**
 * Author : Arslan Hassan
 * Script : ClipBucket v2
 * License : Attribution Assurance License -- http://www.opensource.org/licenses/attribution.php
 *
 *
 * Class : Video
 * Used to perform function swith videos
 * -- history
 * all function that were in my_query
 * has been transfered here
 * however thhey will still work from there
 * too
 */
 
define("QUICK_LIST_SESS","quick_list");

class CBvideo extends CBCategory
{
	var $embed_func_list = array(); //Function list that are applied while asking for video embed code
	var $embed_src_func_list = array(); //Function list that are applied while asking for video embed src
	var $action = ''; // variable used to call action class
	var $collection = '';
	var $email_template_vars = array();
	
	var $dbtbl = array('video'=>'video');
	
	var $video_manager_links = array();
	var $video_manager_funcs = array();
	
	var $video_delete_functions = array(); //Holds all delete functions of video

    private $basic_fields = array();
    private $extra_fields = array();

	/**
	 * __Constructor of CBVideo
	 */	
	function init()
	{
		global $Cbucket, $cb_columns;
		$this->cat_tbl = 'video_categories';
		$this->section_tbl = 'video';
		$this->use_sub_cats = TRUE;
		$this->init_actions();
		$this->init_collections();
		
		if(config('vid_cat_height'));
		$this->cat_thumb_height =  config('vid_cat_height');
		if(config('vid_cat_width'));
		$this->cat_thumb_width =   config('vid_cat_width');
		
		if(isSectionEnabled('videos'))
		$Cbucket->search_types['videos'] = "cbvid";
		$Cbucket->clipbucket_footer[] = 'check_cbvideo';
		
		$this->video_delete_functions[] = 'delete_video_from_collection';

        $basic_fields = array(
            'videoid', 'videokey', 'userid', 'title','server_ip', 'description', 'tags', 'category','file_directory',
            'active', 'date_added', 'broadcast', 'rating', 'file_server_path', 'files_thumbs_path',
            'file_thumbs_count', 'duration', 'has_hq', 'has_mobile', 'views', 'file_name', 'rated_by',
            'default_thumb', 'comments_count', 'last_viewed', 'featured', 'featured_date', 'status','re_conv_status','conv_progress','embed_code'
        );

        $cb_columns->object( 'videos' )->register_columns( $basic_fields );
        #$this->basic_fields_setup();
	}

    /**
     * @return array
     */
    function get_basic_fields() {
        return $this->basic_fields;
    }

    function set_basic_fields( $fields = array() ) {
        return $this->basic_fields = $fields;
    }

    function basic_fields_setup() {

        # Set basic video fields
        $basic_fields = array(
            'videoid', 'videokey', 'userid', 'title', 'description', 'tags', 'category',
            'active', 'date_added', 'broadcast', 'rating', 'file_server_path', 'files_thumbs_path',
            'file_thumbs_count', 'duration', 'has_hq', 'has_mobile', 'views', 'file_name', 'rated_by',
            'default_thumb', 'comments_count', 'last_viewed'
        );

        return $this->set_basic_fields( $basic_fields );
    }

    function get_extra_fields() {
        return $this->extra_fields;
    }

    function set_extra_fields( $fields = array() ) {
        return $this->extra_fields = $fields;
    }

    function get_video_fields( $extra_fields = array() ) {
        $fields = $this->get_basic_fields();
        $extra = $this->get_extra_fields();

        if ( empty( $fields ) ) {
            $fields = $this->basic_fields_setup();
        }

        if ( !empty( $extra ) ) {
            $fields = array_merge( $fields, $extra );
        }

        if ( !empty( $extra_fields ) ) {
            if ( is_array( $extra_fields ) ) {
                $fields = array_merge( $fields, $extra_fields );
            } else {
                $fields[] = $extra_fields;
            }
        }

        # Do make array unqiue, otherwise we might get duplicate
        # fields
        $fields = array_unique( $fields );

        return $fields;
    }

    function add_video_field( $field ) {
        $extra_fields = $this->get_extra_fields();

        if ( is_array( $field ) ) {
            $extra_fields = array_merge( $extra_fields, $field );
        } else {
            $extra_fields[] = $field;
        }


        return $this->set_extra_fields( $extra_fields );
    }

	/**
	 * Initiating Collections
	 */
	function init_collections()
	{
		$this->collection = new Collections();
		$this->collection->objType = "v";
		$this->collection->objClass = "cbvideo";
		$this->collection->objTable = "video";
		$this->collection->objName = "Video";
		$this->collection->objFunction = "video_exists";
		$this->collection->objFieldID = "videoid";	
	}
	
	
	/**
	 * Function used to check weather video exists or not
	 * @param VID or VKEY
	 */
	function video_exists($vid)
	{
		global $db;
		if(is_numeric($vid))
		return $db->count(tbl("video"),"videoid"," videoid='$vid' ");
		else
		return $db->count(tbl("video"),"videoid"," videokey='$vid' ");
		//return $this->get_video($vid);
	}
	function exists($vid){return $this->video_exists($vid);}
	function videoexists($vid){return $this->video_exists($vid);}
	
	
	/**
	 * Function used to get video data
	 */
	function get_video( $vid, $file=false, $basic = false )
	{
		global $db, $cb_columns;
		
		$userFields = get_user_fields();
        $videoFields = array( 'video' => '*' );

        if ( $basic === true ) {
            $videoFields = get_video_fields();
        }

        $fields = array(
            'video' => $videoFields,
            'users' => $userFields
        );


        $cond = ( ( $file ) ? 'video.file_name' : ( is_numeric( $vid ) ? 'video.videoid' : 'video.videokey' ) )." = '%s' ";

        $query = "SELECT ".tbl_fields( $fields )." FROM ".cb_sql_table( 'video' );
        $query .= " LEFT JOIN ".cb_sql_table( 'users' )." ON video.userid = users.userid";

        if ( $cond ) {
            $query .= " WHERE ".sprintf( $cond, $vid );
        }

        $query .= 'LIMIT 1';
        $query_id = cb_query_id ( $query );

        $data = cb_do_action( 'select_video', array( 'query_id' => $query_id, 'videoid' => $vid ) );

        if ( $data ) {
            return $data;
        }
        //pr($query,true);
        $result = select( $query );

        if ( $result ) {

            $result = apply_filters( $result[ 0 ], 'get_video' );

            cb_do_action( 'return_video', array(
                'query_id' => $query_id,
                'results' => $result,
                'object_id' => $vid,
                'videoid' => $vid
            ) );

            return $result;
        } else {
            return false;
        }
	}

	function getvideo($vid){return $this->get_video($vid);}
	function get_video_data($vid){return $this->get_video($vid);}
	function getvideodata($vid){return $this->get_video($vid);}
	function get_video_details($vid){return $this->get_video($vid);}
	function getvideodetails($vid){return $this->get_video($vid);}
	function get_basic_video_details( $vid ){ return $this->get_video( $vid, false, true ); }

	/**
	 * Function used to perform several actions with a video
	 */
	function action($case,$vid)
	{
		global $db,$eh;

		$video = $this->get_video_details( $vid );
		 
		if(!$video)
			return false;
		//Lets just check weathter video exists or not
		switch($case)
		{
			//Activating a video
			case 'activate':
			case 'av':
			case 'a':
			{
				$db->update(tbl("video"),array('active'),array('yes')," videoid='$vid' OR videokey = '$vid' ");
				e(lang("class_vdo_act_msg"),'m');
				
				if(SEND_VID_APPROVE_EMAIL=='yes')
				{
					//Sending Email
					global $cbemail,$userquery;
					$tpl = $cbemail->get_template('video_activation_email');
					#$user_fields = $userquery->get_user_field($video['userid'],"username,email");
					$more_var = array
					('{username}'	=> $video['username'],
					 '{video_link}' => videoLink($video)
					);
					if(!is_array($var))
						$var = array();
					$var = array_merge($more_var,$var);
					$subj = $cbemail->replace($tpl['email_template_subject'],$var);
					$msg = nl2br($cbemail->replace($tpl['email_template'],$var));
					
					//Now Finally Sending Email
					cbmail(array('to'=>$video['email'],'from'=>WEBSITE_EMAIL,'subject'=>$subj,'content'=>$msg));
				}
				
				
				if(($video['broadcast']=='public' || $video['broadcast'] =="logged")
					&& $video['subscription_email']=='pending')
				{
					//Sending Subscription email in background
					if (stristr(PHP_OS, 'WIN'))
					{
						exec(php_path()." -q ".BASEDIR."/actions/send_subscription_email.php $vid ");
					} else {
						exec(php_path()." -q ".BASEDIR."/actions/send_subscription_email.php $vid &> /dev/null &");
					}
				}
			}
			break;
			
			//Deactivating a video
			case "deactivate":
			case "dav":
			case "d":
			{
				$db->update(tbl("video"),array('active'),array('no')," videoid='$vid' OR videokey = '$vid' ");
				e(lang("class_vdo_act_msg1"),'m');
			}
			break;
			
			//Featuring Video
			case "feature":
			case "featured":
			case "f":
			{
				$db->update(tbl("video"),array('featured','featured_date'),array('yes',now())," videoid='$vid' OR videokey = '$vid' ");
				e(lang("class_vdo_fr_msg"),'m');
				return "featured";
			}
			break;
			
			
			//Unfeatured video
			case "unfeature":
			case "unfeatured":
			case "uf":
			{
				$db->update(tbl("video"),array('featured'),array('no')," videoid='$vid' OR videokey = '$vid' ");
				e(lang("class_fr_msg1"),'m');
			}
			break;
		}
	}
	
	
	
	/**
	 * Function used to update video
	 */
	function update_video($array=NULL)
	{
		global $eh,$Cbucket,$db,$Upload;		
			 
		$Upload->validate_video_upload_form(NULL,TRUE);
		
		if(empty($eh->error_list))
		{
			$required_fields = $Upload->loadRequiredFields($array);
			$location_fields = $Upload->loadLocationFields($array);
			$option_fields = $Upload->loadOptionFields($array);
									
			$upload_fields = array_merge($required_fields,$location_fields,$option_fields);
			
			//Adding Custom Upload Fields
			if (function_exists('custom_fields_list')) {
				$custom_flds = $Upload->load_custom_form_fields($array,true);
				#pr($custom_flds,true);
				$upload_fields = array_merge($upload_fields,$custom_flds);
			}
			
			
			/*//Adding Custom Form Fields
			if(count($Upload->custom_form_fields)>0)
				$upload_fields = array_merge($upload_fields,$Upload->custom_form_fields);
			
			//Adding custom fields from group
			if(count($Upload->custom_form_fields_groups)>0)
			{
				$custom_fields_from_group_fields = array();
				$custom_fields_from_group = $Upload->custom_form_fields_groups;
				foreach($custom_fields_from_group as $cffg)
				{
					$custom_fields_from_group_fields = array_merge($custom_fields_from_group_fields,$cffg['fields']);
				}						
				
				$upload_fields = array_merge($upload_fields,$custom_fields_from_group_fields);
			}*/
			
			
			if(!$array)
			 $array = $_POST;
			 
			#pr($array,true);
			$vid = $array['videoid'];

			if(is_array($_FILES))
			$array = array_merge($array,$_FILES);
			
		
			foreach($upload_fields as $field)
			{
				$name = formObj::rmBrackets($field['name']);
				$val = $array[$name];
				
				if(empty($val) && $field['use_if_value'])
				{
				}else
				{
					if($field['use_func_val'])
						$val = $field['validate_function']($val);
					
					
					if(!empty($field['db_field']))
					$query_field[] = $field['db_field'];
					
					if(is_array($val))
					{
						$new_val = '';
						foreach($val as $v)
						{
							$new_val .= "#".$v."# ";
						}
						$val = $new_val;
					}
					if(!$field['clean_func'] || (!apply_func($field['clean_func'],$val) && !is_array($field['clean_func'])))
						$val = ($val);
					else
						$val = apply_func($field['clean_func'],sql_free('|no_mc|'.$val));
					
					if(!empty($field['db_field']))
					$query_val[] = $val;

				}
				
			}		
			
			#$query = "INSERT INTO video (";
			$total_fields = count($query_field);
			
			/*for($key=0;$key<$total_fields;$key++)
			{
				$query .= query_field[$key]." = '".$query_val[$key]."'" ;
				if($key<$total_fields-1)
				$query .= ',';
			}*/
			
			if(has_access('admin_access',TRUE))
			{
				if(!empty($array['status']))
				{
					$query_field[] = 'status';
					$query_val[] = $array['status'];
				}
				
				if(!empty($array['duration']) && is_numeric($array['duration']) && $array['duration']>0)
				{
					$query_field[] = 'duration';
					$query_val[] = $array['duration'];
				}
				
				if(!empty($array['views']))
				{
					$query_field[] = 'views';
					$query_val[] = $array['views'];
				}

				if(!empty($array['video_users']))
				{
					$query_field[] = 'video_users';
					$query_val[] = $array['video_users'];
				}
				
				if(!empty($array['rating']))
				{
					$query_field[] = 'rating';
					$rating = $array['rating'];
					if(!is_numeric($rating) || $rating<0 || $rating>10)
						$rating = 1;
					$query_val[] = $rating;
				}
				
				if(!empty($array['rated_by']))
				{
					$query_field[] = 'rated_by';
					$query_val[] = $array['rated_by'];
				}

				if (!empty($array['embed_code'])) 
				{
					$query_field[] = 'embed_code';
					$query_val[] = $array['embed_code'];
				}
			}
			//changes made
			//title index
			$query_val[0] = str_replace('&lt;!--', '', $query_val[0]);
			$query_val[0] = str_replace("'", "’", $query_val[0]);
			//description index
			$query_val[1] = str_replace('&lt;!--', '', $query_val[1]);
			$query_val[1] = str_replace("'", "’", $query_val[1]);
			//Tag index
			$query_val[3] = strtolower($query_val[3]);
			// $query_val[3] = str_replace("'", "’", $query_val[3]);
			//changes made

			
			if(!userid())
			{
				e(lang("you_dont_have_permission_to_update_this_video"));
			}elseif(!$this->video_exists($vid)){
				e(lang("class_vdo_del_err"));
			}elseif(!$this->is_video_owner($vid,userid()) && !has_access('admin_access',TRUE))
			{
				e(lang("no_edit_video"));
			}elseif(strlen($array['title']) > 100){
				e(lang("Title exceeds max length of 100 characters"));
			}else{
				//pr($upload_fields);	
				#pr($query_field,true);
				$db->update(tbl('video'),$query_field,$query_val," videoid='$vid'");

                cb_do_action( 'update_video', array(
                    'object_id' => $vid,
                    'results' => $array
                ));

				//echo $db->db_query;
				e(lang("class_vdo_update_msg"),'m');

				// condition for Clip press plugin
				if ( function_exists('post_to_wp_upload_culr') )
				{
					post_to_wp_upload_culr ( $vid );
				}
			}
			
		}
	}
	
	
	/**
	 * Function used to delete a video
	 */
	function delete_video($vid)
	{
		global $db;
		
		if($this->video_exists($vid))
		{
			
			$vdetails = $this->get_video($vid);

			if($this->is_video_owner($vid,userid()) || has_access('admin_access',TRUE))
			{
				#THIS SHOULD NOT BE REMOVED :O
				//list of functions to perform while deleting a video
				$del_vid_funcs = $this->video_delete_functions;
				
				
				if(is_array($del_vid_funcs))
				{
					foreach($del_vid_funcs as $func)
					{
						
						

						if(function_exists($func))
						{
							$func($vdetails);
						}
					}
				}



				//Finally Removing Database entry of video
				$db->execute("DELETE FROM ".tbl("video")." WHERE videoid='$vid'");
				//Removing Video From Playlist
				$db->execute("DELETE FROM ".tbl("playlist_items")." WHERE object_id='$vid' AND playlist_item_type='v'");
				
				$db->update(tbl("users"),array("total_videos"),array("|f|total_videos-1")," userid='".$vdetails['userid']."'");
				
				//Removing video Comments
				$db->delete(tbl("comments"),array("type","type_id"),array("v",$vdetails['videoid']));
				//Removing video From Favortes
				$db->delete(tbl("favorites"),array("type","id"),array("v",$vdetails['videoid']));
				
				e(lang("class_vdo_del_msg"),'m');
			}else{
				e(lang("You cannot delete this video"));
			}
		}else{
			e(lang("class_vdo_del_err"));
		}
		
	}
	
	/**
	 * Function used to remove video thumbs
	 */
	function remove_thumbs($vdetails)
	{
		//First lets get list of all thumbs
		$thumbs = get_thumb($vdetails,1,true,false,false,true,false);

		if(!is_default_thumb($thumbs))
		{
		
			if(is_array($thumbs))
			{
				foreach($thumbs as $thumb)
				{
					if (strstr($thumb,'timthumb'))
						$thumb = $this->convert_tim_thumb_url_to_file($thumb,$file_name=false);

					if (!empty($vdetails['file_directory'])){
						$file = THUMBS_DIR.'/'.$vdetails['file_directory'].'/'.$thumb;
					}else{
						$file = THUMBS_DIR.'/'.$thumb;
					}
					
					if(file_exists($file) && is_file($file)){
						unlink($file);
					}
				}
			}
			else
			{
				if (strstr($thumbs,'timthumb'))
					$thumbs_ = $this->convert_tim_thumb_url_to_file($thumbs,$file_name=false);
				else
					$thumbs_ = substr($thumbs, 0, -6);

				$file = THUMBS_DIR.'/'.$thumbs_;
				if(file_exists($file) && is_file($file))
					unlink($file);
				

				if (strstr($thumbs,'timthumb'))
					$fn = $this->convert_tim_thumb_url_to_file($thumbs,$file_name=true);
				else
					$fn = substr($thumbs, 0, -6);

				$result = db_select("SELECT * FROM ".tbl("video")." WHERE file_name = '$fn'");
				if($result)
				{
					foreach($result as $result1)
					{
						$str = '/'.$result1['file_directory'].'/';
						$file1 = THUMBS_DIR.$str.$thumbs;
						if(file_exists($file1) && is_file($file1))
						{
							unlink($file1);
						}
					}
				}
			}
			
			e(lang("vid_thumb_removed_msg"),'m');
		}
	}
	
	
	
	/**
	 * Function used to remove video log
	 */
	function remove_log($vdetails)
	{
		global $db;
		$src = $vdetails['videoid'];
		$file = LOGS_DIR.'/'.$vdetails['file_name'].'.log';
		$db->execute("DELETE FROM ".tbl("video_file")." WHERE src_name = '$src'");
		if(file_exists($file))
			unlink($file);
		$fn = $vdetails['file_name'];
		$result = db_select("SELECT * FROM ".tbl("video")." WHERE file_name = '$fn'");
		if($result)
		{
			foreach($result as $result1)
			{
				$str = '/'.$result1['file_directory'].'/';
				$file1 = LOGS_DIR.$str.$vdetails['file_name'].'.log';
				if(file_exists($file1) && is_file($file1))
					unlink($file1);
			}
		}
		e(lang("vid_log_delete_msg"),'m');
	}
	
	/**
	 * Function used to remove video files
	 */
	function remove_files($vdetails)
	{
		//Return nothing incase there is no input
		if(!$vdetails)
		{
			e("No input details specified");
			return false;
		}
		//Callign Video Delete Functions
	    call_delete_video_function($vdetails);
        
		//Getting list of files
		$files = get_video_file($vdetails,false,false,true);
		if(is_array($files))
		{
			foreach($files as $file)
			{
				if(file_exists(VIDEOS_DIR.'/'.$file) && is_file(VIDEOS_DIR.'/'.$file))
					unlink(VIDEOS_DIR.'/'.$file);

				//Extracting File Name for Video File 
				$fn = explode('-', $file);
				$fn = $fn[0];

				$result = db_select("SELECT * FROM ".tbl("video")." WHERE file_name = '$fn'");
				if($result)
				{
					foreach($result as $result1)
					{
						$str = '/'.$result1['file_directory'].'/';
						if(file_exists(VIDEOS_DIR.$str.$file) && is_file(VIDEOS_DIR.$str.$file))
						unlink(VIDEOS_DIR.$str.$file);
					}
				}

			}
		}
		else
		{
			if(file_exists(VIDEOS_DIR.'/'.$files) && is_file(VIDEOS_DIR.'/'.$files))
					unlink(VIDEOS_DIR.'/'.$files);
			$fn = substr($files, 0, -7);
			$result = db_select("SELECT * FROM ".tbl("video")." WHERE file_name = '$fn'");
			if($result)
			{
				foreach($result as $result1)
				{
					$str = '/'.$result1['file_directory'].'/';
					if(file_exists(VIDEOS_DIR.$str.$files) && is_file(VIDEOS_DIR.$str.$files))
						unlink(VIDEOS_DIR.$str.$files);
				}
			}

		}
		e(lang("vid_files_removed_msg"),'m');
	}
	
	
	/**
	 * Function used to get videos
	 * this function has all options
	 * that you need to fetch videos
	 * please see docs.clip-bucket.com for more details
	 */
	function get_videos($params)
	{
		global $db, $cb_columns;

		$limit = $params['limit'];
		$order = $params['order'];
		
		$cond = "";
		$superCond = "";
		if( !has_access('admin_access',TRUE) )
		{
			$superCond = " ".("video.status")."='Successful' AND
			".("video.active")."='yes' AND ".("video.broadcast")." !='unlisted' ";
		}
			
		else
		{
			if($params['active'])
				$cond .= " ".("video.active")."='".$params['active']."'";

			if($params['status'])
			{
				if($cond!='')
					$cond .=" AND ";
				$cond .= " ".("video.status")."='".$params['status']."'";
			}
			if($params['broadcast'])
			{
				if($cond!='')
					$cond .=" AND ";
				$cond .= " ".("video.broadcast")."='".$params['broadcast']."'";
			}
		}
		
		//Setting Category Condition
		$all = false;
		if(!is_array($params['category']))
			if(strtolower($params['category'])=='all')
				$all = true;
				
		if($params['category'] && !$all)
		{
			if($cond!='')
				$cond .= ' AND ';
				
			$cond .= " (";
			
			if(!is_array($params['category']))
			{
				$cats = explode(',',$params['category']);
			}else
				$cats = $params['category'];
				
			$count = 0;
			
			foreach($cats as $cat_params)
			{
				$count ++;
				if($count>1)
				$cond .=" OR ";
				$cond .= " ".("video.category")." LIKE '%#$cat_params#%' ";
			}
			
			$cond .= ")";
		}
		
		//date span
		if($params['date_span'])
		{
			if($cond!='')
				$cond .= ' AND ';
			
			if($params['date_span_column'])
				$column = $params['date_span_column'];
			else
				$column = 'date_added';

			$cond .= " ".cbsearch::date_margin($column,$params['date_span']);
		}
		
		//uid 
		if($params['user'])
		{
			if(!is_array($params['user']))
			{
				if($cond!='')
					$cond .= ' AND ';
				$cond .= " ".("video.userid")."='".$params['user']."'";
			}else
			{
				if($cond!='')
						$cond .= ' AND (';
				
				$uQu = 0;		
				foreach($params['user'] as $user)
				{
					if($uQu>0)
						$cond .= ' OR ';
					$cond .= " ".("video.userid")."='".$user."'";
					$uQu++;
				}
				
				$cond .=" ) ";
			}

		}

		//non-uid to exclude user videos from related
		if($params['nonuser'])
		{
			if($cond!='')
				$cond .= ' AND ';
			$cond .= " ".("video.userid")." <> '".$params['nonuser']."' ";

		}	

		if($params['editor_pick'])
		{
			if($cond!='')
				$cond .= ' AND ';
			$cond .= " ".("in_editor_pick")." = '".$params['editor_pick']."' ";

		}	

		//padding videos in mass_embed pllugin
		if($params['mass_embed_status'])
		{
			if($cond!='')
				$cond .= ' AND ';
			$cond .= " ".("video.mass_embed_status")." = '".$params['mass_embed_status']."' ";

		}	
					
		$tag_n_title='';
		//Tags
		if($params['tags'])
		{
			//checking for commas ;)
			$tags = explode(",",$params['tags']);
			if(count($tags)>0)
			{
				if($tag_n_title!='')
					$tag_n_title .= ' OR ';
				$total = count($tags);
				$loop = 1;
				foreach($tags as $tag)
				{
					$tag_n_title .= " ".('video.tags')." LIKE '%".$tag."%'";
					if($loop<$total)
					$tag_n_title .= " OR ";
					$loop++;
					
				}
			}else
			{
				if($tag_n_title!='')
					$tag_n_title .= ' OR ';
				$tag_n_title .= " ".('video.tags')." LIKE '%".$params['tags']."%'";
			}
		}
		//TITLE
		if($params['title'])
		{
			if($tag_n_title!='')
				$tag_n_title .= ' OR ';
			$tag_n_title .= " ".('video.title')." LIKE '%".$params['title']."%'";
		}
		
		if($tag_n_title)
		{
			if($cond!='')
				$cond .= ' AND ';
			$cond .= " ($tag_n_title) ";
		}
		
		//FEATURED
		if($params['featured'])
		{
			if($cond!='')
				$cond .= ' AND ';
			$cond .= " ".("video.featured")." = '".$params['featured']."' ";
		}
		
		//VIDEO ID
		if($params['videoid'])
		{
			if($cond!='')
				$cond .= ' AND ';
			$cond .= " ".("video.videoid")." = '".$params['videoid']."' ";
		}
		
		//VIDEO ID
		if($params['videoids'])
		{
			if(is_array($params['videoids']))
			{
				if($cond!='')
				$cond .= ' AND ';
				$cond .= ' ( ';
				$curVid = 0;
				foreach($params['videoids'] as $vid)
				{
					if(is_numeric($vid))
					{
						if($curVid>0)
							$cond .= " OR ";
						$cond .= " ".("video.videoid")." = '".$vid."' ";
					}
					$curVid++;
				}
				$cond .= ' ) ';				
			}
		}
		
		//VIDEO KEY
		if($params['videokey'])
		{
			
			if(!is_array($params['videokey']))
			{
				if($cond!='')
					$cond .= ' AND ';
				$cond .= " ".tbl("video.videokey")." = '".$params['videokey']."' ";
			}else
			{
				if($cond!='')
						$cond .= ' AND (';
				
				$vkeyQue = 0;		
				foreach($params['videokey'] as $videokey)
				{
					if($vkeyQue>0)
						$cond .= ' OR ';
					$cond .= " ".tbl("video.videokey")." = '".$videokey."' ";
					$vkeyQue++;
				}
				
				$cond .=" ) ";
			}
		}		
		
		
		//Exclude Vids
		if($params['exclude'])
		{
			if(!is_array($params['exclude']))
			{
				if($cond!='')
					$cond .= ' AND ';
				$cond .= " ".('video.videoid')." <> '".$params['exclude']."' ";
			}else
			{
				foreach($params['exclude'] as $exclude)
				{
					if($cond!='')
						$cond .= ' AND ';
					$cond .= " ".('video.videoid')." <> '".$exclude."' ";
				}
			}
		}
		
		//Duration
		
		if($params['duration'])
		{
			$duration_op = $params['duration_op'];
			if(!$duration_op) $duration_op = "=";
			
			if($cond!='')
				$cond .= ' AND ';
			$cond .= " ".('video.duration')." ".$duration_op." '".$params['duration']."' ";
		}
		
		//Filename
		
		if($params['filename'])
		{
			if(!is_array($params['filename']))
			{
				if($cond!='')
					$cond .= ' AND ';
				$cond .= " ".('video.file_name')." <> '".$params['filename']."' ";
			}else
			{
				if($cond!='')
						$cond .= ' AND (';
				
				$fileNameQue = 0;		
				foreach($params['filename'] as $filename)
				{
					if($fileNameQue>0)
						$cond .= ' OR ';
					$cond .= " ".("video.file_name")." = '".$filename."' ";
					$fileNameQue++;
				}
				
				$cond .=" ) ";
			}
		}
		
		if($params['cond'])
		{
			if($params['cond_and'])
				if($cond!='')
					$cond .= ' AND ';
			$cond .= " ".$params['cond'];
		}
		
		
		$functions = cb_get_functions('get_videos');
		if($functions)
		{
			foreach($functions as $func)
			{
				$array = array('params'=>$params,'cond'=>$cond);
				if(function_exists($func['func']))
				{
					$returned = $func['func']($array);
					if($returned)
						$cond = $returned;
				}
			}
		}

        $fields = array(
            'video' => get_video_fields(),
           // 'users' => get_user_fields()
               'users' =>  $cb_columns->object('users')->temp_change('featured','user_featured')->get_columns()
        );

        if (!isset($fields['video_users'])) {
        	$fields[] = 'video_users';
        }

        $fields = tbl_fields( $fields );
		
		if(!$params['count_only'] &&  !$params['show_related'])
		{

            $query = "SELECT $fields FROM ".cb_sql_table( 'video' );
            $query .= " LEFT JOIN ".cb_sql_table( 'users' )." ON video.userid = users.userid";
            
            if(!empty($superCond))
            {
				if ($cond !=="")
            	{
            		$cond .=  " AND ".$superCond;
            	}
            	else
            	{
            		$cond .= $superCond;
            	}	
            }
           
			if( $cond ) {
                $query .= " WHERE ".$cond;
            }

            $query .= $order ? " ORDER BY ".$order : false;
            $query .= $limit ? " LIMIT ".$limit : false;
            #pr( $query, true );

            $result = select( $query );

			#if(!empty($cond))
			#	$cond .= " AND ";
			#$result = $db->select(tbl('video,users'),tbl('video.*,users.userid,users.username'),$cond." ".tbl("video.userid")." = ".tbl("users.userid"),$limit,$order);
			//echo $db->db_query;
		}

        global $cbsearch;
		
		if($params['show_related'])
		{
			$cond = "";
			if($superCond)
				$cond = $superCond." AND ";
			
			$cond .= "MATCH(".("video.title,video.tags").")
			AGAINST ('".$params['title']."' IN BOOLEAN MODE) ";

			if($params['exclude'])
			{
				if($cond!='')
					$cond .= ' AND ';
				$cond .= " ".('video.videoid')." <> '".$params['exclude']."' ";
			}

            $query = " SELECT ".$fields." FROM ".cb_sql_table('video');
            $query .= " LEFT JOIN ".cb_sql_table('users');
            $query .= " ON video.userid = users.userid ";

            if( $cond ) {
                $query .= " WHERE ".$cond;
            }

            $query .= $order ? " ORDER BY ".$order : false;
            $query .= $limit ? " LIMIT ".$limit : false;

            $result = select( $query );

			#$result = $db->select(tbl('video,users'),tbl('video.*,users.userid,users.username'),
			#$cond." AND ".tbl("video.userid")." = ".tbl("users.userid"),$limit,$order);

			if($db->num_rows == 0)
			{
				$cond = "";
				if($superCond)
					$cond = $superCond." AND ";
				//Try Finding videos via tags
				$cond .= "MATCH(".("video.title,video.tags").")
				AGAINST ('".($params['tags'])."' IN BOOLEAN MODE) ";
				if($params['exclude'])
				{
					if($cond!='')
						$cond .= ' AND ';
					$cond .= " ".('video.videoid')." <> '".$params['exclude']."' ";
				}


                $query = " SELECT ".$fields." FROM ".cb_sql_table('video');
                $query .= " LEFT JOIN ".cb_sql_table('users');
                $query .= " ON video.userid = users.userid ";
                
                if( $cond ) {
                $query .= " WHERE ".$cond;
                 }

                $query .= $order ? " ORDER BY ".$order : false;
                $query .= $limit ? " LIMIT ".$limit : false;

                $result = select( $query );

				#$result = $db->select(tbl('video,users'),tbl('video.*,users.userid,users.username'),
				#$cond." AND ".tbl("video.userid")." = ".tbl("users.userid"),$limit,$order);
			}
			
			assign($params['assign'],$result);
		}
		
		if($params['pr']) pr($result,true);
		if($params['count_only']){
			if (!empty($superCond)){
				if (!empty($cond)){
					$cond .= " AND ";
				}
				$cond .= $superCond;
			}
			return $result = $db->count( cb_sql_table('video') , 'videoid' ,$cond );
		}
		if($params['assign'])
			assign($params['assign'], apply_filters( $result, 'get_video' ) );
		else
			return apply_filters( $result, 'get_video' );
	}
	
	/**
	 * Function used to count total video comments
	 */
	function count_video_comments($id)
	{
		global $db;
		$total_comments = $db->count(tbl('comments'),"comment_id","type='v' AND type_id='$id' AND parent_id='0'");
		return $total_comments;
	}
	
	
	/**
	 * Function used to update video comments count
	 */
	function update_comments_count($id)
	{
		global $db;
		$total_comments = $this->count_video_comments($id);
		$db->update(tbl("video"),array("comments_count","last_commented"),array($total_comments,now())," videoid='$id'");
	}
	
	/**
	 * Function used to add video comment
	 */
	function add_comment($comment,$obj_id,$reply_to=NULL,$force_name_email=false)
	{
		global $myquery,$db;
		
		$video = $this->get_video_details($obj_id);
		
		if(!$video)
			e(lang("class_vdo_del_err"));
		else
		{
			//Getting Owner Id
			$owner_id = $this->get_video_owner($obj_id,true);
			$add_comment =  $myquery->add_comment($comment,$obj_id,$reply_to,'v',$owner_id,videoLink($video),$force_name_email);
			if($add_comment)
			{
				//Loggin Comment			
				$log_array = array
				(
				 'success'=>'yes',
				 'details'=> "comment on a video",
				 'action_obj_id' => $obj_id,
				 'action_done_id' => $add_comment,
				);
				insert_log('video_comment',$log_array);
				
				//Updating Number of comments of video if comment is not a reply
				if ($reply_to < 1)
					$this->update_comments_count($obj_id);
				
				
			}
			return $add_comment;
		}
	}
	
	/**
	 * Function used to remove video comment
	 */
	function delete_comment($cid,$is_reply=FALSE)
	{
		global $myquery,$db;
		$remove_comment =  $myquery->delete_comment($cid,'v',$is_reply);
		if($remove_comment)
		{
			//Updating Number of comments of video
			$this->update_comments_count($obj_id);
		}
		return $remove_comment;
	}
	
	
	/**
	 * Function used to generate Embed Code
	 */
	function embed_code($vdetails,$type='embed_object')
	{
		//Checking for video details
		if(!is_array($vdetails))
		{
			$vdetails = $this->get_video($vdetails);
		}
				
		$embed_code = false;
		
		$funcs = $this->embed_func_list;
		
		if(is_array($funcs))
		{
			foreach($funcs as $func)
			{
				if(@function_exists($func))
					$embed_code = $func($vdetails);
				
				if($embed_code)
					break;
			}
		}
		
		
		if($type=='iframe')
		{
			$embed_code = '<iframe width="'.config('embed_player_width').'" height="'.config('embed_player_height').'" ';
			$embed_code .= 'src="'.BASEURL.'/player/embed_player.php?vid='.$vdetails['videokey'].'&width='.
			config('embed_player_width').'&height='.config('embed_player_height').
			'&autoplay='.config('autoplay_embed').'" frameborder="0" allowfullscreen></iframe>';
		}
		
		if(!$embed_code)
		{
		
			//Default ClipBucket Embed Code
			if(function_exists('default_embed_code'))
			{				
				$embed_code = default_embed_code($vdetails);
			}else
			{
				//return new Embed Code
				$vid_file = get_video_file($vdetails,false,false);
				if($vid_file)
				{
					$code = '';
					$code .= '<object width="'.EMBED_VDO_WIDTH.'" height="'.EMBED_VDO_HEIGHT.'">';
					$code .= '<param name="movie" value="'.PLAYER_URL.'/embed_player.php?vid='.$vdetails['videoid'].'"></param>';
					$code .= '<param name="allowFullScreen" value="true"></param>';
					$code .= '<param name="allowscriptaccess" value="always"></param>';
					$code .= '<embed src="'.PLAYER_URL.'/embed_player.php?vid='.$vdetails['videoid'].'"';
					$code .= 'type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="300" height="250"></embed>';
					return $code .= '</object>';
				}else
				{
					return embeded_code($vdetails);
				}
			}
		}
		
		return $embed_code;
		
	}
	
	
	/**
	 * Function used to initialize action class
	 * in order to call actions.class.php to
	 * work with Video section, this function will be called first
	 */
	function init_actions()
	{
		$this->action = new cbactions();
		$this->action->init();
		$this->action->type = 'v';
		$this->action->name = 'video';
		$this->action->obj_class = 'cbvideo';
		$this->action->check_func = 'video_exists';
		$this->action->type_tbl = $this->dbtbl['video'];
		$this->action->type_id_field = 'videoid';
	}
	
	
		
	/**
	 * Function used to create value array for email templates
	 * @param video_details ARRAY
	 */
	function set_share_email($details)
	{
		$this->email_template_vars = array
		('{video_title}' => $details['title'],
		 '{video_description}' => $details['description'],
		 '{video_tags}' => $details['tags'],
		 '{video_date}' => cbdate(DATE_FORMAT,strtotime($details['date_added'])),
		 '{video_link}' => video_link($details),
		 '{video_thumb}'=> GetThumb($details)
		 );
		
		$this->action->share_template_name = 'share_video_template';
		$this->action->val_array = $this->email_template_vars;
	}
	
	
	/**
	 * Function used to use to initialize search object for video section
	 * op=>operator (AND OR)
	 */
	function init_search()
	{
			
		$this->search = new cbsearch;
		$this->search->db_tbl = "video";
		$this->search->columns =array(
			array('field'=>'title','type'=>'LIKE','var'=>'%{KEY}%'),
			array('field'=>'tags','type'=>'LIKE','var'=>'%{KEY}%','op'=>'OR'),
			array('field'=>'broadcast','type'=>'!=','var'=>'unlisted','op'=>'AND','value'=>'static'),
			array('field'=>'status','type'=>'=','var'=>'Successful','op'=>'AND','value'=>'static')
		);
		//commit this line so that videos search can be applied to %like% instead of whole word search
		//$this->search->use_match_method = true;
		$this->search->match_fields = array("title","tags");
		
		$this->search->cat_tbl = $this->cat_tbl;
		
		$this->search->display_template = LAYOUT.'/blocks/video.html';
		$this->search->template_var = 'video';
		$this->search->has_user_id = true;
		
		/**
		 * Setting up the sorting thing
		 */
		
		$sorting	= 	array(
						'date_added'=> lang("date_added"),
						'views'		=> lang("views"),
						'comments'  => lang("comments"),
						'rating' 	=> lang("rating"),
						'favorites'	=> lang("favorites")
						);
		
		$this->search->sorting	= array(
						'date_added'=> " date_added DESC",
						'views'		=> " views DESC",
						'comments'  => " comments_count DESC ",
						'rating' 	=> " rating DESC",
						'favorites'	=> " favorites DeSC"
						);
		/**
		 * Setting Up The Search Fields
		 */
		 
		$default = $_GET;
		if(is_array($default['category']))
			$cat_array = array($default['category']);		
		$uploaded = $default['datemargin'];
		$sort = $default['sort'];
		
		$this->search->search_type['videos'] = array('title'=>lang('videos'));
		$this->search->results_per_page = config('videos_items_search_page');
		
		$fields = array(
		'query'	=> array(
						'title'=> lang('keywords'),
						'type'=> 'textfield',
						'name'=> 'query',
						'id'=> 'query',
						'value'=>cleanForm($default['query'])
						),
		'category'	=>  array(
						'title'		=> lang('vdo_cat'),
						'type'		=> 'checkbox',
						'name'		=> 'category[]',
						'id'		=> 'category',
						'value'		=> array('category',$cat_array),
						),
		'uploaded'	=>  array(
						'title'		=> lang('uploaded'),
						'type'		=> 'dropdown',
						'name'		=> 'datemargin',
						'id'		=> 'datemargin',
						'value'		=> $this->search->date_margins(),
						'checked'	=> $uploaded,
						),
		'sort'		=> array(
						'title'		=> lang('sort_by'),
						'type'		=> 'dropdown',
						'name'		=> 'sort',
						'value'		=> $sorting,
						'checked'	=> $sort
							)
		);

		$this->search->search_type['videos']['fields'] = $fields;
	}
	
	
	/*
	 * Function used to update video and set a thumb as default
	 * @param VID
	 * @param THUMB NUM
	 */
	function set_default_thumb($vid,$thumb)
	{
		global $db,$LANG;
		$num = get_thumb_num($thumb);
		$file = THUMBS_DIR.'/'.$thumb;
		//if(file_exists($file))
		//{
			$db->update(tbl("video"),array("default_thumb"),array($num)," videoid='$vid'");
			e(lang('vid_thumb_changed'),'m');
		//}else{
		//	e(lang('vid_thumb_change_err'));
		//}
	}	
	
	
	/**
	 * Function used to get video owner
	 */
	function get_video_owner($vid,$idonly=false)
	{
		global $db;
		if($idonly)
		{
			$results = $db->select(tbl("video"),"userid"," videoid='$vid' ",1);
			if($db->num_rows>0)
				return $results[0]['userid'];
			else
				return false;
		}else{
			$results = $db->select(tbl("video"),"*"," videoid='$vid' ",1);
			if($db->num_rows>0)
				return $results[0];
			else
				return false;
		}
	}
	
	/**
	 * Function used to check weather current user is video owner or not
	 */
	function is_video_owner($vid,$uid)
	{
		global $db;
		
		$result = $db->count(tbl($this->dbtbl['video']),'videoid',"videoid='$vid' AND userid='$uid' ");
		if($result>0)
			return true;
		else
			return false;
	}
	
	/**
	 * Function used to display video manger link
	 */
	function video_manager_link($link,$vid)
	{
		if(function_exists($link) && !is_array($link))
		{
			return $link($vid);
		}else
		{
			if(!empty($link['title']) && !empty($link['link']))
			{
				return '<a href="'.$link['link'].'">'.$link['title'].'</a>';
			}
		}
	}
	

	/**
	 * Function used to display video manger link temporay
	 */
	function video_manager_link_new($link,$vid)
	{
		if(function_exists($link) && !is_array($link))
		{
			return $link($vid);
		}else
		{
			if(!empty($link['title']) && !empty($link['link']))
			{
				return '<a href="'.$link['link'].'">'.$link['title'].'</a>';
			}
		}
	}


	/**
	 * Function used to display video categories manger link temporay
	 */
	function video_categories_manager_link($link,$vid)
	{
		if(function_exists($link) && !is_array($link))
		{
			return $link($vid);
		}else
		{
			if(!empty($link['title']) && !empty($link['link']))
			{
				return '<a href="'.$link['link'].'">'.$link['title'].'</a>';
			}
		}
	}
	
	
	/**
	 * Function used to get video rating details
	 */
	function get_video_rating($id)
	{
		global $db;
		if(is_numeric($id))
		{
			$results = $db->select(tbl("video"),"userid,allow_rating,rating,rated_by,voter_ids"," videoid='$id'");
		}else
			$results = $db->select(tbl("video"),"userid,allow_rating,rating,rated_by,voter_ids"," videokey='$id'");
		if($db->num_rows>0)
			return $results[0];
		else
			return false;
	}
	
	/**
	 * Function used to display rating option for videos
	 * this is an OLD TYPICAL RATING SYSTEM
	 * and yes, still with AJAX
	 */
	function show_video_rating($params)
	{

		$rating 	= $params['rating'];
		$ratings 	= $params['ratings'];
		$total 		= $params['total'];
		$id 			= $params['id'];
		$type 		= $params['type'];
		$data_only 		= $params['data_only'];
		
		if (empty($ratings)) {
			$ratings = $params['rated_by'];
		}
		//Checking Percent
		{
			if($total<=10)
				$total = 10;
			$perc = $rating*100/$total;
			$perc = round($perc);
			$disperc = 100 - $perc;
			if($ratings <= 0 && $disperc == 100)
				$disperc = 0;
		}
				
		$perc = $perc.'%';
		$disperc = $disperc.'%';
		$likes = round($ratings*$perc/100); // get lowest integer
		
		if($params['is_rating'])
		{
			if(error())
			{
				$rating_msg = error();
				$rating_msg = '<span class="error">'.$rating_msg[0].'</span>';
			}
			if(msg())
			{
				$rating_msg = msg();
				$rating_msg = '<span class="msg">'.$rating_msg[0].'</span>';
			}
		}
			
		if($data_only){
			$data = array(
					'perc'=>$perc,
					'disperc'=>$disperc,
					'id'=>$id,
					'type'=>$type,
					'id'=>$id,
					'rating_msg'=>$rating_msg,
					'likes'=>$likes,
					'dislikes'=>($ratings-$likes),
					'disable'=>$params['disable']
				);
			return $data;	
		}
		assign('perc',$perc);
		assign('disperc',$disperc);
		assign('id',$id);
		assign('type',$type);
		assign('id',$id);
		assign('rating_msg',$rating_msg);
		assign("likes",$likes);
		assign("dislikes",($ratings-$likes));
		assign('disable',$params['disable']);


        if(SMARTY_VERSION>2)
            Template('blocks/common/rating.html');
        else
		    Template('blocks/rating.html');
		
	}
	
	/** 
	 * Calculate rating and likes
	 *
	 * @param Arrray $video
	 * @return Array $rating
	 */
	function get_video_likes($video)
	{
		$ratings 	= $video['rated_by'];
		$rating 	= $video['rating'];

		$perc 		= $rating * 10;

		$likes = round(($perc * $ratings / 100) );

		return array('likes' => $likes, 'dislikes' => $ratings - $likes);

	}
	
	/**
	 * Function used to rate video
	 */
	function rate_video($id,$rating)
	{
		global $db;
		
		if(!is_numeric($rating) || $rating <= 9)
			$rating = 0;
		if($rating >= 10)
			$rating = 10;

		$rating_details = $this->get_video_rating($id);
		$voter_id = $rating_details['voter_ids'];
		
		$new_by = $rating_details['rated_by'];
		$newrate = $rating_details['rating'];
		if(phpversion < '5.2.0')
			global $json; $js = $json;
		
		$Oldvoters = explode('|',$voter_id);
		
		if(is_array($Oldvoters) && count($Oldvoters)>2)
		{
			foreach($Oldvoters as $voter)
			{
				if($voter)
				{
					$voters[$voter] = array(
					"userid"	=>	$voter,
					"time"	=>	now(),
					"method" => 'old',
					);
				}
			}
		}else
		{
			if(!empty($js))
				$voters = $js->json_decode($voter_id,TRUE);
			else
				$voters = json_decode($voter_id,TRUE);	
		}

		if(!empty($voters))
			$already_voted = array_key_exists(userid(),$voters);
			
		if(!userid())
			e(lang("please_login_to_rate"));
		elseif(userid()==$rating_details['userid'] && !config('own_video_rating'))
			e(lang("you_cant_rate_own_video"));
		elseif(!empty($already_voted))
			e(lang("you_hv_already_rated_vdo"));
		elseif(!config('video_rating') || $rating_details['allow_rating'] !='yes' )
			e(lang("vid_rate_disabled"));
		else
		{
			$voters[userid()] = array(
				"userid"	=>	userid(),
				"username"	=>	username(),
				"time"	=>	now(),
				"rating"	=>	$rating
			);
			
			$total_voters = count($voters);
			
			if(!empty($js))
				$voters = $js->json_encode($voters);
			else
				$voters = json_encode($voters);
					
			$t = $rating_details['rated_by'] * $rating_details['rating'];
			//$new_by = $rating_details['rated_by'] + 1;
			$new_by = $total_voters;
			
			$newrate = ($t + $rating) / $new_by;
			if($newrate>10)
				$newrate = 10;
			$db->update(tbl($this->dbtbl['video']),array("rating","rated_by","voter_ids"),array($newrate,$new_by,"|no_mc|$voters")," videoid='$id'");
			$userDetails = array(
				"object_id"	=>	$id,
				"type"	=>	"video",
				"time"	=>	now(),
				"rating"	=>	$rating,
				"userid"	=>	userid(),
				"username"	=>	username()
			);	
			/* Updating user details */		
			update_user_voted($userDetails);
			e(lang("thnx_for_voting"),"m");		
		}
		
		$result = array('rating'=>$newrate,'ratings'=>$new_by,'total'=>10,'id'=>$id,'type'=>'video','disable'=>'disabled');
		return $result;
		
		
		/*
		Following code is unused
		$niddle = "|";
		$niddle .= userid();
		$niddle .= "|";
		$flag = strstr($voter_id, $niddle);
		
		//checking if raings are allowed or not
		$vid_rating = config('video_rating');
		
		if(!userid())
			e(lang("please_login_to_rate"));
		elseif(userid()==$rating_details['userid'] && !config('own_video_rating'))
			e(lang("you_cant_rate_own_video"));
		elseif(!empty($flag))
			e(lang("you_hv_already_rated_vdo"));
		elseif(!config('video_rating') || $rating_details['allow_rating'] !='yes' )
			e(lang("vid_rate_disabled"));
		else
		{
			if(empty($voter_id))
				$voter_id .= "|";
			$voter_id .= userid();
			$voter_id .= "|";
			$t = $rating_details['rated_by'] * $rating_details['rating'];
			$new_by = $rating_details['rated_by'] + 1;
			$newrate = ($t + $rating) / $new_by;
			
			$db->update(tbl($this->dbtbl['video']),array("rating","rated_by","voter_ids"),array($newrate,$new_by,$voter_id)," videoid='$id'");
			e(lang("thnx_for_voting"),"m");	
		}
		
		$result = array('rating'=>$newrate,'ratings'=>$new_by,'total'=>10,'id'=>$id,'type'=>'video','disable'=>'disabled');
		return $result;
		*/
	}
	
	
	/**
	 * Function used to get playlist items
	 */
	function get_playlist_items( $playlist_id, $order = null, $limit = 10 )
	{
        global $db, $cb_columns;

        $fields = array(
            'playlist_items' => $cb_columns->object( 'playlist_items' )->temp_change( 'date_added', 'item_added' )->get_columns(),
            'playlists' => $cb_columns->object( 'playlists' )->temp_remove( 'first_item,cover' )->temp_change( 'date_added,description,tags,category', 'playlist_added,playlist_description,playlist_tags,playlist_category' )->get_columns(),
            'video' => $cb_columns->object( 'videos' )->get_columns()
        );


        $query = "SELECT ".table_fields( $fields )." FROM ".table( 'playlist_items' );
        $query .= " LEFT JOIN ".table( 'playlists' )." ON playlist_items.playlist_id = playlists.playlist_id";
        $query .= " LEFT JOIN ".table( 'video' )." ON playlist_items.object_id = video.videoid";

        $query .= " WHERE playlist_items.playlist_id = '".$playlist_id."' ";

        if ( !is_null( $order ) ) {
            $query .= "ORDER BY ".$order;
        }

        if ( $limit > 0 ) {
            $query .= " LIMIT ".$limit;
        }

        $query_id = cb_query_id( $query );

        $data = cb_do_action( 'select_playlist_items', array( 'query_id' => $query_id, 'playlist_id' => $playlist_id ) );

        if ( $data ) {
            return $data;
        }


        $data = select( $query );

        if ( $data ) {

            cb_do_action( 'return_playlist_items', array(
                'query_id' => $query_id,
                'results' => $data
            ) );

            return $data;
        }

        return false;

	}	
	
	/**
	 * Function used to add video in quicklist
	 */
	function add_to_quicklist($id)
	{
		global $json, $sess, $userquery;
		
		if($this->exists($id))
		{
            if(phpversion() < '5.2.0')
            {
			    $list = $json->decode($sess->get_cookie(QUICK_LIST_SESS), true);
            }
            else
            {
			    $list = json_decode($sess->get_cookie(QUICK_LIST_SESS), true);
            }
			
			$list[] = $id;
			$new_list = array_unique($list);

			/*//Getting list of videos
			$vids = $this->get_videos(array('videoids'=>$new_list));
			$newlist = array();
			//setting up the list
			if($vids)
			foreach($vids as $vid)
			{
				$newlist[$vid['videoid']] = 
				array(
				'title' => $vid['title'],
				'description' => $vid['description'],
				'duration' => SetTime($vid['duration']),
				'thumb'	=> get_thumb($vid),
				'url'	=> video_link($video),
				'owner' => $vid['username'],
				'ownner_url' => $userquery->profile_link($vid),
				'date_added' => $vid['date_added'],
				'views'	=> $vid['views'],
				);
			}*/
            if(phpversion() < '5.2.0')
            {
			    $sess->set_cookie(QUICK_LIST_SESS,$json->encode($new_list));
            }
            else
            {
                $sess->set_cookie(QUICK_LIST_SESS,json_encode($new_list));
            }
			return true;

		}else
			return false;
	}
	
	/**
	 * Removing video from quicklist
	 */
	function remove_from_quicklist($id)
	{
		global $json, $sess;

        if(phpversion() < '5.2.0')
        {
		    $list = $json->decode($sess->get_cookie(QUICK_LIST_SESS), true);
        }
        else
        {
            $list = json_decode($sess->get_cookie(QUICK_LIST_SESS), true);
        }
		$key = array_search($id,$list);
		unset($list[$key]);
        if(phpversion() < '5.2.0')
        {
		    $sess->set_cookie(QUICK_LIST_SESS,$json->encode($list));
        }
        else
        {
		    $sess->set_cookie(QUICK_LIST_SESS,json_encode($list));
        }
		return true;
	}
	
	
	/**
	 * function used to count num of quicklist
	 */
	function total_quicklist()
	{
		global $json, $sess;

		$total = $sess->get_cookie(QUICK_LIST_SESS);

        if(phpversion() < '5.2.0')
        {
		    $total = $json->decode($total, true);
        }
        else
        {
            $total = json_decode($total, true);
        }
		
		return count($total);
	}
	
	/**
	 * Function used to get quicklist
	 */
	function get_quicklist()
	{
		global $json, $sess;
        if(phpversion() < '5.2.0')
        {
		    return $json->decode($sess->get_cookie(QUICK_LIST_SESS), true);
        }
        else
        {
            return json_decode($sess->get_cookie(QUICK_LIST_SESS), true);
        }
	}
	
	/**
	 * Function used to remove all items of quicklist
	 */
	function clear_quicklist()
	{
		global $sess;
		$sess->set_cookie(QUICK_LIST_SESS,'');
	}
	
	/**
	 * Function used to check weather video is downloadable or not
	 */
	function downloadable($vdo)
	{
		$file = get_video_file($vdo,false);
		if($file)
			return true;
		else
			return false;
	}
	
	/**
	 * Function used get comments of videos
	 */
	function get_comments($params=NULL)
	{
		global $db;
		$comtbl = tbl("comments");
		$limit = $params['limit'];
		$order = $params['order'];
		$type = $params['type'];
		
		if($type)
			$cond = " $comtbl.type = '$type'";
		else
			$cond = '';
			
		switch($type) {
			case 'v':
			{
				$sectbl = tbl('video');
				$sectblName = 'video';
				$secfields =  $sectbl.".videokey,".$sectbl.".videoid,".$sectbl.".file_name,".$sectbl.".title";
				if($cond) {
					$cond .= " AND";
				}
				$cond .= " $comtbl.type_id = $sectbl.videoid";
			}
			break;
			
			case 't':
			{
				$sectbl = tbl('group_topics');
				$sectblName = 'group_topics';
				$secfields = $sectbl.".topic_title,".$sectbl.".topic_id,".$sectbl.".topic_title";
				if($cond) {
					$cond .= " AND";
				}
				$cond .= " $comtbl.type_id = $sectbl.topic_id";
			}
			break;
			
			case 'c':
			{
				$sectbl = tbl('users');
				$sectblName = 'users';
				$secfields = $sectbl.".username,".$sectbl.".userid";
				if($cond) {
					$cond .= " AND";
				}
				$cond .= " $comtbl.type_id = $sectbl.userid";
			}
			break;
			
			
			default:
			{
				$sectbl = tbl('video');
				$sectblName = 'video';
				$secfields =  $sectbl.".videokey,".$sectbl.".videoid,".$sectbl.".file_name,".$sectbl.".title";
				if($cond) {
					$cond .= " AND";
				}
				$cond .= " $comtbl.type_id = $sectbl.videoid";
			}			
		}	

		if($params['cond'])
			$cond .= " ".$params['cond'];
			
		if(!$params['count_only']) {
		$result = $db->select(tbl("comments,".$sectblName.""),
								  "$comtbl.*,$secfields",
								  $cond,$limit,$order);
		// echo $db->db_query;
		}
		if($params['count_only'])
			return $result = $db->count(tbl("comments,video"),"*",$cond);
		else
			return $result;
	}
	
	/**
	 * Function used get single comment
	 */	

	function get_comment($cid) {
		global $db;
		$result = $db->select(tbl("comments"),"*", " comment_id = $cid");
		if($result)
			return $result[0];
		else
			return false;
	}
	/**
	 * Function used get single comment
	 */	

	function send_notifications() {
		// Put your device token here (without spaces):
                $deviceToken = '610b18964b4ec3ccb8157ff22cb917fe0f6b9c1673cd4229674d87eb9aa0e5b1';

                // Put your private key's passphrase here:
                $passphrase = 'janjua';

                // Put your alert message here:
                $message = 'Jeevay Pakistan!';

                ////////////////////////////////////////////////////////////////////////////////

                $ctx = stream_context_create();
                stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck.pem');
                stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

                // Open a connection to the APNS server
                $fp = stream_socket_client(
                        'ssl://gateway.sandbox.push.apple.com:2195', $err,
                        $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

                if (!$fp)
                        exit("Failed to connect: $err $errstr" . PHP_EOL);

                echo 'Connected to APNS' . PHP_EOL;

                // Create the payload body
                $body['aps'] = array(
                        'alert' => $message,
                        'sound' => 'default'
                        );

                // Encode the payload as JSON
                $payload = json_encode($body);

                // Build the binary notification
                $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

                // Send it to the server
                $result = fwrite($fp, $msg, strlen($msg));

                if (!$result)
                        echo 'Message not delivered' . PHP_EOL;
                else
                        echo 'Message successfully delivered' . PHP_EOL;

                // Close the connection to the server
                fclose($fp);

	}

	/**
	 * Function used to convert timthumb url to filename and file
	 * @param  : url, flag
	 * @date   : 6/2/2015
	 * @author : Fahad Abbas
	 * @reason : to delete the thumb from server forcefully  
	 */	

	function convert_tim_thumb_url_to_file($url,$file_name=false)
	{
		$thumb = explode('src=',$url);
		if ($file_name)
			$thumb = explode('-', $thumb[1]);
		else
			$thumb = explode('&', $thumb[1]);

		$fn = $thumb[0];
		return $fn;
	}

	
	/**
	 * Function used update comment
	 */	
	
//	function update_comment($cid,$comment) {
//			global $db;
//			if(!$comment) {
//				e(lang('usr_cmt_err1'),e);
//			} else {
//				$db->update(tbl("comments"),array("comment"),array($comment)," comment_id = $cid");
//				e(lang("Comment Updated"),m);
//			}
//	}
	
}
?>