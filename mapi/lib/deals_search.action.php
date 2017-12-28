<?php

class deals_search
{
	function index(){
		$root = get_baseroot();
		$root['response_code'] = 1;
		$root['program_title'] = "出借搜索";
		
		$level_list = load_auto_cache("level");
		$root["level_list"] = $level_list['list'];
		output($root);		
	}
}
?>
