<?php
/*
Plugin Name: Character counter
Description: This plugin will count number of characters  typed in a textfield and also displays number of characters allowed or left
Author: Arslan Hassan
Author Website: http://clip-bucket.com/
ClipBucket Version: 2
Version: 1.0
Website: http://clip-bucket.com/
Plugin Type: global
*/

add_js(array('jquery_plugs/counter.min.js'=>'global'));		

function character_counter($type)
{
	
	switch($type)
	{
		case "comment":
		default:
		{
			echo '<script  type="text/javascript">';
			echo '$("#comment_box").counter({goal: '.MAX_COMMENT_CHR.'});';
			echo '</script>';
		}
		break;
	}
}

function character_counter_comment(){ return character_counter('comment'); }

register_anchor_function('character_counter_comment','after_compose_box');
