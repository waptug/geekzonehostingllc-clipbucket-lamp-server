<?php
/**
 *************************************************************
 | Copyright (c) 2007-2017 Clip-Bucket.com. All rights reserved.
 | @ Author	   : Arslan Hassan								
 | @ Software  : ClipBucket
 | @ Modified : { January 10th, 2017 } { Saqib Razzaq } { Updated copyright date }
 *************************************************************
*/

	define('THIS_PAGE','search_result');
	require_once 'includes/config.inc.php';
	$pages->page_redir();
							
	$page = $_GET['page'];
	$type = strtolower($_GET['type']);
	if( !$type || !in_array($type, array('videos','photos','collections','channels') ) ){
	    $type = 'videos';
    }
	$chkType = $type;
	//Checking if search for specific section is allowed or not
	if($type == 'users'){
		$chkType = 'channels';
    }
	isSectionEnabled($chkType,true);

	$userquery->perm_check('view_'.$type);

	$search = cbsearch::init_search($type);

	$search->key = mysql_clean($_GET['query']);

	if(!is_array($_GET['category'])) {
		$_GET['category'] = mysql_clean($_GET['category']);
	}

	if($type!='videos') {
		$search->category = $_GET['category'];
	} else {
		$child_ids = array();
		if($_GET['category'])
		{
			foreach($_GET['category'] as $category)
			{
				$childs = $cbvid->get_sub_categories($category);
				if($childs)
				{
					foreach($childs as $child)
					{
						$child_ids[] = $child['category_id'];
						$subchilds = $childs = $cbvid->get_sub_categories($child['category_id']);
						if($subchilds)
						{
							foreach($subchilds as $subchild) {
								$child_ids[] = $subchild['category_id'];
							}
						}
					}
					$child_ids[] = mysql_clean($category);	
				}
			}
		}
		$search->category = $child_ids;

		$search->query_conds[] = tbl('video').'.active = "yes"';
		if( !has_access('admin_access',TRUE) )
		{
			$search->query_conds[] = 'AND '.tbl('video').'.status = "Successful"';
			$search->query_conds[] = 'AND '.tbl('video').'.broadcast != "unlisted"';
		}
	}

	$search->date_margin = mysql_clean($_GET['datemargin']);
	$search->sort_by = mysql_clean($_GET['sort']);
	$search->limit = create_query_limit($page,$search->results_per_page);
	$results = $search->search();

	//Collecting Data for Pagination
	$total_rows = $search->total_results;
	$total_pages = count_pages($total_rows,$search->results_per_page);

	//Pagination
	$pages->paginate($total_pages,$page);

	assign('type', display_clean($type) );
	assign('results',array_reverse($results) );
	assign('template_var',$search->template_var);
	assign('display_template',$search->display_template);

	if(empty($search->key)) {
		assign('search_type_title',$search->search_type[$type]['title']);
	} else {
		assign('search_type_title',sprintf(lang('searching_keyword_in_obj'), display_clean(get('query')), lang($type)));
	}

	if(get('query'))
	{
		$squery = get('query');
		if ($squery == 'clipbucket') {
			subtitle("Awesomeness...!!");
		} else {
			subtitle($search->search_type[$type]['title'].' : '.display_clean(get('query')));
		}
	}

	//Displaying The Template
	template_files('search.html');
	display_it();

