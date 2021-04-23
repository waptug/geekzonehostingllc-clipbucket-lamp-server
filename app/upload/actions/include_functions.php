<?php
	function RandomString($length)
	{
		// Generate random 32 character string
		$string = md5(microtime());

		// Position Limiting
		$highest_startpoint = 32-$length;

		// Take a random starting point in the randomly
		// Generated String, not going any higher then $highest_startpoint
		$randomString = substr($string,rand(0,$highest_startpoint),$length);

		return $randomString;
	}

	function formatfilesize($bytes, $decimals = 2)
	{
        $units = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
        $factor = (int)floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . $units[$factor];
    }

    /**
     * Function used to get file name
     *
     * @param $file
     *
     * @return bool|false|string
     */
	function GetName($file)
	{
		if(!is_string($file))
			return false;
		$path = explode('/',$file);
		if(is_array($path))
			$file = $path[count($path)-1];
		$new_name 	 = substr($file, 0, strrpos($file, '.'));
		return $new_name;
	}

	//Function Used TO Get Extension of File
	function GetExt($file){
		return substr($file, strrpos($file,'.') + 1);
	}

	function old_set_time($temps)
	{
		round($temps);
		$heures = floor($temps / 3600);
		$minutes = round(floor(($temps - ($heures * 3600)) / 60));
		if ($minutes < 10)
				$minutes = "0" . round($minutes);
		$secondes = round($temps - ($heures * 3600) - ($minutes * 60));
		if ($secondes < 10)
				$secondes = "0" . round($secondes);
		return $minutes . ':' . $secondes;
	}

	function SetTime($sec, $padHours = true)
	{
		if($sec < 3600)
			return old_set_time($sec);
			
		$hms = "";
	
		// there are 3600 seconds in an hour, so if we
		// divide total seconds by 3600 and throw away
		// the remainder, we've got the number of hours
		$hours = intval(intval($sec) / 3600);
	
		// add to $hms, with a leading 0 if asked for
		$hms .= ($padHours)
			  ? str_pad($hours, 2, "0", STR_PAD_LEFT). ':'
			  : $hours. ':';
	
		// dividing the total seconds by 60 will give us
		// the number of minutes, but we're interested in
		// minutes past the hour: to get that, we need to
		// divide by 60 again and keep the remainder
		$minutes = intval(($sec / 60) % 60);
	
		// then add to $hms (with a leading 0 if needed)
		$hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT). ':';
	
		// seconds are simple - just divide the total
		// seconds by 60 and keep the remainder
		$seconds = intval($sec % 60);
	
		// add to $hms, again with a leading 0 if needed
		$hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);
	
		return $hms;
	}
