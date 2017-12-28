<?php
class edition_contents
{
	public function index(){
		$edition_contents =$GLOBALS['db']->getAll("select title,description,img from ".DB_PREFIX."app_show where is_effect =1 order by sort asc");
		$root['edition_contents'] = $edition_contents;
		$root['program_title'] = "版本内容";
		output($root);		
	}
}
?>