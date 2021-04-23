<?php
	// This script runs only via command line
	sleep(5);
	include(dirname(__FILE__)."/../includes/config.inc.php");
	require_once(dirname(dirname(__FILE__))."/includes/classes/sLog.php");
	define("MP4Box_BINARY",get_binaries('MP4Box'));
	define('FFMPEG_BINARY', get_binaries('ffmpeg'));

	/*
		getting the arguments
		$argv[1] => first argument, in our case its the path of the file
	*/
	if (config('use_crons') == 'yes') {
		$argv = convertWithCron();
	}

	logData(json_encode($argv),"argvs");
	$fileName = (isset($argv[1])) ? $argv[1] : false;
	//This is exact file name of a video e.g 132456789
	$_filename = (isset($argv[2])) ? $argv[2] : false;

	$file_directory_ = (isset($argv[3])) ? $argv[3] : false;
	$file_directory = $file_directory_.'/';

	$logFile = (isset($argv[4])) ? $argv[4] : false;
	logData($logFile,'argvs');
	if (empty($logFile)) {
		$logFile = LOGS_DIR.'/'.$file_directory.$_filename.'.log';
	}

	// TODO : Support multi audio tracks ; $audio_track =  false;
	$audio_track = (isset($argv[5])) ? $argv[5] : false;
	$reconvert = (isset($argv[6])) ? $argv[6] : false;

	$file = FILES_DIR.'/temp/args.txt';
	$text = "fileName [".$fileName.'] _filename ['.$_filename.'] file_directory ['.$file_directory.'] logfile ['.$logFile.']';
	file_put_contents($file, $text);

	$log = new SLog($logFile);
	
	$log->newSection("Starting Conversion Log");
	$TempLogData = "Filename : {$fileName}\n";
	$TempLogData .= "File directory : {$file_directory_}\n";
	$TempLogData .= "Log file : {$logFile}\n";
	$log->writeLine("Getting Arguments",$TempLogData, true, true);

	/*
		Getting the videos which are currently in our queue
		waiting for conversion
	*/

	if(isset($_GET['test'])) {
		$queue_details = get_queued_video(false, $fileName);
	} else {
		$queue_details = get_queued_video(true, $fileName);
	}

	$log->writeLine("Conversion queue","Getting the file information from the queue for conversion", true);

	if(!$file_directory_){
		$fileDir = $queue_details["date_added"];
	} else {
		$fileDir = $file_directory;
	}
	$dateAdded = explode(" ", $fileDir);
	$dateAdded = array_shift($dateAdded);
	$file_directory = implode("/", explode("-", $dateAdded));

	/*
		Getting the file information from the queue for conversion
	*/
	$tmp_file = $queue_details['cqueue_name'];
	$tmp_ext  = $queue_details['cqueue_tmp_ext'];
	$ext 	  = $queue_details['cqueue_ext'];
	$outputFileName = $tmp_file;
	if(!empty($tmp_file))
	{
		$temp_file = TEMP_DIR.'/'.$tmp_file.'.'.$tmp_ext;
		$orig_file = CON_DIR.'/'.$tmp_file.'.'.$ext;

		/*
			Delete the uploaded file from temp directory
			and move it into the conversion queue directory for conversion
		*/

		if(isset($_GET['test'])){
			$renamed = copy( $temp_file, $orig_file );
		} else {
			$renamed = rename( $temp_file, $orig_file );
		}

		if ($renamed){
			$log->writeLine("Conversion queue","File has been moved from temporary dir to Conversion Queue", true);
		} else {
			$log->writeLine("Conversion queue","Something went wrong in moving the file to Conversion Queue", true);
		}

		/*
			Preparing the configurations for video conversion from database
		*/
		logData('Preparing configuration to parse in ffmpeg class','checkpoints');

		$configs = array(
			'use_video_rate' 	 => true,
			'use_video_bit_rate' => true,
			'use_audio_rate' 	 => true,
			'use_audio_bit_rate' => true,
			'use_audio_codec'    => true,
			'use_video_codec' 	 => true,
			'format' 			 => 'mp4',
			'video_codec'		 => config('video_codec'),
			'audio_codec'		 => config('audio_codec'),
			'audio_rate'		 => config("srate"),
			'audio_bitrate'		 => config("sbrate"),
			'video_rate'		 => config("vrate"),
			'video_bitrate'		 => config("vbrate"),
			'video_bitrate_hd'	 => config("vbrate_hd"),
			'normal_res' 		 => config('normal_resolution'),
			'high_res' 			 => config('high_resolution'),
			'max_video_duration' => config('max_video_duration'),
			'resize'			 => 'max',
			'outputPath' 		 => $fileDir,
			'cb_combo_res' 		 => config('cb_combo_res'),
			'gen_240' 			 => config('gen_240'),
			'gen_360' 			 => config('gen_360'),
			'gen_480' 			 => config('gen_480'),
			'gen_720' 			 => config('gen_720'),
			'gen_1080' 			 => config('gen_1080'),
			'chromecast_fix' 	 => config('chromecast_fix'),
			'force_8bits' 	     => config('force_8bits')
		);

		foreach ($configs as $key => $value){
			$configLog .= "<strong>{$key}</strong> : {$value}\n";
		}

		$log->writeLine("Parsing FFmpeg Configurations",$configLog, true);

		logData('Inlcuding FFmpeg Class','checkpoints');
		require_once(BASEDIR.'/includes/classes/conversion/ffmpeg.class.php');

		$ffmpeg = new FFMpeg($configs, $log);
		$ffmpeg->ffmpeg($orig_file);
		$ffmpeg->file_name = $tmp_file;
		$ffmpeg->raw_path = VIDEOS_DIR.'/'.$file_directory.$_filename;

		if( $audio_track && is_numeric($audio_track) ){
			$ffmpeg->audio_track = $audio_track;
		}

		if( $reconvert ){
			$ffmpeg->reconvert = true;
		}

		$ffmpeg->ClipBucket();
		if ($ffmpeg->lock_file && file_exists($ffmpeg->lock_file)){
			unlink($ffmpeg->lock_file);
		}
		logData($ffmpeg->video_files,'video_files');

		$video_files = json_encode($ffmpeg->video_files);
		$db->update(tbl('video'), array("video_files"), array($video_files), " file_name = '{$outputFileName}'");

		if( config('chromecast_fix') ){
			$db->update(tbl('video'), array("is_castable"), array(true), " file_name = '{$outputFileName}'");
		}

        $vidDetails = $ffmpeg->get_file_info();
		if( !config('force_8bits') || $vidDetails['bits_per_raw_sample'] <= 8 ){
            $db->update(tbl('video'), array("bits_color"), array($vidDetails['bits_per_raw_sample']), " file_name = '{$outputFileName}'");
        } else if( config('force_8bits') && $vidDetails['bits_per_raw_sample'] > 8 ){
            $db->update(tbl('video'), array("bits_color"), array(8), " file_name = '{$outputFileName}'");
        }

		if( $reconvert ) {
			setVideoStatus( $outputFileName, 'completed', true, true );
		}

		if (stristr(PHP_OS, 'WIN'))
		{
			exec(php_path()." -q ".BASEDIR."/actions/verify_converted_videos.php $orig_file");
		} elseif(stristr(PHP_OS, 'darwin')) {
			exec(php_path()." -q ".BASEDIR."/actions/verify_converted_videos.php $orig_file </dev/null >/dev/null &");
		} else {
			exec(php_path()." -q ".BASEDIR."/actions/verify_converted_videos.php $orig_file &> /dev/null &");
		}

		if(!isset($_GET['test'])){
			unlink($orig_file);
		}
	}
