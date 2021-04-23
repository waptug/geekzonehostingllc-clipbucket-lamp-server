<?php

class CBTemplate {
	/**
	 * Function used to set Smarty Functions
	 */
    function init() {
        global $Smarty;
        if (!isset($Smarty)) {
           $this->load_smarty();
        }
    }

    function load_smarty()
    {
        global $Smarty;
        if($this->smarty_version < 3){
            $Smarty = new Smarty;
        } else {
            $Smarty = new SmartyBC;
        }

        $Smarty->compile_check = true;
        $Smarty->debugging = false;
        $Smarty->template_dir = BASEDIR."/styles";
        $Smarty->compile_dir  = BASEDIR."/cache/views";
    }

    function create()
    {
        global $Smarty;

        if (!isset($Smarty)) {
            $this->load_smarty();
        }
        return true;
    }
    
    function setCompileDir($dir_name)
    {
        global $Smarty;
        if (!isset($Smarty)) {
            $this->create();
        }
        $Smarty->compile_dir = $dir_name;
    }

    function setType($type)
    {
        global $Smarty;
        if (!isset($Smarty)) {
            $this->create();
        }
        $Smarty->type = $type;
    }

    function assign($var, $value)
    {
        global $Smarty;
        if (!isset($Smarty)) {
            $this->create();
        }
        $Smarty->assign($var, $value);
    }

    function setTplDir($dir_name = null)
    {
        global $Smarty;
        if (!isset($Smarty)) {
            $this->create();
        }
        if (!$dir_name) {
            $Smarty->template_dir = BASEDIR."/styles/clipbucketblue";
        } else {
            $Smarty->template_dir = $dir_name;
        }
    }

    function setModule($module)
    {
        global $Smarty;
        if (!isset($Smarty)) {
            $this->create();
        }
        $Smarty->theme = $module;
        $Smarty->type  = "module";
    }

    function setTheme($theme)
    {
        global $Smarty;
        if (!isset($Smarty)) {
            $this->create();
        }
        $Smarty->template_dir = BASEDIR."/styles/" . $theme;
        $Smarty->compile_dir  = BASEDIR."/styles/" . $theme;
        $Smarty->theme        = $theme;
        $Smarty->type         = "theme";
    }

    function getTplDir()
    {
        global $Smarty;
        if (!isset($Smarty)) {
            $this->create();
        }
        return $Smarty->template_dir;
    }

     function display($filename)
     {
        global $Smarty;
        if (!isset($Smarty)) {
            $this->create();
        }
        $Smarty->display($filename);
    }

    function fetch($filename)
    {
        global $Smarty;
        if (!isset($Smarty)) {
            $this->create();
        }
        return $Smarty->fetch($filename);
    }
    
    function getVars()
    {
        global $Smarty;
        if (!isset($Smarty)) {
            $this->create();
        }
        return $Smarty->get_template_vars();
    }
	
	/**
	 * Function used to get available templates
	 */
	function get_templates()
	{
		$dir = STYLES_DIR;
		//Scaning Dir
		$dirs = scandir($dir);
		foreach($dirs as $tpl) {
			if(substr($tpl,0,1)!='.'){
				$tpl_dirs[] = $tpl;
            }
		}
		//Now Checking for template template.xml
		$tpls = array();
		foreach($tpl_dirs as $tpl_dir) {
			$tpl_details = CBTemplate::get_template_details($tpl_dir);
			
			if($tpl_details && $tpl_details['name']!=''){
				$tpls[$tpl_details['name']] = $tpl_details;
            }
		}
		
		return $tpls;
	}

	function gettemplates()
	{
		return $this->get_templates();
	}
	
	function get_template_details($temp,$file='template.xml')
	{
		$file = STYLES_DIR.'/'.$temp.'/template.xml';
		if(file_exists($file))
		{
			$content = file_get_contents($file);
			preg_match('/<name>(.*)<\/name>/',$content,$name);
			preg_match('/<author>(.*)<\/author>/',$content,$author);
			preg_match('/<version>(.*)<\/version>/',$content,$version);
			preg_match('/<released>(.*)<\/released>/',$content,$released);
			preg_match('/<description>(.*)<\/description>/',$content,$description);
			preg_match('/<website title="(.*)">(.*)<\/website>/',$content,$website_arr);

            /* For 2.7 and Smarty v3 Support */
            preg_match('/<min_version>(.*)<\/min_version>/',$content,$min_version);
            preg_match('/<smarty_version>(.*)<\/smarty_version>/',$content,$smarty_version);

            $name = isset($name[1]) ? $name[1] : false;
			$author = isset($author[1]) ? $author[1] : false;
			$version = isset($version[1]) ? $version[1] : false;
			$released = isset($released[1]) ? $released[1] : false;
			$description = isset($description[1]) ? $description[1] : false;
            $min_version = isset($min_version[1]) ? $min_version[1] : false;
            $smarty_version = isset($smarty_version[1]) ? $smarty_version[1] : false;

			$website = array('title'=>$website_arr[1],'link'=>$website_arr[2]);
			
			//Now Create array
			$template_details = array(
			    'name'=>$name,
                'author'=>$author,
                'version'=>$version,
                'released'=>$released,
                'description'=>$description,
                'website'=>$website,
                'dir'=>$temp,
                'min_version'=>$min_version,
                'smarty_version'=>$smarty_version,
                'path'=>TEMPLATEFOLDER.'/'.$temp
            );
			
			return $template_details;
		}
		return false;
	}

    /**
     * Function used to get template thumb
     *
     * @param $template
     *
     * @return string
     */
	function get_preview_thumb($template)
	{
		$path = TEMPLATEFOLDER.'/'.$template.'/images/preview.';
		$exts = array('png','jpg','gif');
		$thumb_path = '/images/icons/no_thumb_template.png';
		foreach($exts as $ext) {
			$file = BASEDIR.'/'.$path.$ext;
			if(file_exists($file)) {
				$thumb_path = '/'.$path.$ext;
				break;
			}
		}
		
		return $thumb_path;		
	}
	
	/**
	 * Function used to get any template
	 */
	function get_any_template()
	{
		$templates = $this->get_templates();
		if(is_array($templates)) {
			foreach($templates as $template) {
				if(!empty($template['name'])){
					return $template['dir'];
                }
			}
		}
        return false;
	}

    /**
     * Function used to check weather given template is ClipBucket Template or not
     * It will read Template XML file
     *
     * @param $folder
     *
     * @return array|bool
     */
	function is_template($folder)
	{
		return $this->get_template_details($folder);
	}

    /**
     * Function used to get list of template file frrom its layout and styles folder
     *
     * @param      $template
     * @param null $type
     *
     * @return array
     */
	function get_template_files($template,$type=NULL)
	{
		switch($type)
		{
			case "layout":
			default:
				$style_dir = STYLES_DIR."/$template/layout/";
				$files_patt = $style_dir."*.html";
				$files = glob($files_patt);
				/**
				 * All Files IN Layout Folder
				 */
				$new_files = array();
				foreach($files as $file) {
					$new_files[] = str_replace($style_dir,'',$file);
				}
				
				/**
				 * Now Reading Blocks Folder
				 */
				$blocks = $style_dir.'blocks/';
				$file_patt = $blocks.'*.html';
				$files = glob($file_patt);
				foreach($files as $file) {
					$new_files['blocks'][] = str_replace($blocks,'',$file);
				}
				return $new_files;

			case "theme":
				if ($template == 'cb_27'){
					$style_dir = STYLES_DIR."/$template/theme/css/";
                } else {
					$style_dir = STYLES_DIR."/$template/theme/";
                }
				$files_patt = $style_dir."*.css";
				$files = glob($files_patt);
				/**
				 * All Files IN CSS Folder
				 */
				$new_files = array();
				foreach($files as $file) {
					$new_files[] = str_replace($style_dir,'',$file);
				}

				return $new_files;
		}
	}
}
