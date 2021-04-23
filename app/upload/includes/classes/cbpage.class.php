<?php
	/**
	 * Class use to create and manage simple pages
	 * ie About us, Privacy Policy etc
	 */

	class cbpage
	{

		var $page_tbl = '';

		/**
		 * _CONTRUCTOR
		 */
		function __construct()
		{
			$this->page_tbl = 'pages';
		}

		/**
		 * Function used to create new page
		 *
		 * @param $param array
		 *
		 * @return bool
		 */
		function create_page($param)
		{
			global $db;
			$name = mysql_clean($param['page_name']);
			$title = mysql_clean($param['page_title']);
			$content = addslashes($param['page_content']);

			if(empty($name))
				e(lang("page_name_empty"));
			if(empty($title))
				e(lang("page_title_empty"));
			if(empty($content))
				e(lang("page_content_empty"));

			if(!error())
			{
				$db->insert(tbl($this->page_tbl),array("page_name","page_title","page_content","userid","date_added","active"),
												  array($name,$title,"|no_mc|".$content,userid(),now(),"yes"));
				e(lang("new_page_added_successfully"),"m");
				return false;
			}
			return false;
		}

		/**
		 * Function used to get details using id
		 *
		 * @param $id
		 *
		 * @return bool
		 */
		function get_page($id)
		{
			global $db;
			$result = $db->select(tbl($this->page_tbl),"*"," page_id ='$id' ");
			if(count($result)>0)
				return $result[0];
			return false;
		}

		/**
		 * Function used to get all pages from database
		 *
		 * @param bool $params
		 *
		 * @return array|bool
		 */
		function get_pages($params=false)
		{
			global $db;
			$order = NULL;
			$limit = NULL;
			$conds = array();
			$cond = false;
			if(isset($params['order']))
			{
				$order = $params['order'];
			}
			if(isset($params['limit']))
			{
				$limit = $params['limit'];
			}

			if(isset($params['active']))
			{
				$conds[] = " active='".$params['active']."'";
			}

			if(isset($params['display_only']))
			{
				$conds[] = " display='yes' ";
			}

			if($conds)
			{
				foreach($conds as $c)
				{
					if($cond)
						$cond .= " AND ";

					$cond .= $c;
				}
			}

			$result = $db->select(tbl($this->page_tbl),"*",$cond,$limit,$order);
			if(count($result)>0)
				return $result;
			return false;
		}

		/**
		 * Function used to edit page
		 *
		 * @param $param
		 */
		function edit_page($param)
		{
			global $db;
			$id = $param['page_id'];
			$name = mysql_clean($param['page_name']);
			$title = mysql_clean($param['page_title']);
			$content = mysql_clean($param['page_content']);

			$page = $this->get_page($id);

			if(!$page)
				e(lang("page_doesnt_exist"));
			if(empty($name))
				e(lang("page_name_empty"));
			if(empty($title))
				e(lang("page_title_empty"));
			if(empty($content))
				e(lang("page_content_empty"));

			if(!error())
			{
				$db->update(tbl($this->page_tbl),array("page_name","page_title","page_content"),
												  array($name,$title,'|no_mc|'.$content)," page_id='$id'");
				e(lang("page_updated"),"m");
			}
		}

		/**
		 * Function used to delete page
		 *
		 * @param $id
		 */
		function delete_page($id)
		{
			global $db;

			$page = $this->get_page($id);
			if(!$page)
				e(lang("page_doesnt_exist"));
			if(!error())
			{
				$db->delete(tbl($this->page_tbl),array("page_id"),array($id));
				e(lang("page_deleted"),"m");
			}
		}

		/**
		 * Function used to create page link
		 *
		 * @param $pdetails
		 *
		 * @return string
		 */
		function page_link($pdetails)
		{
			if(SEO=='yes')
				return '/page/'.$pdetails['page_id'].'/'.SEO(strtolower($pdetails['page_name']));
			return '/view_page.php?pid='.$pdetails['page_id'];
		}

		/**
		 * Function used to get page link fro id
		 *
		 * @param $id
		 *
		 * @return string
		 */
		function get_page_link($id)
		{
			$page = $this->get_page($id);
			return $this->page_link($page);
		}

		/**
		 * Function used to activate, deactivate or to delete pages
		 *
		 * @param $type
		 * @param $id
		 */
		function page_actions($type,$id)
		{
			global $db;
			$page = $this->get_page($id);
			if(!$page)
				e(lang("page_doent_exist"));
			else
			{
				switch($type)
				{
					case "activate";
					$db->update(tbl($this->page_tbl),array("active"),array("yes")," page_id='$id'");
					e(lang("page_activated"),"m");
					break;
					case "deactivate";
					$db->update(tbl($this->page_tbl),array("active"),array("no")," page_id='$id'");
					e(lang("page_deactivated"),"m");
					break;
					case "delete";
					{
						if($page['delete_able']=='yes')
						{
							$db->delete(tbl($this->page_tbl),array("page_id"),array($id));
							e(lang("page_deleted"),"m");
						}else
							e(lang("you_cant_delete_this_page"));
					}

					break;

					case "display":
					{
						$db->update(tbl($this->page_tbl),array("display"),array("yes")," page_id='$id'");
						e(lang("Page displaye mode has been changed"),"m");
					}
					break;

					case "hide":
					{
						$db->update(tbl($this->page_tbl),array("display"),array("no")," page_id='$id'");
						e(lang("Page displaye mode has been changed"),"m");
					}
					break;
				}
			}
		}

		/**
		 * function used to check weather page is active or not
		 *
		 * @param $id
		 *
		 * @return bool
		 */
		function is_active($id)
		{
			$id = mysql_clean($id);

			global $db;
			$result = $db->count(tbl($this->page_tbl),"page_id"," page_id='$id' AND active='yes' ");
			if($result>0)
				return true;
			return false;
		}

		/**
		 * Function used to update order
		 */
		function update_order()
		{
			global $db;
			$pages = $this->get_pages();
			foreach($pages as $page)
			{
				$db->update(tbl($this->page_tbl),array("page_order"),array($_POST['page_ord_'.$page['page_id']])," page_id='".$page['page_id']."'");
			}
		}
	}
