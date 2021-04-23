<?php
/*
    Plugin Name: Global announcement
    Description: This will let you post a global announcement on your website
    Author: Arslan Hassan & MacWarrior
    ClipBucket Version: CB5.4
    Website: https://github.com/MacWarrior/clipbucket-v5/
*/

function get_global_announcement()
{
    global $db;
    $results = $db->select(tbl('global_announcement'),'*');
    $ann = $results[0]['announcement'];
    if (!$ann ==''){
        echo '<div class="alert alert-info margin-bottom-10 ">'.$ann.'</div>';
    }
}

function update_announcement($text)
{
    global $db;
    $textCheck = str_replace(array('<p>','</p>','<br>'), '', $text);
    if (strlen($textCheck) < 1) {
        $text = '';
    }
    $db->Execute("UPDATE ".tbl("global_announcement")." SET announcement='$text'");
}

register_anchor_function('get_global_announcement','global');

add_admin_menu('Plugin Manager','Announcement',PLUG_URL.'/global_announcement/edit_announcement.php');
