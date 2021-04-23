<?php
define("THIS_PAGE","cb_install");
include('../includes/clipbucket.php');

/**
* ClipBucket v2.1 Installation Ajax
*/
$mode = $_POST['mode'];

if($mode!='finish_upgrade'){
    include("functions.php");
}
include("upgradeable.php");

if($mode=='dataimport')
{
	$result = array();

	$dbhost = $_POST['dbhost'];
	$dbpass = $_POST['dbpass'];
	$dbuser = $_POST['dbuser'];
	$dbname = $_POST['dbname'];

	$cnnct = @mysqli_connect($dbhost,$dbuser,$dbpass);

	if(!$cnnct)
		$result['err'] = "<span class='alert'>Unable to connect to mysql : ".mysqli_connect_error().'</span>';
	else {
		$dbselect = @mysqli_select_db($cnnct, $dbname);
		if(!$dbselect)
			$result['err'] = "<span class='alert'>Unable to select database : ".mysqli_connect_error().'</span>';
	}
	echo json_encode($result);
}

if($mode=='adminsettings')
{
	$dbhost = $_POST['dbhost'];
	$dbpass = $_POST['dbpass'];
	$dbuser = $_POST['dbuser'];
	$dbname = $_POST['dbname'];
	$dbprefix = $_POST['dbprefix'];

	$cnnct = @mysqli_connect($dbhost,$dbuser,$dbpass);

	if(!$cnnct)
		$result['err'] = "<span class='alert'>Unable to connect to mysql : ".mysqli_connect_error().'</span>';
	else {
		$dbselect = @mysqli_select_db($cnnct, $dbname);
		if(!$dbselect){
			$result['err'] = "<span class='alert'>Unable to select database : ".mysqli_error($cnnct).'</span>';
		}
	}

	if(@$result['err']) {
		exit(json_encode($result));
	}

	$step = $_POST['step'];
	$files = array(
		'structure' 		=> 'structure.sql',
		'configs'			=> 'configs.sql',
		'ads_placements'	=> 'ads_placements.sql',
		'countries'			=> 'countries.sql',
		'email_templates'	=> 'email_templates.sql',
		'pages'				=> 'pages.sql',
		'user_levels'		=> 'user_levels.sql'
	);

	$next = false;
	if(array_key_exists($step,$files) && $step)
	{
		$total = count($files);
		$count = 0;
		foreach($files as $key => $file)
		{
			$count++;
			if($next) {
				$next = $key;
				break;
			}
			if($key==$step) {
				$current = $step;
				if($count<$total)
					$next = true;
			}
		}

		if(!$next) {
			$next = 'add_categories';
			$next_msg = 'Creating categories';
		}

		if($current) {
			$lines = file(BASEDIR."/cb_install/sql/".$files[$current]);
			foreach ($lines as $line_num => $line)
			{
				if (substr($line, 0, 2) != '--' && $line != '')
				{
					@$templine .= $line;
					if (substr(trim($line), -1, 1) == ';')
					{
						@$templine = preg_replace("/{tbl_prefix}/",$dbprefix,$templine);
						mysqli_query($cnnct, $templine);

						if( $cnnct->error != '' ) {
							$result['err'] = "<span class='alert'>An SQL error occures : ".$cnnct->error.'</span>';
							error_log('SQL : ' . $templine);
							error_log('ERROR : ' . $cnnct->error );
							exit(json_encode($result));
						}

						$templine = '';
					}
				}
			}
		}

		$return = array();
		$return['msg'] = '<div class="ok green">'.$files[$current].' has been imported successfully</div>';

		if(@$files[$next]) {
			$return['status'] = 'importing '.$files[$next];
		} else {
			$return['status'] = $next_msg;
		}

		$return['step'] = $next;

		if( $step == 'configs' ) {
			$sql = 'UPDATE '.$dbprefix.'config SET value = "'.$cnnct->real_escape_string(exec("which php")).'" WHERE name = "php_path"';
			mysqli_query($cnnct, $sql);
			$sql = 'UPDATE '.$dbprefix.'config SET value = "'.$cnnct->real_escape_string(exec("which ffmpeg")).'" WHERE name = "ffmpegpath"';
			mysqli_query($cnnct, $sql);
			$sql = 'UPDATE '.$dbprefix.'config SET value = "'.$cnnct->real_escape_string(exec("which ffprobe")).'" WHERE name = "ffprobe_path"';
			mysqli_query($cnnct, $sql);
			$sql = 'UPDATE '.$dbprefix.'config SET value = "'.$cnnct->real_escape_string(exec("which MP4Box")).'" WHERE name = "mp4boxpath"';
			mysqli_query($cnnct, $sql);
			$sql = 'UPDATE '.$dbprefix.'config SET value = "'.$cnnct->real_escape_string(exec("which mediainfo")).'" WHERE name = "media_info"';
			mysqli_query($cnnct, $sql);
		}
	} else {
		switch($step)
		{
			case 'add_categories':
				$lines = file(BASEDIR."/cb_install/sql/categories.sql");
				foreach ($lines as $line_num => $line)
				{
					if (substr($line, 0, 2) != '--' && $line != '')
					{
						@$templine .= $line;
						if (substr(trim($line), -1, 1) == ';')
						{
							@$templine = preg_replace("/{tbl_prefix}/",$dbprefix,$templine);
							mysqli_query($cnnct, $templine);

							if( $cnnct->error != '' ) {
								$result['err'] = "<span class='alert'>An SQL error occures : ".$cnnct->error.'</span>';
								error_log('SQL : ' . $templine);
								error_log('ERROR : ' . $cnnct->error );
								exit(json_encode($result));
							}

							$templine = '';
						}
					}
				}
				$return['msg'] = '<div class="ok green">Videos, Users, Groups and Collections Categories have been created</div>';
				$return['status'] = 'adding admin account..';
				$return['step'] = 'add_admin';
				break;

			case "add_admin":
				$lines = file(BASEDIR."/cb_install/sql/add_admin.sql");
				foreach ($lines as $line_num => $line)
				{
					if (substr($line, 0, 2) != '--' && $line != '')
					{
						@$templine .= $line;
						if (substr(trim($line), -1, 1) == ';')
						{
							@$templine = preg_replace("/{tbl_prefix}/",$dbprefix,$templine);
							mysqli_query($cnnct, $templine);

							if( $cnnct->error != '' ) {
								$result['err'] = "<span class='alert'>An SQL error occures : ".$cnnct->error.'</span>';
								error_log('SQL : ' . $templine);
								error_log('ERROR : ' . $cnnct->error );
								exit(json_encode($result));
							}

							$templine = '';
						}
					}
				}
				$return['msg'] = '<div class="ok green">Admin account has been created</div>';
				$return['status'] = 'Creating config files...';
				$return['step'] = 'create_files';
				break;

			case "create_files":
				mysqli_close($cnnct);
				$dbconnect = file_get_contents(BASEDIR.'/cb_install/config.php');
				$dbconnect = str_replace('_DB_HOST_', $dbhost, $dbconnect);
				$dbconnect = str_replace('_DB_NAME_', $dbname, $dbconnect);
				$dbconnect = str_replace('_DB_USER_', $dbuser, $dbconnect);
				$dbconnect = str_replace('_DB_PASS_', $dbpass, $dbconnect);
				$dbconnect = str_replace('_TABLE_PREFIX_', $dbprefix, $dbconnect);

				$fp = fopen(BASEDIR.'/includes/config.php', 'w');
				fwrite($fp, $dbconnect);
				fclose($fp);

				$return['msg'] = '<div class="ok green">Config file have been created</div>';
				$return['status'] = 'forwarding you to admin settings..';
				$return['step'] = 'forward';
				break;
		}
	}
	echo json_encode($return);
}

if($mode=='finish_upgrade')
{
	chdir("..");
	require_once 'includes/config.inc.php';
	chdir("cb_install");
	include("functions.php");
	$files = getUpgradeFiles();

	if($files)
	{
		$step = $_POST['step'];
		if($step=='upgrade')
			$index = 0;
		else
			$index = $step;

		$total = count($files);

		if($index >= $total)
		{
			$return['msg'] = '<div class="ok green">Upgrade clipbucket</div>';
			$return['status'] = 'finalizing upgrade...';
			$return['step'] = 'forward';
		}

		if($index+1 >= $total)
			$next = 'forward';
		else
			$next = $index+1;

		if($next=='forward')
			$status = 'finalizing upgrade...';
		else
			$status = 'Importing upgrade_'.$files[$next].'.sql';

		$sqlfile = BASEDIR."/cb_install/sql/upgrade_".$files[$index].".sql";
		if(file_exists($sqlfile))
		{
			$lines = file($sqlfile);
			foreach ($lines as $line_num => $line)
			{
				if (substr($line, 0, 2) != '--' && $line != '')
				{
					@$templine .= $line;
					if (substr(trim($line), -1, 1) == ';')
					{
						@$templine = preg_replace("/{tbl_prefix}/",TABLE_PREFIX,$templine);
						$db->execute($templine);
						$templine = '';
					}
				}
			}
		}

		//There were problems with email templates with version lower than 2.4
		//therefore we are dumping all existing email templates and re-import them
		if($upgrade<'2.4.5')
		{
			mysqli_query($cnnct,'TRUNCATE '.TABLE_PREFIX.'email_templates');
			//Dumping
			$sqlfile = BASEDIR."/cb_install/sql/email_templates.sql";
			if(file_exists($sqlfile))
			{
				$lines = file($sqlfile);
				foreach ($lines as $line_num => $line)
				{
					if (substr($line, 0, 2) != '--' && $line != '')
					{
						@$templine .= $line;
						if (substr(trim($line), -1, 1) == ';')
						{
							@$templine = preg_replace("/{tbl_prefix}/",TABLE_PREFIX,$templine);
							$db->execute($templine);
							$templine = '';
						}
					}
				}
			}
		}
		//Dumping finished

		$return['msg'] = '<div class="ok green">upgrade_'.$files[$index].'.sql has been imported</div>';
		$return['status'] = $status;
		$return['step'] = $next;
	} else {
		$return['msg'] = '<div class="ok green">Upgrade clipbucket</div>';
		$return['status'] = 'finalizing upgrade...';
		$return['step'] = 'forward';
	}

	echo json_encode($return);
}
