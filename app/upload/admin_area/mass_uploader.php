<?php
	/*
	 **************************************************************
	 | Copyright (c) 2007-2010 Clip-Bucket.com. All rights reserved.
	 | @ Author : ArslanHassan
	 | @ Software : ClipBucket , © PHPBucket.com
	 **************************************************************
	*/

	require_once '../includes/admin_config.php';
	require_once(dirname(dirname(__FILE__))."/includes/classes/sLog.php");
	$userquery->admin_login_check();
	$pages->page_redir();
	global $Cbucket;

	$delMassUpload = config('delete_mass_upload');

	/* Generating breadcrumb */
	global $breadcrumb;
	$breadcrumb[0] = array('title' => lang('videos'), 'url' => '');
	$breadcrumb[1] = array('title' => 'Mass Upload Videos', 'url' => ADMIN_BASEURL.'/mass_uploader.php');

	global $cbvid;
	$cats = $cbvid->get_categories();
	$total_cats = count($cats);
	$category_names = array();
	for ($i=0; $i < $total_cats ; $i++) {
		$category_values = $cats[$i]['category_id'];
		$category_names[$category_values] = $cats[$i]['category_name'];
	}

	assign("cats", $cats);
	assign("cat_values", $category_values);
	assign("total_cats", $total_cats);

	if(isset($_POST['mass_upload_video']))
	{
		$files  = $cbmass->get_video_files_list_clear();
		$vtitle = $_POST['title'];

		foreach($files as $file)
		{
			$hash = hash('sha512', $file['path'].$file['file']);
			if( !isset($_POST[$hash]) )
				continue;

			$file_key = time().RandomString(5);
			$file_arr = $file;
			$file_path = $file['path'];
			$file_orgname = $file['file'];

			$file_title = $_POST[$hash.'_title'];
			$file_description = $_POST[$hash.'_description'];
			$file_tags = $_POST[$hash.'_tags'];
			$file_categories = $_POST[$hash.'_cat'];

			$file_track = '';
			if( isset($_POST[$hash.'_track']) ){
				$file_track = $_POST[$hash.'_track'];
            }

			$code = $i+1;
			//Inserting Video Data...

			if (gotPlugin('cb_multiserver.php'))
			{
				// multiserver is installed
				$uploadPath = $Cbucket->theUploaderDetails['uploadScriptPath'];
				$fullFilePath = $file_arr['path'].$file_arr['file'];
				//Initialise the cURL var
				$ch = curl_init();

				//Get the response from cURL
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

				//Set the Url
				curl_setopt($ch, CURLOPT_URL, $uploadPath);

				//Create a POST array with the file in it
				$postData = array('Filedata' => '@'.$fullFilePath);

				curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

				// Execute the request
				$response = curl_exec($ch);
				if ($response)
				{
					$cleaned = json_decode($response,true);
					$file_name = $cleaned['file_name'];
					if (!empty($file_name))
					{
						$vCategory = $file_categories;
						if (empty($vCategory)) {
							$vCategory = "#1#";
						}
						$array = array(
							'title' => $file_title,
							'description' => $file_description,
							'tags' => $file_tags,
							'category' => array($cbvid->get_default_cid()),
							'file_name' => $file_name,
							'file_directory' => $file_path
						);

						$vid = $Upload->submit_upload($array);
						if ($vid) {
							goto crapCleanStep;
						}
					} else {
						e("No filename returned from server");
					}
				} else {
					e("Error moving file : ".curl_error($ch));
				}
				exit("FAILED");
			}

			$file_directory = createDataFolders();
			$array = array(
				'title' => $file_title,
				'description' => $file_description,
				'tags' => $file_tags,
				'category' => $file_categories,
				'file_name' => $file_key,
				'file_directory' => $file_directory,
			);

			$vid = $Upload->submit_upload($array);

			if( error() )
			{
				e('Unable to upload "'.$file_arr['title'].'"', 'e');
			} else {
				e('"'.$file_arr['title'].'" has been uploaded successfully','m');
			}

			if($vid)
			{
				//Moving file to temp dir and Inserting in conversion queue..
				$file_name = $cbmass->move_to_temp($file_arr,$file_key);

				createDataFolders(LOGS_DIR);
				$logFile = LOGS_DIR.'/'.$file_directory.'/'.$file_key.'.log';
				$log = new SLog($logFile);

				$log->newSection("Pre-Check Configurations");
				$log->writeLine("File to be converted", 'Initializing File <strong>'.$file_name.'</strong> and pre checking configurations...', true);

				if( DEVELOPMENT_MODE )
				{
					$hardware = shell_exec( 'lshw -short 2>&1' );
					if( $hardware ) {
						$log->writeLine( "System hardware Information", $hardware, true );
					} else {
						$log->writeLine( 'System hardware Information', 'Unable log System hardware information, please install "lshw" ', true );
					}
				}

				$results=$Upload->add_conversion_queue($file_name);
				$str = "/".date("Y")."/".date("m")."/".date("d")."/";
				$str1 = date("Y")."/".date("m")."/".date("d");
				mkdir(FILES_DIR.'/videos'.$str);
				$tbl=tbl("video");
				$fields['file_directory']=$str1;
				$fname=explode('.', $file_name);
				$cond='file_name='.'\''.$fname[0].'\'';
				$result=$db->db_update($tbl, $fields, $cond);
				$result=exec(php_path()." -q ".BASEDIR."/actions/video_convert.php {$file_name} {$file_key} {$file_directory} {$logFile} {$file_track} > /dev/null &");
				if(file_exists(CON_DIR.'/'.$file_name))
				{
					unlink(CON_DIR.'/'.$file_name);
					foreach ($vtitle as &$title)
					{
						$resul1 = glob(FILES_DIR.'/videos/'.$title.".*");
						unlink($resul1[0]);
					}
				}

				crapCleanStep:
				if ($delMassUpload != 'no')
				{
					if( is_writable($file_path.$file_orgname) )
					{
						$unlink = unlink($file_path.$file_orgname);
						if( !$unlink )
							e('Can\'t delete file "'.$file_path.$file_orgname.'"', 'w');
					} else {
						e('File "'.$file_path.$file_orgname.'" is not writable', 'w');
					}
				}
			}
		}
	}

	if(count($error_lists)>0)
	{
		foreach($error_lists as $e)
			e($e);
	}

	subtitle("Mass Uploader");
	template_files("mass_uploader.html");
	display_it();
