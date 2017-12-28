<?php

require APP_ROOT_PATH.'app/Lib/uc.php';

class uc_accountModule extends SiteBaseModule
{
	public function index()
	{

		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_ACCOUNT']);
		
		//扩展字段
		$field_list = load_auto_cache("user_field_list");
		
		foreach($field_list as $k=>$v)
		{
			$field_list[$k]['value'] = $GLOBALS['db']->getOne("select value from ".DB_PREFIX."user_extend where user_id=".$GLOBALS['user_info']['id']." and field_id=".$v['id']);
		}
		
		$GLOBALS['tmpl']->assign("field_list",$field_list);
		
		
		//地区列表
		$region_lv2_name ="";
		$region_lv2 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."region_conf where region_level = 2");  //二级地址
		foreach($region_lv2 as $k=>$v)
		{
			if($v['id'] == intval($GLOBALS['user_info']['province_id']))
			{
				$region_lv2[$k]['selected'] = 1;
				$region_lv2_name = $v['name'];
			}
			
			if($v['id'] == intval($GLOBALS['user_info']['n_province_id']))
			{
				$region_lv2[$k]['nselected'] = 1;
				$n_region_lv2_name = $v['name'];
			}
		}
		$GLOBALS['tmpl']->assign("region_lv2",$region_lv2);
		$GLOBALS['tmpl']->assign("region_lv2_name",$region_lv2_name);
		$GLOBALS['tmpl']->assign("n_region_lv2_name",$n_region_lv2_name);

		$region_lv3_name = "";
		$region_lv3 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."region_conf where pid = ".intval($GLOBALS['user_info']['province_id']));  //三级地址
		foreach($region_lv3 as $k=>$v)
		{
			if($v['id'] == intval($GLOBALS['user_info']['city_id']))
			{
				$region_lv3[$k]['selected'] = 1;
				$region_lv3_name = $v['name'];
				break;
			}
		}
		$GLOBALS['tmpl']->assign("region_lv3",$region_lv3);
		$GLOBALS['tmpl']->assign("region_lv3_name",$region_lv3_name);

		$n_region_lv3_name = "";
		$n_region_lv3 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."region_conf where pid = ".intval($GLOBALS['user_info']['n_province_id']));  //三级地址
		foreach($n_region_lv3 as $k=>$v)
		{
			if($v['id'] == intval($GLOBALS['user_info']['n_city_id']))
			{
				$n_region_lv3[$k]['selected'] = 1;
				$n_region_lv3_name = $v['name'];
				break;
			}
		}
		$GLOBALS['tmpl']->assign("n_region_lv3",$n_region_lv3);
		$GLOBALS['tmpl']->assign("n_region_lv3_name",$n_region_lv3_name);
		$user = $GLOBALS['db']->getRow("SELECT AES_DECRYPT(mobile_encrypt,'".AES_DECRYPT_KEY."') as mobile,AES_DECRYPT(idno_encrypt,'".AES_DECRYPT_KEY."') as idno,AES_DECRYPT(real_name_encrypt,'".AES_DECRYPT_KEY."') as real_name FROM ".DB_PREFIX."user WHERE id=".$GLOBALS['user_info']['id']);
		$GLOBALS['tmpl']->assign("user_info_list",$user);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_account_index.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	
	public function work(){
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_WORK_AUTH']);
		//地区列表
		$work =  $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."user_work where user_id =".$GLOBALS['user_info']['id']);
		$GLOBALS['tmpl']->assign("work",$work);
		
		$region_lv2 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."region_conf where region_level = 2");  //二级地址
		foreach($region_lv2 as $k=>$v)
		{
			if($v['id'] == intval($work['province_id']))
			{
				$region_lv2[$k]['selected'] = 1;
				break;
			}
		}
		$GLOBALS['tmpl']->assign("region_lv2",$region_lv2);
		
		$region_lv3 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."region_conf where pid = ".intval($work['province_id']));  //三级地址
		foreach($region_lv3 as $k=>$v)
		{
			if($v['id'] == intval($work['city_id']))
			{
				$region_lv3[$k]['selected'] = 1;
				break;
			}
		}
		$GLOBALS['tmpl']->assign("region_lv3",$region_lv3);
		
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_work_index.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	
	public function mobile(){
		$is_ajax = intval($_REQUEST['is_ajax']);
		$form = strim($_REQUEST['from']);
		$user_phone = $GLOBALS['db']->getRow("SELECT mobile FROM  ".DB_PREFIX."user WHERE id =".$GLOBALS['user_info']['id']);
		$user_info = $GLOBALS['user_info'];
		if(!$user_info['idcardpassed']){
			$ajax['url'] = url("index","uc_center#identity");
			$ajax['code'] = 0;
		}else{
			$ajax['url'] = url("index","uc_account#authentication");
			$ajax['code'] = 1;
		}
		$t = time();
		$begin_time = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t)); 
		$send_count = $GLOBALS['db']->getOne("select send_count from ".DB_PREFIX."mobile_verify_code where mobile =".$user_phone['mobile']." and create_time >".$begin_time);
		$GLOBALS['tmpl']->assign('send_count',$send_count);
		$GLOBALS['tmpl']->assign('user_info',$user_info);
		$GLOBALS['tmpl']->assign('ajax',$ajax);
		$GLOBALS['tmpl']->assign('user_phone',$user_phone);
		$GLOBALS['tmpl']->assign("is_ajax",$is_ajax);
		if($is_ajax==0){
			$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_MOBILE']);
			$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_mobile_index.html");
			$GLOBALS['tmpl']->display("page/uc.html");
		}
		else{
			if($form == "debit")
			{
				$GLOBALS['tmpl']->display("debit/debit_uc_mobile_index.html");
			}
			else
			{
				$GLOBALS['tmpl']->display("inc/uc/uc_mobile_index.html");
			}
		}
	}
	public function authentication(){
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_authentication.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	public function get_authentication(){
		foreach($_POST as $k=>$v)
		{
			$user_data[$k] = htmlspecialchars(addslashes($v));
		}
		$user = $GLOBALS['db']->getRow("SELECT AES_DECRYPT(idno_encrypt,'".AES_DECRYPT_KEY."') as idno,AES_DECRYPT(real_name_encrypt,'".AES_DECRYPT_KEY."') as real_name FROM ".DB_PREFIX."user WHERE id=".$GLOBALS['user_info']['id']);
		if($user['real_name'] == $user_data['real_name'] && $user['idno'] == $user_data['idno']){
			es_session::set("mobile_step_one",$user['idno']);
			$data['status'] = 1;
			//$data['info'] = "真实姓名或身份证号正确";
			ajax_return($data);
		}else{
			$data['status'] = 0;
			$data['info'] = "真实姓名或身份证号错误";
			ajax_return($data);
		}
	}
	public  function reset_login_psw(){
		$GLOBALS['tmpl']->assign("page_title","登录密码");
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_reset_login_psw.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}

	/*****pc2.0修改绑定手机号第一步：判断原有手机号*******/
	public function pc_regsms_code_fir(){
		require_once APP_ROOT_PATH."system/libs/user.php";
		$user_data = $_POST;
		if(!$user_data){
			app_redirect("404.html");
			exit();
		}
		foreach($user_data as $k=>$v)
		{
			$user_data[$k] = htmlspecialchars(addslashes($v));
		}
		if($mobile != $user_data['old_phone']){
			$data['status'] = 0;
			$data['info'] = "旧手机号码错误";
			ajax_return($data);
		}
		if(strim($user_data['oldverify']) == ""){
			$return['status'] = 0;
			$return['info'] = "请输入手机验证码";
			ajax_return($return);
			//showErr("请输入手机验证码");
		}
		//判断验证码是否正确
		if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".strim($user_data['old_mobile'])."' AND verify_code='".strim($user_data['oldverify'])."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ")==0){
			$return['status'] = 0;
			$return['info'] = "手机验证码出错,或已过期";
			ajax_return($return);
			//showErr("手机验证码出错,或已过期");
		}
		$return['status'] = 1;
		ajax_return($return);
	}
	/*****wap2.0修改绑定手机号*******/
	public function regsms_code(){
		require_once APP_ROOT_PATH."system/libs/user.php";
		$mobile = $GLOBALS['user_info']['mobile'];
		$user_data = $_POST;
		if(!$user_data){
			app_redirect("404.html");
			exit();
		}
		foreach($user_data as $k=>$v)
		{
			$user_data[$k] = htmlspecialchars(addslashes($v));
		}
		$info = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."user WHERE mobile='".strim($user_data['mobile'])."'");
		if($info  > 0){
			$data['status'] = 0;
			$data['info'] = "手机号码已被注册";
			ajax_return($data);
		}
		if(strim($user_data['code']) == ""){
			$return['status'] =	0;
			$return['info'] = "请输入手机验证码";
			ajax_return($return);
				//showErr("请输入手机验证码");
		}
		//判断验证码是否正确
		if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".strim($user_data['mobile'])."' AND verify_code='".strim($user_data['code'])."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ")==0){
			$return['status'] = 0;
			$return['info'] = "手机验证码出错,或已过期";
			ajax_return($return);
			//showErr("手机验证码出错,或已过期");
		}else{
			$data['user_name'] = "w".$user_data['mobile'];
			$data['mobile'] = $user_data['mobile'];
			$data['mobile_encrypt'] = "AES_ENCRYPT('".$user_data['mobile']."','".AES_DECRYPT_KEY."')";
			//修改绑定手机号时，更新被邀请人信息
			// $result = $GLOBALS['db']->getAll("SELECT id FROM ".DB_PREFIX."user where referer = ".$mobile);
			// foreach ($result as $k => $v) {
			// 	$referer['referer'] = $data['mobile'];
			// 	$GLOBALS['db']->autoExecute(DB_PREFIX."user",$referer,"UPDATE","id=".$v['id']);
			// }
			if($user_id = get_user_info("id","mobile_encrypt =AES_ENCRYPT('".$mobile."','".AES_DECRYPT_KEY."') ","ONE")){
				$res=$GLOBALS['db']->autoExecute(DB_PREFIX."user",$data,"UPDATE","id=".$user_id);
				//修改绑定手机号时，更新被邀请人信息
				if($res){
					$referer['referer'] = $data['mobile'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."user",$referer,"UPDATE","pid=".$user_id);
				}else{
					$return['status'] = 0;
					$return['info'] = "修改失败,请返回重试";
					ajax_return($return);
				}
				showSuccess("绑定手机修改成功",1,url("index","user#loginout"));//绑定手机修改成功
			}else{
				showErr($GLOBALS['lang']['NO_THIS_MOBILE'],1);  //没有该手机账户
			}
		}
	}
	public function mobile_step_one(){
		foreach ($_POST as $k=> $v) {
			$user_data[$k] = htmlspecialchars(addslashes($v));
		}
		/*$verify =  strim($user_data['verify']);
		if($verify){
			if(!checkVeifyCode($verify)){
				$data['status'] = 0;
				$data['info'] = "图形验证码错误";
				ajax_return($data);
			}
		}*/
		//判断验证码是否正确
		if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".strim($user_data['mobile'])."' AND verify_code='".strim($user_data['code'])."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ")==0){
			$return['status'] = 0;
			$return['info'] = "手机验证码出错,或已过期";
			ajax_return($return);
		}else{
			es_session::set("mobile_step_one",$user_data['mobile']);
			$return['status'] = 1;
			$return['info'] = "手机验证码正确";
			ajax_return($return);
		}
	}
	public function mobile_step_two(){
		if(!es_session::get("mobile_step_one")){
			exit("非法操作");
		}
		$t = time();
		$begin_time = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t)); 
		$user_mobile_info = es_session::get("user_mobile_info");
		$send_count = $GLOBALS['db']->getOne("select send_count from ".DB_PREFIX."mobile_verify_code where mobile =".$user_mobile_info." and create_time >".$begin_time);
		$GLOBALS['tmpl']->assign("send_count",$send_count);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_mobile_step_two.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	/*****wap2.0修改我的银行卡*******/
	public function bank(){
		jumpUrl("jump_carry_bank");
		$is_ajax = intval($_REQUEST['is_ajax']);
		$form = strim($_REQUEST['from']);
		$user_info = $GLOBALS['db']->getRow("SELECT mobile FROM  ".DB_PREFIX."user WHERE id =".$GLOBALS['user_info']['id']);
		$bank = $GLOBALS['db']->getAll("SELECT ub.id as bid,b.id,ub.bankcard,b.icon,b.name FROM  ".DB_PREFIX."user_bank ub LEFT JOIN ".DB_PREFIX."bank b on ub.bank_id = b.bankid WHERE ub.status =1 and ub.cunguan_tag=1 and user_id =".$GLOBALS['user_info']['id']." limit 0,1");
		foreach ($bank as $k => $v) {
			$v['bankcard'] = substr($v['bankcard'],-4,4);
			$list[] = $v;
		}
		$user_money = get_user_money_info($GLOBALS['user_info']['id']);
		$GLOBALS['tmpl']->assign('total_money',$user_money['cunguan_total_money']);
		$GLOBALS['tmpl']->assign('ajax_code',$ajax_code);
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('user_info',$user_info);
		$GLOBALS['tmpl']->assign("is_ajax",$is_ajax);
		if($is_ajax==0){
			$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_MOBILE']);
			$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_bank_index.html");
			$GLOBALS['tmpl']->display("page/uc.html");
		}
		else{
			if($form == "debit")
			{
				$GLOBALS['tmpl']->display("debit/debit_uc_mobile_index.html");
			}
			else
			{
				$GLOBALS['tmpl']->display("inc/uc/uc_bank_index.html");
			}
		}
	}
	public function email(){
		$GLOBALS['user_info']=$GLOBALS['db']->getRow("SELECT id,emailpassed,AES_DECRYPT(email_encrypt,'".AES_DECRYPT_KEY."') as email FROM  ".DB_PREFIX."user WHERE id =".$GLOBALS['user_info']['id']);
		if($GLOBALS['user_info']['emailpassed']==1){
			exit();
		}
		
		if(get_site_email($GLOBALS['user_info']['id']) ==$GLOBALS['user_info']['email']){
			$email="";
		}
		else
			$email=$GLOBALS['user_info']['email'];
		$GLOBALS['tmpl']->assign("email",$email);
		$step = intval($_REQUEST['step']);
		$GLOBALS['tmpl']->assign("step",$step);
		$form = strim($_REQUEST["from"]);
		if($form == "debit")
		{
			$GLOBALS['tmpl']->assign("inc_file","debit/debit_uc_email_step.html");
		}
		else
		{
			$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_email_step.html");
		}
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_MOBILE']);
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	
	public function saveemail(){
		$oemail =  strim($_REQUEST['oemail']);
		$email =  strim($_REQUEST['email']);
		$code = strim($_REQUEST['code']);
		
		
		if($GLOBALS['user_info']['email']!="" && get_site_email($GLOBALS['user_info']['id']) !=$GLOBALS['user_info']['email']){
			if($oemail!=$GLOBALS['user_info']['email']){
				$result['info'] = "旧邮箱确认失败";
				ajax_return($result);
			}
		}
		if($email!="" && !check_email($email)){
			$result['info'] = "新邮箱格式错误";
			ajax_return($result);
		}
		if($GLOBALS['user_info']['emailpassed']==1){
			$result['info'] = "该账户已绑定认证过邮箱，无法进行此操作";
			ajax_return($result);
		}
		if($code == "" || $code != $GLOBALS['user_info']['verify']){
			$result['info'] = "验证码错误";
			ajax_return($result);
		}
		
		if($email==""){
			$email = $oemail;
		}

		$GLOBALS['db']->query("update ".DB_PREFIX."user set email_encrypt = AES_ENCRYPT('".$email."','".AES_DECRYPT_KEY."'),verify = '',emailpassed = 1 where id = ".$GLOBALS['user_info']['id']);
		
		$result['status'] = 1;
		$result['info'] = "邮箱绑定成功";
		ajax_return($result);
	}
	
	public function save()
	{
		require_once APP_ROOT_PATH.'system/libs/user.php';
		foreach($_REQUEST as $k=>$v)
		{
			$_REQUEST[$k] = htmlspecialchars(addslashes(trim($v)));
		}

		if ($_REQUEST['sta'] == 1){
			if(md5(strim($_REQUEST['old_password']).$GLOBALS['user_info']['code'])!=$GLOBALS['user_info']['user_pwd'])
			{
				showErr("旧密码错误！",intval($_REQUEST['is_ajax']));
			}
		}

		$_REQUEST['id'] = intval($GLOBALS['user_info']['id']);
		
		if($GLOBALS['user_info']['user_name']!="")
			$_REQUEST['user_name'] =  $_REQUEST['old_user_name'] = $GLOBALS['user_info']['user_name'];
		if($GLOBALS['user_info']['email']!="")
			$_REQUEST['email'] = $_REQUEST['old_email'] = $GLOBALS['user_info']['email'];
		if($GLOBALS['user_info']['mobile']!="")
			$_REQUEST['mobile'] = $_REQUEST['old_mobile'] = $GLOBALS['user_info']['mobile'];

		$_REQUEST['old_password'] = strim($_REQUEST['old_password']);
		
		$res = save_user($_REQUEST,'UPDATE');
		if($res['status'] == 1)
		{
			$s_user_info = es_session::get("user_info");
			$user_info = get_user_info("*","id = '".intval($s_user_info['id'])."'");
			es_session::set("user_info",$user_info);
			if(intval($_REQUEST['is_ajax'])==1)
				showSuccess($GLOBALS['lang']['SUCCESS_TITLE'],1);
			else{
				app_redirect(url("index","uc_account#index"));
			}		
		}
		else
		{
			$error = $res['data'];		
			if(!$error['field_show_name'])
			{
				$error['field_show_name'] = $GLOBALS['lang']['USER_TITLE_'.strtoupper($error['field_name'])];
			}
			if($error['error']==EMPTY_ERROR)
			{
				$error_msg = sprintf($GLOBALS['lang']['EMPTY_ERROR_TIP'],$error['field_show_name']);
			}
			if($error['error']==FORMAT_ERROR)
			{
				$error_msg = sprintf($GLOBALS['lang']['FORMAT_ERROR_TIP'],$error['field_show_name']);
			}
			if($error['error']==EXIST_ERROR)
			{
				$error_msg = sprintf($GLOBALS['lang']['EXIST_ERROR_TIP'],$error['field_show_name']);
			}
			showErr($error_msg,intval($_REQUEST['is_ajax']));
		}
	}
	
	public function savework(){
		foreach($_REQUEST as $k=>$v)
		{
			$_REQUEST[$k] = htmlspecialchars(addslashes(trim($v)));
		}
		if(isset($_REQUEST['office']))
			$data['office'] = trim($_REQUEST['office']);
		if(isset($_REQUEST['jobtype']))
			$data['jobtype'] = trim($_REQUEST['jobtype']);
		if(isset($_REQUEST['province_id']))
			$data['province_id'] = intval($_REQUEST['province_id']);
		if(isset($_REQUEST['city_id']))
			$data['city_id'] = intval($_REQUEST['city_id']);
		if(isset($_REQUEST['officetype']))
			$data['officetype'] = trim($_REQUEST['officetype']);
		if(isset($_REQUEST['officedomain']))
			$data['officedomain'] = trim($_REQUEST['officedomain']);
		if(isset($_REQUEST['officecale']))
			$data['officecale'] = trim($_REQUEST['officecale']);
		if(isset($_REQUEST['position']))
			$data['position'] = trim($_REQUEST['position']);
		if(isset($_REQUEST['salary']))
			$data['salary'] = trim($_REQUEST['salary']);
		if(isset($_REQUEST['workyears']))
			$data['workyears'] = trim($_REQUEST['workyears']);
		if(isset($_REQUEST['workphone']))
			$data['workphone'] = trim($_REQUEST['workphone']);
		if(isset($_REQUEST['workemail']))
			$data['workemail'] = trim($_REQUEST['workemail']);
		if(isset($_REQUEST['officeaddress']))
			$data['officeaddress'] = trim($_REQUEST['officeaddress']);
		
		if(isset($_REQUEST['urgentcontact']))
			$data['urgentcontact'] = trim($_REQUEST['urgentcontact']);
		if(isset($_REQUEST['urgentrelation']))
			$data['urgentrelation'] = trim($_REQUEST['urgentrelation']);
		if(isset($_REQUEST['urgentmobile']))
			$data['urgentmobile'] = trim($_REQUEST['urgentmobile']);
		if(isset($_REQUEST['urgentcontact2']))
			$data['urgentcontact2'] = trim($_REQUEST['urgentcontact2']);
		if(isset($_REQUEST['urgentrelation2']))
			$data['urgentrelation2'] = trim($_REQUEST['urgentrelation2']);
		if(isset($_REQUEST['urgentmobile2']))
			$data['urgentmobile2'] = trim($_REQUEST['urgentmobile2']);

		$data['user_id'] = intval($GLOBALS['user_info']['id']);

		if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."user_work WHERE user_id=".$data['user_id'])==0){
			//添加
			$GLOBALS['db']->autoExecute(DB_PREFIX."user_work",$data,"INSERT");
		}
		else{
			//编辑
			$GLOBALS['db']->autoExecute(DB_PREFIX."user_work",$data,"UPDATE","user_id=".$data['user_id']);
		}
		
		showSuccess($GLOBALS['lang']['SAVE_USER_SUCCESS'],intval($_REQUEST['is_ajax']));
	}
	
	public function security(){
		jumpUrl("jump_url_depository");
		if($GLOBALS['user_info']['idcardpassed'] == 0)
		{
			$idcard_credit = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."user_credit_file WHERE type='credit_identificationscanning' and user_id=".$GLOBALS['user_info']['id']." ");

			if($idcard_credit){
				$file_list = array();
				if($idcard_credit['file'])
					$file_list = unserialize($idcard_credit['file']);

				if(is_array($file_list)) 
					$idcard_credit['file_list']= $file_list;

			}

			$GLOBALS['tmpl']->assign('idcard_credit',$idcard_credit);
		}
		$user_info = $GLOBALS['user_info'];
		$self_info = $GLOBALS['db']->getRow("SELECT accno,cunguan_pwd,AES_DECRYPT(mobile_encrypt,'".AES_DECRYPT_KEY."') as mobile,AES_DECRYPT(real_name_encrypt,'".AES_DECRYPT_KEY."') as real_name,AES_DECRYPT(idno_encrypt,'".AES_DECRYPT_KEY."') as idcard FROM ".DB_PREFIX."user WHERE id=".$GLOBALS['user_info']['id']);
		$self_info['idcard']=str_replace(substr($self_info['idcard'],2,13),str_repeat('*',13),$self_info['idcard']);
		$usinfos = $GLOBALS['db']->getRow("select AES_DECRYPT(u.real_name_encrypt,'".AES_DECRYPT_KEY."') as realname,AES_DECRYPT(u.idno_encrypt,'".AES_DECRYPT_KEY."') as idno,u.paypassword,b.bankcard,b.status from ".DB_PREFIX."user u left join ".DB_PREFIX."user_bank b on u.id = b.user_id where u.id= ".$user_info['id']." and b.status=1 and b.cunguan_tag=1");
		$user_status = $GLOBALS['db']->getOne("select sum(status) as status from ".DB_PREFIX."user_bank  where  user_id= ".$user_info['id']);
		if(!$usinfos['bankcard'] && !$usinfos['idno']){
			$ajax['url'] = url("index","uc_center#identity");
			$ajax['code'] = 0;
		}else if(!$usinfos['bankcard'] && $usinfos['idno']){
			$ajax['url'] = url("index","uc_account#bind_bank");
			$ajax['code'] = 0;
		}else if($usinfos['bankcard'] && $user_status == 0){
			$ajax['url'] = url("index","uc_account#bind_bank");
			$ajax['code'] = 0;
		}else if(!$usinfos['paypassword']){
			$ajax['url'] = url("index","uc_account#wappaypassword");
			$ajax['code'] = 0;
		}else{
			$ajax['url'] = url("index","uc_account#bank");
			$ajax['code'] = 1;
		}
        $score=$GLOBALS['db']->getOne("select score from ".DB_PREFIX."wenjuan_user_answer_record where user_id =".$GLOBALS['user_info']['id']);
        $GLOBALS['tmpl']->assign('score',$score);
		$GLOBALS['tmpl']->assign("page_title","安全中心");
		$GLOBALS['tmpl']->assign('ajax',$ajax);
		$GLOBALS['tmpl']->assign('user_info',$user_info);
		$GLOBALS['tmpl']->assign('usinfos',$usinfos);
		$GLOBALS['tmpl']->assign('self_info',$self_info);
		$user_type = get_user_info("user_type","id=".$GLOBALS['user_info']['id'],"ONE");
		$GLOBALS['tmpl']->assign('user_type',$user_type);		
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_security.html");
		$GLOBALS['tmpl']->display("page/uc.html");
		
		
	}
	public function reset_password(){
		$user_id = $GLOBALS['user_info']['id'];
		$user_mobile =  $GLOBALS['db']->getOne("SELECT mobile FROM ".DB_PREFIX."user where id =".$user_id);
		$sms_count = es_session::get('sms_count');
		$GLOBALS['tmpl']->assign("sms_count",$sms_count);
		$GLOBALS['tmpl']->assign("user_mobile",$user_mobile);		
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_reset_password.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	public function reset_password_two(){
		if(!es_session::get("check_reset_password")){
			exit('非法请求');
		}	
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_reset_password_two.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	public function query_pw(){
		if(!$_POST)
		{
			 app_redirect("404.html");
			 exit();
		}
		foreach($_POST as $k=>$v)
		{
			$_POST[$k] = htmlspecialchars(addslashes($v));
		}
		$user_id = $GLOBALS['user_info']['id'];
		$old = md5($_POST['old_pwd']);
		$old_pwd =  $GLOBALS['db']->getOne("SELECT user_pwd FROM ".DB_PREFIX."user where id =".$user_id);
		if(trim($old) != trim($old_pwd)){
			$return['info'] = "原始登录密码错误";
			ajax_return($return);
		}else{
			$return['status'] = 1;
			$return['info'] = "原始登录密码正确";
			ajax_return($return);
		}
	}
	public function query_password(){
		if(!$_POST)
		{
			 app_redirect("404.html");
			 exit();
		}
		foreach($_POST as $k=>$v)
		{
			$data[$k] = htmlspecialchars(addslashes($v));
		}
		$user_id = $GLOBALS['user_info']['id'];
		$password= md5($data['password']);
		//判断验证码是否正确
		if(get_user_info("user_pwd","id=".$user_id,"ONE")==MD5($data['password'])){
			$return['info'] = "新密码不能和原密码相同哦";
			ajax_return($return);
		}elseif(get_user_info("paypassword","id=".$user_id,"ONE")==MD5($data['password'])){
			$return['info'] = "登录密码不能与交易密码相同";
			ajax_return($return);
		}else{
			$data['user_pwd'] = MD5($data['password']);
			$result = $GLOBALS['db']->autoExecute(DB_PREFIX."user",$data,"UPDATE","id=".$user_id);
			if($result){
				es_session::delete('check_reset_password');
				es_session::delete('sms_count');
				$return['status'] = 1;
				$return['info'] = "修改成功";
				ajax_return($return);
			}			
			
		}
	}
	public  function check_reset_password(){
		if(!$_POST)
		{
			 app_redirect("404.html");
			 exit();
		}
		foreach($_POST as $k=>$v)
		{
			$data[$k] = htmlspecialchars(addslashes($v));
		}
		$mobile = $GLOBALS['user_info']['mobile'];
		if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".strim($mobile)."' AND verify_code='".strim($data['sms_code'])."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ")==0){
				$return['info'] = "手机验证码出错,或已过期";
				ajax_return($return);
		}else{
			es_session::set("check_reset_password",$mobile);
			$return['status'] = 1;
			$return['info'] = "手机验证码出错,或已过期";
			ajax_return($return);
		}

	}
	public function save_password(){
		if(!$_POST)
		{
			 app_redirect("404.html");
			 exit();
		}
		foreach($_POST as $k=>$v)
		{
			$_POST[$k] = htmlspecialchars(addslashes($v));
		}
		$user_id = $GLOBALS['user_info']['id'];
		$mobile = strim($_POST['user_mobile']);
		$sms_code = strim($_POST['sms_code']);
		$password = strim($_POST['pw']);
		//判断验证码是否正确
		if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".strim($mobile)."' AND verify_code='".strim($sms_code)."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ")==0){
				showErr("手机验证码出错,或已过期",1);
		}elseif(get_user_info("user_pwd","id=".$user_id,"ONE")==MD5($password)){
				showErr("不能修改为原密码",1);
		}elseif(get_user_info("paypassword","id=".$user_id,"ONE")==MD5($password)){
				showErr("登录密码不能与交易密码相同",1);
		}else{
			$data['user_pwd'] = MD5($password);
			$result = $GLOBALS['db']->autoExecute(DB_PREFIX."user",$data,"UPDATE","id=".$user_id);
			if($result){
				es_session::delete('sms_count');
				showSuccess("修改成功",1,url("index","user#loginout"));
			}			
			
		}
	}
	public function paypassword(){
		//require APP_ROOT_PATH."system/utils/Depository/DepSdk.php";
		$is_ajax = intval($_REQUEST['is_ajax']);
		$from = strim($_REQUEST["from"]);
		if(!$GLOBALS['user_info']){
			showErr('请先登录',$is_ajax);
		}
		if(intval(get_user_info("mobilepassed","id=".$GLOBALS['user_info']['id'],"ONE")) != 1){
			showErr('手机号码未绑定',$is_ajax);
		}
		if(intval(get_user_info("idcardpassed","id=".$GLOBALS['user_info']['id'],"ONE")) != 1){
			showErr('银行卡未绑定',$is_ajax);
		}
		$GLOBALS['tmpl']->assign("is_ajax",$is_ajax);
		$GLOBALS['tmpl']->assign("cate_title","设置交易密码");
		if($is_ajax==1){
			if($from=="debit")
			{
				showSuccess($GLOBALS['tmpl']->fetch("debit/debit_uc_paypassword_index.html"),$is_ajax);
			}
			else
			{
				showSuccess($GLOBALS['tmpl']->fetch("inc/uc/uc_paypassword_index.html"),$is_ajax);
			}
		}
		else
		{
			$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_PAYPASSWORD']);
			$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_paypassword_index.html");
			$GLOBALS['tmpl']->display("page/uc.html");
		}
	}
	public function wappaypassword(){
		if(!$GLOBALS['user_info']){
			showErr('请先登录',$is_ajax);
		}
		if(!$GLOBALS['user_info']['idcardpassed']){
			app_redirect(url("member","uc_center#identity"));
		}
		$GLOBALS['tmpl']->assign("is_ajax",$is_ajax);
		$GLOBALS['tmpl']->assign("cate_title","设置交易密码");
		if($is_ajax==1){
			if($from=="debit")
			{
				showSuccess($GLOBALS['tmpl']->fetch("debit/debit_uc_paypassword_index.html"),$is_ajax);
			}
			else
			{
				showSuccess($GLOBALS['tmpl']->fetch("inc/uc/uc_paypassword_index.html"),$is_ajax);
			}
		}
		else
		{
			$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_PAYPASSWORD']);
			$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_paypassword_index.html");
			$GLOBALS['tmpl']->display("page/uc.html");
		}
	}
	//三步验证 银行卡页面
 	public function bind_bank(){
 		header('content-type:text/html;charset=utf-8');
 		if(!$GLOBALS['user_info']){
			showErr('请先登录',$is_ajax);
		}
 		$user_info = $GLOBALS['db']->getRow("SELECT AES_DECRYPT(real_name_encrypt,'".AES_DECRYPT_KEY."') as real_name,AES_DECRYPT(idno_encrypt,'".AES_DECRYPT_KEY."') as idno FROM ".DB_PREFIX."user WHERE id=".$GLOBALS['user_info']['id']);
 		$MachineInfo = explode("|||",es_session::get('MachineInfo'));
		$GLOBALS['tmpl']->assign("MachineInfo",$MachineInfo[0]);
 		$bankList=$GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."bank where is_rec = 1");
 		$sms_count = es_session::get('sms_count');
		$GLOBALS['tmpl']->assign("sms_count",$sms_count);
 		$GLOBALS['tmpl']->assign('user_info',$user_info);
 		$GLOBALS['tmpl']->assign('bankList',$bankList);
 		$GLOBALS['tmpl']->assign("cate_title","更改绑定银行卡");
 		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_change_bank.html");
		$GLOBALS['tmpl']->display("page/uc.html");
 	}
 	/*
 	*  	第三方银行卡验证
 	*	$name  			真实姓名
 	*	$idnum 			身份证号
 	*	$phone 			银行卡绑定手机号
 	*	$bankCard 		银行卡号
 	*	$sid        	企业账号
 	*	$md5key     	MD5密码
 	*	$despwd   		3DES密码
 	*	$cpserialnum	订单号
 	****/
 	public function bind_bank_card(){
 		require APP_ROOT_PATH."system/utils/Verify.php";
 		require APP_ROOT_PATH."system/utils/BinkCard/Imagebase64.php";
 		require APP_ROOT_PATH."system/utils/bankList.php";
 		require APP_ROOT_PATH.'system/libs/voucher.php';
 		/*require APP_ROOT_PATH."system/utils/bankBin/llpay_apipost_submit.class.php";*/
 		if(!$_POST)
		{
			 app_redirect("404.html");
			 exit();
		}
		foreach($_POST as $k=>$v)
		{
			$_POST[$k] = htmlspecialchars(addslashes($v));
		}
 		if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".strim($_POST["mobile"])."' AND verify_code='".strim($_POST["sms_code"])."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ")==0){
				$json['status'] = 0;
				$json['info'] = "短信验证码出错或已过期";
				ajax_return($json);
		}
		//获取用户信息
		$user_info = $GLOBALS['db']->getRow("SELECT  real_name,idno FROM ".DB_PREFIX."user WHERE id=".$GLOBALS['user_info']['id']);
		if($GLOBALS['db']->getOne("SELECT * FROM  ".DB_PREFIX."user_bank WHERE bankcard ='".$_POST['cardId']."'") > 0){
			$json['status'] = 0;
			$json['info'] = "该身份信息已被占用";
			ajax_return($json);
		}
		if($GLOBALS['db']->getOne("SELECT id FROM  ".DB_PREFIX."user WHERE idno =".$_POST['idno']) > 0){
			$json['status'] = 0;
			$json['info'] = "该身份信息已被占用";
			ajax_return($json);
		}
		/*银行卡类型校验*/
		/*$llpay_gateway_new = 'https://queryapi.lianlianpay.com/bankcardbin.htm';
		$llpay_config = $this->llpay_return_config();
		$parameter = array(
			'api_version'=>$llpay_config['version'],
			'pay_type'=>'2',
			'flag_amt_limit'=>'0',
			"card_no" => $_POST['cardId'],
    		"oid_partner" => trim($llpay_config['oid_partner']),
    		"sign_type" => trim($llpay_config['sign_type']),    		
    	);

    	$llpaySubmit = new LLpaySubmit($llpay_config);
		$html_text = $llpaySubmit->buildRequestJSON($parameter,$llpay_gateway_new);
		$arr = json_decode($html_text,1);
		if($arr['ret_code']=='0000' && $arr['card_type'] == 3){
			$json['status'] = 0;
			$json['info'] = "暂时不支持信用卡绑定";
			ajax_return($json);
		}*/
		$url = "http://verifyapi.huiyuenet.com/zxbank/verifyApi.do";
		if(!$_REQUEST['real_name'] && !$_REQUEST['idno']){
			//更改绑定银行卡 自动获取参数
			$name = $user_info['real_name'];
			$idnum = $user_info['idno'];
		}else{
			//绑定银行卡 传入参数
			$name = $_POST['real_name'];
			$idnum = $_POST['idno'];
		}
		$phone = $_POST['mobile'];
		$bankCard = $_POST['cardId'];
		$sid = "jxdbc";
		$cpserialnum = $this->orderId();
		$md5key = "l46g6i";
		$despwd = "9cwcweunozhw15ul6elezl5y";
		$vtype = "03";
		$verifyFun = new VerifyFun($url);
		/*--------------------实名验证DEMO----------------------------------*/
		$result=$verifyFun->zXBank($sid, $name, $idnum, $vtype, $phone, $bankCard, $cpserialnum, $despwd,$md5key);
		$array = json_decode($result,1);
		switch ($array['result']) {
			case 'BANKCONSISTENT':
				if(!$GLOBALS['user_info']['idcardpassed']){
					$res = $this->addCard($idnum,$_POST['bank'],$name,$bankCard,$phone,$result['cpserialnum'],$result['sysserialnum'],$result['md5num'],$_POST['bank_code']);
					//发送邀请代金券
					$ecv_list = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."ecv_type WHERE (begin_time= 0 or begin_time < ".TIME_UTC.") AND (end_time= 0 or end_time +24*3600-1 > ".TIME_UTC.") AND  send_type=4");
					if(is_get_rewards($GLOBALS['user_info']['id']) && $res && $ecv_list){
						send_voucher($ecv_list['id'],$GLOBALS['user_info']['pid'],false,$ecv_list['money'],$GLOBALS['user_info']['id']);
					}
				}else{
					/*$res = $this->saveCard($_POST['bank'],$name,$bankCard,$phone,$result['cpserialnum'],$result['sysserialnum'],$result['md5num'],$_POST['bank_code']);*/
					$res = $this->saveCard($idnum,$_POST['bank'],$name,$bankCard,$phone,$result['cpserialnum'],$result['sysserialnum'],$result['md5num'],$_POST['bank_code']);
				}				
				if($res){
					
					$json['status'] = 1;
					$json['info'] = "银行卡绑定成功";
					ajax_return($json);
				}else{
					$json['status'] = 0;
					$json['info'] = "暂不支持此银行";
					ajax_return($json);
				}
				break;
			case 'BANKNOLIB':
				$json['status'] = 0;
				$json['info'] = "没有此银行卡信息";
				ajax_return($json);
				break;
			case 'BANKINCONSISTENT':
				$json['status'] = 0;
				$json['info'] = "银行卡信息不一致";
				ajax_return($json);
				break;
			case 'BANKUNKNOWN':
				$json['status'] = 0;
				$json['info'] = "银行卡信息未知";
				ajax_return($json);
				break;
			case 'FAIL':
				$info = $this->fail($array['errmsg']);
				$json['status'] = 0;
				$json['info'] = $info;
				ajax_return($json);
				break;
			default:
				# code...
				break;
		}
 	}
 	public function llpay_return_config(){
    	$wapllpay_config =array(
    		'oid_partner'=>'201408071000001543',
    		'RSA_PRIVATE_KEY'=>'-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQCmRl6Zn4MmtoBoelHRT6j6ounts/x1+GiJTB9/eBTl01cBK50h
mOUtGBcOVrJCa0C1NkR8BYgOT/WLfFT8cICw6XSJtf2uzZco71jbwXfFe8MiEx/L
XiQNQHuclpkUa1hXFUUo6Qat8X8L++pVZfjav40dPKf7oFWCYLWBCDOdyQIDAQAB
AoGANe0mqz4/o+OWu8vIE1F5pWgG5G/2VjBtfvHwWUARzwP++MMzX/0dfsWMXLsj
b0UnpF3oUizdFn86TLXTPlgidDg6h0RbGwMZou/OIcwWRzgMaCVePT/D1cuhyD7Y
V8YkjVHGnErfxyia1COswAqcpiS4lcTG/RqkAMsdwSZe640CQQDRvkQ7M2WJdydc
9QLQ9FoIMnKx9mDge7+aN6ijs9gEOgh1gKUjenLr6hcGlLRyvYDKQ4b1kes22FUT
/n+AMaEPAkEAyvH05KRzax3NNdRPI45N1KuT1kydIwL3KpOK6mWuHlffed2EiWLS
dhZNiZy9wWuwFPqkrZ8g+jL0iKcCD0mjpwJBAKbWxWmeCZ+eY3ZjAtl59X/duTRs
ekU2yoN+0KtfLG64RvBI45NkHLQiIiy+7wbyTNcXfewrJUIcNRjRcVRkpesCQEM8
BbX6BYLnTKUYwV82NfLPJRtKJoUC5n/kgZFGPnkvA4qMKOybIL6ehPGiS/tYge1x
XD1pCrPZTco4CiambuECQDNtlC31iqzSKmgSWmA5kErqVJB0f1i+a0CbQLlaPGYN
/qwa7TE13yByaUdDDaTIEUrDyuqWd5+IvlbwuVsSlMw=
-----END RSA PRIVATE KEY-----',
    		'key'=>'201408071000001543test_20140812',
    		'version'=>'1.2',
    		'app_request'=>'3',
    		'sign_type'=>strtoupper('MD5'),
    		'valid_order'=>'30',
    		'input_charset'=>strtolower('utf-8'),
    		'transport'=>'http',
    		);

    	return $wapllpay_config;
    }
 	public function orderId(){
 		$yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
		$orderSn = $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
		return $orderSn;

 	}
 	//添加银行卡
 	public function addCard($idnum,$bank,$name,$bankCard,$phone,$cpserialnum,$sysserialnum,$md5num,$bank_code){
 		/*$bank_info = $GLOBALS['db']->getOne("SELECT id FROM  ".DB_PREFIX."bank WHERE Banklist =".$bank_code);*/
 		/*$bank_info = $GLOBALS['db']->getOne("SELECT id FROM  ".DB_PREFIX."bank WHERE name like '%$bank'");
 		if($bank_info){*/
 			$user_info['user_id'] = $GLOBALS['user_info']['id'];
	 		$user_info['real_name'] = $name;
	 		$user_info['bankcard'] = $bankCard;
	 		$user_info['bank_id'] = $bank_code;
	 		$user_info['bank_mobile'] = $phone;
	 		$user_info['status'] = 1;
	 		$user_info['create_time'] = TIME_UTC;
	 		$res = $GLOBALS['db']->getOne("SELECT * FROM  ".DB_PREFIX."user_bank WHERE user_id=".$GLOBALS['user_info']['id']);
	 		if(!$res){
	 			$result	= $GLOBALS['db']->autoExecute(DB_PREFIX."user_bank",$user_info);
	 		}
	 		if(isset($result)){
				//绑卡成功处理渠道记录
				
					//addsource(0,0,$user_info['user_id'],2);
				
	 			$data['real_name'] = $name;
	 			$data['idno'] = $idnum;
	 			$data['idcardpassed']=1;
	 			$data['idcardpassed_time']=TIME_UTC;
	 			$data["real_name_encrypt"] = " AES_ENCRYPT('".$name."','".AES_DECRYPT_KEY."') ";
				$data["idno_encrypt"] = " AES_ENCRYPT('".$idnum."','".AES_DECRYPT_KEY."') ";
	 			return $GLOBALS['db']->autoExecute(DB_PREFIX."user",$data,"UPDATE","id=".$GLOBALS['user_info']['id']);
	 		}else{
	 			return false;
	 		}
 		/*}else{
 			return false;
 		}	*/
 		
 	}
 	//更换银行卡
 	public function saveCard($idnum,$bank,$name,$bankCard,$phone,$cpserialnum,$sysserialnum,$md5num,$bank_code){
 		/*$bank_info = $GLOBALS['db']->getOne("SELECT id FROM  ".DB_PREFIX."bank WHERE Banklist =".$bank_code);*/
 		/*$bank_info = $GLOBALS['db']->getOne("SELECT id FROM  ".DB_PREFIX."bank WHERE name like '%$bank'");
 		if($bank_info){*/
 			$user_info['user_id'] = $GLOBALS['user_info']['id'];
	 		$user_info['real_name'] = $name;
	 		$user_info['bankcard'] = $bankCard;
	 		$user_info['bank_id'] = $bank_code;
	 		$user_info['bank_mobile'] = $phone;
	 		$user_info['status'] = 1;
	 		$user_info['create_time'] = TIME_UTC;
	 		$res = $GLOBALS['db']->getOne("SELECT * FROM  ".DB_PREFIX."user_bank WHERE user_id=".$GLOBALS['user_info']['id']);
	 		if($res){
	 			$GLOBALS['db']->autoExecute(DB_PREFIX."user_bank",$user_info);
	 			$id = $GLOBALS['db']->insert_id();
	 			if($id > 0){
	 				$user_status['status'] = 0;
	 				$result=$GLOBALS['db']->autoExecute(DB_PREFIX."user_bank",$user_status,"UPDATE","id !=".$id." and user_id=".$GLOBALS['user_info']['id']);
	 				if(isset($result)){
	 					$data['real_name'] = $name;
			 			$data['idno'] = $idnum;
			 			$data['idcardpassed']=1;
			 			$data['idcardpassed_time']=TIME_UTC;
			 			$data["real_name_encrypt"] = " AES_ENCRYPT('".$name."','".AES_DECRYPT_KEY."') ";
						$data["idno_encrypt"] = " AES_ENCRYPT('".$idnum."','".AES_DECRYPT_KEY."') ";
			 			return $GLOBALS['db']->autoExecute(DB_PREFIX."user",$data,"UPDATE","id=".$GLOBALS['user_info']['id']);
	 				}else{
	 					return false;
	 				}
	 			}else{
	 				return false;
	 			}
	 		}else{
	 				return false;
	 		}
 	}
 	/*public function saveCard($bank,$name,$bankCard,$phone,$cpserialnum,$sysserialnum,$md5num,$bank_code){*/
 		/*$bank_info = $GLOBALS['db']->getOne("SELECT id FROM  ".DB_PREFIX."bank WHERE name like '%$bank'");*/
 		/*$bank_info = $GLOBALS['db']->getOne("SELECT id FROM  ".DB_PREFIX."bank WHERE Banklist =".$bank_code);*/
 		/*$res = $GLOBALS['db']->getOne("SELECT id FROM  ".DB_PREFIX."user_bank WHERE user_id=".$GLOBALS['user_info']['id']." order by id desc");
 		if($res){
 			$user_info['real_name'] = $name;
	 		$user_info['bankcard'] = $bankCard;
	 		$user_info['bank_id'] = $bank_code;
	 		$user_info['bank_mobile'] = $phone;
	 		$user_info['status'] = 1;
	 		$user_info['create_time'] = TIME_UTC;
	 		$user_info['region_lv2'] = "";
	 		$user_info['region_lv3'] = "";
	 		$user_info['region_lv4'] = "";
	 		$user_info['bankzone'] = "";
	 		$result=$GLOBALS['db']->autoExecute(DB_PREFIX."user_bank",$user_info,"UPDATE","id=".$res);
	 		if($result){
	 			return true;
	 		}else{
	 			return false;
	 		}
 		}else{
 			return false;
 		}
 	}*/
 	public function fail($err){
        switch ($err) {
            case 'ERR2011':
                $info = "身份证号码不存在";
                break;
            case 'ERR2012':
                $info = "数据错误，请稍后重试";
                break;
            case 'ERR2013':
                $info = "身份证号已被停用";
                break;
            case 'ERR2014':
                $info = "传输出错，请稍后重试";
                break;
            case 'ERR2015':
                $info = "身份证号无效";
                break;
            case 'ERR2016':
                $info = "无法验证该卡";
                break;
            case 'ERR2021':
                $info = "银行处理失败，请稍后重试";
                break;
            case 'ERR2020':
                $info = "卡号异常，请更换后重试";
                break;
            case 'ERR2022':
                $info = "姓名/身份证号/手机号不匹配";
                break;
            case 'ERR9999':
                $info = "未知错误，请稍后重试";
                break;
            default:
                # code...
                break;
        }
        return $info;
 	}
 	//修改交易密码
 	public function trade_password(){
 		if(!$GLOBALS['user_info']){
			showErr('请先登录',$is_ajax);
		}
		$sms_count = es_session::get('sms_count');
 		$user_id = $GLOBALS['user_info']['id'];
		$user_mobile =  $GLOBALS['db']->getOne("SELECT mobile FROM ".DB_PREFIX."user where id =".$user_id);
		$GLOBALS['tmpl']->assign("sms_count",$sms_count);
		$GLOBALS['tmpl']->assign("user_mobile",$user_mobile);		
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_get_trade_password.html");
		$GLOBALS['tmpl']->display("page/uc.html");
 	}
 	public function check_mobile_code(){
		if(!$_POST)
		{
			 app_redirect("404.html");
			 exit();
		}
		foreach($_POST as $k=>$v)
		{
			$data[$k] = htmlspecialchars(addslashes($v));
		}
		$user_id = $GLOBALS['user_info']['id'];
		$old = md5($data['old_pwd']);
		if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile=".strim($data['mobile'])." AND verify_code=".strim($data['sms_code']))==0){
			$return['status'] = 0;
			$return['info'] = "短信验证码错误";
			ajax_return($return);
		}
		if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".strim($data['mobile'])."' AND verify_code='".strim($data['sms_code'])."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ")==0){
			$return['status'] = 0;
			$return['info'] = "短信验证码失效，请重新获取";
			ajax_return($return);
		}
		$old_pwd =  $GLOBALS['db']->getOne("SELECT paypassword FROM ".DB_PREFIX."user where id =".$user_id);
		if(trim($old) != trim($old_pwd)){
			$return['status'] = 0;
			$return['info'] = "原始交易密码错误";
			ajax_return($return);
		}else{
			$return['status'] = 1;
			$return['info'] = "原始交易密码正确";
			ajax_return($return);
		}
	}
	public function check_save_trade_password(){
		/*$redis = new RedisCluster();
		$redis->connect(array('host'=>REDIS_HOST,'port'=>REDIS_PORT));*/
		if(!$_POST)
		{
			 app_redirect("404.html");
			 exit();
		}
		foreach($_POST as $k=>$v)
		{
			$data[$k] = htmlspecialchars(addslashes($v));
		}
		$user_pwd =  $GLOBALS['db']->getRow("SELECT paypassword,user_pwd FROM ".DB_PREFIX."user where id =".$GLOBALS['user_info']['id']);
		if($user_pwd['paypassword'] == md5($data['pwd'])){
			$return['status'] = 0;
			$return['info'] = "新密码不能和原密码相同哦";
			ajax_return($return);
		}
		if($user_pwd['user_pwd'] == md5($data['pwd'])){
			$return['status'] = 0;
			$return['info'] = "交易密码不能和登录密码相同";
			ajax_return($return);
		}
		$user_info['paypassword'] = md5($data['pwd']);
		$result	= $GLOBALS['db']->autoExecute(DB_PREFIX."user",$user_info,"UPDATE","id=".$GLOBALS['user_info']['id']);
		if($result){
			/*$redis->remove($GLOBALS['user_info']['mobile']);*/
			$return['status'] = 1;
			$return['info'] = "设置成功";
			ajax_return($return);
		}else{
			$return['status'] = 0;
			$return['info'] = "设置失败";
			ajax_return($return);
		}
	}
	//重置交易密码
	public function reset_trade_password(){
		if(!$GLOBALS['user_info']){
			showErr('请先登录',$is_ajax);
		}
		$user_id = $GLOBALS['user_info']['id'];
		$user_mobile =  $GLOBALS['db']->getOne("SELECT mobile FROM ".DB_PREFIX."user where id =".$user_id);
		$sms_count = es_session::get('sms_count');
		$GLOBALS['tmpl']->assign("sms_count",$sms_count);
		$GLOBALS['tmpl']->assign("user_mobile",$user_mobile);		
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_reset_trade_password.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	
	public function check_trade_mobile_code(){
		if(!$_POST)
		{
			 app_redirect("404.html");
			 exit();
		}
		foreach($_POST as $k=>$v)
		{
			$_POST[$k] = htmlspecialchars(addslashes($v));
		}
		$user_id = $GLOBALS['user_info']['id'];
		$old = md5($_POST['old_pwd']);
		if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".strim($_POST['mobile'])."' AND verify_code='".strim($_POST['sms_code'])."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ")==0){
			$return['status'] = 0;
			$return['info'] = "手机验证码出错,或已过期";
			ajax_return($return);
		}else{
			$return['status'] = 1;
			$return['info'] = "手机验证码正确";
			ajax_return($return);
		}
		
	}
	public function bank_info(){
		if(!$GLOBALS['user_info']){
			showErr('请先登录',$is_ajax);
		}
		$provinces_info =  $GLOBALS['db']->getAll("SELECT * FROM  ".DB_PREFIX."region_conf where region_level = 2 ");
		$GLOBALS['tmpl']->assign('provinces_info',$provinces_info);
		$city_info2 =  $GLOBALS['db']->getAll("SELECT * FROM  ".DB_PREFIX."region_conf where region_level = 3 ");
		$GLOBALS['tmpl']->assign('city_info2',$city_info2);
		$bank = $GLOBALS['db']->getRow("SELECT ub.id as bid,ub.bankzone,ub.bankcard,b.icon,b.name,ub.region_lv2,ub.region_lv3 FROM  ".DB_PREFIX."user_bank ub LEFT JOIN ".DB_PREFIX."bank b on ub.bank_id = b.bankid WHERE ub.id =".$_REQUEST['id'].' and ub.status=1 and ub.cunguan_tag=1');
		$region_lv2 = $GLOBALS['db']->getOne("SELECT name FROM ".DB_PREFIX."region_conf WHERE id =".$bank['region_lv2']);
		$region_lv3 = $GLOBALS['db']->getOne("SELECT name FROM ".DB_PREFIX."region_conf WHERE id =".$bank['region_lv3']);		
		if(!$region_lv2 && !$region_lv3){
			$city_code=false;
			$city = "请选择城市";
		}
		else{
			$city_code=true;
			$city = $region_lv2." ".$region_lv3;
		}		
		//$city_info = explode("-",$bank["bankzone"]);
		$city_info = $bank["bankzone"];
		$GLOBALS['tmpl']->assign('city_code',$city_code);
		$GLOBALS['tmpl']->assign('city',$city);
		$GLOBALS['tmpl']->assign('city_info',$city_info);
		$bank['bankcard'] = substr($bank['bankcard'],-4,4);
		$jumpUrl = es_cookie::get("jump_carry_bank");
		$GLOBALS['tmpl']->assign('jumpUrl',$jumpUrl);
		$GLOBALS['tmpl']->assign('bank',$bank);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_bank_info.html");
		$GLOBALS['tmpl']->display("page/uc.html");
 	}

 	public function set_bank_info(){
 		if(!$_POST)
		{
			 app_redirect("404.html");
			 exit();
		}
		foreach($_POST as $k=>$v)
		{
			$data[$k] = htmlspecialchars(addslashes($v));
		}
		$user_info['region_lv1']=1;
		$user_info['region_lv2']= $data['province_code'];
		$user_info['region_lv3']= $data['city_code'];
		$user_info['region_lv4']="";
		//$user_info['bankzone'] = preg_replace('# #', '', $data['city'])."-".$data['branch'];
		$user_info['bankzone'] = $data['branch'];
		$user_bank_info = $GLOBALS['db']->getOne("select region_lv2 from ".DB_PREFIX."user_bank where id=".$data['id']);
		$result	= $GLOBALS['db']->autoExecute(DB_PREFIX."user_bank",$user_info,"UPDATE","id=".$data['id']);
		if($result && $user_bank_info){
			$return['status'] = 1;
			$return['info'] = "开户行信息修改成功";
			ajax_return($return);
		}else if($result && !$user_bank_info){
			$return['status'] = 1;
			$return['info'] = "开户行信息添加成功";
			ajax_return($return);
		}else{
			$return['status'] = 0;
			$return['info'] = "开户行信息添加失败";
			ajax_return($return);
		}
 	}	
	public function assessment(){
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_assessment_list.html");
		$GLOBALS['tmpl']->assign("page_title","风险评估");
		$GLOBALS['tmpl']->display("page/uc.html");
	}	
	public function assessment_do(){
        $user_id=$GLOBALS['user_info']['id'];
        $score1=$_POST['score1'];
        $score2=$_POST['score2'];
        $score3=$_POST['score3'];
        $score4=$_POST['score4'];
        $score5=$_POST['score5'];
        $score6=$_POST['score6'];
        $score7=$_POST['score7'];
        $score8=$_POST['score8'];
        $score9=$_POST['score9'];
        $score10=$_POST['score10'];
        $score11=$_POST['score11'];
        $score12=$_POST['score12'];
        $data['user_id']=$user_id;
        $data['answer']=$score1.",".$score2.",".$score3.",".$score4.",".$score5.",".$score6.",".$score7.",".$score8.",".$score9.",".$score10.",".$score11.",".$score12;
        $data['score'] = $score1+$score2+$score3+$score4+$score5+$score6+$score7+$score8+$score9+$score10+$score11+$score12;
        $data['test_time'] = time();
        $data['status']='1';
        $request=$GLOBALS['db']->getOne("select * from ".DB_PREFIX."wenjuan_user_answer_record where user_id =".$user_id);
        if (!empty($request)) {
            $res = $GLOBALS['db']->autoExecute(DB_PREFIX."wenjuan_user_answer_record",$data,"UPDATE","user_id=".$user_id);
        }else{
            $res = $GLOBALS['db']->autoExecute(DB_PREFIX."wenjuan_user_answer_record",$data);
        }

        if($res){
            if(12 <= $data['score'] && $data['score'] <=24){
                $info1 = "保守型";
                $info2 = "此类出借人风险偏好较低，愿意用较小的风险来获得确定的收益。此类出借人愿意承受或能承受少许本金的损失和波动。";
            }else if(25 <= $data['score'] && $data['score'] <=36){
                $info1 = "稳健型";
                $info2= "此类出借人愿意承担一定程度的风险，强调出借风险和资产升值之间的平衡，主要出借目标是资产的升值，为实现目标往往愿意承担相当程度的风险，比较适合组合出借";
            }else if(37 <= $data['score'] && $data['score'] <=60){
                $info1 = "积极型";
                $info2 = "此类出借人为了获得高回报的出借收益，能够承受出借产品价格的显著波动，主要出借目标是实现资产升值，为实现目标往往愿意承担相当程度的风险，此类出借人可以承受一定的资产波动风险和本金亏损风险。";
            }
        }
                $GLOBALS['tmpl']->assign('info2',$info2);
                $GLOBALS['tmpl']->assign('info1',$info1);
		$info =  $GLOBALS["tmpl"]->fetch("inc/uc/uc_assessment_success.html");
        // 风险评估奖励成长值
        $is_get=$GLOBALS['db']->getOne("select id from ".DB_PREFIX."user_grow_point where user_id=".$user_id." and task_type=5");
        if(!$is_get){
            require_once APP_ROOT_PATH."system/user_level/Level.php";
            $level=new Level();
            $level->get_grow_point(5);
        }

		showSuccess($info,1);
	}
}
?>