<?php
define(ACTION_NAME,"user");
define(ACTN,"login_reg");
define(MODULE_NAMEN,"index");
class userModule extends SiteBaseModule
{
	public function register_success(){
		$GLOBALS['tmpl']->display("success.html");
	}
	public function extend_register(){
		$login_info = es_session::get("user_info");
		if($login_info)
		{
			app_redirect(url("index"));		
		}
		$code=$_REQUEST['code'];
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['USER_REGISTER']);
		
		$field_list =load_auto_cache("user_field_list");
		foreach($field_list as $k=>$v){
			if($v['is_show']==0){
				unset($field_list[$k]);
			}
		}
		
		$api_uinfo = es_session::get("api_user_info");
		$GLOBALS['tmpl']->assign("code",$code);
		$GLOBALS['tmpl']->assign("reg_name",$api_uinfo['name']);
		
		$GLOBALS['tmpl']->assign("field_list",$field_list);
		
		$GLOBALS['tmpl']->assign("agreement",app_conf("AGREEMENT"));
		$GLOBALS['tmpl']->assign("privacy",app_conf("PRIVACY"));
		$referer = "";
		if(isset($_REQUEST['r'])){
			$referer = strim(base64_decode($_REQUEST['r']));
		}
		$GLOBALS['tmpl']->assign("referer",$referer);
		$GLOBALS['tmpl']->assign("ACT",ACTN);
		$GLOBALS['tmpl']->display("extend_register.html");
	}
	public function register()
	{	
		$login_info = es_session::get("user_info");
		if($login_info)
		{
			app_redirect(url("index"));		
		}
		$code=$_REQUEST['code'];
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['USER_REGISTER']);
		
		$field_list =load_auto_cache("user_field_list");
		foreach($field_list as $k=>$v){
			if($v['is_show']==0){
				unset($field_list[$k]);
			}
		}
		
		$api_uinfo = es_session::get("api_user_info");
		$GLOBALS['tmpl']->assign("code",$code);
		$GLOBALS['tmpl']->assign("id",$_GET['id']);
		$GLOBALS['tmpl']->assign("reg_name",$api_uinfo['name']);
		$GLOBALS['tmpl']->assign("field_list",$field_list);
		
		$GLOBALS['tmpl']->assign("agreement",app_conf("AGREEMENT"));
		$GLOBALS['tmpl']->assign("privacy",app_conf("PRIVACY"));
		$referer = "";
		if(isset($_REQUEST['r'])){
			$referer = strim(base64_decode($_REQUEST['r']));
		}
		$GLOBALS['tmpl']->assign("referer",$referer);
		$GLOBALS['tmpl']->assign("ACT",ACTN);
		$GLOBALS['tmpl']->display("user_step_one.html");
	}
	public function wapRegister()
	{	
		$jump_url = "https://wapcg.9caitong.com/index.php?ctl=find&act=W644&code=".$_REQUEST['code'];
		header('Location:'.$jump_url);	
		$login_info = es_session::get("user_info");
		if($login_info)
		{
			app_redirect(url("index"));		
		}
		
		if (strlen($_REQUEST['code']) > 11) {
			$code=base64_decode($_REQUEST['code']);
		}else{
			$code=$_REQUEST['code'];
		}
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['USER_REGISTER']);
		
		$field_list =load_auto_cache("user_field_list");
		foreach($field_list as $k=>$v){
			if($v['is_show']==0){
				unset($field_list[$k]);
			}
		}
		
		$api_uinfo = es_session::get("api_user_info");
		$GLOBALS['tmpl']->assign("code",$code);
		$GLOBALS['tmpl']->assign("reg_name",$api_uinfo['name']);
		
		$GLOBALS['tmpl']->assign("field_list",$field_list);
		
		$GLOBALS['tmpl']->assign("agreement",app_conf("AGREEMENT"));
		$GLOBALS['tmpl']->assign("privacy",app_conf("PRIVACY"));
		$referer = "";
		if(isset($_REQUEST['r'])){
			$referer = strim(base64_decode($_REQUEST['r']));
		}
		$stats = site_statics();
		$wapregistered_user = strip_tags(number_format($stats['user_count']));//注册用户数
		$GLOBALS['tmpl']->assign('wapregistered_user',$wapregistered_user); //累计注册用户
		$GLOBALS['tmpl']->assign("referer",$referer);
		$GLOBALS['tmpl']->assign("ACT",ACTN);
		$GLOBALS['tmpl']->display("wapRegister.html");
	}
	public function debitregister(){
		
		$GLOBALS['tmpl']->display("debitregister.html");
	}
	public function agreement(){
		$GLOBALS['tmpl']->display("page/user_agreement.html");
	}
	public function repayment_agreement(){
		$GLOBALS['tmpl']->display("page/repayment_agreement.html");
	}
	public function getregister(){
		$mobile = strim($_REQUEST['mobile']);
		$info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."user WHERE mobile='".$mobile."'");
		if($info  > 0){
			$return['status'] = 1;
			$return['info'] = "手机号码已被注册";
			ajax_return($return);
		}
	}


	public function doreferer(){
		$user_data = $_POST;
		foreach($user_data as $k=>$v)
		{
			$user_data[$k] = htmlspecialchars(addslashes($v));
		}
		//if(get_user_info("count(*)","mobile_encrypt = AES_ENCRYPT('".$user_data['referer']."','".AES_DECRYPT_KEY."') OR user_name='".$user_data['referer']."'","ONE") > 0){
		//zhuxiang  2017 513
        if(get_user_info("count(*)","mobile_encrypt = AES_ENCRYPT('".$user_data['referer']."','".AES_DECRYPT_KEY."')","ONE") > 0){
			$result['status'] = 1;
			$result['info'] = "推荐人正确";
			ajax_return($result);
		}else{
			$result['info'] = "推荐人不存在";
			ajax_return($result);
		}
	}

	public function doregister()
	{
		$switch_conf = $GLOBALS['db']->getAll("SELECT status FROM ".DB_PREFIX."switch_conf where switch_id=1 or switch_id=2");
		foreach ($switch_conf as $k => $v) {
			if($v['status'] != 1){
				$return['status'] = 0;
				$return['info'] = "系统正在升级，请稍后再试";
				ajax_return($return);
			}
		}
		//注册验证码
		if(intval(app_conf("VERIFY_IMAGE")) == 1 && intval(app_conf("USER_VERIFY")) >= 3){
			$verify = strim($_REQUEST['verify']);
			if(!checkVeifyCode($verify))
			{
				showErr($GLOBALS['lang']['VERIFY_CODE_ERROR'],0,url("shop","user#register"));
			}
		}
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
		//防止wap冲突
		if(trim($user_data['user_pwd_confirm']) != ""){
			if(trim($user_data['user_pwd'])!=trim($user_data['user_pwd_confirm']))
			{
				// $return['status'] = 1;
				$return['info'] = $GLOBALS['lang']['USER_PWD_CONFIRM_ERROR'];
				ajax_return($return);
				//showErr($GLOBALS['lang']['USER_PWD_CONFIRM_ERROR']);
			}
		}
		//判断验证码是否正确
		if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".strim($user_data['mobile'])."' AND verify_code='".strim($user_data['sms_code'])."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ")==0){
		    // $return['status'] = 1;
		    $return['info'] = "手机验证码出错,或已过期";
		    ajax_return($return);
		    //showErr("手机验证码出错,或已过期");
		}
		
		if(trim($user_data['user_pwd'])=='')
		{	
			// $return['status'] = 1;
			$return['info'] = $GLOBALS['lang']['USER_PWD_ERROR'];
			ajax_return($return);
			//showErr($GLOBALS['lang']['USER_PWD_ERROR']);
		}
		if(trim($user_data['user_pwd'])!=''&&strlen(trim($user_data['user_pwd']))<8)
		{	
				$return['info'] = "密码长度不够";
				ajax_return($return);
			
			//showErr($GLOBALS['lang']['USER_PWD_ERROR']);
		}
		
		
		if(isset($user_data['referer']) && $user_data['referer']!=""){
			//$p_user_data = get_user_info("id,user_type","mobile_encrypt =AES_ENCRYPT('".$user_data['referer']."','".AES_DECRYPT_KEY."') OR user_name='w".$user_data['referer']."'");
			
            $p_user_data = get_user_info("id,user_type","mobile_encrypt =AES_ENCRYPT('".$user_data['referer']."','".AES_DECRYPT_KEY."')");
		
			if($p_user_data["user_type"] == 3)
			{
				$user_data['referer_memo'] = $p_user_data['id'];
				//$user_data['pid'] = $p_user_data['id'];
				$user_data['pid'] = 0;
			}
			elseif($p_user_data["user_type"] < 2)
			{
				$user_data['pid'] = $p_user_data["id"];
				if($user_data['pid'] > 0){
					$refer_count = get_user_info("count(*)","pid='".$user_data['pid']."' ","ONE");
					if($refer_count == 0){
						$user_data['referral_rate'] = (float)trim(app_conf("INVITE_REFERRALS_MIN"));
					}
					elseif((float)trim(app_conf("INVITE_REFERRALS_MIN")) + $refer_count*(float)trim(app_conf("INVITE_REFERRALS_RATE")) > (float)trim(app_conf("INVITE_REFERRALS_MAX"))){
						$user_data['referral_rate'] =(float)trim(app_conf("INVITE_REFERRALS_MAX"));
					}
					else{
						$user_data['referral_rate'] =(float)trim(app_conf("INVITE_REFERRALS_MIN")) + $refer_count*(float)trim(app_conf("INVITE_REFERRALS_RATE"));
					}
						
					
					if(intval(app_conf("REFERRAL_IP_LIMIT")) > 0 && get_user_info("count(*)","register_ip ='".CLIENT_IP."' AND pid='".$user_data['pid']."'","ONE") > 0){
						$user_data['referral_rate'] = 0;
					}
				}
				else{
					$user_data['pid'] = 0;
					$user_data['referer_memo'] = $user_data['referer'];
				}
			}
		}
		//避免手机重复注册
		$info = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."user WHERE mobile ='".$user_data['mobile']."'");
		if($info  > 0){
			$data['info'] = "手机号码已被注册";
			ajax_return($data);
		}else if($user_data['mobile']==''){
			$return['info'] = "手机号不能为空";
			ajax_return($return);
		}
//		// 判断邀请码是否有效
//		if(isset($user_data['referer'])&&$user_data['referer']!=''){
//			$p_user_id = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."user WHERE mobile ='".$user_data['referer']."'");
//			if(empty($p_user_id)){
//				$return['info'] = "邀请码不存在";
//				ajax_return($return);
//			}
//		}
		//判断是否为手机注册
		if((app_conf("REGISTER_TYPE") == 0 || app_conf("REGISTER_TYPE") == 1) && (app_conf("USER_VERIFY") == 0 || app_conf("USER_VERIFY") == 2)){
			if(strim($user_data['sms_code']) == ""){
				// $return['status'] = 1;
				$return['info'] = "请输入手机验证码";
				ajax_return($return);
				//showErr("请输入手机验证码");
			}
			
			$user_data['is_effect'] = 1;
			$user_data['mobilepassed'] = 1;
			$user_data["mobile_encrypt"] = " AES_ENCRYPT('".$user_data['mobile']."','".AES_DECRYPT_KEY."') ";
		}
		//判断是否为邮箱注册
		/*if((app_conf("REGISTER_TYPE") == 0 || app_conf("REGISTER_TYPE") == 2) && (app_conf("USER_VERIFY") == 1 || app_conf("USER_VERIFY") == 2)){
			
			if(strim($user_data['emsms_code'])==""){
				showErr("请输入邮箱验证码");
			}
			//判断验证码是否正确
			if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."email_verify_code WHERE email='".strim($user_data['email'])."' AND verify_code='".strim($user_data['emsms_code'])."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ")==0){
				showErr("邮箱验证码出错,或已过期");
			}
			$user_data['is_effect'] = 1;
			$user_data['emailpassed'] = 1;
				
		}*/
		// 判断是否已注册
		/*$self_user_id=get_user_info("id","mobile_encrypt =AES_ENCRYPT('".$user_data['mobile']."','".AES_DECRYPT_KEY."') OR user_name='w".$user_data['mobile']."'","ONE");
		if($user_data['mobile']==''){
			$return['info'] = "手机号不能为空";
			ajax_return($return);
		}elseif($self_user_id>0){
				$return['info'] = "该手机号已被注册";
				ajax_return($return);
		}*/
		//避免手机重复注册
		$info = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."user WHERE mobile ='".$user_data['mobile']."'");
		if($info  > 0){
			$data['info'] = "手机号码已被注册";
			ajax_return($data);
		}else if($user_data['mobile']==''){
			$return['info'] = "手机号不能为空";
			ajax_return($return);
		}
		// 判断邀请码是否有效
		if(isset($user_data['referer'])&&$user_data['referer']!=''){
			$p_user_id = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."user WHERE mobile ='".$user_data['referer']."'");
			if(!$p_user_id){
				$return['info'] = "邀请码不存在";
				ajax_return($return);
			}
		}
		$res = save_user($user_data);
		/*if($_REQUEST['subscribe']==1)
		{
			//订阅
			if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."mail_list where mail_address = '".$user_data['email']."'")==0)
			{
				$mail_item['city_id'] = intval($_REQUEST['city_id']);
				$mail_item['mail_address'] = $user_data['email'];
				$mail_item['is_effect'] = app_conf("USER_VERIFY");
				$GLOBALS['db']->autoExecute(DB_PREFIX."mail_list",$mail_item,'INSERT','','SILENT');
			}
			if($user_data['mobile']!=''&&$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."mobile_list where mobile = '".$user_data['mobile']."'")==0)
			{
				$mobile['city_id'] = intval($_REQUEST['city_id']);
				$mobile['mobile'] = $user_data['mobile'];
				$mobile['is_effect'] = app_conf("USER_VERIFY");
				$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_list",$mobile,'INSERT','','SILENT');
			}
			
		}
		*/
		if($res['status'] == 1)
		{
			// 邀请人数量加1
			// $res_ref=$GLOBALS['db']->query("update ".DB_PREFIX."user set referral_count=referral_count+1 where id=".$p_user_id);
   //          if($res_ref&&$p_user_id){
   //              $order_data['begin_time'] = TIME_UTC;
   //              $order_data['end_time'] = to_timespan(to_date($order_data['begin_time'])." ".app_conf("INTERESTRATE_TIME")." month -1 day");
   //              $order_data['money'] = 20;
   //              $order_data['ecv_type_id'] = 5;
   //              $sn = unpack('H12',str_shuffle(md5(uniqid())));
   //              $order_data['sn'] = $sn[1];
   //              $order_data['password'] = rand(10000000,99999999);
   //              $order_data['user_id']=$p_user_id;
   //              $order_data['child_id']=$GLOBALS['user_info']['id'];
   //              $order_data['content']="邀请好友奖励代金券！";
   //              $order_data['cunguan_tag']=1;
   //              $check=$GLOBALS['db']->getOne("select id from ".DB_PREFIX."ecv where user_id=".$GLOBALS['user_info']['pid']." and child_id=".$GLOBALS['user_info']['id']);
   //              if(empty($check)){
   //                  $result=$GLOBALS['db']->autoExecute(DB_PREFIX."ecv",$order_data,"INSERT");
   //                  if($result==false){
   //                      showErr("代金券发放失败",1);
   //                  }
   //              }
   //          }
			
			
			// 注册成功站内信
			$user_id = intval($res['data']);
			$notices['site_name'] = app_conf("SHOP_TITLE");
			$notices['user_name'] = $user_data['mobile'];
			$notices['time'] = to_date(TIME_UTC,"Y年m月d日 H:i");
			$time=TIME_UTC;
			$tmpl_contents = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_INS_REGISTER_SUCCESS_MSG'",false);
			$GLOBALS['tmpl']->assign("notice",$notices);
			$content = $GLOBALS['tmpl']->fetch("str:".$tmpl_contents['content']);
			
			send_user_msg("恭喜您获得84442体验金+512红包",$content,0,$user_id,$time,0,true,21);

			// 给用户发送短信通知
            if(app_conf("SMS_ON")==1)
            {
                $user_info=$GLOBALS['db']->getRow("select user_name,mobile from ".DB_PREFIX."user where id=".$user_id);
                $tmpl_content =  $GLOBALS['db']->getOne("select content from ".DB_PREFIX."msg_template where name = 'TPL_SMS_REGISTER_SUCCESS'");
                // $notice['user_name'] = $user_info['user_name'];
                // $notice['release_date'] = to_date(TIME_UTC,"Y-m-d");
                // $notice['site_name'] = app_conf("SHOP_TITLE");
                // // $notice['recharge_money'] = round($storage['money'],2);
                // $GLOBALS['tmpl']->assign("notice",$notice);
                $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);

                $msg_data['dest'] = $user_info['mobile'];
                $msg_data['send_type'] = 0;
                $msg_data['title'] = "注册成功短信通知";
                $msg_data['content'] = addslashes($msg);
                $msg_data['send_time'] = time();
                $msg_data['is_send'] = 0;
                $msg_data['create_time'] = TIME_UTC;
                $msg_data['user_id'] = $user_id;
                $msg_data['is_html'] = 0;
                send_sms_email($msg_data);
                $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
            }

			//更新来路
			//$GLOBALS['db']->query("update ".DB_PREFIX."user set referer = '".$GLOBALS['referer']."' where id = ".$user_id);
			/*$user_info = get_user_info("is_effect","id = ".$user_id);
			if($user_info['is_effect']==1)
			{*/
				//在此自动登录
				$result = do_login_user($user_data['user_name'],$user_data['user_pwd']);
                // 注册成功成长值
                require_once APP_ROOT_PATH."system/user_level/Level.php";
                $level=new Level();
                $level->get_grow_point(1);

				$return['status'] = 1;
				//$return['info'] = "注册成功<br/>恭喜您已获得8888元体验金+518元红包，请到我的账户查看!";
				$return['info'] = $GLOBALS["tmpl"]->fetch("reg_successTip.html");
				//$return['msg'] = "8888元注册体验金+16666元分享体验金+58888出借体验金+50元代金券";
				$return['jump'] = url("index","user#steptwo");
				ajax_return($return);
				/*$GLOBALS['tmpl']->assign('integrate_result',$result['msg']);				
				app_redirect(url("index","user#steptwo"));*/
			/*}
			else{
				showSuccess($GLOBALS['lang']['WAIT_VERIFY_USER'],0,APP_ROOT."/");
			}*/
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
			showErr($error_msg);
		}
	}
	
	public function login()
	{
		$count = es_session::get("check_login_count");
		$mobile = es_session::get("mobile");
		$login_info = es_session::get("user_info");
		if($login_info)
		{
			app_redirect(url("index"));		
		}
		if(!$count){
			$GLOBALS['tmpl']->assign("count","1");
		}else{
			$GLOBALS['tmpl']->assign("count",$count);
		}
		$jump_url = explode("jumpUrl=",$_SERVER['REQUEST_URI']);
		$GLOBALS['tmpl']->assign("jump_url",$jump_url[1]);	
		$GLOBALS['tmpl']->assign("mobile",$mobile);			
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['USER_LOGIN']);
		$GLOBALS['tmpl']->assign("CREATE_TIP",$GLOBALS['lang']['REGISTER']);
		$GLOBALS['tmpl']->assign("ACTION_NAME",ACTION_NAME);
		$GLOBALS['tmpl']->assign("MODULE_NAMEN",MODULE_NAMEN);
		$GLOBALS['tmpl']->assign("ACT",ACTN);
		
		$GLOBALS['tmpl']->display("user_login.html");
	}
	public function api_login()
	{

		$s_api_user_info = es_session::get("api_user_info");
		if($s_api_user_info)
		{
			 
			$GLOBALS['tmpl']->assign("page_title",$s_api_user_info['name'].$GLOBALS['lang']['HELLO'].",".$GLOBALS['lang']['USER_LOGIN_BIND']);
			$GLOBALS['tmpl']->assign("CREATE_TIP",$GLOBALS['lang']['REGISTER_BIND']);
			$GLOBALS['tmpl']->assign("api_callback",true);
			$GLOBALS['tmpl']->display("user_login.html");
		}
		else
		{
			showErr($GLOBALS['lang']['INVALID_VISIT']);
		}
	}	
	public function dologin()
	{

		
        require_once APP_ROOT_PATH."system/user_level/Level.php";
        $level=new Level();
		$switch_conf = $GLOBALS['db']->getAll("SELECT status FROM ".DB_PREFIX."switch_conf where switch_id=1 or switch_id=3");
		foreach ($switch_conf as $k => $v) {
			if($v['status'] != 1){
				$return['status'] = 0;
				$return['info'] = "系统正在升级，请稍后再试";
				ajax_return($return);
			}
		}
		if(!$_POST)
		{
			 app_redirect("404.html");
			 exit();
		}
		foreach($_POST as $k=>$v)
		{
			$_POST[$k] = htmlspecialchars(addslashes($v));
		}
		$ajax = intval($_REQUEST['ajax']);
		if(!check_hash_key()){
			showErr("非法请求!",$ajax);
		}
		
		//企业用户不让登录
		$host = $_SERVER['HTTP_HOST'];
		
        $ret = $GLOBALS['db']->getAll("SELECT id FROM ".DB_PREFIX."user where mobile=".$_POST['mobile']."  and   user_type=1");
        
        if($ret &&  strpos($host,'wap')){
        	showErr("企业用户暂不支持WAP登录，请从PC和APP存管版登录",$ajax,url("shop","user#login"));
        }
		//验证码
		
		/* if($_REQUEST['verify'] != "")
		{
			$verify = strim($_REQUEST['verify']);
			if(!checkVeifyCode($verify))
			{				
				showErr($GLOBALS['lang']['VERIFY_CODE_ERROR'],$ajax,url("shop","user#login"));
			}
		}else{
            showErr("验证码不能为空!",$ajax);
        } */
        //账号是否被封
        $is_ban = $GLOBALS['db']->getRow("SELECT id,is_effect FROM ".DB_PREFIX."user where mobile=".$_POST['mobile']);
        if($is_ban && $is_ban['is_effect'] < 1){
            $return['status'] = 0;
            $return['info'] = "您的账号异常，请联系客服";
            ajax_return($return);
        }
        
		require_once APP_ROOT_PATH."system/libs/user.php";
		if(es_session::get("mobile")){
			es_session::delete("mobile");
			es_session::set("mobile",$_POST['mobile']);
		}else{
			es_session::set("mobile",$_POST['mobile']);
		}		
		$_POST['user_pwd'] = trim(FW_DESPWD($_POST['user_pwd']));
		if(/*intval(es_session::get("check_login_count")) <= 3 && */check_ipop_limit(CLIENT_IP,"user_dologin",intval(app_conf("SUBMIT_DELAY")))){
			$result = do_login_user($_POST['mobile'],$_POST['user_pwd']);
		}
		else
			showErr($GLOBALS['lang']['SUBMIT_TOO_FAST'],$ajax,url("shop","user#login"));
		if($result['status'])
		{
			es_session::set("check_login_count",0);	
			$s_user_info = es_session::get("user_info");
			if(intval($_POST['auto_login'])==1)
			{
				//自动登录，保存cookie
				$user_data = $s_user_info;
				$ck_user_name = "";
				if($user_data['email']==""){
					$ck_user_name = $user_data['user_name'] ;
				}
				if($ck_user_name==""){
					$ck_user_name = $user_data['mobile'] ;
				}
				es_cookie::set("user_name",$ck_user_name,3600*24*30);		
				es_cookie::set("user_pwd",md5($user_data['user_pwd']."_EASE_COOKIE"),3600*24*30);

			}
			if($ajax == 0 && trim(app_conf("INTEGRATE_CODE")) ==''){
				$redirect = $_SERVER['HTTP_REFERER']?$_SERVER['HTTP_REFERER']:url("index");
				app_redirect($redirect);
			}else{	
				if($_REQUEST['jump_url']){
					$jump_url = $_REQUEST['jump_url'];
				}else{
					es_session::set("before_login",$_SERVER['HTTP_REFERER']);
					$jump_url = get_gopreview();
				}
				$s_user_info = es_session::get("user_info");
				if (WAP == 1) {
					if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."session WHERE user_id =".$GLOBALS['user_info']['id']) == 0){
						$sess['user_id'] = $GLOBALS['user_info']['id'];
						$sess['session_id'] = es_session::id();
						$sess['session_data'] = date("Y-m-d H:i:s",TIME_UTC);
						$sess['session_time'] = TIME_UTC;
						$GLOBALS['db']->autoExecute(DB_PREFIX."session",$sess,"INSERT");
					}else{
						$sess['session_id'] = es_session::id();
						$sess['session_data'] = date("Y-m-d H:i:s",TIME_UTC);
						$sess['session_time'] = TIME_UTC;
						$GLOBALS['db']->autoExecute(DB_PREFIX."session",$sess,"UPDATE","user_id=".$GLOBALS['user_info']['id']);
					}					 
				}
				// 使用玖财通时长
	            $use_days=ceil((time()-$GLOBALS['user_info']['create_time'])/3600/24);
	            if($use_days>=100){
	                $task_type=14;
	            }elseif($use_days>=60){
	                $task_type=13;
	            }elseif($use_days>=30){
	                $task_type=12;
	            }elseif($use_days>=7){
	                $task_type=11;
	            }
	            $is_get_reward = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user_grow_point where user_id=".$GLOBALS['user_info']['id']." and task_type=".$task_type);
	            if(!$is_get_reward){
	                // 登录奖励成长值
	                $level->get_grow_point(11,$use_days);
	            }
	            // 3月未增加成长值扣除10%成长值
	            $last_get_grow=$GLOBALS['db']->getOne("select create_time from ".DB_PREFIX."user_grow_point where user_id=".$GLOBALS['user_info']['id']." order by id desc limit 1");
	            if((time()-$last_get_grow)/3600/24/30>=3){
	                $level->get_grow_point(19);
	            }
				/*if($s_user_info['ips_acct_no']== null && app_conf("OPEN_IPS")){
					if($ajax==1){
						$return['status'] = 2;
						$return['info'] = "本站需绑定第三方托管账户，是否马上去绑定";
						$return['data'] = $result['msg'];
						$return['jump'] = $jump_url;
						$return['jump1'] = APP_ROOT."/index.php?ctl=collocation&act=CreateNewAcct&user_type=0&user_id=".$s_user_info['id'];
						ajax_return($return);
					}else{
						$GLOBALS['tmpl']->assign('integrate_result',$result['msg']);					
						showSuccess($GLOBALS['lang']['LOGIN_SUCCESS'],$ajax,$jump_url);
					}
				}else{*/
					if($ajax==1){
						$return['status'] = 1;
						$return['info'] = $GLOBALS['lang']['LOGIN_SUCCESS'];
						$return['data'] = $result['msg'];
						$return['jump'] = $jump_url;
						ajax_return($return);
					}else{
						$GLOBALS['tmpl']->assign('integrate_result',$result['msg']);					
						showSuccess($GLOBALS['lang']['LOGIN_SUCCESS'],$ajax,$jump_url);
					}
				/*}*/
			}
			
		}else{
			if($result['data'] == ACCOUNT_NO_EXIST_ERROR){
				$err = $GLOBALS['lang']['USER_NOT_EXIST'];
			}
			if($result['data'] == ACCOUNT_PASSWORD_ERROR){	
				$err = $GLOBALS['lang']['PASSWORD_ERROR'];
			}
			if($result['data'] == ACCOUNT_NO_VERIFY_ERROR){
				$err = $GLOBALS['lang']['USER_NOT_VERIFY'];
				if(app_conf("MAIL_ON")==1&&$ajax==0){				
					$GLOBALS['tmpl']->assign("page_title",$err);
					$GLOBALS['tmpl']->assign("user_info",$result['user']);
					$GLOBALS['tmpl']->display("verify_user.html");
					exit;
				}
			}			
			if(es_session::is_set("check_login_count")){
				$check_count = es_session::get("check_login_count");
			}else{
				$check_count = 0;
			}
	
			es_session::set("check_login_count",$check_count+1);			
			showErr($err,$ajax);
			
		}
	}
	
	
	
	public function steptwo(){
		if(intval($GLOBALS['user_info']['id'])==0)
		{
			es_session::set('before_login',$_SERVER['REQUEST_URI']);
			app_redirect(url("shop","user#login"));
		}
		
	   if($GLOBALS['user_info']['idno']!=""){
	        showErr("操作失败");
	    }
		$bank_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."bank where is_rec=1 and id not in(45,46,47,48,49,50,51) ORDER BY is_rec DESC,sort DESC,id ASC");
        $GLOBALS['tmpl']->assign("bank_list",$bank_list);
		$GLOBALS['tmpl']->display("user_step_two.html");
		exit;
	}
	public function stepthree(){
		if(intval($GLOBALS['user_info']['id'])==0)
		{
			es_session::set('before_login',$_SERVER['REQUEST_URI']);
			app_redirect(url("shop","user#login"));
		}
		if($GLOBALS['user_info']['paypassword']!=""){
	        showErr("操作失败");
	    }
	    $GLOBALS['tmpl']->display("user_step_three.html");
		exit;
	}
	
	public function stepsave(){
		if(intval($GLOBALS['user_info']['id'])==0)
		{
			es_session::set('before_login',$_SERVER['REQUEST_URI']);
			app_redirect(url("shop","user#login"));
		}
		$user_id=intval($GLOBALS['user_info']['id']);
		$focus_list = explode(",",$_REQUEST['user_ids']);
		foreach($focus_list as $k=>$focus_uid)
		{
			if(intval($focus_uid) > 0){
				$focus_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id." and focused_user_id = ".intval($focus_uid));
				if(!$focus_data)
				{
						$focused_user_name = get_user_info("user_name","id = ".$focus_uid,"ONE");
						$focus_data = array();
						$focus_data['focus_user_id'] = $user_id;
						$focus_data['focused_user_id'] = $focus_uid;
						$focus_data['focus_user_name'] = $GLOBALS['user_info']['user_name'];
						$focus_data['focused_user_name'] = $focused_user_name;
						$GLOBALS['db']->autoExecute(DB_PREFIX."user_focus",$focus_data,"INSERT");
						$GLOBALS['db']->query("update ".DB_PREFIX."user set focus_count = focus_count + 1 where id = ".$user_id);
						$GLOBALS['db']->query("update ".DB_PREFIX."user set focused_count = focused_count + 1 where id = ".$focus_uid);
				}
			}
		}		
		showSuccess($GLOBALS['lang']['REGISTER_SUCCESS'],0,url("shop","uc_center"));
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
			if(WAP == 1){
				$before_loginout = url("index","user#login");
			}else{
				$before_loginout = $_SERVER['HTTP_REFERER']?$_SERVER['HTTP_REFERER']:url("index");
			}			
			if(trim(app_conf("INTEGRATE_CODE"))=='')
			{
				app_redirect($before_loginout);
			}
			else
			showSuccess($GLOBALS['lang']['LOGINOUT_SUCCESS'],0,$before_loginout);
		}
		else
		{
			app_redirect(url("index"));		
		}
	}
	
	public function getpassword()
	{
		$GLOBALS['tmpl']->caching = true;
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.$GLOBALS['deal_city']['id']);		
		if (!$GLOBALS['tmpl']->is_cached('user_get_password.html', $cache_id))	
		{
			 
			$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['GET_PASSWORD_BACK']);
		}
		$sms_count = es_session::get('sms_count');
		$GLOBALS['tmpl']->assign("sms_count",$sms_count);
		$GLOBALS['tmpl']->assign("ACT",ACTN);
		$GLOBALS['tmpl']->display("user_get_password.html",$cache_id);
	}
	
	public function send_password()
	{
		$email = addslashes(trim($_REQUEST['email']));
		$user_pwd = strim($_REQUEST['pwd_m']);
		$sms_code =strim($_REQUEST['sms_codes']);
				
		if(!check_email($email))
		{
			showErr($GLOBALS['lang']['MAIL_FORMAT_ERROR']); //没输入邮件
		}

		if(get_user_info("count(*)","email_encrypt =AES_ENCRYPT('".$email."','".AES_DECRYPT_KEY."')","ONE") == 0)
		{
			showErr($GLOBALS['lang']['NO_THIS_MAIL']);  //无此邮箱用户
		}
		if($sms_code==""){
			showErr("请输入手机验证码",1);
		}
		
		if($user_pwd==""){
			showErr("请输入密码",1);
		}
		
		$yanzheng = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."email_verify_code WHERE email='".$email."' AND verify_code='".$sms_code."'");
		
		if(!$yanzheng){
			showErr("邮箱验证码出错",1);
		}
		
		if($user_info = get_user_info("*","email_encrypt =AES_ENCRYPT('".$email."','".AES_DECRYPT_KEY."')"))
		{
			$result = 1;  //初始为1
			//载入会员整合
			$integrate_code = trim(app_conf("INTEGRATE_CODE"));
			if($integrate_code!='')
			{
				$integrate_file = APP_ROOT_PATH."system/integrate/".$integrate_code."_integrate.php";
				if(file_exists($integrate_file))
				{
					require_once $integrate_file;
					$integrate_class = $integrate_code."_integrate";
					$integrate_obj = new $integrate_class;
				}
			}
				
			if($integrate_obj)
			{
				$result = $integrate_obj->edit_user($user_info,$user_pwd);
			}
				
			if($result>0)
			{
				$user_info_m['user_pwd'] = md5($user_pwd);
				$GLOBALS['db']->autoExecute(DB_PREFIX."user",$user_info_m,"UPDATE","id=".$user_info['id']);
			}
				
			showSuccess($GLOBALS['lang']['MOBILE_SUCCESS'],1);//密码修改成功
				
		}
		else{
			showErr("邮箱账户不存在",1);  //没有该手机账户
		}
		
	}

	// wap密码手机修改 code验证  2016.9.2
	public function phone_send_code(){
		foreach ($_POST as $k=> $v) {
			$user_data[$k] = htmlspecialchars(addslashes($v));
		}
		$mobile = strim($user_data['mobile']);
		$sms_code=strim($user_data['sms_code']);
		/*$verify =  strim($user_data['verify']);*/
		if(!$mobile){
			showErr($GLOBALS['lang']['MOBILE_FORMAT_ERROR']); //手机格式错误
		}
		/*if($verify){
			if(!checkVeifyCode($verify)){
				$data['status'] = 0;
				$data['info'] = "图形验证码错误";
				ajax_return($data);
			}
		}*/
		if($sms_code==""){
			showErr("请输入手机验证码",1);
		}
		//判断验证码是否正确
		if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".strim($mobile)."' AND verify_code='".strim($sms_code)."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ")==0){
				showErr("手机验证码出错,或已过期",1);
		}else{
			session_start();
			$_SESSION['mobile'] = $mobile;
			showSuccess("手机验证码正确",1);
			
		}
		/*//判断验证码是否正确
		if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$mobile."' AND verify_code='".$sms_code."'")==0){
			showErr("手机验证码出错",1);
		}else{
			session_start();
			$_SESSION['mobile'] = $mobile;
			showSuccess("手机验证码正确",1);
			
		}*/
	}
	// wap密码手机修改 更改密码 2016.9.2
	public function user_set_password(){
		//禁止url直接访问
		if(!$_SESSION['mobile']){
			exit('非法请求');
		}
		$GLOBALS['tmpl']->caching = true;
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.$GLOBALS['deal_city']['id']);		
		if (!$GLOBALS['tmpl']->is_cached('user_get_password.html', $cache_id))	
		{
			 
			$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['GET_PASSWORD_BACK']);
		}
		$GLOBALS['tmpl']->display("user_set_password.html",$cache_id);
	}
	// wap 密码修改 2016.9.2
	public function set_password(){
		$user_pwd = strim($_REQUEST['pwd_m']);
		$mobile = $_SESSION['mobile'];

		if($user_info = get_user_info("*","mobile_encrypt =AES_ENCRYPT('".$mobile."','".AES_DECRYPT_KEY."') "))
		{
			$result = 1;  //初始为1
			//载入会员整合
			$integrate_code = trim(app_conf("INTEGRATE_CODE"));
			if($integrate_code!='')
			{
				$integrate_file = APP_ROOT_PATH."system/integrate/".$integrate_code."_integrate.php";
				if(file_exists($integrate_file))
				{
					require_once $integrate_file;
					$integrate_class = $integrate_code."_integrate";
					$integrate_obj = new $integrate_class;
				}	
			}
			
			if($integrate_obj)
			{
				$result = $integrate_obj->edit_user($user_info,$user_pwd);				
			}
			$user_id = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."user where mobile =".$mobile);
			if(get_user_info("paypassword","id=".$user_id,"ONE")== MD5($user_pwd)){
				showErr("登录密码不能与交易密码相同",1); 
			}
			if($result>0)
			{
				$user_info_m['user_pwd'] = md5($user_pwd);
				$GLOBALS['db']->autoExecute(DB_PREFIX."user",$user_info_m,"UPDATE","id=".$user_info['id']);
			}
			es_session::delete('sms_count');
			showSuccess("密码找回成功!请重新登录",1);//密码修改成功
			
		}
		else{
			showErr($GLOBALS['lang']['NO_THIS_MOBILE'],1);  //没有该手机账户
		}
	}
	
	public function phone_send_password()
	{	
		$mobile = strim($_REQUEST['phone']);
		$user_pwd = strim($_REQUEST['pwd']);
		$sms_code=strim($_POST['sms_code']);
		
		if(!$mobile)
		{
			showErr($GLOBALS['lang']['MOBILE_FORMAT_ERROR']); //手机格式错误
		}
		
		if($sms_code==""){
			showErr("请输入手机验证码!",1);
		}
		
		if($user_pwd==""){
			showErr("请输入密码!",1);
		}
		//判断验证码是否正确
		if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$mobile."' AND verify_code='".$sms_code."'")==0){
			showErr("手机验证码出错",1);
		}
		$www=get_user_info("*","mobile_encrypt =AES_ENCRYPT('".$mobile."','".AES_DECRYPT_KEY."') ");
		if(empty($www)){
			showErr("该用户不存在!",1);
		}
		if(get_user_info("user_pwd","mobile_encrypt =AES_ENCRYPT('".$mobile."','".AES_DECRYPT_KEY."') ","ONE")==MD5($user_pwd)){
			showErr("不能修改为原密码!",1);
		}
		if(get_user_info("paypassword","mobile_encrypt =AES_ENCRYPT('".$mobile."','".AES_DECRYPT_KEY."') ","ONE")==MD5($user_pwd)){
			showErr("登录密码不能与交易密码一样!",1);
		}
		
	
		if($user_info = get_user_info("*","mobile_encrypt =AES_ENCRYPT('".$mobile."','".AES_DECRYPT_KEY."') "))
		{
			$result = 1;  //初始为1
			//载入会员整合
			$integrate_code = trim(app_conf("INTEGRATE_CODE"));
			if($integrate_code!='')
			{
				$integrate_file = APP_ROOT_PATH."system/integrate/".$integrate_code."_integrate.php";
				if(file_exists($integrate_file))
				{
					require_once $integrate_file;
					$integrate_class = $integrate_code."_integrate";
					$integrate_obj = new $integrate_class;
				}	
			}
			
			if($integrate_obj)
			{
				$result = $integrate_obj->edit_user($user_info,$user_pwd);				
			}
			
			if($result>0)
			{
				$user_info_m['user_pwd'] = md5($user_pwd);
				$GLOBALS['db']->autoExecute(DB_PREFIX."user",$user_info_m,"UPDATE","id=".$user_info['id']);
			}
			
			showSuccess($GLOBALS['lang']['MOBILE_SUCCESS'],1,url("index","user#login"));//密码修改成功
			
		}
		else{
			showErr($GLOBALS['lang']['NO_THIS_MOBILE'],1);  //没有该手机账户
		}
		
		
		
	}
	
	
	
	public function api_create()
	{
		$s_api_user_info = es_session::get("api_user_info");
		if($s_api_user_info)
		{
			if($s_api_user_info['field'])
			{
				$module = str_replace("_id","",$s_api_user_info['field']);
				$module = strtoupper(substr($module,0,1)).substr($module,1);
				require_once APP_ROOT_PATH."system/api_login/".$module."_api.php";
				$class = $module."_api";
				$obj = new $class();
				$obj->create_user();
				app_redirect(APP_ROOT."/");
				exit;
			}			
			showErr($GLOBALS['lang']['INVALID_VISIT']);
		}
		else
		{
			showErr($GLOBALS['lang']['INVALID_VISIT']);
		}
	}
	
	public function do_re_name_id()
	{

	    if(!$GLOBALS['user_info'] || $GLOBALS['user_info']['idno']!=""){
	        showErr("操作失败");
	    }
	    
		$id= $GLOBALS['user_info']['id'];
		$real_name = strim($_REQUEST['real_name']);
		$idno = strim($_REQUEST['idno']);
		$sex = strim($_REQUEST['sex']);
		$byear = intval($_REQUEST['byear']);
		$bmonth = intval($_REQUEST['bmonth']);
		$bday = intval($_REQUEST['bday']);
		
		$user_type = intval($GLOBALS['user_info']['user_type']);
		if($user_type ==  1)
		{
			$enterpriseName = strim($_REQUEST['enterpriseName']);
			$bankLicense = strim($_REQUEST['bankLicense']);
			$orgNo = strim($_REQUEST['orgNo']);
			$businessLicense  = strim($_REQUEST['businessLicense']);
			$taxNo = strim($_REQUEST['taxNo']);
			
			if($enterpriseName==""){
				showErr("请输入企业名称");
			}
			if($bankLicense==""){
				showErr("请输入开户银行许可证");
			}
			if($orgNo==""){
				showErr("请输入组织机构代码");
			}
			if($businessLicense==""){
				showErr("请输入营业执照编号");
			}
			if($taxNo==""){
				showErr("请输入税务登记号");
			}
			
			
		}
		
		if(!$id)
		{
			showErr("该用户尚未登陆",url("index","user#login")); 
		}
		
		if(!$real_name)
		{
			showErr("请输入真实姓名"); //姓名格式错误
		}
		
		
		if($idno==""){
			showErr("请输入身份证号");
		}
		
		if(getIDCardInfo($idno)==0){
    			showErr("身份证号码错误！");
    	}
    	
    	
		
	
	
		//判断该实名是否存在
		if(get_user_info("count(*)","idno_encrypt = AES_ENCRYPT('.$idno.','".AES_DECRYPT_KEY."') and id<> $id ","ONE") > 0 ){
			showErr("该实名已被其他用户认证，非本人请联系客服");
		}
		
		
		if($user = get_user_info("*","id =".$id))
		{	
			$user_info_re = array();
			$user_info_re['id'] = $id;
			$user_info_re['real_name'] = $real_name;
			$user_info_re['idno'] = $idno;
			$user_info_re['sex'] = $sex;
			$user_info_re['byear'] = $byear;
			$user_info_re['bmonth'] = $bmonth;
			$user_info_re['bday'] = $bday;
			if( $user_type == 1){
				$user_info_re['enterpriseName'] = $enterpriseName;
				$user_info_re['bankLicense'] = $bankLicense;
				$user_info_re['orgNo'] = $orgNo;
				$user_info_re['businessLicense'] = $businessLicense;
				$user_info_re['taxNo'] = $taxNo;
			}
			$user_info_re['email'] = $user['email'];
			if($user['email']=="" && (int)app_conf("OPEN_IPS") > 0){
				$user_info_re['email'] = get_site_email($id);
			}
			
			
			require_once APP_ROOT_PATH."system/libs/user.php";
			$res = save_user($user_info_re,"UPDATE");
			
			if($res['status'] == 1)
			{
				/*
				$data['user_id'] = $GLOBALS['user_info']['id'];
		    	$data['type'] = "credit_identificationscanning";
		    	$data['status'] = 0;
		    	$data['create_time'] = TIME_UTC;
		    	$data['passed'] = 0;
		    	
		    	$condition = "";
		    	if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."user_credit_file WHERE user_id=".$GLOBALS['user_info']['id']." AND type='credit_identificationscanning'") > 0)
		    	{
		    		$mode = "UPDATE";
		    		$condition = "user_id=".$GLOBALS['user_info']['id']." AND type='credit_identificationscanning'";
		    	}
		    	else{
		    		$mode = "INSERT";
		    	}
		    	$GLOBALS['db']->autoExecute(DB_PREFIX."user_credit_file",$data,$mode,$condition);
				*/
				if( $user_type == 1){
					$user_company = array();
					$user_company['company_name'] = $enterpriseName;
					$user_company['contact'] = $real_name;
					
					$user_company['bankLicense'] = $bankLicense;
					$user_company['orgNo'] = $orgNo;
					$user_company['businessLicense'] = $businessLicense;
					$user_company['taxNo'] = $taxNo;
					if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."user_company WHERE user_id=".intval($GLOBALS['user_info']['id'])) > 0){
						$GLOBALS['db']->autoExecute(DB_PREFIX."user_company",$user_company,"UPDATE","user_id=".$id);
					}
					else{
						$user_company['user_id'] = $id;
						$GLOBALS['db']->autoExecute(DB_PREFIX."user_company",$user_company,"INSERT");
					}
				}
				
				
				if(app_conf("OPEN_IPS") == 1){
					//showSuccess("验证成功",0,APP_ROOT."/index.php?ctl=collocation&act=CreateNewAcct&user_type=0&user_id=".$id);
					showSuccessIPS("验证成功",0,APP_ROOT."/index.php?ctl=collocation&act=CreateNewAcct&user_type=0&user_id=".$id);
				}
				else{
					showSuccess("注册成功",0,APP_ROOT."/");
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
				showErr($error_msg);
			}
				
		}
		else{
			showErr("该用户尚未注册");  //尚未注册
		}
		
	}
	
	public function wx_login()
	{
		$img = url("index","ajax#weixin_login",array("rand"=>time()));
		$GLOBALS['tmpl']->assign("img",$img);
		$GLOBALS['tmpl']->display("inc/weixin_login.html");
	}
	public function registerll(){
		 require APP_ROOT_PATH."system/utils/Verify.php";
        require APP_ROOT_PATH."system/utils/BinkCard/Imagebase64.php";
        require APP_ROOT_PATH."system/utils/bankList.php";
		require APP_ROOT_PATH."app/Lib/module/ajaxModule.class.php";
        $data['uc_IDcard']=$_REQUEST['IDcard'];//身份证

            if(strpos($data['uc_IDcard'],"****") ){
                $data['uc_IDcard'] = $GLOBALS['db']->getOne("SELECT AES_DECRYPT(idno_encrypt,'".AES_DECRYPT_KEY."') as idno FROM ".DB_PREFIX."user WHERE id=".$GLOBALS['user_info']['id']);
            }else{
                $data['uc_IDcard']=$data['uc_IDcard'];
            }

        $data['bank_mobile']=$_REQUEST['phone'];//手机号
        $data['validateCode']=$_REQUEST['bank_phone_code'];//验证码

        $verify_code=$GLOBALS['db']->getOne("SELECT verify_code  FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$data['bank_mobile']."' AND verify_code='".$data['validateCode']."'  AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC."");
        if($data['validateCode'] !=$verify_code){
            showErr("验证码不一样或已过期",1);
        }


        $data['bank_id'] = intval($_REQUEST['bank_id']);

		if($data['bank_id'] == 0)
		{
			$data['bank_id'] = intval($_REQUEST['otherbank']);
		}
		
		if($data['bank_id'] == 0)
		{
			showErr($GLOBALS['lang']['PLASE_ENTER_CARRY_BANK'],1);
		}
		
		$data['real_name'] = trim($_REQUEST['realname']);
        if($data['real_name'] == ""){
            showErr("请输入开户名",1);
        }

		$data['bankcard'] = trim($_REQUEST['bankcard']);
        $data['bankcard']=str_replace(" ","",$data['bankcard']);
        #$data['bankcard'] = preg_replace("/\s/","",$data['bankcard']);

		if(str_replace(" ","",$data['bankcard']) == ""){
			showErr($GLOBALS['lang']['PLASE_ENTER_CARRY_BANK_CODE'],1);
		}
		
		if(strlen($data['bankcard']) < 10){
			showErr("最少输入10位账号信息！",1);
		}
		
		$data['user_id'] = $GLOBALS['user_info']['id'];

        if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."user_bank WHERE bankcard='".$data['bankcard']."'  AND user_id=".$GLOBALS['user_info']['id']) > 0){
			showErr("该银行卡已存在",1);
		}
		$isset_id=$GLOBALS['db']->getOne("SELECT id FROM  ".DB_PREFIX."user WHERE idno='". $data['uc_IDcard']."'");
        if($isset_id>0&&$isset_id!=$GLOBALS['user_info']['id']){
        	showErr("认证失败,同一身份证不能绑定多个账号!",1);
        }
       
       // $ajax=new ajaxModule;
       // $bank_msg=$ajax->ajax_bank_card();
       // $bank_list=$GLOBALS['db']->getOne("SELECT Banklist FROM ".DB_PREFIX."bank WHERE id=".$data['bank_id']);
       // if($bank_list!=$bank_msg['bank_code']){
       // 		showErr("认证失败,银行卡号与所选银行不一致！",1);
       // }
       //  if($bank_msg['card_type']==3){
       // 		showErr("认证失败,暂不支持绑定信用卡！",1);
       // }
        $idno = $GLOBALS['db']->getOne("SELECT idno FROM  ".DB_PREFIX."user WHERE id=".$GLOBALS['user_info']['id']);
        $url = "http://verifyapi.huiyuenet.com/zxbank/verifyApi.do";
        $name=$data['real_name'];
        
        $phone =$data['bank_mobile'];
        #$bankCard = str_replace(" ","",$data['bankcard']);
        $bankCard =  $data['bankcard'];
        $idnum=$data['uc_IDcard'];
        $sid = "jxdbc";
        $cpserialnum = $this->orderId();
        $md5key = "l46g6i";
        $despwd = "9cwcweunozhw15ul6elezl5y";
        $vtype = "03";
        $verifyFun = new VerifyFun($url);
        $result=$verifyFun->zXBank($sid, $name, $idnum, $vtype, $phone, $bankCard, $cpserialnum, $despwd,$md5key);
        $array = json_decode($result,1);

        switch ($array['result']) {
            case 'BANKCONSISTENT':
                // if(!$idno){
                //     $res = $this->addCard($data['uc_IDcard'],$data['bank_id'],$data['real_name'],$data['bankcard'],$data['bank_mobile']);
                // }else{
                //     $res = $this->saveCard($data['bank_id'],$data['real_name'],$data['bankcard'],$data['bank_mobile']);
                // }
                 $res = $this->addCard($data['uc_IDcard'],$data['bank_id'],$data['real_name'],$data['bankcard'],$data['bank_mobile']);
                
                if($res){
                	 // 判断是否满足给邀请人发放奖励的条件
//                	if(is_get_rewards($GLOBALS['user_info']['id'])){
//                		// 邀请发放站内信
//		        		$notices['site_name'] = app_conf("SHOP_TITLE");
//						$notices['friend_name'] = utf_substr($name);
//						$notices['user_name'] = get_user_info("real_name","id=".$GLOBALS['user_info']['pid'],"ONE");
//						$to_user_id=$GLOBALS['user_info']['pid'];
//						$time=TIME_UTC;
//						$tmpl_contents = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_INS_INVITE_REWARDS'",false);
//						$GLOBALS['tmpl']->assign("notice",$notices);
//						$content = $GLOBALS['tmpl']->fetch("str:".$tmpl_contents['content']);
//						send_user_msg("您成功邀请好友".$notices['friend_name']."，获得一张20元代金券",$content,0,$to_user_id,$time,0,true,22);
//		        		//////////////////
//                		$order_data['begin_time'] = TIME_UTC;
//						$order_data['end_time'] = to_timespan(to_date($order_data['begin_time'])." ".app_conf("INTERESTRATE_TIME")." month -1 day");
//						$order_data['money'] = 20;
//						$order_data['ecv_type_id'] = 5;
//						$sn = unpack('H12',str_shuffle(md5(uniqid())));
//						$order_data['sn'] = $sn[1];
//						$order_data['password'] = rand(10000000,99999999);
//						$order_data['user_id']=$GLOBALS['user_info']['pid'];
//						$order_data['child_id']=$GLOBALS['user_info']['id'];
//                		$result=$GLOBALS['db']->autoExecute(DB_PREFIX."ecv",$order_data,"INSERT");
//                		if($result==false){
//                			showErr("代金券发放失败",1);
//                		}
//                	}
					/* //绑卡成功处理渠道记录
					$source_id = es_session::get("source_id");
					$device = es_session::get("device");
					if($source_id!=''&&$device!=''){
						addsource($source_id,$device,$GLOBALS['user_info']['id'],2);
					} */
                    showSuccess("绑定银行卡成功",1,'/index.php?ctl=user&act=stepthree');
                }else{
                    showErr("保存失败",1);
                }
                break;

            case 'BANKNOLIB':
                showErr("没有此银行卡信息",1);
                break;

            case 'BANKINCONSISTENT':
                showErr("银行卡信息不一致",1);
                break;

            case 'BANKUNKNOWN':
                showErr("银行卡信息未知",1);
                break;

            case 'FAIL':
            	$info = $this->fail($array['errmsg']);
                showErr("$info",1);
                break;
            default:
                showErr("银行卡信息未知",1);
                break;
        }

      //end
	}
	public function fail($err){
        switch ($err) {
            case 'ERR2011':
                $info = "不存在该银行账户";
                break;
            case 'ERR2012':
                $info = "数据传输错误，请稍后再试";
                break;
            case 'ERR2013':
                $info = "银行账户已停用";
                break;
            case 'ERR2014':
                $info = "数据传输错误，请稍后再试";
                break;
            case 'ERR2015':
                $info = "身份证号无效";
                break;
            case 'ERR2016':
                $info = "银行卡内部接口错误";
                break;
            case 'ERR2017':
                $info = "其他错误（银行返回）";
                break;
            case 'ERR9999':
                $info = "服务器错误，请稍后再试";
                break;
            default:
                # code...
                break;
        }
        return $info;
    }
	public function orderId(){
        $yCode = array('Q', 'W', 'E', 'R', 'T', 'Y', 'N', 'M', 'C', 'O');
        $orderSn = $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
        return $orderSn;
    }
     //添加银行卡
    public function addCard($uc_IDcard,$bank_id,$real_name,$bankcard,$bank_mobile){
        $user_info['user_id'] = $GLOBALS['user_info']['id'];
        $user_info['real_name'] = $real_name;
        #$user_info['bankcard'] = str_replace(" ","",$bankcard);
        $user_info['bankcard'] =$bankcard;
        $user_info['bank_id'] = $bank_id;
        $user_info['create_time'] = TIME_UTC;
        $user_info['bank_mobile'] = $bank_mobile;
        $user_info["status"] = 1;
        $res = $GLOBALS['db']->getOne("SELECT * FROM  ".DB_PREFIX."user_bank WHERE status=1 and user_id=".$GLOBALS['user_info']['id']);
        if(!$res){
            $result	=$GLOBALS['db']->autoExecute(DB_PREFIX."user_bank",$user_info,"INSERT");
        }
        if(isset($result)){
			//绑卡成功处理渠道记录
			
			/* addsource(0,0,$user_info['user_id'],2); */
			
            $data['real_name'] = $real_name;
            #$data['idno'] = str_replace(" ","",$bankcard);
            $data['idno'] = $uc_IDcard;
            $data["real_name_encrypt"] = " AES_ENCRYPT('".$real_name."','".AES_DECRYPT_KEY."') ";
            $data["idno_encrypt"] = " AES_ENCRYPT('".$uc_IDcard."','".AES_DECRYPT_KEY."') ";
            $data["idcardpassed"] = 1;
            $data["idcardpassed_time"] = TIME_UTC;
            

            $addlt= $GLOBALS['db']->autoExecute(DB_PREFIX."user",$data,"UPDATE","id=".$GLOBALS['user_info']['id']);

            return $addlt;
        }
    }

    //更换银行卡
    // public function saveCard($bank_id,$real_name,$bankcard,$bank_mobile){
    //     $user_info['real_name'] = $real_name;
    //     #$user_info['bankcard'] = str_replace(" ","",$bankcard);
    //     $user_info['bankcard']  = $bankcard;
    //     $user_info['bank_id'] = $bank_id;
    //     $user_info['bank_mobile'] = $bank_mobile;
    //     $result	= $GLOBALS['db']->autoExecute(DB_PREFIX."user_bank",$user_info,"UPDATE","user_id=".$GLOBALS['user_info']['id']);
    //     if(isset($result)){
    //         $data['real_name'] = $real_name;
    //         $data['idno'] = $bankcard;
    //         $data["real_name_encrypt"] = " AES_ENCRYPT('".$real_name."','".AES_DECRYPT_KEY."') ";
    //         $data["idno_encrypt"] = " AES_ENCRYPT('".$uc_IDcard."','".AES_DECRYPT_KEY."') ";
    //         $data["idcardpassed"] = 1;
    //         $lt= $GLOBALS['db']->autoExecute(DB_PREFIX."user",$data,"UPDATE","id=".$GLOBALS['user_info']['id']);
    //         return $lt;
    //     }
    // }
    // 设置交易密码
    public function user_reg_settingpwd(){
    	$ajax = intval($_REQUEST['ajax']);
		if(!$GLOBALS['user_info']){
			showErr($GLOBALS['lang']['PLEASE_LOGIN_FIRST'],$ajax);
		}

		$paypassword = FW_DESPWD(trim($_REQUEST['paypassword']));
		if(!preg_match("/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,16}$/",$paypassword)){
			showErr("交易密码为6-16位字母数字组合",$ajax);
		}
		$user_pwd =  $GLOBALS['db']->getOne("SELECT user_pwd FROM ".DB_PREFIX."user where id =".$GLOBALS['user_info']['id']);
		if($user_pwd == MD5($paypassword)){
			showErr("交易密码不能是登录密码",$ajax);
		}
		$GLOBALS['db']->query("update ".DB_PREFIX."user set paypassword='".md5($paypassword)."', bind_verify = '', verify_create_time = 0 where id = ".intval($GLOBALS['user_info']['id']));
		if($GLOBALS['db']->affected_rows() > 0){
			showSuccess($GLOBALS['lang']['MOBILE_BIND_SUCCESS'],$ajax,'/index.php?ctl=user&act=success');
		}
		else{
			showErr("绑定失败",$ajax);
		}
    }
    public function success(){
    	if(!$GLOBALS['user_info']){
			showErr($GLOBALS['lang']['PLEASE_LOGIN_FIRST'],$ajax);
		}
		$GLOBALS['tmpl']->assign("jump","index.php");
		$GLOBALS['tmpl']->display("success.html");
    }
    //业务员登录 2017.5.18 wwm
    public function salesman_login()
	{
		$login_info = es_session::get("user_info");
		if($login_info)
		{
			app_redirect(url("index")."?ctl=salesman");		
		}
		$GLOBALS['tmpl']->display("inc/uc/sale/salesman_login.html");
	}
	public function salesman_do_login(){
		if(!$_POST){
			 app_redirect("404.html");
			 exit();
		}
		foreach($_POST as $k=>$v){
			$date[$k] = htmlspecialchars(addslashes($v));
		}

		$ajax = intval($date['ajax']);
		require_once APP_ROOT_PATH."system/libs/user.php";
		$date['user_pwd'] = trim(FW_DESPWD($date['user_pwd']));
		$result = salesman_do_login($date['mobile'],$date['user_pwd']);
		if($result['status']){
			$s_user_info = es_session::get("user_info");
			if($ajax==1){
				$return['status'] = 1;
				$return['info'] = $GLOBALS['lang']['LOGIN_SUCCESS'];
				ajax_return($return);
			}else{
				$return['status'] = 2;
				$return['info'] = "登录失败";
				ajax_return($return);
			}
			
		}else{
			$return['status'] = 2;
			$return['info'] = "登录失败";
			ajax_return($return);
		}
	}


	public function company_register()
	{	
		$login_info = es_session::get("user_info");
		if($login_info)
		{
			app_redirect(url("index"));		
		}
		$code=$_REQUEST['code'];
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['USER_REGISTER']);
		
		$field_list =load_auto_cache("user_field_list");
		foreach($field_list as $k=>$v){
			if($v['is_show']==0){
				unset($field_list[$k]);
			}
		}
		
		$api_uinfo = es_session::get("api_user_info");
		$GLOBALS['tmpl']->assign("code",$code);
		$GLOBALS['tmpl']->assign("reg_name",$api_uinfo['name']);
		$GLOBALS['tmpl']->assign("field_list",$field_list);
		
		$GLOBALS['tmpl']->assign("agreement",app_conf("AGREEMENT"));
		$GLOBALS['tmpl']->assign("privacy",app_conf("PRIVACY"));
		$referer = "";
		if(isset($_REQUEST['r'])){
			$referer = strim(base64_decode($_REQUEST['r']));
		}
		$GLOBALS['tmpl']->assign("referer",$referer);
		$GLOBALS['tmpl']->assign("ACT",ACTN);
		$GLOBALS['tmpl']->display("company_register.html");
	}



	public function company_doregister()
	{
		$switch_conf = $GLOBALS['db']->getAll("SELECT status FROM ".DB_PREFIX."switch_conf where switch_id=1 or switch_id=2");
		foreach ($switch_conf as $k => $v) {
			if($v['status'] != 1){
				$return['status'] = 0;
				$return['info'] = "系统正在升级，请稍后再试";
				ajax_return($return);
			}
		}
		//注册验证码
		if(intval(app_conf("VERIFY_IMAGE")) == 1 && intval(app_conf("USER_VERIFY")) >= 3){
			$verify = strim($_REQUEST['verify']);
			if(!checkVeifyCode($verify))
			{
				showErr($GLOBALS['lang']['VERIFY_CODE_ERROR'],0,url("shop","user#register"));
			}
		}
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
		//防止wap冲突
		if(trim($user_data['user_pwd_confirm']) != ""){
			if(trim($user_data['user_pwd'])!=trim($user_data['user_pwd_confirm']))
			{
				// $return['status'] = 1;
				$return['info'] = $GLOBALS['lang']['USER_PWD_CONFIRM_ERROR'];
				ajax_return($return);
				//showErr($GLOBALS['lang']['USER_PWD_CONFIRM_ERROR']);
			}
		}
		//判断验证码是否正确
		if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".strim($user_data['mobile'])."' AND verify_code='".strim($user_data['sms_code'])."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ")==0){
		    // $return['status'] = 1;
		    $return['info'] = "手机验证码出错,或已过期";
		    ajax_return($return);
		    //showErr("手机验证码出错,或已过期");
		}
		
		if(trim($user_data['user_pwd'])=='')
		{	
			// $return['status'] = 1;
			$return['info'] = $GLOBALS['lang']['USER_PWD_ERROR'];
			ajax_return($return);
			//showErr($GLOBALS['lang']['USER_PWD_ERROR']);
		}
		
		
		if(isset($user_data['referer']) && $user_data['referer']!=""){
			//$p_user_data = get_user_info("id,user_type","mobile_encrypt =AES_ENCRYPT('".$user_data['referer']."','".AES_DECRYPT_KEY."') OR user_name='w".$user_data['referer']."'");

            $p_user_data = get_user_info("id,user_type","mobile_encrypt =AES_ENCRYPT('".$user_data['referer']."','".AES_DECRYPT_KEY."')");

			if($p_user_data["user_type"] == 3)
			{
				$user_data['referer_memo'] = $p_user_data['id'];
				//$user_data['pid'] = $p_user_data['id'];
				$user_data['pid'] = 0;
			}
			elseif($p_user_data["user_type"] < 2)
			{
				$user_data['pid'] = $p_user_data["id"];
				if($user_data['pid'] > 0){
					$refer_count = get_user_info("count(*)","pid='".$user_data['pid']."' ","ONE");
					if($refer_count == 0){
						$user_data['referral_rate'] = (float)trim(app_conf("INVITE_REFERRALS_MIN"));
					}
					elseif((float)trim(app_conf("INVITE_REFERRALS_MIN")) + $refer_count*(float)trim(app_conf("INVITE_REFERRALS_RATE")) > (float)trim(app_conf("INVITE_REFERRALS_MAX"))){
						$user_data['referral_rate'] =(float)trim(app_conf("INVITE_REFERRALS_MAX"));
					}
					else{
						$user_data['referral_rate'] =(float)trim(app_conf("INVITE_REFERRALS_MIN")) + $refer_count*(float)trim(app_conf("INVITE_REFERRALS_RATE"));
					}
						
					
					if(intval(app_conf("REFERRAL_IP_LIMIT")) > 0 && get_user_info("count(*)","register_ip ='".CLIENT_IP."' AND pid='".$user_data['pid']."'","ONE") > 0){
						$user_data['referral_rate'] = 0;
					}
				}
				else{
					$user_data['pid'] = 0;
					$user_data['referer_memo'] = $user_data['referer'];
				}
			}
		}
		//避免手机重复注册
		$info = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."user WHERE mobile ='".$user_data['mobile']."'");
		if($info  > 0){
			$data['info'] = "手机号码已被注册";
			ajax_return($data);
		}else if($user_data['mobile']==''){
			$return['info'] = "手机号不能为空";
			ajax_return($return);
		}

		//判断是否为手机注册
		if((app_conf("REGISTER_TYPE") == 0 || app_conf("REGISTER_TYPE") == 1) && (app_conf("USER_VERIFY") == 0 || app_conf("USER_VERIFY") == 2)){
			if(strim($user_data['sms_code']) == ""){
				// $return['status'] = 1;
				$return['info'] = "请输入手机验证码";
				ajax_return($return);
				//showErr("请输入手机验证码");
			}
			
			$user_data['is_effect'] = 1;
			$user_data['mobilepassed'] = 1;
			$user_data["mobile_encrypt"] = " AES_ENCRYPT('".$user_data['mobile']."','".AES_DECRYPT_KEY."') ";
		}
	
		//避免手机重复注册
		$info = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."user WHERE mobile ='".$user_data['mobile']."'");
		if($info  > 0){
			$data['info'] = "手机号码已被注册";
			ajax_return($data);
		}else if($user_data['mobile']==''){
			$return['info'] = "手机号不能为空";
			ajax_return($return);
		}
		// 判断邀请码是否有效
		if(isset($user_data['referer'])&&$user_data['referer']!=''){
			$p_user_id = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."user WHERE mobile ='".$user_data['referer']."'");
			if(!$p_user_id){
				$return['info'] = "邀请码不存在";
				ajax_return($return);
			}
		}
		

		//企业用户注册
		
        $user_data['user_type']=1;
		

		$res = save_user($user_data);
		

		if($res['status'] == 1)
		{
			

			// 注册成功站内信
			$user_id = intval($res['data']);
			$notices['site_name'] = app_conf("SHOP_TITLE");
			$notices['user_name'] = $user_data['mobile'];
			$notices['time'] = to_date(TIME_UTC,"Y年m月d日 H:i");
			$time=TIME_UTC;
			$tmpl_contents = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_INS_REGISTER_SUCCESS_MSG'",false);
			$GLOBALS['tmpl']->assign("notice",$notices);
			$content = $GLOBALS['tmpl']->fetch("str:".$tmpl_contents['content']);
			
			send_user_msg("恭喜您获得84442体验金+512红包",$content,0,$user_id,$time,0,true,21);

			// 给用户发送短信通知
            // if(app_conf("SMS_ON")==1)
            // {
            //     $user_info=$GLOBALS['db']->getRow("select user_name,mobile from ".DB_PREFIX."user where id=".$user_id);
            //     $tmpl_content =  $GLOBALS['db']->getOne("select content from ".DB_PREFIX."msg_template where name = 'TPL_SMS_REGISTER_SUCCESS'");
            //     // $notice['user_name'] = $user_info['user_name'];
            //     // $notice['release_date'] = to_date(TIME_UTC,"Y-m-d");
            //     // $notice['site_name'] = app_conf("SHOP_TITLE");
            //     // // $notice['recharge_money'] = round($storage['money'],2);
            //     // $GLOBALS['tmpl']->assign("notice",$notice);
            //     $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);

            //     $msg_data['dest'] = $user_info['mobile'];
            //     $msg_data['send_type'] = 0;
            //     $msg_data['title'] = "注册成功短信通知";
            //     $msg_data['content'] = addslashes($msg);
            //     $msg_data['send_time'] = time();
            //     $msg_data['is_send'] = 0;
            //     $msg_data['create_time'] = TIME_UTC;
            //     $msg_data['user_id'] = $user_id;
            //     $msg_data['is_html'] = 0;
            //     send_sms_email($msg_data);
            //     $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
            // }

			//更新来路
			//$GLOBALS['db']->query("update ".DB_PREFIX."user set referer = '".$GLOBALS['referer']."' where id = ".$user_id);
			/*$user_info = get_user_info("is_effect","id = ".$user_id);
			if($user_info['is_effect']==1)
			{*/
				//在此自动登录
				$result = do_login_user($user_data['user_name'],$user_data['user_pwd']);
                // 注册成功成长值
                require_once APP_ROOT_PATH."system/user_level/Level.php";
                $level=new Level();
                $level->get_grow_point(1);

				$return['status'] = 2;
				//$return['info'] = "注册成功<br/>恭喜您已获得8888元体验金+518元红包，请到我的账户查看!";
				$return['info'] = $GLOBALS["tmpl"]->fetch("company_reg_successTip.html");
				//$return['msg'] = "8888元注册体验金+16666元分享体验金+58888出借体验金+50元代金券";
				$return['jump'] = url("index","user#company_steptwo");
				ajax_return($return);
				/*$GLOBALS['tmpl']->assign('integrate_result',$result['msg']);				
				app_redirect(url("index","user#steptwo"));*/
			/*}
			else{
				showSuccess($GLOBALS['lang']['WAIT_VERIFY_USER'],0,APP_ROOT."/");
			}*/
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
			showErr($error_msg);
		}
	}


	public function company_steptwo(){
		
		
		$user_id = $GLOBALS['user_info']['id'];
        if(!$user_id){
          
          app_redirect(url("index","user#login"));
        }
        $jump = machineInfo();

        $GLOBALS['tmpl']->assign("jump",$jump);
		
		$GLOBALS['tmpl']->display("company_steptwo.html");
		exit;
	}
}
?>