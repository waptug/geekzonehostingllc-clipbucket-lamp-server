INSERT INTO `{tbl_prefix}config`(`name`, `value`) VALUES
	('enable_update_checker', '1');

ALTER TABLE `{tbl_prefix}user_profile`
	MODIFY COLUMN `user_profile_id` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `{tbl_prefix}plugins`
	MODIFY COLUMN `plugin_version` FLOAT NOT NULL DEFAULT '0';