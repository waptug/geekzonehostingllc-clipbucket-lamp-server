<?php
require_once('../includes/common.php');

function install_embed_video_mode()
{
	global $db;
	$db->Execute("ALTER TABLE `".tbl('video')."` ADD `embed_code` TEXT NOT NULL ");
}

install_embed_video_mode();
