INSERT INTO `{tbl_prefix}config`(`name`, `value`) VALUES
	('logo_name', ''),
	('favicon_name', ''),
	('comment_per_page', '10'),
	('stay_mp4', 'no'),
	('allow_conversion_1_percent', 'no'),
	('force_8bits', '1'),
	('bits_color_warning', '1'),
	('control_bar_logo', 'yes'),
	('contextual_menu_disabled', ''),
	('control_bar_logo_url', '/images/icons/player-logo.png'),
	('player_thumbnails', 'yes');

ALTER TABLE `{tbl_prefix}user_levels_permissions` MODIFY COLUMN `plugins_perms` text NOT NULL DEFAULT '';
ALTER TABLE `{tbl_prefix}users`
    MODIFY COLUMN `featured_video` mediumtext DEFAULT '' NOT NULL,
    MODIFY COLUMN `avatar_url` text DEFAULT '' NOT NULL,
    MODIFY COLUMN `featured_date` DATETIME NULL DEFAULT NULL,
	MODIFY COLUMN `total_videos` BIGINT(20) NOT NULL DEFAULT '0',
	MODIFY COLUMN `total_comments` BIGINT(20) NOT NULL DEFAULT '0',
	MODIFY COLUMN `total_photos` BIGINT(255) NOT NULL DEFAULT '0',
	MODIFY COLUMN `total_collections` BIGINT(255) NOT NULL DEFAULT '0',
	MODIFY COLUMN `comments_count` BIGINT(20) NOT NULL DEFAULT '0',
	MODIFY COLUMN `last_commented` DATETIME NULL DEFAULT NULL,
	MODIFY COLUMN `total_subscriptions` BIGINT(255) NOT NULL DEFAULT '0',
	MODIFY COLUMN `background` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
	MODIFY COLUMN `background_color` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
	MODIFY COLUMN `background_url` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
	MODIFY COLUMN `total_groups` BIGINT(20) NOT NULL DEFAULT '0',
	MODIFY COLUMN `banned_users` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
	MODIFY COLUMN `total_downloads` BIGINT(255) NOT NULL DEFAULT '0';

DELETE FROM `{tbl_prefix}config` WHERE name = 'i_magick';

ALTER TABLE `{tbl_prefix}video`
	MODIFY COLUMN `username` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
	MODIFY COLUMN `flv` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
	MODIFY COLUMN `category_parents` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
	MODIFY COLUMN `blocked_countries` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
	MODIFY COLUMN `voter_ids` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
	MODIFY COLUMN `last_commented` DATETIME NULL DEFAULT NULL,
	MODIFY COLUMN `featured_date` DATETIME NULL DEFAULT NULL,
	MODIFY COLUMN `featured_description` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
	MODIFY COLUMN `aspect_ratio` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
	MODIFY COLUMN `embed_code` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
	MODIFY COLUMN `refer_url` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
	MODIFY COLUMN `downloads` BIGINT(255) NOT NULL DEFAULT '0',
	MODIFY COLUMN `unique_embed_code` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
	MODIFY COLUMN `remote_play_url` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
	MODIFY COLUMN `video_files` TINYTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
	MODIFY COLUMN `server_ip` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
	MODIFY COLUMN `file_server_path` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
	MODIFY COLUMN `files_thumbs_path` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
	MODIFY COLUMN `file_thumbs_count` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
	MODIFY COLUMN `filegrp_size` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
	MODIFY COLUMN `extras` VARCHAR(225) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
	MODIFY COLUMN `re_conv_status` TINYTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
	MODIFY COLUMN `conv_progress` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';

ALTER TABLE `{tbl_prefix}video`
    ADD `is_castable` BOOLEAN NOT NULL DEFAULT FALSE,
    ADD `bits_color` tinyint(4) DEFAULT NULL;

ALTER TABLE `{tbl_prefix}user_profile`
	MODIFY COLUMN `fb_url` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
	MODIFY COLUMN `twitter_url` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
	MODIFY COLUMN `profile_title` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
	MODIFY COLUMN `profile_desc` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
	MODIFY COLUMN `featured_video` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
	MODIFY COLUMN `about_me` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
	MODIFY COLUMN `schools` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
	MODIFY COLUMN `occupation` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
	MODIFY COLUMN `companies` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
	MODIFY COLUMN `hobbies` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
	MODIFY COLUMN `fav_movies` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
	MODIFY COLUMN `fav_music` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
	MODIFY COLUMN `fav_books` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
	MODIFY COLUMN `background` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
	MODIFY COLUMN `profile_video` INT(255) NULL DEFAULT NULL,
	MODIFY COLUMN `profile_item` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
	MODIFY COLUMN `rating` TINYINT(2) NULL DEFAULT NULL,
	MODIFY COLUMN `voters` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
	MODIFY COLUMN `rated_by` INT(150) NULL DEFAULT NULL,
	MODIFY COLUMN `insta_url` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '';

ALTER TABLE `{tbl_prefix}photos`
	MODIFY COLUMN `views` BIGINT(255) NOT NULL DEFAULT '0',
    MODIFY COLUMN `total_comments` INT(255) NOT NULL DEFAULT '0',
    MODIFY COLUMN `last_commented` DATETIME NULL DEFAULT NULL,
    MODIFY COLUMN `total_favorites` INT(255) NOT NULL DEFAULT '0',
    MODIFY COLUMN `rating` INT(15) NOT NULL DEFAULT '0',
	MODIFY COLUMN `rated_by` INT(25) NOT NULL DEFAULT '0',
    MODIFY COLUMN `voters` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
    MODIFY COLUMN `downloaded` BIGINT(255) NOT NULL DEFAULT '0',
    MODIFY COLUMN `server_url` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
	MODIFY COLUMN `photo_details` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';

ALTER TABLE `{tbl_prefix}collections`
    MODIFY COLUMN `views` BIGINT(20) NOT NULL DEFAULT '0',
	MODIFY COLUMN `total_comments` BIGINT(20) NOT NULL DEFAULT '0',
    MODIFY COLUMN `last_commented` DATETIME NULL,
    MODIFY COLUMN `total_objects` BIGINT(20) NOT NULL DEFAULT '0',
    MODIFY COLUMN `rating` BIGINT(20) NOT NULL DEFAULT '0',
    MODIFY COLUMN `rated_by` BIGINT(20) NOT NULL DEFAULT '0',
    MODIFY COLUMN `voters` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

ALTER TABLE `{tbl_prefix}action_log`
    MODIFY COLUMN `action_success` ENUM('yes','no') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

ALTER TABLE `{tbl_prefix}comments`
    MODIFY `vote` VARCHAR(225) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
    MODIFY `voters` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
    MODIFY `spam_votes` BIGINT(20) NOT NULL DEFAULT '0',
    MODIFY `spam_voters` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';