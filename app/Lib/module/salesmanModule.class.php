<?php 
class salesmanModule extends SiteBaseModule{
	public function index(){
		$login_info = es_session::get("user_info");
		if(!$login_info)
		{
			app_redirect(url("index")."?ctl=user&act=salesman_login");		
		}
		$user_info = $GLOBALS['user_info'];
		$strtotime = strtotime(date("Y-m-1"));
		$strend = strtotime("+1 month",$strtotime);
		$user_list = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."user where pid=".$user_info['id']." and create_time >=".$strtotime." and create_time <".$strend);
		$GLOBALS['tmpl']->assign("user_list",$user_list);
		$GLOBALS['tmpl']->assign("user_data",$user_info);
		//本月业绩
		$month_money = $GLOBALS['db']->getOne("select sum(l.money*d.repay_time/12) from ".DB_PREFIX."deal d inner join ".DB_PREFIX."deal_load l on d.id = l.deal_id inner join ".DB_PREFIX."user u on l.user_id = u.id where u.pid=".$user_info['id']." and l.create_time >=".$strtotime." and l.create_time <".$strend);
		if(!$month_money)
			$month_money = 0;
		$GLOBALS['tmpl']->assign("month_money",floatval($month_money));
		//总业绩
		$total_money = $GLOBALS['db']->getOne("select sum(l.money*d.repay_time/12) from ".DB_PREFIX."deal d inner join ".DB_PREFIX."deal_load l on d.id = l.deal_id inner join ".DB_PREFIX."user u on l.user_id = u.id where u.pid=".$user_info['id']);
		if(!$total_money)
			$total_money = 0;
		$GLOBALS['tmpl']->assign("total_money",floatval($total_money));
		$GLOBALS['tmpl']->assign("header",$GLOBALS['db']->getOne("select header_url from ".DB_PREFIX."user where id=".$user_info['id']));
		$GLOBALS['tmpl']->assign('cate_title',"功能列表");
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/sale/salesman.html");
		$GLOBALS['tmpl']->display("page/uc.html");	
	}
	public function notice(){
		$login_info = es_session::get("user_info");
		if(!$login_info)
		{
			app_redirect(url("index")."?ctl=user&act=salesman_login");		
		}
		$activity = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."activity where is_effect = 1 and disable =1 and is_delete=1 and start_time < ".TIME_UTC." and end_time > ".TIME_UTC." and use_way=1 or use_way=3 order by activity_id desc");
        foreach ($activity as $k => $v) {
            $v['end_time'] = intval(($v['end_time'] - TIME_UTC)/24/60/60);
            $v['appwap_url']= $v['appwap_url'];
            $list[] = $v;
        }
		$GLOBALS['tmpl']->assign("list",$list);
		$GLOBALS['tmpl']->assign('cate_title',"公告");
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/sale/notice.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	//名片
	public function card(){
		$login_info = es_session::get("user_info");
		if(!$login_info)
		{
			app_redirect(url("index")."?ctl=user&act=salesman_login");		
		}
		$user_info = $GLOBALS['user_info'];
		$mobile = $GLOBALS['db']->getOne("SELECT AES_DECRYPT(mobile_encrypt,'".AES_DECRYPT_KEY."') as mobile FROM ".DB_PREFIX."user WHERE id=".$user_info['id']);
		$value = WAP_SITE_DOMAIN."/index.php?ctl=user&act=wapRegister&code=".$mobile;
		$GLOBALS['tmpl']->assign("wximg",$value);
		$GLOBALS['tmpl']->assign("user_data",$user_info);
		$GLOBALS['tmpl']->assign('cate_title',"名片");
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/sale/name_card.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	//本月业绩
	public function achievement(){
		$login_info = es_session::get("user_info");
		if(!$login_info)
		{
			app_redirect(url("index")."?ctl=user&act=salesman_login");		
		}
		$user_info = $GLOBALS['user_info'];
		$strtotime = strtotime(date("Y-m-1"));
		$strend = strtotime("+1 month",$strtotime);
		$page = 1;
        $pageSize = 10;
        $limit = (($page-1)*$pageSize.",".$pageSize);
		$user_list = $GLOBALS['db']->getAll("select d.repay_time,u.mobile,l.create_time,l.money from  ".DB_PREFIX."deal d inner join ".DB_PREFIX."deal_load l on d.id = l.deal_id inner join ".DB_PREFIX."user u on l.user_id = u.id where u.pid=".$user_info['id']." and l.create_time >=".$strtotime." and l.create_time <".$strend." order by l.id desc limit $limit");
		foreach ($user_list as $k => $v) {
			if(!$v['real_name'])
				$v['real_name']="未知";
			$v['create_time'] = date("Y-m-d H:i",$v['create_time']);
			/*$v['money'] = ($v['money'] * $v['repay_time'])/12;*/
			$list[] = $v;
		}
		$GLOBALS['tmpl']->assign("list",$list);
		$GLOBALS['tmpl']->assign('cate_title',"本月业绩");
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/sale/achievement.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}

	//本月业绩ajax分页加载
	public function ajax_achievement(){
		$user_info = $GLOBALS['user_info'];
		$strtotime = strtotime(date("Y-m-1"));
		$strend = strtotime("+1 month",$strtotime);
		$page = intval($_REQUEST['page']);
        $pageSize = 10;
        $limit = (($page)*$pageSize.",".$pageSize);
		$user_list = $GLOBALS['db']->getAll("select d.repay_time,u.mobile,l.create_time,l.money from  ".DB_PREFIX."deal d inner join ".DB_PREFIX."deal_load l on d.id = l.deal_id inner join ".DB_PREFIX."user u on l.user_id = u.id where u.pid=".$user_info['id']." and l.create_time >=".$strtotime." and l.create_time <".$strend." order by l.id desc limit $limit");
		foreach ($user_list as $k => $v) {
			if(!$v['real_name'])
				$v['real_name']="未知";
			$v['create_time'] = date("Y-m-d H:i",$v['create_time']);
			/*$v['money'] = ($v['money'] * $v['repay_time'])/12;*/
			$list[] = $v;
		}
		$pageCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_load l left join ".DB_PREFIX."user u on l.user_id = u.id where u.pid=".$user_info['id']." and l.create_time >=".$strtotime." and l.create_time <".$strend);
		$return['data'] = $list;
		$return['pages'] = ceil($pageCount/$pageSize);
		ajax_return($return);

	}
	//本月用户
	public function sale_user(){
		$login_info = es_session::get("user_info");
		if(!$login_info)
		{
			app_redirect(url("index")."?ctl=user&act=salesman_login");		
		}
		$user_info = $GLOBALS['user_info'];
		$strtotime = strtotime(date("Y-m-1"));
		$strend = strtotime("+1 month",$strtotime);
        $page = 1;
        $pageSize = 10;
        $limit = (($page-1)*$pageSize.",".$pageSize);
		$user_list = $GLOBALS['db']->getAll("select id,mobile,real_name,idcardpassed,create_time from ".DB_PREFIX."user where pid=".$user_info['id']." and create_time >=".$strtotime." and create_time <".$strend." order by id desc limit $limit");
		foreach ($user_list as $k => $v){
			$v['type'] = "注册";
			if($v['idcardpassed'] == 1){
				$v['type'] = "实名";
			}
			if($GLOBALS['db']->getOne("select sum(money) as money from ".DB_PREFIX."deal_load where user_id=".$v['id'])>0){
				$v['type'] = "出借";
			}
			if(!$v['real_name'])
				$v['real_name'] = "未知";
			$v['create_time'] = date("Y-m-d H:i",$v['create_time']);
			$list[] = $v;
		}
		$GLOBALS['tmpl']->assign("list",$list);
		$GLOBALS['tmpl']->assign('cate_title',"本月用户");
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/sale/sale_user.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	//本月用户ajax分页加载
	public function ajax_sale_user_month(){
		$user_info = $GLOBALS['user_info'];
		$strtotime = strtotime(date("Y-m-1"));
		$strend = strtotime("+1 month",$strtotime);
		$page = intval($_REQUEST['page']);
        $pageSize = 10;
        $limit = (($page)*$pageSize.",".$pageSize);
		$user_list = $GLOBALS['db']->getAll("select id,mobile,real_name,idcardpassed,create_time from ".DB_PREFIX."user where pid=".$user_info['id']." and create_time >=".$strtotime." and create_time <".$strend." order by id desc limit $limit");
		foreach ($user_list as $k => $v){
			$v['type'] = "注册";
			if($v['idcardpassed'] == 1){
				$v['type'] = "实名";
			}
			if($GLOBALS['db']->getOne("select sum(money) as money from ".DB_PREFIX."deal_load where user_id=".$v['id'])>0){
				$v['type'] = "出借";
			}
			if(!$v['real_name'])
				$v['real_name'] = "未知";
			$v['create_time'] = date("Y-m-d H:i",$v['create_time']);
			$list[] = $v;
		}
		$pageCount = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."user where pid=".$user_info['id']." and create_time >=".$strtotime." and create_time <".$strend);
		$return['data'] = $list;
		$return['pages'] = ceil($pageCount/$pageSize);
		ajax_return($return);

	}

	public function total_performance(){
		$login_info = es_session::get("user_info");
		if(!$login_info)
		{
			app_redirect(url("index")."?ctl=user&act=salesman_login");		
		}
		$user_info = $GLOBALS['user_info'];
		$user_list = $GLOBALS['db']->getAll("SELECT from_unixtime(t.create_time,'%Y/%m') as create_time,t.create_time as ti,u.create_time as t,SUM(t.money*d.repay_time/12) as money FROM jctp2p_user u left JOIN jctp2p_deal_load t on u.id = t.user_id left join jctp2p_deal d on t.deal_id = d.id WHERE u.pid =".$user_info['id']." AND from_unixtime(t.create_time,'%Y-%m') > '2015-01' and from_unixtime(t.create_time,'%Y-%m') < '2019-12'  GROUP BY from_unixtime(t.create_time,'%Y-%m') order by t.create_time desc");
		foreach ($user_list as $k => $v) {
			$begin = strtotime(date("Y-m",$v['ti'])."-1 00:00:00");
			$end = strtotime("1 month",$begin);
			$v['user_count'] = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."user where pid=".$user_info['id']." and create_time >=".$begin." and create_time <".$end);
			if(!$v['money']){
				$v['money'] = 0;
			}else{
				$v['money'] = sprintf('%.2f',$v['money']);
			}
			$list[] = $v;
		}
		$GLOBALS['tmpl']->assign("list",$list);
		$GLOBALS['tmpl']->assign('cate_title',"总业绩");
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/sale/total_performance.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}

	public function performance_list(){
		$login_info = es_session::get("user_info");
		if(!$login_info)
		{
			app_redirect(url("index")."?ctl=user&act=salesman_login");		
		}
		$user_info = $GLOBALS['user_info'];
		$strtotime = strtotime(date("Y-m-1 00:00:00",$_REQUEST['id']));
		$strend = strtotime("+1 month",$strtotime);
		$page = 1;
	    $pageSize = 10;
	    $limit = (($page-1)*$pageSize.",".$pageSize);
		$user_list = $GLOBALS['db']->getAll("select d.repay_time,u.mobile,u.real_name,l.money,l.create_time from ".DB_PREFIX."deal d inner join ".DB_PREFIX."deal_load l on d.id = l.deal_id inner join ".DB_PREFIX."user u on u.id = l.user_id where u.pid=".$user_info['id']." and l.create_time >=".$strtotime." and l.create_time <".$strend." order by l.id desc  limit $limit");
		foreach ($user_list as $k => $v) {
			if(!$v['real_name'])
				$v['real_name']="未知";
			$v['create_time'] = date("Y-m-d H:i",$v['create_time']);
			$list[]=$v;
		}
		$GLOBALS['tmpl']->assign("id",$_REQUEST['id']);
		$GLOBALS['tmpl']->assign("list",$list);
		$GLOBALS['tmpl']->assign('cate_title',"业绩详情");
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/sale/performance_list.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}

	//月业绩详览
	public function ajax_performance_list(){
		$user_info = $GLOBALS['user_info'];
		foreach($_POST as $k=>$v)
		{
			$data[$k] = htmlspecialchars(addslashes($v));
		}
		$page = intval($data['page']);
		$strtotime = strtotime(date("Y-m-1 00:00:00",$data['id']));
		$strend = strtotime("+1 month",$strtotime);
        $pageSize = 10;
        $limit = (($page)*$pageSize.",".$pageSize);
		$user_list = $GLOBALS['db']->getAll("select d.repay_time,u.mobile,u.real_name,l.money,l.create_time from ".DB_PREFIX."deal d inner join ".DB_PREFIX."deal_load l on d.id = l.deal_id inner join ".DB_PREFIX."user u on u.id = l.user_id where u.pid=".$user_info['id']." and l.create_time >=".$strtotime." and l.create_time <".$strend." order by l.id desc  limit $limit");
		foreach ($user_list as $k => $v) {
			if(!$v['real_name'])
				$v['real_name']="未知";
			$v['create_time'] = date("Y-m-d H:i",$v['create_time']);
			$list[]=$v;
		}
		$pageCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_load l left join ".DB_PREFIX."user u on u.id = l.user_id where u.pid=".$user_info['id']." and l.create_time >=".$strtotime." and l.create_time <".$strend);
		$return['data'] = $list;
		$return['pages'] = ceil($pageCount/$pageSize);
		ajax_return($return);

	}
	//全部用户
	public function total_user(){
		$login_info = es_session::get("user_info");
		if(!$login_info)
		{
			app_redirect(url("index")."?ctl=user&act=salesman_login");		
		}
		$user_info = $GLOBALS['user_info'];
		$page = 1;
        $pageSize = 10;
        $limit = (($page-1)*$pageSize.",".$pageSize);
        foreach($_POST as $k=>$v)
		{
			$data[$k] = htmlspecialchars(addslashes($v));
		}
		if($data['real_name'] != ""){
			$where = " and (u.mobile ='".$data['real_name']."' or u.real_name='".$data['real_name']."')";
		}
		if($data['type'] > 0){
			switch ($data['type']) {
				case '1':
					$where .= " and u.idno = ''";
					break;
				case '2':
					$where .= " and u.idno != '' and l.user_id is null";
					break;
				case '3':
					$where .= " and l.money > 0";
					break;
				default:
					# code...
					break;
			}
		}
		
		$user_list = $GLOBALS['db']->getAll("select DISTINCT(u.id),u.mobile,u.real_name,u.idcardpassed,u.create_time from ".DB_PREFIX."user u left join ".DB_PREFIX."deal_load l on u.id = l.user_id where pid=".$user_info['id']." $where order by u.id desc limit $limit");	
		foreach ($user_list as $k => $v){
			$money = $GLOBALS['db']->getOne("select sum(money) as money from ".DB_PREFIX."deal_load where user_id=".$v['id']);
			$v['type'] = "注册";
			$v['create_time'] = date("Y-m-d H:i",$v['create_time']);
			if($v['idcardpassed'] == 1){
				$v['type'] = "实名";
				$create_time = $GLOBALS['db']->getOne("select create_time from ".DB_PREFIX."user_bank where user_id=".$v['id']);
				if($create_time)
					$v['create_time'] = date("Y-m-d H:i",$create_time);
				else
					$v['create_time'] = date("Y-m-d H:i",$v['create_time']);
			}
			if($money > 0){
				$v['type'] = "出借";
				$create_time = $GLOBALS['db']->getOne("select create_time from ".DB_PREFIX."deal_load where user_id=".$v['id']." order by id desc");
				$v['create_time'] = date("Y-m-d H:i",$create_time);
				if(!$v['real_name'])
				$v['real_name'] = "未知";
			}else{
				if(!$v['real_name'])
				$v['real_name'] = "未知";
			}			
			$list[] = $v;
		}
		$GLOBALS['tmpl']->assign("list",$list);
		$GLOBALS['tmpl']->assign("type",$data['type']);
		$GLOBALS['tmpl']->assign("real_name",$data['real_name']);
		$GLOBALS['tmpl']->assign('cate_title',"所有用户");
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/sale/total_user.html");		
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	//全部用户ajax分页加载
	public function ajax_sale_user(){
		$user_info = $GLOBALS['user_info'];
		foreach($_POST as $k=>$v)
		{
			$data[$k] = htmlspecialchars(addslashes($v));
		}
		if($data['real_name'] != ""){
			$where = " and (mobile ='".$data['real_name']."' or real_name='".$data['real_name']."')";
		}
		if($data['type'] > 0){
			switch ($data['type']) {
				case '1':
					$where .= " and u.idno = ''";
					break;
				case '2':
					$where .= " and u.idno != '' and l.user_id is null";
					break;
				case '3':
					$where .= " and l.money > 0";
					break;
				default:
					# code...
					break;
			}
		}
		$page = intval($data['page']);
        $pageSize = 10;
        $limit = (($page)*$pageSize.",".$pageSize);
		$user_list = $GLOBALS['db']->getAll("select DISTINCT(u.id),u.mobile,u.real_name,u.idcardpassed,u.create_time from ".DB_PREFIX."user u left join ".DB_PREFIX."deal_load l on u.id = l.user_id where pid=".$user_info['id']." $where order by u.id desc limit $limit");
		foreach ($user_list as $k => $v){
			$v['type'] = "注册";
			$v['create_time'] = date("Y-m-d H:i",$v['create_time']);
			if($v['idcardpassed'] == 1){
				$v['type'] = "实名";
				$create_time = $GLOBALS['db']->getOne("select create_time from ".DB_PREFIX."user_bank where user_id=".$v['id']." order by id asc");
				if($create_time)
					$v['create_time'] = date("Y-m-d H:i",$create_time);
				else
					$v['create_time'] = date("Y-m-d H:i",$v['create_time']);
			}
			if($GLOBALS['db']->getOne("select sum(money) as money from ".DB_PREFIX."deal_load where user_id=".$v['id'])>0){
				$v['type'] = "出借";
				$create_time = $GLOBALS['db']->getOne("select create_time from ".DB_PREFIX."deal_load where user_id=".$v['id']." order by id desc");
				$v['create_time'] = date("Y-m-d H:i",$create_time);
			}
			if(!$v['real_name'])
				$v['real_name'] = "未知";
			$list[] = $v;
		}
		$pageCount = $GLOBALS['db']->getOne("select count(DISTINCT(u.id)) from ".DB_PREFIX."user u left join ".DB_PREFIX."deal_load l on u.id = l.user_id where pid=".$user_info['id']." $where");
		$return['data'] = $list;
		$return['pages'] = ceil($pageCount/$pageSize);
		ajax_return($return);

	}
	//全部用户投资详情
	public function total_user_info(){
		$login_info = es_session::get("user_info");
		if(!$login_info)
		{
			app_redirect(url("index")."?ctl=user&act=salesman_login");		
		}
		$user_id = $_REQUEST['id'];
		$page = 1;
        $pageSize = 10;
        $limit = (($page-1)*$pageSize.",".$pageSize);
        $user_list_info = $GLOBALS['db']->getAll("select id,mobile,real_name,idcardpassed,create_time from ".DB_PREFIX."user where id=".$user_id);
        foreach ($user_list_info as $k => $v) {
        	$v['type'] = "注册";
			$v['create_time'] = date("Y-m-d H:i",$v['create_time']);
			if($v['idcardpassed'] == 1){
				$v['type'] = "实名";
				$create_time = $GLOBALS['db']->getOne("select create_time from ".DB_PREFIX."user_bank where user_id=".$v['id']." order by id asc");
				$v['create_time'] = date("Y-m-d H:i",$create_time);
			}
			if($GLOBALS['db']->getOne("select sum(money) as money from ".DB_PREFIX."deal_load where user_id=".$v['id'])>0){
				$v['type'] = "出借";
				$create_time = $GLOBALS['db']->getOne("select create_time from ".DB_PREFIX."deal_load where user_id=".$v['id']." order by id asc");
				$v['create_time'] = date("Y-m-d H:i",$create_time);
			}
			if(!$v['real_name'])
				$v['real_name'] = "未知";
			$list_info = $v;
        }
        $GLOBALS['tmpl']->assign("list_info",$list_info);
		$user_list = $GLOBALS['db']->getAll("select d.repay_time,u.mobile,u.real_name,l.money,l.create_time from ".DB_PREFIX."deal d inner join ".DB_PREFIX."deal_load l on d.id = l.deal_id inner join ".DB_PREFIX."user u on u.id = l.user_id where u.id=".$user_id." order by l.id desc limit $limit");
		foreach ($user_list as $k => $v) {
			if(!$v['real_name'])
				$v['real_name']="未知";
			$v['create_time'] = date("Y-m-d H:i",$v['create_time']);
			$v['s_money'] = $v['money'];
			$v['money'] = ($v['money'] * $v['repay_time'])/12;
			$list[] = $v;
		}
		$GLOBALS['tmpl']->assign("list",$list);
		$GLOBALS['tmpl']->assign('cate_title',"用户详情");
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/sale/total_user_info.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	//全部用户投资详情ajax分页加载
	public function ajax_total_user_info(){
		$user_info = $GLOBALS['user_info'];
		foreach($_POST as $k=>$v)
		{
			$data[$k] = htmlspecialchars(addslashes($v));
		}
		$user_id = $data['id'];
		$page = intval($data['page']);
        $pageSize = 10;
        $limit = (($page)*$pageSize.",".$pageSize);
		$user_list = $GLOBALS['db']->getAll("select d.repay_time,u.mobile,u.real_name,l.money,l.create_time from ".DB_PREFIX."deal d inner join ".DB_PREFIX."deal_load l on d.id = l.deal_id inner join ".DB_PREFIX."user u on u.id = l.user_id where u.id=".$user_id." order by l.id desc limit $limit");
		foreach ($user_list as $k => $v) {
			if(!$v['real_name'])
				$v['real_name']="未知";
			$v['create_time'] = date("Y-m-d H:i",$v['create_time']);
			$v['s_money'] = $v['money'];
			$v['money'] = ($v['money'] * $v['repay_time'])/12;
			$list[] = $v;
		}
		$pageCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_load l left join ".DB_PREFIX."user u on u.id = l.user_id where u.id=".$user_id);
		$return['data'] = $list;
		$return['pages'] = ceil($pageCount/$pageSize);
		ajax_return($return);

	}

	//退出登录
	public function login_out(){
		$user = $GLOBALS['db']->getRow("SELECT AES_DECRYPT(mobile_encrypt,'".AES_DECRYPT_KEY."') as mobile,AES_DECRYPT(idno_encrypt,'".AES_DECRYPT_KEY."') as idno,AES_DECRYPT(real_name_encrypt,'".AES_DECRYPT_KEY."') as real_name FROM ".DB_PREFIX."user WHERE id=".$GLOBALS['user_info']['id']);
		$GLOBALS['tmpl']->assign("user_info_list",$user);
		$GLOBALS['tmpl']->assign('cate_title',"登录");
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/sale/login_out.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	public function loginout()
	{
		require_once APP_ROOT_PATH."system/libs/user.php";
		$result = loginout_user();
		if($result['status'])
		{
			$s_user_info = es_session::get("user_info");
			es_cookie::delete("user_name");
			es_cookie::delete("user_pwd");
			$GLOBALS['tmpl']->assign('integrate_result',$result['msg']);
				app_redirect(url("index")."?ctl=user&act=salesman_login");
		}
		else
		{
			app_redirect(url("index")."?ctl=user&act=salesman_login");
		}
	}
}
?>