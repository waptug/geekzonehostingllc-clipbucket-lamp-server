<div class="nav_des clearfix">
    <div class="cb_container" style="color:#fff;">
	Welcome to ClipBucket <?php echo VERSION; ?> Installer, this will let you install clipbucket with few clicks, Please read <a href="http://docs.clip-bucket.com/clipbucket-installation" style="color:#fff;text-decoration:underline;">this documentation</a> for further instructions.
	Clipbucket is an open-source software under <a href="http://opensource.org/licenses/AAL" style="color:#fff;text-decoration:underline;">Attribution assurance license</a>. Its not scary, it just states that those who did all the tough job should get credit for their work in form of their brand name in the footer, you can edit, sell, use this script but you must keep author's and product name on your website until and unless you purchase our <a href="http://clip-bucket.com/product/ClipBucket-Branding-Removals/85" style="color:#fff;">branding removal</a> license.
	</div>
</div>

<div id="sub_container">
	<h4 class="grey-text">License</h4>
	<div class="cb-instal-licenc-holder">
		<div class="cb-instal-licenc-sec">
			<?php echo get_cbla(); ?>
		</div>
	</div>

	<form method="post" id="installation">
		<input type="hidden" name="mode" value="precheck"/>
    	<div style="padding:10px 0;text-align:right;"><?php button ('Ok, I Agree, Now let me Continue!',' onclick="$(\'#installation\').submit()" '); ?></div>
	</form>
</div>
