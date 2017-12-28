<?php

class spreadModule extends SiteBaseModule
{
    //收益计算器
	public function calculator(){
		//添加渠道来源
		$source_id = es_session::get("source_id");
		$device    = es_session::get("device");
		if($source_id!= ''&&$device!= ''){
            if(!empty($source_id)){
			$source_id = $source_id."_";
			}
		} 
		$GLOBALS['tmpl']->assign('source_id',$source_id);
	    $GLOBALS['tmpl']->display("page/spread/calculator.html");
	}
	//关于我们
	public function end(){
	    $GLOBALS['tmpl']->display("page/spread/end.html");
	}
	//注册就送体验金
	public function tasteRegister(){
	    $user_id = $GLOBALS['user_info']['id'];
		$source_id = es_session::get("source_id");
		$device    = es_session::get("device");
		$pid = strim($_REQUEST['pid']);
		if(!empty($source_id)){
			es_session::set("add_time",TIME_UTC);
		}
		$GLOBALS['tmpl']->assign('pid',$pid);
        $GLOBALS['tmpl']->assign('uid',$user_id);
	    $GLOBALS['tmpl']->display("page/spread/tasteRegister.html");
	}
	//注册就送体验金1
	public function tasteRegister1(){
		$source_id = es_session::get("source_id");
		$device    = es_session::get("device");
		$pid = strim($_REQUEST['pid']);
		if(!empty($source_id)){
			es_session::set("add_time",TIME_UTC);
		}
		$GLOBALS['tmpl']->assign('pid',$pid);
	    $GLOBALS['tmpl']->display("page/spread/tasteRegister1.html");
	}
	//注册就送体验金2
	public function tasteRegister2(){
		$source_id = es_session::get("source_id");
		$device    = es_session::get("device");
		$pid = strim($_REQUEST['pid']);
		if(!empty($source_id)){
			es_session::set("add_time",TIME_UTC);
		}
		$GLOBALS['tmpl']->assign('pid',$pid);
	    $GLOBALS['tmpl']->display("page/spread/tasteRegister2.html");
	}
	//注册就送体验金3
	public function tasteRegister3(){
	    $user_id = $GLOBALS['user_info']['id'];
		$source_id = es_session::get("source_id");
		$device    = es_session::get("device");
		$pid = strim($_REQUEST['pid']);
		if(!empty($source_id)){
			es_session::set("add_time",TIME_UTC);
		}
		$GLOBALS['tmpl']->assign('pid',$pid);
	    $GLOBALS['tmpl']->assign('uid',$user_id);
	    $GLOBALS['tmpl']->display("page/spread/tasteRegister3.html");
	}
	//注册就送体验金4
	public function tasteRegister4(){
	    $user_id = $GLOBALS['user_info']['id'];
		$source_id = es_session::get("source_id");
		$device    = es_session::get("device");
		$pid = strim($_REQUEST['pid']);
		if(!empty($source_id)){
			es_session::set("add_time",TIME_UTC);
		}
		$GLOBALS['tmpl']->assign('pid',$pid);
	    $GLOBALS['tmpl']->assign('uid',$user_id);
	    $GLOBALS['tmpl']->display("page/spread/tasteRegister4.html");
	}
	//二维码下载a
	public function dlShowa(){

		//添加渠道来源
		$source_id = es_session::get("source_id");
		$device    = es_session::get("device");
		if(!empty($source_id)){
			$source_id = $source_id."_";
		}
		$GLOBALS['tmpl']->assign('source_id',$source_id);
	    $GLOBALS['tmpl']->display("page/spread/dlShowa.html");
	}
	//二维码下载b
	public function dlShowb(){

		 //添加渠道来源
		$source_id = es_session::get("source_id");
		$device    = es_session::get("device");
		if(!empty($source_id)){
			$source_id = $source_id."_";
		}
		$GLOBALS['tmpl']->assign('source_id',$source_id);
	    $GLOBALS['tmpl']->display("page/spread/dlShowb.html");
	}
	//二维码下载c
	public function dlShowc(){

		 //添加渠道来源
		$source_id = es_session::get("source_id"); 
		if(!empty($source_id)){
			$source_id = $source_id."_";
			
		}
		$GLOBALS['tmpl']->assign('source_id',$source_id);
	    $GLOBALS['tmpl']->display("page/spread/dlShowc.html");
	}
	//二维码下载d
	public function dlShowd(){
		 //添加渠道来源
		$source_id = es_session::get("source_id");
		$device    = es_session::get("device");
		if(!empty($source_id)){
			$source_id = $source_id."_";
		}
		$GLOBALS['tmpl']->assign('source_id',$source_id);
	    $GLOBALS['tmpl']->display("page/spread/dlShowd.html");
	}
	//二维码下载e
	public function dlShowe(){

		 //添加渠道来源
		$source_id = es_session::get("source_id");
		$device    = es_session::get("device");
		if(!empty($source_id)){
			$source_id = $source_id."_";
		}
		$GLOBALS['tmpl']->assign('source_id',$source_id);
	    $GLOBALS['tmpl']->display("page/spread/dlShowe.html");
	}
	//二维码下载f
	public function dlShowf(){
		 //添加渠道来源
		$source_id = es_session::get("source_id");
		$device    = es_session::get("device");
		if(!empty($source_id)){
			$source_id = $source_id."_";
		}
		$GLOBALS['tmpl']->assign('source_id',$source_id);
	    $GLOBALS['tmpl']->display("page/spread/dlShowf.html");
	}
	//二维码下载g
	public function dlShowg(){
		 //添加渠道来源
		$source_id = es_session::get("source_id");
		$device    = es_session::get("device");
		if(!empty($source_id)){
			$source_id = $source_id."_";
		}
		$GLOBALS['tmpl']->assign('source_id',$source_id);
	    $GLOBALS['tmpl']->display("page/spread/dlShowg.html");
	}
}
?>
