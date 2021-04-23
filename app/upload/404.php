<?php
define("THIS_PAGE",'404');
require 'includes/config.inc.php';

if(file_exists(LAYOUT."/404.html")) {
    template_files('404.html');
    $videos = $cbvid->get_videos(array("order"=>"date_added DESC","limit"=>4));
    assign('videos',$videos);
} else {
    $data = "404_error";
    if(has_access('admin_access')) {
        e(sprintf(lang("err_warning"),"404","http://docs.clip-bucket.com/?p=154"),"w");
    }
    e(lang($data));
}

display_it();
