<?php
require APP_ROOT_PATH.'system/libs/user.php';
require APP_ROOT_PATH.'system/libs/voucher.php';
require APP_ROOT_PATH.'system/user_level/Level.php';
require_once APP_ROOT_PATH.'system/alioss/aliyun-oss-php-sdk.phar';
use OSS\OssClient;
use OSS\Core\OssException;
class ajaxModule extends SiteBaseModule
{ 
	public function __construct(){
		parent::__construct();
		$no_action_array = array("check_field","load_api_url","weixin_login","bid_calculate");
		if(!in_array(ACTION_NAME,$no_action_array) && !check_hash_key()){
			showErr("非法请求!",1);
		}
	}
	public function check_field()
	{
		$result = array("status"=>1,"info"=>'');
		$field_name = addslashes(trim($_REQUEST['field_name']));
		$field_data = addslashes(trim($_REQUEST['field_data']));
		if($field_name==""){
			$result['status'] = 0;
			$result['info'] = $error_msg;
			ajax_return($result);
		}
		require_once APP_ROOT_PATH."system/libs/user.php";
		$res = check_user($field_name,$field_data);
		
		if($res['status'])
		{
			ajax_return($result);
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
			$result['status'] = 0;
			$result['info'] = $error_msg;
			ajax_return($result);
		}
	}
	
	function check_user(){
		
		$val = strim($_REQUEST['val']);
		//if(get_user_info("count(*)","mobile_encrypt = AES_ENCRYPT('".$val."','".AES_DECRYPT_KEY."') OR user_name='".$val."'","ONE") > 0){
		//zhuxiang 2017513
        if(get_user_info("count(*)","mobile_encrypt = AES_ENCRYPT('".$val."','".AES_DECRYPT_KEY."')","ONE") > 0){
			$result['status'] = 1;
			ajax_return($result);
		}
		else{
			$result['status'] = 0;
			ajax_return($result);
		}
	}
	function repair_user(){
		require_once APP_ROOT_PATH.'system/libs/voucher.php';
		$id=$GLOBALS['user_info']['id'];
		$val = strim($_REQUEST['val']);
		$referer = $GLOBALS['db']->getOne("SELECT referer FROM ".DB_PREFIX."user WHERE mobile=".$val);
		$user_id = get_user_info("id","mobile = ".$val,"ONE");
		if($val == $GLOBALS['user_info']['mobile']){
			$result['status'] = 2;
			$result['info'] = "邀请人不可以是本人";
			ajax_return($result);
		}else if($GLOBALS['user_info']['mobile'] == $referer){
			$result['status'] = 2;
			$result['info'] = "不可以相互邀请";
			ajax_return($result);
		}elseif(get_user_info("count(*)","mobile = ".$val,"ONE") > 0){
			$data['referer'] = $val;
            if(isset($val) && !empty($val)){
                $data['referer_time'] = time();
            }
			$data['pid'] = $user_id;
			$res = $GLOBALS['db']->autoExecute(DB_PREFIX."user",$data,"UPDATE","id=".$id);
			//发送邀请奖励
			/*$repay_time = $GLOBALS['db']->getOne("select repay_time from ".DB_PREFIX."deal where id = $deal_id");*/
			$uid = $GLOBALS['user_info']['id'];
			$pid = $user_id;
			if($pid){
				/*$res = set_invite_cash_red_packet($uid,$pid,$bid_money,$repay_time); //奖励一 发放*/
				$timer = check_register_time($uid); //注册时间小于15天
				if($timer){
					$money = $GLOBALS['db']->getOne("select money from ".DB_PREFIX."deal_load where user_id=".$uid." order by id asc");
					if($money){
						$res1 = set_invite_cash_red_packet_two($uid,$pid,$money); //奖励二 发放
					}				
				}
				//验证出借人是否在当前月注册并投资  满足条件总数
				$count = check_register_lend_count($uid,$pid);
				if($count == 2){
					$is_grant=is_grant($pid,30); //是否已经发放
					if($is_grant){
						$res2 = set_invite_lend_red_packet($uid,$pid,30);
					}
				}else if($count == 4){
					$is_grant=is_grant($pid,70); //是否已经发放
					if($is_grant){
						$res2 = set_invite_lend_red_packet($uid,$pid,70);
					}
				}
			}
			if($res){
                //建立的邀请关系的用户，彼此添加为抢红包好友
                $this->insert_packet_friend($uid,$pid);
                $this->insert_packet_friend($pid,$uid);
				$result['status'] = 1;
				$result['info'] = "邀请码补填成功";
				ajax_return($result);
			}else{
				$result['status'] = 0;
				$result['info'] = "邀请码补填失败";
				ajax_return($result);
			}
			
		}else{
			$result['status'] = 0;
			$result['info'] = "邀请码不存在";
			ajax_return($result);
		}
	}
    /**
     * 抢红包好友入库
     * @param $user_id
     * @param $friend_id
     * @author:zhuxiang
     */
    function insert_packet_friend($user_id,$friend_id){
        //注册成功创建红包好友关系
        $red_data['user_id'] = $user_id;
        $red_data['friend_id'] = $friend_id;
        $red_data['status'] = 0;
        $red_data['addtime'] = TIME_UTC;
        $GLOBALS['db']->autoExecute(DB_PREFIX."red_packet_friends",$red_data,"INSERT");
    }
	public function sendoldverify(){
		if(!$GLOBALS['user_info']){
			$data['status'] = 0;
			$data['info'] = "请先登录";
			ajax_return($data);
		}
		
		if(app_conf("SMS_ON")==0)
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['SMS_OFF'];
			ajax_return($data);
		}
		
		$user_mobile = strim($_REQUEST['user_mobile']);

		if($user_mobile == '')
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_MOBILE_EMPTY'];
			ajax_return($data);
		}

		if(!check_mobile($user_mobile))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'];
			ajax_return($data);
		}

		if($GLOBALS['user_info']['mobile'] != $user_mobile){
			$data['status'] = 0;
			$data['info'] = "旧手机号码错误";
			ajax_return($data);
		}

		if(!check_ipop_limit(CLIENT_IP,"old_mobile_verify",60,0))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_CODE_SEND_FAST'];
			ajax_return($data);
		}

		/*原有代码
		//开始生成手机验证
		$verify_data['verify'] = rand(111111,999999);
		$verify_data['verify_create_time'] = TIME_UTC;

		$GLOBALS['db']->autoExecute(DB_PREFIX."user",$verify_data,"UPDATE","id=".$GLOBALS['user_info']['id']);

		send_verify_sms($user_mobile,$verify_data['verify'],$GLOBALS['user_info'],true);
		$data['status'] = 1;
		$data['info'] = $GLOBALS['lang']['MOBILE_VERIFY_SEND_OK'];
		ajax_return($data);
		*/

		//开始生成手机验证
		$verify_data['verify_code'] = rand(111111,999999);
		$verify_data['mobile'] = $user_mobile;
		$verify_data['create_time'] = TIME_UTC;
		$verify_data['client_ip'] = CLIENT_IP;
		$verify_data['send_count'] = 1;
		$t = time();
		$begin_time = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t)); 	//今天开始时间
		if($info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$user_mobile."'")){
			if($info['create_time'] < $begin_time){
				$verify_data['send_count'] = 1;
			}
			else{
				$verify_data['send_count'] = $info['send_count'] + 1;
			}
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"UPDATE","id=".$info['id']);
		}
		else
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"INSERT");

		send_verify_sms($user_mobile,$verify_data['verify_code'],'',true);
		$data['status'] = 1;
		$data['info'] = $GLOBALS['lang']['MOBILE_VERIFY_SEND_OK'];
		ajax_return($data);
	}
	
	//用户注册_生成邮箱验证码
	public function get_email_verify()
	{
		//开始生成邮箱验证码
		$verify_data['verify_code'] = rand(111111,999999);
		$verify_data['email'] = $_REQUEST['user_email'];
		$verify_data['create_time'] = TIME_UTC;
		$verify_data['client_ip'] = CLIENT_IP;
		
		if($vid = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."email_verify_code WHERE email='".$verify_data['email']."'"))
			$GLOBALS['db']->autoExecute(DB_PREFIX."email_verify_code",$verify_data,"UPDATE","id=".$vid);
		else
			$GLOBALS['db']->autoExecute(DB_PREFIX."email_verify_code",$verify_data,"INSERT");
		$email_x = $verify_data['email'];
		send_user_verify_mail($email_x,$verify_data['verify_code'],null,true);
		$data['status'] = 1;
		$data['info'] = "验证邮件已经发送，请注意查收";
		ajax_return($data);
	}
	
	//密码取回_生成邮箱验证码
	public function get_email_verifyss()
	{
		$email = $verify_data['email'] = strim($_REQUEST['user_email']);
		if(!check_email($email)){
			$data['status'] = 0;
			$data['info'] = "邮箱格式错误";
			ajax_return($data);
		}
		$user_info =  get_user_info("*","email_encrypt=AES_ENCRYPT('".$email."','".AES_DECRYPT_KEY."')","ROW");
		if(!$user_info){
			$data['status'] = 0;
			$data['info'] = "邮箱对应会员不存在";
			ajax_return($data);
		}
		//开始生成邮箱验证码
		$verify_data['verify_code'] = rand(111111,999999);
		$verify_data['create_time'] = TIME_UTC;
		$verify_data['client_ip'] = CLIENT_IP;

		if($vid = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."email_verify_code WHERE email='".$verify_data['email']."'"))
			$GLOBALS['db']->autoExecute(DB_PREFIX."email_verify_code",$verify_data,"UPDATE","id=".$vid);
		else
			$GLOBALS['db']->autoExecute(DB_PREFIX."email_verify_code",$verify_data,"INSERT");

		send_user_verify_mails($email,$verify_data['verify_code'],$user_info);
		$data['status'] = 1;
		$data['info'] = "验证邮件已经发送，请注意查收";
		ajax_return($data);
	}
	
	//密码取回_生成邮箱验证码
	public function unit_get_email_verifyss()
	{
		$email = $verify_data['email'] = strim($_REQUEST['user_email']);
		if(!check_email($email)){
			$data['status'] = 0;
			$data['info'] = "邮箱格式错误";
			ajax_return($data);
		}
		$user_info =  get_user_info("count(*)","email_encrypt=AES_ENCRYPT('".$email."','".AES_DECRYPT_KEY."')","ONE");
		if(!$user_info){
			$data['status'] = 0;
			$data['info'] = "邮箱对应会员不存在";
			ajax_return($data);
		}
		//开始生成邮箱验证码
		$verify_data['verify_code'] = rand(111111,999999);
		$verify_data['create_time'] = TIME_UTC;
		$verify_data['client_ip'] = CLIENT_IP;

		if($vid = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."email_verify_code WHERE email='".$verify_data['email']."'"))
			$GLOBALS['db']->autoExecute(DB_PREFIX."email_verify_code",$verify_data,"UPDATE","id=".$vid);
		else
			$GLOBALS['db']->autoExecute(DB_PREFIX."email_verify_code",$verify_data,"INSERT");
		
		
		
		
		send_user_verify_mails($email,$verify_data['verify_code'],$user_info);
		$data['status'] = 1;
		$data['info'] = "验证邮件已经发送，请注意查收";
		ajax_return($data);
	}
	
	//邮箱绑定_发送保存验证码
	public function get_authorized_email_verifys()
	{
		$GLOBALS['authorized_info']  = es_session::get("authorized_info");
		$new_email = strim($_REQUEST['user_email']);
		//开始生成邮箱验证码
		$user_count= get_user_info("count(*)","email_encrypt=AES_ENCRYPT('".$new_email."','".AES_DECRYPT_KEY."') and id<>".intval($GLOBALS['authorized_info']['id']),"ONE");
		if($user_count)
		{
			$data['status'] =0;
			$data['info'] = "该邮箱已被其他用户绑定";
			ajax_return($data);
		}
		$email = $new_email;
		$user_id = intval($GLOBALS['authorized_info']['id']);
		$code = rand(111111,999999);
		$GLOBALS['db']->query("update ".DB_PREFIX."user set verify = '".$code."',verify_create_time = '".TIME_UTC."' where id = ".$user_id);
		
		send_user_verify_mails($email,$code,$GLOBALS['authorized_info']);
		$data['status'] = 1;
		$data['info'] = "验证邮件已经发送，请注意查收";
		ajax_return($data);
		//if($vid = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."user WHERE email='".$verify_data['email']."'"))
	}
	
	//邮箱绑定_发送保存验证码
	public function get_email_verifys()
	{
		$new_email = strim($_REQUEST['user_email']);
		//开始生成邮箱验证码
		$user= get_user_info("*","email_encrypt=AES_ENCRYPT('".$new_email."','".AES_DECRYPT_KEY."') and id<>".intval($GLOBALS['user_info']['id']));
		if($user)
		{
			$data['status'] =0;
			$data['info'] = "该邮箱已被其他用户绑定";
			ajax_return($data);
		}
		$email = $new_email;
		$user_id = intval($GLOBALS['user_info']['id']);
		$code = rand(111111,999999);
		$GLOBALS['db']->query("update ".DB_PREFIX."user set verify = '".$code."',verify_create_time = '".TIME_UTC."' where id = ".$user_id);
		
		send_user_verify_mails($email,$code,$GLOBALS['user_info']);
		$data['status'] = 1;
		$data['info'] = "验证邮件已经发送，请注意查收";
		ajax_return($data);
		//if($vid = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."user WHERE email='".$verify_data['email']."'"))
	}
	
	//邮箱绑定_发送保存验证码--担保机构
	public function get_unit_email_verifys()
	{
		$GLOBALS['manageagency_info']  = es_session::get("manageagency_info");
		$new_email = strim($_REQUEST['user_email']);
		//开始生成邮箱验证码
		$user= get_user_info("*","email_encrypt=AES_ENCRYPT('".$new_email."','".AES_DECRYPT_KEY."') and id<>".intval($GLOBALS['manageagency_info']['id']));
		if($user)
		{
			$data['status'] = 0;
			$data['info'] = "该邮箱已被其他用户绑定";
			ajax_return($data);
		}
		$email = $new_email;
		
		$user_id = intval($GLOBALS['manageagency_info']['id']);
		$code = rand(111111,999999);
		$GLOBALS['db']->query("update ".DB_PREFIX."user set verify = '".$code."',verify_create_time = '".TIME_UTC."' where id = ".$user_id);
		
		send_user_verify_mails($email,$code,$GLOBALS['manageagency_info']);
		$data['status'] = 1;
		$data['info'] = "验证邮件已经发送，请注意查收";
		ajax_return($data);
		//if($vid = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."user WHERE email='".$verify_data['email']."'"))
	}
	
	//获取手机注册验证码
	public function get_register_verify_code()
	{
		$user_mobile = strim($_REQUEST['user_mobile']);
		$verify =  strim($_REQUEST['smsverify']);
		$ajax =  strim($_REQUEST['ajax']);
		if(!preg_match("/^1[34578]\d{9}$/", $user_mobile)){
			$data['status'] = 0;
			$data['info'] = "请输入正确的手机号码";
			ajax_return($data);
		}
		$t = time();
		$begin_time = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t)); 	//今天开始时间 
		$result = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."mobile_verify_code where mobile =".$user_mobile." and create_time >".$begin_time);
		if(app_conf("SMS_ON")==0)
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['SMS_OFF'];
			ajax_return($data);
		}	

		if($user_mobile == '')
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_MOBILE_EMPTY'];
			ajax_return($data);
		}

		if(!check_mobile($user_mobile))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'];
			ajax_return($data);
		}
		//避免手机重复注册
		$info = $GLOBALS['db']->getRow("SELECT id FROM ".DB_PREFIX."user WHERE mobile ='".$user_mobile."'");
		if($info  > 0){
			$data['status'] = 0;
			$data['info'] = "手机号码已被注册";
			ajax_return($data);
		}
		if($ajax == 1){
			if($result && $result['send_count'] >=3){
				$return["status"] = 3;
				$return["info"] = "获取次数过多，请输入图文验证码";
				ajax_return($return);   
			}
		}
		if($verify){
			if(!checkVeifyCode($verify)){
				$data['status'] = 0;
				$data['info'] = "图文验证码错误";
				ajax_return($data);
			}
		}
		if(!check_ipop_limit(CLIENT_IP,"register_mobile_verify",60,0))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_CODE_SEND_FAST'];
			ajax_return($data);
		}
		if($GLOBALS['db']->getOne("select send_count from ".DB_PREFIX."mobile_verify_code where mobile =".$user_mobile." and create_time >".$begin_time) >= SEND_VERIFYSMS_LIMIT){
			$data['status'] = 0;
			$data['info'] = "你今天已经不能再发验证码了";
			ajax_return($data);
		}		
		/*if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE client_ip='".CLIENT_IP."'  AND  create_time >=".(TIME_UTC - 60)."  ") > 0){
			$data['status'] = 0;
			$data['info'] = "请稍后再试";
			ajax_return($data);
		}*/
		//开始生成手机验证
		$verify_data['verify_code'] = rand(111111,999999);
		$verify_data['mobile'] = $user_mobile;
		$verify_data['create_time'] = TIME_UTC;
		$verify_data['client_ip'] = CLIENT_IP;
		$verify_data['send_count'] = 1;
		
		if($info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$user_mobile."'")){
			if($info['create_time'] < $begin_time){
				$verify_data['send_count'] = 1;
			}
			else{
				$verify_data['send_count'] = $info['send_count'] + 1;
			}
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"UPDATE","id=".$info['id']);	
		}
		else
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"INSERT");
		
		send_verify_sms($user_mobile,$verify_data['verify_code'],'',true);
		$data['status'] = 1;
		$data['info'] = $GLOBALS['lang']['MOBILE_VERIFY_SEND_OK'];
		ajax_return($data);
	}
	//wap端邀请注册页
	/* status 2 手机验证提示 3 图形验证码提示
	****/
	public function get_wap_register_verify_code()
	{
		$user_mobile = strim($_REQUEST['user_mobile']);
		$verify =  strim($_REQUEST['smsverify']);
		$ajax =  strim($_REQUEST['ajax']);
		if(!preg_match("/^1[34578]\d{9}$/", $user_mobile)){
			$data['status'] = 2;
			$data['info'] = "请输入正确的手机号码";
			ajax_return($data);
		}
		$t = time();
		$begin_time = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t)); 	//今天开始时间 
		$result = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."mobile_verify_code where mobile =".$user_mobile." and create_time >".$begin_time);
		if(app_conf("SMS_ON")==0)
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['SMS_OFF'];
			ajax_return($data);
		}	

		if($user_mobile == '')
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_MOBILE_EMPTY'];
			ajax_return($data);
		}

		if(!check_mobile($user_mobile))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'];
			ajax_return($data);
		}
		//避免手机重复注册
		$info = $GLOBALS['db']->getRow("SELECT id FROM ".DB_PREFIX."user WHERE mobile ='".$user_mobile."'");
		if($info  > 0){
			$data['status'] = 2;
			$data['info'] = "手机号码已被注册";
			ajax_return($data);
		}
		if($verify){
			if(!checkVeifyCode($verify)){
				$data['status'] = 0;
				$data['info'] = "图文验证码错误";
				ajax_return($data);
			}
		}
		if(!check_ipop_limit(CLIENT_IP,"register_mobile_verify",60,0))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_CODE_SEND_FAST'];
			ajax_return($data);
		}
		if($GLOBALS['db']->getOne("select send_count from ".DB_PREFIX."mobile_verify_code where mobile =".$user_mobile." and create_time >".$begin_time) >= SEND_VERIFYSMS_LIMIT){
			$data['status'] = 0;
			$data['info'] = "你今天已经不能再发验证码了";
			ajax_return($data);
		}		
		/*if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE client_ip='".CLIENT_IP."'  AND  create_time >=".(TIME_UTC - 60)."  ") > 0){
			$data['status'] = 0;
			$data['info'] = "请稍后再试";
			ajax_return($data);
		}*/
		//开始生成手机验证
		$verify_data['verify_code'] = rand(111111,999999);
		$verify_data['mobile'] = $user_mobile;
		$verify_data['create_time'] = TIME_UTC;
		$verify_data['client_ip'] = CLIENT_IP;
		$verify_data['send_count'] = 1;
		
		if($info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$user_mobile."'")){
			if($info['create_time'] < $begin_time){
				$verify_data['send_count'] = 1;
			}
			else{
				$verify_data['send_count'] = $info['send_count'] + 1;
			}
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"UPDATE","id=".$info['id']);	
		}
		else
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"INSERT");
		
		send_verify_sms($user_mobile,$verify_data['verify_code'],'',true);
		$data['status'] = 1;
		$data['info'] = $GLOBALS['lang']['MOBILE_VERIFY_SEND_OK'];
		ajax_return($data);
	}
	//获取更改绑定手机号码 验证码
	public function get_bind_mobile_code()
	{
		if(!$_POST)
		{
			 app_redirect("404.html");
			 exit();
		}
		foreach($_POST as $k=>$v)
		{
			$data[$k] = htmlspecialchars(addslashes($v));
		}
		$user_mobile = strim($data['user_mobile']);
		$verify =  strim($data['verify']);
		$ajax =  strim($data['ajax']);
		$t = time();
		$begin_time = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t)); 	//今天开始时间 
		$result = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."mobile_verify_code where mobile =".$user_mobile." and create_time >".$begin_time);
		if(app_conf("SMS_ON")==0)
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['SMS_OFF'];
			ajax_return($data);
		}	
		if($user_mobile == '')
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_MOBILE_EMPTY'];
			ajax_return($data);
		}

		if(!check_mobile($user_mobile))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'];
			ajax_return($data);
		}
		// if(!check_ipop_limit(CLIENT_IP,"register_mobile_verify",60,0))
		// {
		// 	$data['status'] = 0;
		// 	$data['info'] = $GLOBALS['lang']['VERIFY_CODE_SEND_FAST'];
		// 	ajax_return($data);
		// }
		if($GLOBALS['db']->getOne("select send_count from ".DB_PREFIX."mobile_verify_code where mobile =".$user_mobile." and create_time >".$begin_time) >= SEND_VERIFYSMS_LIMIT){
			$data['status'] = 0;
			$data['info'] = "你今天已经不能再发验证码了";
			ajax_return($data);
		}		
		/*if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE client_ip='".CLIENT_IP."'  AND  create_time >=".(TIME_UTC - 60)."  ") > 0){
			$data['status'] = 0;
			$data['info'] = "请稍后再试";
			ajax_return($data);
		}*/
		if($verify){
			if(!checkVeifyCode($verify)){
				$data['status'] = 0;
				$data['info'] = "图形验证码错误";
				ajax_return($data);
			}
		}else{
			if($ajax == 1){
				if($result && $result['send_count'] >=3){
					$return["status"] = 0;
					$return["info"] = "获取次数过多，请输入图文验证码";
					ajax_return($return);   
				}
			}
		}
		es_session::set("user_mobile_info",$user_mobile);
		//开始生成手机验证
		$verify_data['verify_code'] = rand(111111,999999);
		$verify_data['mobile'] = $user_mobile;
		$verify_data['create_time'] = TIME_UTC;
		$verify_data['client_ip'] = CLIENT_IP;
		$verify_data['send_count'] = 1;
		
		if($info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$user_mobile."'")){
			if($info['create_time'] < $begin_time){
				$verify_data['send_count'] = 1;
			}
			else{
				$verify_data['send_count'] = $info['send_count'] + 1;
			}
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"UPDATE","id=".$info['id']);	
		}
		else
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"INSERT");
		
		send_verify_sms($user_mobile,$verify_data['verify_code'],'',true);
		$data['status'] = 1;
		$data['info'] = $GLOBALS['lang']['MOBILE_VERIFY_SEND_OK'];
		ajax_return($data);
	}
	public function get_bind_mobile_code_step_two()
	{
		if(!$_POST)
		{
			 app_redirect("404.html");
			 exit();
		}
		foreach($_POST as $k=>$v)
		{
			$data[$k] = htmlspecialchars(addslashes($v));
		}
		$user_mobile = strim($data['user_mobile']);
		$verify =  strim($data['verify']);
		$ajax =  strim($data['ajax']);
		$t = time();
		$begin_time = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t)); 	//今天开始时间 
		$result = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."mobile_verify_code where mobile =".$user_mobile." and create_time >".$begin_time);
		if(app_conf("SMS_ON")==0)
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['SMS_OFF'];
			ajax_return($data);
		}	
		if($user_mobile == '')
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_MOBILE_EMPTY'];
			ajax_return($data);
		}
		if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."user where mobile =".$user_mobile) == 1){
			$data['status'] = 0;
			$data['info'] = "该手机号已被注册";
			ajax_return($data);
		}
		if(!check_mobile($user_mobile))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'];
			ajax_return($data);
		}
		// if(!check_ipop_limit(CLIENT_IP,"register_mobile_verify",60,0))
		// {
		// 	$data['status'] = 0;
		// 	$data['info'] = $GLOBALS['lang']['VERIFY_CODE_SEND_FAST'];
		// 	ajax_return($data);
		// }
		if($GLOBALS['db']->getOne("select send_count from ".DB_PREFIX."mobile_verify_code where mobile =".$user_mobile." and create_time >".$begin_time) >= SEND_VERIFYSMS_LIMIT){
			$data['status'] = 0;
			$data['info'] = "你今天已经不能再发验证码了";
			ajax_return($data);
		}		
		/*if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE client_ip='".CLIENT_IP."'  AND  create_time >=".(TIME_UTC - 60)."  ") > 0){
			$data['status'] = 0;
			$data['info'] = "请稍后再试";
			ajax_return($data);
		}*/
		if($verify){
			if(!checkVeifyCode($verify)){
				$data['status'] = 0;
				$data['info'] = "图形验证码错误";
				ajax_return($data);
			}
		}else{
			if($ajax == 1){
				if($result && $result['send_count'] >=3){
					$return["status"] = 0;
					$return["info"] = "获取次数过多，请输入图文验证码";
					ajax_return($return);   
				}
			}
		}
		//开始生成手机验证
		$verify_data['verify_code'] = rand(111111,999999);
		$verify_data['mobile'] = $user_mobile;
		$verify_data['create_time'] = TIME_UTC;
		$verify_data['client_ip'] = CLIENT_IP;
		$verify_data['send_count'] = 1;
		
		if($info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$user_mobile."'")){
			if($info['create_time'] < $begin_time){
				$verify_data['send_count'] = 1;
			}
			else{
				$verify_data['send_count'] = $info['send_count'] + 1;
			}
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"UPDATE","id=".$info['id']);	
		}
		else
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"INSERT");
		
		send_verify_sms($user_mobile,$verify_data['verify_code'],'',true);
		$data['status'] = 1;
		$data['info'] = $GLOBALS['lang']['MOBILE_VERIFY_SEND_OK'];
		ajax_return($data);
	}
	public function get_pwd_verify_code()
	{
		if(app_conf("SMS_ON")==0)
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['SMS_OFF'];
			ajax_return($data);
		}
		$user_mobile = strim($_REQUEST['user_mobile']);

		if($user_mobile == '')
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_MOBILE_EMPTY'];
			ajax_return($data);
		}

		if(!check_mobile($user_mobile))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'];
			ajax_return($data);
		}
		
		if($users = get_user_info("*","mobile_encrypt=AES_ENCRYPT('".$user_mobile."','".AES_DECRYPT_KEY."') ")){
			if($users['is_delete'] == 1 || $users['is_effect']==0){
				$data['status'] = 0;
				$data['info'] = "会员被锁定或者删除";
				ajax_return($data);
			}
		}
		else{
			$data['status'] = 0;
			$data['info'] = "会员不存在";
			ajax_return($data);
		}

		if(!check_ipop_limit(CLIENT_IP,"register_mobile_verify",60,0))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_CODE_SEND_FAST'];
			ajax_return($data);
		}


		//开始生成手机验证
		$verify_data['verify_code'] = rand(111111,999999);
		$verify_data['mobile'] = $user_mobile;
		$verify_data['create_time'] = TIME_UTC;
		$verify_data['client_ip'] = CLIENT_IP;
		
		if($vid = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$user_mobile."'"))
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"UPDATE","id=".$vid);	
		else
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"INSERT");
		
		send_verify_sms($user_mobile,$verify_data['verify_code'],true);
		$data['status'] = 1;
		$data['info'] = $GLOBALS['lang']['MOBILE_VERIFY_SEND_OK'];
		ajax_return($data);
	}
	
	//手机验证短信_取回密码
	public function get_re_pwd_verify_code()
	{
		if(!$_POST)
		{
			 app_redirect("404.html");
			 exit();
		}
		foreach($_POST as $k=>$v)
		{
			$data[$k] = htmlspecialchars(addslashes($v));
		}
		if(app_conf("SMS_ON")==0)
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['SMS_OFF'];
			ajax_return($data);
		}
		$user_mobile = strim($data['user_mobile']);
		$verify = strim($data['verify']);
		if($user_mobile == '')
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_MOBILE_EMPTY'];
			ajax_return($data);
		}
		if(!check_mobile($user_mobile))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'];
			ajax_return($data);
		}
		$get_user_info = get_user_info("mobile_encrypt","mobile_encrypt =AES_ENCRYPT('".$user_mobile."','".AES_DECRYPT_KEY."') ");
		if(empty($get_user_info)){
			$data['status'] = 0;
			$data['info'] = "手机号码尚未注册";
			ajax_return($data);
		}
		/*if($users = get_user_info("*","mobile_encrypt=AES_ENCRYPT('".$user_mobile."','".AES_DECRYPT_KEY."')")){
			if($users['is_delete'] == 1 || $users['is_effect']==0){
				$data['status'] = 0;
				$data['info'] = "会员被锁定或者删除";
				ajax_return($data);
			}
		}
		else{
			$data['status'] = 0;
			$data['info'] = "会员不存在";
			ajax_return($data);
		}*/

		if(!check_ipop_limit(CLIENT_IP,"register_mobile_verify",60,0))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_CODE_SEND_FAST'];
			ajax_return($data);
		}
		if($verify){
			if(!checkVeifyCode($verify)){
				$data['status'] = 0;
				$data['info'] = "图形验证码错误";
				ajax_return($data);
			}
		}else{
			$sms_count = 1 + es_session::get('sms_count');
			es_session::set("sms_count",$sms_count);
			if(es_session::get('sms_count') >= 3){
				$data['status'] = 0;
				$data['info'] = "获取次数过多，请输入图文验证码";
				ajax_return($data);
			}
		}

		//开始生成手机验证
		$verify_data['verify_code'] = rand(111111,999999);
		$verify_data['mobile'] = $user_mobile;
		$verify_data['create_time'] = TIME_UTC;
		$verify_data['client_ip'] = CLIENT_IP;

		if($vid = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$user_mobile."'"))
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"UPDATE","id=".$vid);
		else
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"INSERT");

		send_verify_sms($user_mobile,$verify_data['verify_code'],$users,true);
		$data['status'] = 1;
		$data['info'] = $GLOBALS['lang']['MOBILE_VERIFY_SEND_OK'];
		ajax_return($data);
	}
	//绑定银行卡 获取验证码
	public function get_identity_bank_code()
	{
		if(!$_POST)
		{
			 app_redirect("404.html");
			 exit();
		}
		foreach($_POST as $k=>$v)
		{
			$user_data[$k] = htmlspecialchars(addslashes($v));
		}
		if(app_conf("SMS_ON")==0)
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['SMS_OFF'];
			ajax_return($data);
		}
		$user_mobile = strim($user_data['mobile']);
		if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."user where idno =".$user_data['idno']) == 1){
			$data['status'] = 0;
			$data['info'] = "身份证已经被绑定";
			ajax_return($data);
		}
		if($user_mobile == '')
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_MOBILE_EMPTY'];
			ajax_return($data);
		}
		if(!check_mobile($user_mobile))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'];
			ajax_return($data);
		}
		$verify = strim($user_data['verify']);
		if($verify){
			if(!checkVeifyCode($verify)){
				$data['status'] = 0;
				$data['info'] = "图形验证码错误";
				ajax_return($data);
			}
		}else{
			$sms_count = 1 + es_session::get('sms_count');
			es_session::set("sms_count",$sms_count);
			if(es_session::get('sms_count') >= 3){
				$data['status'] = 2;
				$data['info'] = "获取次数过多，请输入图文验证码";
				ajax_return($data);
			}
		}
		if(!check_ipop_limit(CLIENT_IP,"register_mobile_verify",60,0))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_CODE_SEND_FAST'];
			ajax_return($data);
		}


		//开始生成手机验证
		$verify_data['verify_code'] = rand(111111,999999);
		$verify_data['mobile'] = $user_mobile;
		$verify_data['create_time'] = TIME_UTC;
		$verify_data['client_ip'] = CLIENT_IP;

		if($vid = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$user_mobile."'"))
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"UPDATE","id=".$vid);
		else
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"INSERT");

		send_verify_sms($user_mobile,$verify_data['verify_code'],$users,true);
		$data['status'] = 1;
		$data['info'] = $GLOBALS['lang']['MOBILE_VERIFY_SEND_OK'];
		ajax_return($data);
	}
	//绑定银行卡 获取验证码
	public function res_identity_bank_code()
	{
		if(!$_POST)
		{
			 app_redirect("404.html");
			 exit();
		}
		foreach($_POST as $k=>$v)
		{
			$user_data[$k] = htmlspecialchars(addslashes($v));
		}
		if(app_conf("SMS_ON")==0)
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['SMS_OFF'];
			ajax_return($data);
		}
		$user_mobile = strim($user_data['mobile']);
		if($user_mobile == '')
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_MOBILE_EMPTY'];
			ajax_return($data);
		}
		if(!check_mobile($user_mobile))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'];
			ajax_return($data);
		}
		$verify = strim($user_data['verify']);
		if($verify){
			if(!checkVeifyCode($verify)){
				$data['status'] = 0;
				$data['info'] = "图形验证码错误";
				ajax_return($data);
			}
		}else{
			$sms_count = 1 + es_session::get('sms_count');
			es_session::set("sms_count",$sms_count);
			if(es_session::get('sms_count') >= 3){
				$data['status'] = 2;
				$data['info'] = "获取次数过多，请输入图文验证码";
				ajax_return($data);
			}
		}
		if(!check_ipop_limit(CLIENT_IP,"register_mobile_verify",60,0))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_CODE_SEND_FAST'];
			ajax_return($data);
		}


		//开始生成手机验证
		$verify_data['verify_code'] = rand(111111,999999);
		$verify_data['mobile'] = $user_mobile;
		$verify_data['create_time'] = TIME_UTC;
		$verify_data['client_ip'] = CLIENT_IP;

		if($vid = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$user_mobile."'"))
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"UPDATE","id=".$vid);
		else
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"INSERT");

		send_verify_sms($user_mobile,$verify_data['verify_code'],$users,true);
		$data['status'] = 1;
		$data['info'] = $GLOBALS['lang']['MOBILE_VERIFY_SEND_OK'];
		ajax_return($data);
	}

    //存管绑定银行卡 获取验证码
    public function cg_res_identity_bank_code()
    {
        if(!$_POST)
        {
            app_redirect("404.html");
            exit();
        }
        foreach($_POST as $k=>$v)
        {
            $user_data[$k] = htmlspecialchars(addslashes($v));
        }
        if(app_conf("SMS_ON")==0)
        {
            $data['status'] = 0;
            $data['info'] = $GLOBALS['lang']['SMS_OFF'];
            ajax_return($data);
        }
        $user_mobile = strim($user_data['mobile']);
        if($user_mobile == '')
        {
            $data['status'] = 0;
            $data['info'] = $GLOBALS['lang']['VERIFY_MOBILE_EMPTY'];
            ajax_return($data);
        }
        if(!check_mobile($user_mobile))
        {
            $data['status'] = 0;
            $data['info'] = $GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'];
            ajax_return($data);
        }
        /*$verify = strim($user_data['verify']);
        if($verify){
            if(!checkVeifyCode($verify)){
                $data['status'] = 0;
                $data['info'] = "图形验证码错误";
                ajax_return($data);
            }
        }else{
            $sms_count = 1 + es_session::get('sms_count');
            es_session::set("sms_count",$sms_count);
            if(es_session::get('sms_count') >= 3){
                $data['status'] = 2;
                $data['info'] = "获取次数过多，请输入图文验证码";
                ajax_return($data);
            }
        }*/
        if(!check_ipop_limit(CLIENT_IP,"register_mobile_verify",60,0))
        {
            $data['status'] = 0;
            $data['info'] = $GLOBALS['lang']['VERIFY_CODE_SEND_FAST'];
            ajax_return($data);
        }


        //开始生成手机验证
        $verify_data['verify_code'] = rand(111111,999999);
        $verify_data['mobile'] = $user_mobile;
        $verify_data['create_time'] = TIME_UTC;
        $verify_data['client_ip'] = CLIENT_IP;

        if($vid = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$user_mobile."'"))
            $GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"UPDATE","id=".$vid);
        else
            $GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"INSERT");

        send_verify_sms($user_mobile,$verify_data['verify_code'],$users,true);
        $data['status'] = 1;
        $data['info'] = $GLOBALS['lang']['MOBILE_VERIFY_SEND_OK'];
        ajax_return($data);
    }

	//修改交易密码
	public function get_trade_pwd_code(){
		//$redis = new RedisCluster();
		//$redis->connect(array('host'=>REDIS_HOST,'port'=>REDIS_PORT));
		if(!$_POST)
		{
			 app_redirect("404.html");
			 exit();
		}
		foreach($_POST as $k=>$v)
		{
			$data[$k] = htmlspecialchars(addslashes($v));
		}
		$user_mobile = strim($data['user_mobile']);
		$verify = strim($data['verify']);
		if(app_conf("SMS_ON")==0)
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['SMS_OFF'];
			ajax_return($data);
		}
		$pwd = md5(strim(FW_DESPWD($_REQUEST['pwd'])));
		if($pwd != $GLOBALS['db']->getOne("SELECT paypassword FROM ".DB_PREFIX."user WHERE id=".$GLOBALS['user_info']['id'])){
			$data['status'] = 0;
			$data['info'] = "原密码错误";
			ajax_return($data);
		}
		/*if($users = get_user_info("*","mobile_encrypt=AES_ENCRYPT('".$user_mobile."','".AES_DECRYPT_KEY."')")){
			if($users['is_delete'] == 1 || $users['is_effect']==0){
				$data['status'] = 0;
				$data['info'] = "会员被锁定或者删除";
				ajax_return($data);
			}
		}
		else{
			$data['status'] = 0;
			$data['info'] = "会员不存在";
			ajax_return($data);
		}*/

		if(!check_ipop_limit(CLIENT_IP,"register_mobile_verify",60,0))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_CODE_SEND_FAST'];
			ajax_return($data);
		}
		if($verify){
			if(!checkVeifyCode($verify)){
				$data['status'] = 0;
				$data['info'] = "图形验证码错误";
				ajax_return($data);
			}
		}else{			
			/*$sms_count = 1 + $redis->get($_REQUEST['user_mobile']);
			$redis->set($_REQUEST['user_mobile'],$sms_count);
			if($redis->get($_REQUEST['user_mobile']) >= 3){
				$data['status'] = 2;
				$data['info'] = "获取次数过多，请输入图文验证码";
				ajax_return($data);
			}*/
			$sms_count = 1 + es_session::get('sms_count');
			es_session::set("sms_count",$sms_count);
			if(es_session::get('sms_count') >= 3){
				$data['status'] = 0;
				$data['info'] = "获取次数过多，请输入图文验证码";
				ajax_return($data);
			}
		}

		//开始生成手机验证
		$verify_data['verify_code'] = rand(111111,999999);
		$verify_data['mobile'] = $user_mobile;
		$verify_data['create_time'] = TIME_UTC;
		$verify_data['client_ip'] = CLIENT_IP;

		if($vid = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$user_mobile."'"))
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"UPDATE","id=".$vid);
		else
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"INSERT");

		send_verify_sms($user_mobile,$verify_data['verify_code'],$users,true);
		$data['status'] = 1;
		$data['info'] = $GLOBALS['lang']['MOBILE_VERIFY_SEND_OK'];
		ajax_return($data);
	}

	//重置交易密码
	public function get_reset_trade_pwd_code(){
		/*$redis = new RedisCluster();
		$redis->connect(array('host'=>REDIS_HOST,'port'=>REDIS_PORT));*/
		if(app_conf("SMS_ON")==0)
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['SMS_OFF'];
			ajax_return($data);
		}
		$user_info = $GLOBALS['db']->getRow("SELECT AES_DECRYPT(idno_encrypt,'".AES_DECRYPT_KEY."') as idno,AES_DECRYPT(real_name_encrypt,'".AES_DECRYPT_KEY."') as real_name FROM ".DB_PREFIX."user WHERE id=".$GLOBALS['user_info']['id']);
		if($user_info['real_name'] != $_REQUEST["real_name"]){
			$data['status'] = 0;
			$data['info'] = "真实姓名不一致";
			ajax_return($data);
		}
		if($user_info['idno'] != $_REQUEST["id_card"]){
			$data['status'] = 0;
			$data['info'] = "身份证号不一致";
			ajax_return($data);
		}
		$user_mobile = strim($_REQUEST['user_mobile']);
		$verify = strim($_REQUEST['verify']);		
		if(!check_ipop_limit(CLIENT_IP,"register_mobile_verify",60,0))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_CODE_SEND_FAST'];
			ajax_return($data);
		}
		if($verify){
			if(!checkVeifyCode($verify)){
				$data['status'] = 0;
				$data['info'] = "图形验证码错误";
				ajax_return($data);
			}
		}else{
			$sms_count = 1 + es_session::get('sms_count');
			es_session::set("sms_count",$sms_count);
			if(es_session::get('sms_count') >= 3){
				$data['status'] = 0;
				$data['info'] = "获取次数过多，请输入图文验证码";
				ajax_return($data);
			}
		}

		//开始生成手机验证
		$verify_data['verify_code'] = rand(111111,999999);
		$verify_data['mobile'] = $user_mobile;
		$verify_data['create_time'] = TIME_UTC;
		$verify_data['client_ip'] = CLIENT_IP;
		if($vid = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$user_mobile."'"))
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"UPDATE","id=".$vid);
		else
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"INSERT");
		send_verify_sms($user_mobile,$verify_data['verify_code'],$users,true);
		$data['status'] = 1;
		$data['info'] = $GLOBALS['lang']['MOBILE_VERIFY_SEND_OK'];
		ajax_return($data);
	}
	//手机验证短信_取回密码----管理机构
	public function unit_get_re_pwd_verify_code()
	{
		if(app_conf("SMS_ON")==0)
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['SMS_OFF'];
			ajax_return($data);
		}
		$user_mobile = strim($_REQUEST['user_mobile']);

		if($user_mobile == '')
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_MOBILE_EMPTY'];
			ajax_return($data);
		}

		if(!check_mobile($user_mobile))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'];
			ajax_return($data);
		}

		if($users = get_user_info("*","mobile_encrypt=AES_ENCRYPT('".$user_mobile."','".AES_DECRYPT_KEY."')")){
			/*if($users['is_delete'] == 1 || $users['is_effect']==0){
				$data['status'] = 0;
				$data['info'] = "会员被锁定或者删除";
				ajax_return($data);
			}*/
		}
		else{
			$data['status'] = 0;
			$data['info'] = "会员不存在";
			ajax_return($data);
		}

		if(!check_ipop_limit(CLIENT_IP,"register_mobile_verify",60,0))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_CODE_SEND_FAST'];
			ajax_return($data);
		}


		//开始生成手机验证
		$verify_data['verify_code'] = rand(111111,999999);
		$verify_data['mobile'] = $user_mobile;
		$verify_data['create_time'] = TIME_UTC;
		$verify_data['client_ip'] = CLIENT_IP;

		if($vid = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$user_mobile."'"))
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"UPDATE","id=".$vid);
		else
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"INSERT");

		send_verify_sms($user_mobile,$verify_data['verify_code'],$users,true);
		$data['status'] = 1;
		$data['info'] = $GLOBALS['lang']['MOBILE_VERIFY_SEND_OK'];
		ajax_return($data);
	}
	
	//验证取回手机状态
	public function mobile_get_pwd_check_field()
	{
		$user_mobile = strim($_REQUEST['user_mobile']);
		
		if($user_mobile == '')
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_MOBILE_EMPTY'];
			ajax_return($data);
		}

		if(!check_mobile($user_mobile))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'];
			ajax_return($data);
		}
		
		if($users = get_user_info("is_delete,is_effect","mobile_encrypt = AES_ENCRYPT('".$user_mobile."','".AES_DECRYPT_KEY."')")){
			if($users['is_delete'] == 1 || $users['is_effect']==0){
				$data['status'] = 0;
				$data['info'] = "对应会员被锁定或者删除";
				ajax_return($data);
			}
		}
		else{
			$data['status'] = 0;
			$data['info'] = "对应会员不存在";
			ajax_return($data);
		}
		
		$data['status'] = 1;
		ajax_return($data);
	}
	//验证邮箱取回状态
	public function email_get_pwd_check_field()
	{
		$user_email = strim($_REQUEST['user_email']);

		if($user_email == '')
		{
			$data['status'] = 0;
			$data['info'] = "请输入邮箱";
			ajax_return($data);
		}
		
		if(!check_email($user_email)){
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['MAIL_FORMAT_ERROR'];
			ajax_return($data);
		}

		if($users = get_user_info("*","email_encrypt = AES_ENCRYPT('".$user_email."','".AES_DECRYPT_KEY."'")){

			if($users['is_delete'] == 1 || $users['is_effect']==0){
				$data['status'] = 0;
				$data['info'] = "对应会员被锁定或者删除";
				ajax_return($data);
			}

		}
		else{
			$data['status'] = 0;
			$data['info'] = "对应会员不存在";
			ajax_return($data);
		}

		$data['status'] = 1;
		ajax_return($data);
	}
	
	//验证取回手机状态---担保机构
	public function unit_mobile_get_pwd_check_field()
	{
		$user_mobile = strim($_REQUEST['user_mobile']);
		
		if($user_mobile == '')
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_MOBILE_EMPTY'];
			ajax_return($data);
		}

		if(!check_mobile($user_mobile))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'];
			ajax_return($data);
		}
		
		if($users = get_user_info("*","mobile_encrypt = AES_DECRYPT('".$user_mobile."','".AES_DECRYPT_KEY."')")){
			/*if($users['is_delete'] == 1 || $users['is_effect']==0){
				$data['status'] = 0;
				$data['info'] = "对应会员被锁定或者删除";
				ajax_return($data);
			}*/
		}
		else{
			$data['status'] = 0;
			$data['info'] = "对应会员不存在";
			ajax_return($data);
		}
		
		$data['status'] = 1;
		ajax_return($data);
	}
	
	//验证取回邮箱状态---担保机构
	public function unit_email_get_pwd_check_field()
	{
		$user_email = strim($_REQUEST['user_email']);
		
		if($user_email == '')
		{
			$data['status'] = 0;
			$data['info'] = "请输入邮箱";
			ajax_return($data);
		}
		
		if(!check_email($user_email)){
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['MAIL_FORMAT_ERROR'];
			ajax_return($data);
		}
		if(!$users = get_user_info("*","email_encrypt = AES_ENCRYPT('".$user_email."','".AES_DECRYPT_KEY."') ")){
			
			$data['status'] = 0;
			$data['info'] = "对应会员不存在";
			ajax_return($data);
		}

		$data['status'] = 1;
		ajax_return($data);
	}
	
	
	
	public function get_verify_code()
	{
		if(app_conf("SMS_ON")==0)
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['SMS_OFF'];
			ajax_return($data);		
		}
		$user_mobile = strim($_REQUEST['user_mobile']);
		$user_id = intval($GLOBALS['user_info']['id']);
		if($user_id == 0)
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
			ajax_return($data);
		}

		if($user_mobile == '')
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_MOBILE_EMPTY'];
			ajax_return($data);
		}
		
		if(!check_mobile($user_mobile))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'];
			ajax_return($data);
		}
		
		
		//查询是否有用户绑定
		$user= get_user_info("*","mobile_encrypt = AES_ENCRYPT('".$user_mobile."','".AES_DECRYPT_KEY."') and id <> ".$user_id);
		
		if($user)
		{
			if($user['id'] == intval($GLOBALS['user_info']['id']))
			{
				$data['status'] = 1;
				$data['info'] = $GLOBALS['lang']['MOBILE_VERIFIED'];
			}
			else
			{
				$data['status'] = 0;
				$data['info'] = $GLOBALS['lang']['MOBILE_USED_BIND'];
				
			}
			ajax_return($data);
			
		}
		
		if(!check_ipop_limit(CLIENT_IP,"bind_mobile_verify",60,0))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_CODE_SEND_FAST'];
			ajax_return($data);
		}
		
		//开始生成手机验证
		$verify_data['verify_code'] = rand(111111,999999);
		$verify_data['mobile'] = $user_mobile;
		$verify_data['create_time'] = TIME_UTC;
		$verify_data['client_ip'] = CLIENT_IP;

		if($vid = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$user_mobile."'"))
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"UPDATE","id=".$vid);
		else
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"INSERT");

		send_verify_sms($user_mobile,$verify_data['verify_code'],$GLOBALS['user_info'],true);
		$data['status'] = 1;
		$data['info'] = $GLOBALS['lang']['MOBILE_VERIFY_SEND_OK'];
		
		ajax_return($data);
	}
	
	public function get_unit_verify_code()
	{
		$GLOBALS['manageagency_info'] = es_session::get("manageagency_info");
		if(app_conf("SMS_ON")==0)
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['SMS_OFF'];
			ajax_return($data);		
		}
		$user_mobile = addslashes(htmlspecialchars(trim($_REQUEST['user_mobile'])));
		$user_id = intval($GLOBALS['manageagency_info']['id']);
		if($user_id == 0)
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
			ajax_return($data);
		}

		if($user_mobile == '')
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_MOBILE_EMPTY'];
			ajax_return($data);
		}
		
		if(!check_mobile($user_mobile))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'];
			ajax_return($data);
		}
		
		
		//查询是否有用户绑定
		$user= get_user_info("*","mobile_encrypt = AES_ENCRYPT('".$user_mobile."','".AES_DECRYPT_KEY."') and id <> ".$user_id);
		
		if($user)
		{
			if($user['id'] == intval($GLOBALS['manageagency_info']['id']))
			{
				$data['status'] = 1;
				$data['info'] = $GLOBALS['lang']['MOBILE_VERIFIED'];
			}
			else
			{
				$data['status'] = 0;
				$data['info'] = $GLOBALS['lang']['MOBILE_USED_BIND'];
			}
			
			ajax_return($data);
		}
		
		if(!check_ipop_limit(CLIENT_IP,"bind_mobile_verify",60,0))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_CODE_SEND_FAST'];
			ajax_return($data);
		}
		
		//开始生成手机验证
		$verify_data['verify_code'] = rand(111111,999999);
		$verify_data['mobile'] = $user_mobile;
		$verify_data['create_time'] = TIME_UTC;
		$verify_data['client_ip'] = CLIENT_IP;

		if($vid = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$user_mobile."'"))
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"UPDATE","id=".$vid);
		else
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"INSERT");

		send_verify_sms($user_mobile,$verify_data['verify_code'],$GLOBALS['manageagency_info'],true);
		$data['status'] = 1;
		$data['info'] = $GLOBALS['lang']['MOBILE_VERIFY_SEND_OK'];
		
		ajax_return($data);
	}
	
	public function get_authorized_verify_code()
	{
		$GLOBALS['authorized_info'] = es_session::get("authorized_info");
		if(app_conf("SMS_ON")==0)
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['SMS_OFF'];
			ajax_return($data);		
		}
		$user_mobile = addslashes(htmlspecialchars(trim($_REQUEST['user_mobile'])));
		$user_id = intval($GLOBALS['authorized_info']['id']);
		if($user_id == 0)
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
			ajax_return($data);
		}

		if($user_mobile == '')
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_MOBILE_EMPTY'];
			ajax_return($data);
		}
		
		if(!check_mobile($user_mobile))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'];
			ajax_return($data);
		}
		
		
		//查询是否有用户绑定
		$user= get_user_info("*","mobile_encrypt = AES_ENCRYPT('".$user_mobile."','".AES_DECRYPT_KEY."') and id <> ".$user_id);
		
		if($user)
		{
			if($user['id'] == intval($GLOBALS['manageagency_info']['id']))
			{
				$data['status'] = 1;
				$data['info'] = $GLOBALS['lang']['MOBILE_VERIFIED'];
			}
			else
			{
				$data['status'] = 0;
				$data['info'] = $GLOBALS['lang']['MOBILE_USED_BIND'];
			}
			
			ajax_return($data);
		}
		
		if(!check_ipop_limit(CLIENT_IP,"bind_mobile_verify",60,0))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_CODE_SEND_FAST'];
			ajax_return($data);
		}
		
		//开始生成手机验证
		$verify_data['verify_code'] = rand(111111,999999);
		$verify_data['mobile'] = $user_mobile;
		$verify_data['create_time'] = TIME_UTC;
		$verify_data['client_ip'] = CLIENT_IP;

		if($vid = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$user_mobile."'"))
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"UPDATE","id=".$vid);
		else
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"INSERT");
		
		send_verify_sms($user_mobile,$verify_data['verify_code'],$GLOBALS['authorized_info'],true);
		$data['status'] = 1;
		$data['info'] = $GLOBALS['lang']['MOBILE_VERIFY_SEND_OK'];
		
		ajax_return($data);
	}
	
//手机绑定
	public function check_verify_code(){
		$ajax = intval($_REQUEST['ajax']);
		$verify = strim($_REQUEST['verify']);
		$old_mobile = strim($_REQUEST['old_mobile']);
		if($GLOBALS['user_info']['mobile']!=""){
			if($old_mobile != $GLOBALS['user_info']['mobile']){
				showErr("原手机号码不正确",$ajax);
			}
			
			if(strim($_REQUEST['oldverify']) != $GLOBALS['user_info']['verify'] && $GLOBALS['user_info']['verify_create_time'] + SMS_EXPIRESPAN < TIME_UTC ){
				showErr("验证码不正确或已过期",$ajax);
			}
		}
		if($verify==""){
			showErr("验证码不能为空",$ajax);
		}
		
		$user_mobile = strim($_REQUEST['mobile']);
		
		$inum= get_user_info("count(*)","mobile_encrypt = AES_ENCRYPT('".$user_mobile."','".AES_DECRYPT_KEY."') and id <> ".intval($GLOBALS['user_info']['id']),"ONE");
		if ($inum > 0){
			showErr($user_mobile." 手机号码已被占用",$ajax);
		}

		if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$user_mobile."' AND verify_code='".$verify."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ")==0){
			showErr("手机验证码出错,或已过期",$ajax);
		}	
		else 
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."user set mobile_encrypt = AES_ENCRYPT('".$user_mobile."','".AES_DECRYPT_KEY."'),mobilepassed=1,verify='', bind_verify = '', verify_create_time = 0 where id = ".intval($GLOBALS['user_info']['id']));
			if($GLOBALS['db']->affected_rows() > 0){
				showSuccess($GLOBALS['lang']['MOBILE_BIND_SUCCESS'],$ajax);
			}
			else{
				showErr("绑定失败",$ajax);
			}
		}
		
	}
	
	//手机绑定
	public function check_unit_verify_code(){
		$GLOBALS['manageagency_info'] = es_session::get("manageagency_info");
		
		$GLOBALS['manageagency_info']  = get_user("*",intval($GLOBALS['manageagency_info']['id']));
		
		$ajax = intval($_REQUEST['ajax']);
		$verify = strim($_REQUEST['verify']);
		$old_mobile = strim($_REQUEST['old_mobile']);
		if($GLOBALS['manageagency_info']['mobile']!=""){
			if($old_mobile != $GLOBALS['manageagency_info']['mobile']){
				showErr("原手机号码不正确",$ajax);
			}
		}
		if($verify==""){
			showErr("验证码不能为空。");/*xsz  $GLOBALS['lang']['BIND_MOBILE_VERIFY_ERROR'],$ajax*/
		}
		$user = get_user("*",intval($GLOBALS['manageagency_info']['id']));
		$user_mobile = addslashes(htmlspecialchars(trim($_REQUEST['mobile'])));	
		
		$inum= get_user_info("count(*)","mobile_encrypt = AES_ENCRYPT('".$user_mobile."','".AES_DECRYPT_KEY."') and id <> ".intval($GLOBALS['manageagency_info']['id']),"ONE");
		if ($inum > 0){
			showErr($user_mobile." 手机号码已被占用",$ajax);
		}

		if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$user_mobile."' AND verify_code='".$verify."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ")==0){
			showErr("手机验证码出错,或已过期",$ajax);
		}	
		else 
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."user set mobile_encrypt = AES_ENCRYPT('".$user_mobile."','".AES_DECRYPT_KEY."'),mobilepassed=1, bind_verify = '', verify_create_time = 0 where id = ".intval($GLOBALS['manageagency_info']['id']));
			if($GLOBALS['db']->affected_rows() > 0){
				showSuccess($GLOBALS['lang']['MOBILE_BIND_SUCCESS'],$ajax);
			}
			else{
				showErr("绑定失败",$ajax);
			}
		}
	}
	
	//手机绑定
	public function check_authorized_verify_code(){
		$GLOBALS['authorized_info'] = es_session::get("authorized_info");
		
		$GLOBALS['authorized_info']  = get_user("*",intval($GLOBALS['authorized_info']['id']));
		
		$ajax = intval($_REQUEST['ajax']);
		$verify = strim($_REQUEST['verify']);
		$old_mobile = strim($_REQUEST['old_mobile']);
		if($GLOBALS['authorized_info']['mobile']!=""){
			if($old_mobile != $GLOBALS['authorized_info']['mobile']){
				showErr("原手机号码不正确",$ajax);
			}
		}
		if($verify==""){
			showErr("验证码不能为空。");/*xsz  $GLOBALS['lang']['BIND_MOBILE_VERIFY_ERROR'],$ajax*/
		}
		$user = get_user("*",intval($GLOBALS['authorized_info']['id']));
		$user_mobile = addslashes(htmlspecialchars(trim($_REQUEST['mobile'])));	
		$inum= get_user_info("count(*)","mobile_encrypt = AES_ENCRYPT('".$user_mobile."','".AES_DECRYPT_KEY."') and id <> ".intval($GLOBALS['authorized_info']['id']),"ONE");
		if ($inum > 0){
			showErr($user_mobile." 手机号码已被占用",$ajax);
		}

		if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$user_mobile."' AND verify_code='".$verify."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ")==0){
			showErr("手机验证码出错,或已过期",$ajax);
		}	
		else 
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."user set mobile_encrypt = AES_ENCRYPT('".$user_mobile."','".AES_DECRYPT_KEY."'),mobilepassed=1, bind_verify = '', verify_create_time = 0 where id = ".intval($GLOBALS['authorized_info']['id']));
			if($GLOBALS['db']->affected_rows() > 0){
				showSuccess($GLOBALS['lang']['MOBILE_BIND_SUCCESS'],$ajax);
			}
			else{
				showErr("绑定失败",$ajax);
			}
		}
	}
	
	public function get_paypwd_verify_code()
	{
		if(app_conf("SMS_ON")==0)
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['SMS_OFF'];
			ajax_return($data);		
		}
		
		$user_id = intval($GLOBALS['user_info']['id']);
		if($user_id == 0)
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
			ajax_return($data);
		}
		
		$user_mobile = $GLOBALS['user_info']['mobile'];
		
		//开始生成手机验证
		$verify_data['verify_code'] = rand(111111,999999);
		$verify_data['mobile'] = $user_mobile;
		$verify_data['create_time'] = TIME_UTC;
		$verify_data['client_ip'] = CLIENT_IP;

		if($vid = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$user_mobile."'"))
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"UPDATE","id=".$vid);
		else
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"INSERT");
		
		send_verify_sms($user_mobile,$verify_data['verify_code'],$GLOBALS['user_info'],true);
		$data['status'] = 1;
		$data['info'] = $GLOBALS['lang']['MOBILE_VERIFY_SEND_OK'];

		ajax_return($data);
	}
	// 设置交易密码   UC
	public function check_paypwd_verify_code(){
		$ajax = intval($_REQUEST['ajax']);
		$verify = strim($_REQUEST['verify']);
		if(!$GLOBALS['user_info']){
			showErr($GLOBALS['lang']['PLEASE_LOGIN_FIRST'],$ajax);
		}
		if($verify==""){
			showErr($GLOBALS['lang']['BIND_MOBILE_VERIFY_ERROR'],$ajax);
		}

		$paypassword = trim(FW_DESPWD($_REQUEST['paypassword']));
		
		if(!preg_match("/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,16}$/",$paypassword)){
			showErr("交易密码为6-16位字母数字组合",$ajax);
		}
		if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$GLOBALS['user_info']['mobile']."' AND verify_code='".$verify."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ")==0){
			showErr("手机验证码出错,或已过期",$ajax);
		}elseif(get_user_info('paypassword',"id=".intval($GLOBALS['user_info']['id']),'ONE')==md5($paypassword)){
			showErr("不能修改为原密码",$ajax);
		}else if(get_user_info('user_pwd',"id=".intval($GLOBALS['user_info']['id']),'ONE')==md5($paypassword)){
			showErr("交易密码不能与登录密码相同",$ajax);
		}
		else
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."user set paypassword='".md5($paypassword)."', bind_verify = '', verify_create_time = 0 where id = ".intval($GLOBALS['user_info']['id']));
			if($GLOBALS['db']->affected_rows() > 0){
				showSuccess("交易密码设置成功",$ajax);
			}
			else{
				showErr("绑定失败",$ajax);
			}
		}
	}
	//设置交易密码
	public function check_paypassword_code(){
		$ajax = intval($_REQUEST['ajax']);
		if(!$GLOBALS['user_info']){
			showErr($GLOBALS['lang']['PLEASE_LOGIN_FIRST'],$ajax);
		}

		$paypassword = FW_DESPWD(trim($_REQUEST['paypassword']));
		$user_pwd =  $GLOBALS['db']->getOne("SELECT user_pwd FROM ".DB_PREFIX."user where id =".$GLOBALS['user_info']['id']);
		if($user_pwd == MD5($paypassword)){
			showErr("交易密码不能是登录密码",$ajax);
		}
		$GLOBALS['db']->query("update ".DB_PREFIX."user set paypassword='".md5($paypassword)."', bind_verify = '', verify_create_time = 0 where id = ".intval($GLOBALS['user_info']['id']));
		if($GLOBALS['db']->affected_rows() > 0){
			showSuccess($GLOBALS['lang']['MOBILE_BIND_SUCCESS'],$ajax);
		}
		else{
			showErr("绑定失败",$ajax);
		}
	}
	public function get_authorized_paypwd_verify_code()
	{
		$authorized_info  = es_session::get("authorized_info");
		if(app_conf("SMS_ON")==0)
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['SMS_OFF'];
			ajax_return($data);		
		}
		
		$user_id = intval($authorized_info['id']);
		if($user_id == 0)
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
			ajax_return($data);
		}
		
		
		$user_mobile = $authorized_info['mobile'];

		//开始生成手机验证
		$verify_data['verify_code'] = rand(111111,999999);
		$verify_data['mobile'] = $user_mobile;
		$verify_data['create_time'] = TIME_UTC;
		$verify_data['client_ip'] = CLIENT_IP;

		if($vid = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$user_mobile."'"))
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"UPDATE","id=".$vid);
		else
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"INSERT");
		
		send_verify_sms($user_mobile,$verify_data['verify_code'],$authorized_info,true);
		$data['status'] = 1;
		$data['info'] = $GLOBALS['lang']['MOBILE_VERIFY_SEND_OK'];

		ajax_return($data);
	}
	
	public function check_authorized_paypwd_verify_code(){
		$ajax = intval($_REQUEST['ajax']);
		$verify = strim($_REQUEST['verify']);
		if($verify==""){
			showErr($GLOBALS['lang']['BIND_MOBILE_VERIFY_ERROR'],$ajax);
		}
		$authorized_info  = es_session::get("authorized_info");
		$user = get_user("*",intval($authorized_info['id']));
		$paypassword = strim(FW_DESPWD($_REQUEST['paypassword']));
		
		if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$authorized_info['mobile']."' AND verify_code='".$verify."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ")==0){
			showErr("手机验证码出错,或已过期",$ajax);
		}	
		else 
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."user set paypassword='".md5($paypassword)."', bind_verify = '', verify_create_time = 0 where id = ".intval($authorized_info['id']));
			if($GLOBALS['db']->affected_rows() > 0){
				showSuccess($GLOBALS['lang']['MOBILE_BIND_SUCCESS'],$ajax);
			}
			else{
				showErr("绑定失败",$ajax);
			}
		}
	}
	
	public function set_sort()
	{
		$type = strim($_REQUEST['type']);
		$module = strim($_REQUEST['module']);
		
		$module_array = array("shop","deal","transfer");
		if(!in_array($module,$module_array)){
			$module="shop";
		}
		
		es_cookie::set($module."_sort_field",$type); 
		if($type!='deal_status')
		{
			$sort_type = trim(es_cookie::get($module."_sort_type")); 
			if($sort_type&&$sort_type=='desc')
			{
				es_cookie::set($module."_sort_type",'asc'); 
			}
			else
			{
				es_cookie::set($module."_sort_type",'desc'); 
			}		
		}
		else
		{
			es_cookie::set($module."_sort_type",'asc'); 
		}
	}
	
	
	public function load_filter_group()
	{
		$cate_id = intval($_REQUEST['cate_id']);	
		$ids = load_auto_cache("shop_sub_parent_cate_ids",array("cate_id"=>$cate_id));		
		$filter_group_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."filter_group where is_effect = 1 and cate_id in (".implode(",",$ids).") order by sort desc");
		
		$GLOBALS['tmpl']->assign("filter_group_list",$filter_group_list);
		$GLOBALS['tmpl']->display("inc/inc_filter_group.html");
	}
	
	public function collect()
	{
		if(!$GLOBALS['user_info'])
		{
			$GLOBALS['tmpl']->assign("ajax",1);
			$html = $GLOBALS['tmpl']->fetch("inc/login_form.html");
			//弹出窗口处理
			$res['open_win'] = 1;
			$res['html'] = $html;
			ajax_return($res);
		}
		else
		{
			$goods_id = intval($_REQUEST['id']);
			$goods_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".$goods_id." and is_effect = 1 and is_delete = 0");
			if($goods_info)
			{
				$sql = "INSERT INTO `".DB_PREFIX."deal_collect` (`id`,`deal_id`, `user_id`, `create_time`) select '0','".$goods_info['id']."','".intval($GLOBALS['user_info']['id'])."','".TIME_UTC."' from dual where not exists (select * from `".DB_PREFIX."deal_collect` where `deal_id`= '".$goods_info['id']."' and `user_id` = ".intval($GLOBALS['user_info']['id']).")";
				$GLOBALS['db']->query($sql);
				if($GLOBALS['db']->affected_rows()>0)
				{
					//添加到动态
					insert_topic("deal_collect",$goods_id,intval($GLOBALS['user_info']['id']),$GLOBALS['user_info']['user_name']);
					$res['info'] = $GLOBALS['lang']['COLLECT_SUCCESS'];
				}
				else
				{
					$res['info'] = $GLOBALS['lang']['GOODS_COLLECT_EXIST'];
				}
				$res['open_win'] = 0;
				ajax_return($res);
			}
			else
			{
				$res['open_win'] = 0;
				$res['info'] = $GLOBALS['lang']['INVALID_GOODS'];
				ajax_return($res);
			}
		}
	}
	
	public function focus()
	{
		$user_id = intval($GLOBALS['user_info']['id']);
		if($user_id==0)
		{
			$data['tag'] = 4;
			$data['html'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
			ajax_return($data);
		}
		$focus_uid = intval($_REQUEST['uid']);
		if($user_id==$focus_uid)
		{
			$data['tag'] = 3;
			$data['html'] = $GLOBALS['lang']['FOCUS_SELF'];
			ajax_return($data);
		}
		
		$focus_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id." and focused_user_id = ".$focus_uid);
		if(!$focus_data&&$user_id>0&&$focus_uid>0)
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
			$data['tag'] = 1;
			$data['html'] = $GLOBALS['lang']['CANCEL_FOCUS'];

				//添加到动态
			insert_topic("focus",$focus_uid,$user_id,$GLOBALS['user_info']['user_name']);

			ajax_return($data);
		}
		elseif($focus_data&&$user_id>0&&$focus_uid>0)
		{
			$GLOBALS['db']->query("delete from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id." and focused_user_id = ".$focus_uid);
			$GLOBALS['db']->query("update ".DB_PREFIX."user set focus_count = focus_count - 1 where id = ".$user_id);
			$GLOBALS['db']->query("update ".DB_PREFIX."user set focused_count = focused_count - 1 where id = ".$focus_uid);		
			$data['tag'] =2;
			$data['html'] = $GLOBALS['lang']['FOCUS_THEY'];
			ajax_return($data);
		}
		
	}
	
	public function randuser()
	{
		$user_id = intval($GLOBALS['user_info']['id']);	
		$user_list = get_rand_user(24,0,$user_id);	
		$GLOBALS['tmpl']->assign("user_list",$user_list);		
		$GLOBALS['tmpl']->display("inc/uc/randuser.html");
	}
	
	
	public function relay_topic()
	{
		$topic = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic where id = ".intval($_REQUEST['id']));
		$GLOBALS['tmpl']->assign("topic_info",$topic);
		$GLOBALS['tmpl']->assign("user_info",$GLOBALS['user_info']);
		if($topic['origin_id']!=$topic['id'])
		{
			$origin_topic = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic where id = ".$topic['origin_id']);
			$GLOBALS['tmpl']->assign("origin_topic_info",$origin_topic);
		}
		$GLOBALS['tmpl']->display("inc/ajax_relay_box.html");
	}
	public function fav_topic()
	{
		$topic = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic where id = ".intval($_REQUEST['id']));
		$GLOBALS['tmpl']->assign("topic_info",$topic);
		if($topic['origin_id']!=$topic['id'])
		{
			$origin_topic = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic where id = ".$topic['origin_id']);
			$GLOBALS['tmpl']->assign("origin_topic_info",$origin_topic);
		}
		$GLOBALS['tmpl']->display("inc/ajax_relay_box.html");
	}	
	public function do_relay_topic()
	{
		if(intval($GLOBALS['user_info']['id'])==0)
		{
			$result['status'] = 0;
			$result['info'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
		}
		else
		{
			$result['status'] = 1;
			$content = addslashes(htmlspecialchars(trim(valid_str($_REQUEST['content']))));
			$id = intval($_REQUEST['id']);
			$tid = insert_topic($content,$title="",$type="",$group="", $id, $fav_id=0);
			if($tid)
			{
				increase_user_active(intval($GLOBALS['user_info']['id']),"转发了一则分享");
				$GLOBALS['db']->query("update ".DB_PREFIX."topic set source_name = '网站' where id = ".intval($tid));
			}
			$result['info'] = $GLOBALS['lang']['RELAY_SUCCESS'];
		}
		ajax_return($result);
	}
	public function do_fav_topic()
	{	
		if(intval($GLOBALS['user_info']['id'])==0)
		{
			$result['status'] = 0;
			$result['info'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
		}
		else
		{					
			$id = intval($_REQUEST['id']);
			$topic = $GLOBALS['db']->getRow("select id,user_id from ".DB_PREFIX."topic where id = ".$id);
			if(!$topic)
			{
				$result['status'] = 0;
				$result['info'] = $GLOBALS['lang']['TOPIC_NOT_EXIST'];
			}
			else
			{
				if($topic['user_id']==intval($GLOBALS['user_info']['id']))
				{
					$result['status'] = 0;
					$result['info'] = $GLOBALS['lang']['TOPIC_SELF'];
				}
				else
				{					
					$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."topic where (fav_id = ".$id." or (origin_id = ".$id." and fav_id <> 0)) and user_id = ".intval($GLOBALS['user_info']['id']));
					if($count>0)
					{
						$result['status'] = 0;
						$result['info'] = $GLOBALS['lang']['TOPIC_FAVED'];
					}
					else
					{
						$result['status'] = 1;
						$tid = insert_topic($content,$title="",$type="",$group="", $relay_id = 0, $id);
						if($tid)
						{
							increase_user_active(intval($GLOBALS['user_info']['id']),"喜欢了一则分享");
							$GLOBALS['db']->query("update ".DB_PREFIX."topic set source_name = '网站' where id = ".intval($tid));
						}
						$result['info'] = $GLOBALS['lang']['FAV_SUCCESS'];
					}
				}
			}
		}
		ajax_return($result);
	}
	
	public function msg_reply(){
		$ajax = 1;
		$user_info = $GLOBALS['user_info'];
		if(!$user_info)
		{
			showErr($GLOBALS['lang']['PLEASE_LOGIN_FIRST'],$ajax);
		}
		if($_REQUEST['content']=='')
		{
			showErr($GLOBALS['lang']['MESSAGE_CONTENT_EMPTY'],$ajax);
		}
		
		if(!check_ipop_limit(CLIENT_IP,"message",intval(app_conf("SUBMIT_DELAY")),0))
		{
			showErr($GLOBALS['lang']['MESSAGE_SUBMIT_FAST'],$ajax);
		}
		
		$rel_table = strim($_REQUEST['rel_table']);
		$message_type = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."message_type where type_name='".$rel_table."'");
		if(!$message_type)
		{
			showErr($GLOBALS['lang']['INVALID_MESSAGE_TYPE'],$ajax);
		}			
		//添加留言
		$message['title'] = $_REQUEST['title']?strim($_REQUEST['title']):btrim(valid_str($_REQUEST['content']));
		$message['content'] = btrim(valid_str($_REQUEST['content']));
		$message['title'] = valid_str($message['title']);

		$message['create_time'] = TIME_UTC;
		$message['rel_table'] = $rel_table;
		$message['rel_id'] = intval($_REQUEST['rel_id']);
		$message['user_id'] = intval($GLOBALS['user_info']['id']);
		$message['pid'] = intval($_REQUEST['pid']);
		
		if(app_conf("USER_MESSAGE_AUTO_EFFECT")==0)
		{
			$message_effect = 0;
		}
		else
		{
			$message_effect = $message_type['is_effect'];
		}
		$message['is_effect'] = $message_effect;		
		$GLOBALS['db']->autoExecute(DB_PREFIX."message",$message);
		
		if($rel_table == "deal"){
			$l_user_id =  $GLOBALS['db']->getOne("SELECT user_id FROM ".DB_PREFIX."deal WHERE id=".$message['rel_id']);
		}
		else{
			$l_user_id =  $GLOBALS['db']->getOne("SELECT user_id FROM ".DB_PREFIX."deal_load_transfer WHERE id=".$message['rel_id']);
		}
		
		//添加到动态
		insert_topic($rel_table."_message_reply",$message['rel_id'],$message['user_id'],$GLOBALS['user_info']['user_name'],$l_user_id);
		
		if($rel_table == "deal"){
			
			require_once APP_ROOT_PATH.'app/Lib/deal.php';
			$deal = get_deal($message['rel_id']);
			$msg_u_id = $GLOBALS['db']->getOne("SELECT user_id FROM ".DB_PREFIX."message WHERE id=".$message['pid']);
			
			if($message['user_id'] != $msg_u_id){
				$msg_conf = get_user_msg_conf($deal['user_id']);
				//站内信
				if($msg_conf['sms_answer']==1){
					
					$notices['user_name'] = get_user_name($message['user_id']);
					$notices['url'] =  "“<a href=\"".$deal['url']."\">".$deal['name']."</a>”";
					$notices['msg'] = "“".$message['content']."”";
					
					$tmpl_contents = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_REPLY_MSG'",false);
					$GLOBALS['tmpl']->assign("notice",$notices);
					$content = $GLOBALS['tmpl']->fetch("str:".$tmpl_contents['content']);
					send_user_msg("",$content,0,$msg_u_id,TIME_UTC,0,true,14,$message['rel_id']);
				}
				
				//邮件
				if($msg_conf['mail_answer']==1 && app_conf('MAIL_ON')==1){
					$user_info = get_user("*",$msg_u_id);
					$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_DEAL_REPLY_MSG'",false);
					$tmpl_content = $tmpl['content'];
					
					$notice['user_name'] = $user_info['user_name'];
					$notice['msg_user_name'] = get_user_name($message['user_id'],false);
					$notice['deal_name'] = $deal['name'];
					$notice['deal_url'] = SITE_DOMAIN.url("index","deal",array("id"=>$deal['id']));
					$notice['message'] = $message['content'];
					$notice['site_name'] = app_conf("SHOP_TITLE");
					$notice['site_url'] = SITE_DOMAIN.APP_ROOT;
					$notice['help_url'] = SITE_DOMAIN.url("index","helpcenter");
					
					
					$GLOBALS['tmpl']->assign("notice",$notice);
					
					$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
					$msg_data['dest'] = $user_info['email'];
					$msg_data['send_type'] = 1;
					$msg_data['title'] = "用户".get_user_name($message['user_id'],false)."回复了你的留言！";
					$msg_data['content'] = addslashes($msg);
					$msg_data['send_time'] = 0;
					$msg_data['is_send'] = 0;
					$msg_data['create_time'] = TIME_UTC;
					$msg_data['user_id'] = $user_info['id'];
					$msg_data['is_html'] = $tmpl['is_html'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
				}
			}
		}
		
		showSuccess($GLOBALS['lang']['REPLY_POST_SUCCESS'],$ajax);
	}
	
	public function ajax_login()
	{
		$GLOBALS['tmpl']->display("inc/login_form.html");
	}

	public function drop_pm()
	{
		if($GLOBALS['user_info'])
		{
			$user_id = intval($GLOBALS['user_info']['id']);
			$res = $_REQUEST['pm_key'];
			foreach($res as $key)
			{
				$sql = "update  ".DB_PREFIX."msg_box set is_delete = 1 where ((to_user_id = ".$user_id." and `type` = 0) or (from_user_id = ".$user_id." and `type` = 1)) and group_key = '".$key."'";
				$GLOBALS['db']->query($sql);
			}
			$result['status'] = 1;
			$result['info'] = $GLOBALS['lang']['DELETE_SUCCESS'];
		}
		else
		{
			$result['status'] = 0;
			$result['info'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
		}
		ajax_return($result);
	}
	
	public function drop_pmxiaoxi()
	{
		if($GLOBALS['user_info'])
		{
			$user_id = intval($GLOBALS['user_info']['id']);
			$res = $_REQUEST['pm_key'];
			foreach($res as $key)
			{
				$sql = "update  ".DB_PREFIX."msg_box set is_delete = 1 where ((to_user_id = ".$user_id." and `type` = 0) or (from_user_id = ".$user_id." and `type` = 1)) and group_key = '".$key."'";
				$GLOBALS['db']->query($sql);
			}
			$result['status'] = 1;
			$result['info'] = $GLOBALS['lang']['DELETE_SUCCESS'];
		}
		else
		{
			$result['status'] = 0;
			$result['info'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
		}
		ajax_return($result);
	}
	
	public function drop_pm_item()
	{
		if($GLOBALS['user_info'])
		{
			$user_id = intval($GLOBALS['user_info']['id']);
			$res = $_REQUEST['id'];
			foreach($res as $id)
			{
				$sql = "update  ".DB_PREFIX."msg_box set is_delete = 1 where id = '".intval($id)."'";
				$GLOBALS['db']->query($sql);
			}
			$result['status'] = 1;
			$result['info'] = $GLOBALS['lang']['DELETE_SUCCESS'];
		}
		else
		{
			$result['status'] = 0;
			$result['info'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
		}
		ajax_return($result);
	}	
	public function check_send()
	{
		$user_name = addslashes(trim($_REQUEST['user_name']));
		/*if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_focus where focused_user_name = '".$GLOBALS['user_info']['user_name']."' and focus_user_name = '".$user_name."'")>0)
		{
			//是粉丝
			$result['status'] = 1;
		}
		else
		{
			$result['status'] = 0;
		}*/
		if($user_name == $GLOBALS['user_info']['user_name'])
		{
			$result['status'] = 0;
			$result['info'] = "不能给自己发私信";
		}
		else
			$result['status'] = 1;
		ajax_return($result);
	}
	
	public function send_pm()
	{
		if($GLOBALS['user_info'])
		{
			$user_name = strim($_REQUEST['user_name']);
			$user_id = get_user_info("id","user_name = '".$user_name."'","ONE");
			if(intval($user_id)==0)
			{
				$result['status'] = 0;
				$result['info'] = $GLOBALS['lang']['TO_USER_EMPTY'];
				ajax_return($result);
			}
			/*if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_focus where focused_user_name = '".$GLOBALS['user_info']['user_name']."' and focus_user_name = '".$user_name."'")==0)
			{
				//不是粉丝,验证是否有来信记录
				$sql = "select count(*) from ".DB_PREFIX."msg_box 
						where is_delete = 0 and 
						(to_user_id = ".intval($GLOBALS['user_info']['id'])." and `type` = 0 and from_user_id = ".$user_id.")";
				$inbox_count = $GLOBALS['db']->getOne($sql);
				if($inbox_count==0)
				{
					$result['status'] = 0;
					$result['info'] = $GLOBALS['lang']['FANS_ONLY'];
					ajax_return($result);
				}			
			}*/
			$content = btrim($_REQUEST['content']);
			send_user_msg("",$content,intval($GLOBALS['user_info']['id']),$user_id,TIME_UTC);
			$result['status'] = 1;
			$key = array($user_id,intval($GLOBALS['user_info']['id']));
			sort($key);
			$group_key = implode("_",$key);
			$result['info'] = url("shop","uc_msg#deal",array("id"=>$group_key));
		}
		else
		{
			$result['status'] = 0;
			$result['info'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
		}
		ajax_return($result);
	}
	
	public function usercard()
	{
		$uid = intval($_REQUEST['uid']);		
		$uinfo = get_user("*",$uid);		
		if($uinfo)
		{
			$user_id = intval($GLOBALS['user_info']['id']);
			$focused_uid = intval($uid);
			$focus_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id." and focused_user_id = ".$focused_uid);
			if($focus_data)
				$uinfo['focused'] = 1; 		
			$uinfo['point_level'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."user_level where id = ".intval($uinfo['level_id']));
			$GLOBALS['tmpl']->assign("card_info",$uinfo);		
			$GLOBALS['tmpl']->display("inc/usercard.html");
		}
		else 
		{
			header("Content-Type:text/html; charset=utf-8");
			echo "<div class='load'>该会员已被删除或者已被禁用</div>";
		}
	}
	
	//采集分享
	/**
	 * 传入 class_name,url
	 * **
	 * 传出 
	 *  array("status"=>"","info"=>"", "group"=>"","type"=>"","group_data"=>"","content"=>"","tags"=>"","images"=>array("id"=>"","url"=>""));					
	 */
	public function do_fetch()
	{
		$class_name = addslashes(trim($_REQUEST['class_name']));
		$url = trim($_REQUEST['url']);
		$result['status'] = 0;
		if(file_exists(APP_ROOT_PATH."system/fetch_topic/".$class_name."_fetch_topic.php"))
		{
			require_once APP_ROOT_PATH."system/fetch_topic/".$class_name."_fetch_topic.php";
			$class = $class_name."_fetch_topic";
			if(class_exists($class))
			{
				$api = new $class;
				$rs = $api->fetch($url);
				if($rs['status']==0)
				{
					$result['info'] = $rs['info'];
				}
				else
				{
					$result['status'] = 1;
					$result['group'] = $class_name;
					$result['group_data'] = $rs['group_data'];
					$result['content'] = $rs['content'];
					$result['type'] = $rs['type'];
					$result['tags'] = $rs['tags'];
					$result['images'] = $rs['images'];					 
				}
			}	
			else
			{
				$result['info'] = "接口不存在";
			}		
		}
		else
		{
			$result['info'] = "接口不存在";
		}
		
		ajax_return($result);
	}
	
	
	public function set_syn()
	{
		if($GLOBALS['user_info'])
		{
			$field = addslashes(trim($_REQUEST['field']));
			$user_info = get_user("*",intval($GLOBALS['user_info']['id']));
			$upd_value = intval($user_info[$field]) == 0? 1:0;
			$GLOBALS['db']->query("update ".DB_PREFIX."user set `".$field."` = ".$upd_value." where id = ".intval($GLOBALS['user_info']['id']));
			$result['info'] = "设置成功";
			$user_info[$field] = $upd_value;
			es_session::set("user_info",$user_info);
		}
		else
		{
			$result['info'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
		}
		ajax_return($result);
	}
	
	
	//ajax同步发微博
	public function syn_to_weibo()
	{
		set_time_limit(0);
		$topic_id = intval($_REQUEST['topic_id']);
		$user_id = intval($GLOBALS['user_info']['id']);
		$api_class_name = addslashes(htmlspecialchars(trim($_REQUEST['class_name'])));
		es_session::close();
		$topic = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic where id = ".$topic_id);	
		if($topic['topic_group']!="share")
		{
			$group = $topic['topic_group'];
			if(file_exists(APP_ROOT_PATH."system/fetch_topic/".$group."_fetch_topic.php"))
			{
				require_once APP_ROOT_PATH."system/fetch_topic/".$group."_fetch_topic.php";
				$class_name = $group."_fetch_topic";
				if(class_exists($class_name))
				{
					$fetch_obj = new $class_name;
					$data = $fetch_obj->decode_weibo($topic);
				}
			}
		}
		else
		{
			$data['content'] = $topic['content'];
			
			//图片
			$topic_image = $GLOBALS['db']->getRow("select o_path from ".DB_PREFIX."topic_image where topic_id = ".$topic['id']);
			if($topic_image)
				$data['img'] = SITE_DOMAIN.APP_ROOT."/".$topic_image['o_path'];
		}
		
		$user_info = get_user("*",intval($user_id));
		$api = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."api_login where is_weibo = 1 and class_name = '".$api_class_name."'");
		if($user_info["is_syn_".strtolower($api['class_name'])]==1)
		{
				//发送本微博
			require_once APP_ROOT_PATH."system/api_login/".$api_class_name."_api.php";
			$api_class = $api_class_name."_api";
			$api_obj = new $api_class($api);
			$api_obj->send_message($data);
		}
	}
	
	public function load_api_url()
	{
		$type = intval($_REQUEST['type']);  //0:小登录图标 1:大登录图标 2:绑定图标
		$class_name = addslashes(htmlspecialchars(trim($_REQUEST['class_name'])));
		if(file_exists(APP_ROOT_PATH."system/api_login/".$class_name."_api.php"))
		{
			require_once APP_ROOT_PATH."system/api_login/".$class_name."_api.php";
			$api_class = $class_name."_api";
			$api = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."api_login where class_name = '".$class_name."'");
			$api_obj = new $api_class($api);
			if($type==0)
				$url = $api_obj->get_api_url();
			elseif($type==1)
				$url = $api_obj->get_big_api_url();
			else
				$url = $api_obj->get_bind_api_url();				
		}
		$domain = app_conf("PUBLIC_DOMAIN_ROOT")==''?SITE_DOMAIN.$GLOBALS['IMG_APP_ROOT']:app_conf("PUBLIC_DOMAIN_ROOT");	
		$url = str_replace("./public/",$domain."/public/",$url);	
		header("Content-Type:text/html; charset=utf-8");
		echo $url;
	}
	
	public function update_user_tip()
	{
		require_once APP_ROOT_PATH."app/Lib/insert_libs.php";
		header("Content-Type:text/html; charset=utf-8");
		echo  insert_load_user_tip();
	}
	
	public function check_login_status()
	{
		if($GLOBALS['user_info'])		
			$result['status'] = 1;		
		else
			$result['status'] = 0;
		ajax_return($result);
	}
	
	//验证验证码
	public function checkverify()
	{
		$ajax = intval($_REQUEST['ajax']);
		
		if(app_conf("VERIFY_IMAGE")==1)
		{
			$verify = md5(trim($_REQUEST['verify']));
			$session_verify = es_session::get('verify');
			if($verify!=$session_verify)
			{				
				showErr($GLOBALS['lang']['VERIFY_CODE_ERROR'],$ajax);
			}
			else
			{
				showSuccess("验证成功",$ajax);
			}
		}
		else
		{
			showSuccess("验证成功",$ajax);
		}
	}
	
	public function signin()
	{
		$user_id = intval($GLOBALS['user_info']['id']);
		if($user_id==0)
		{
			$result['status'] = 2;
			ajax_return($result);
		}
		else
		{
			
			$result = signin($user_id);
			ajax_return($result);
		}
	}
	
	public function gopreview()
	{		
		header("Content-Type:text/html; charset=utf-8");
		echo get_gopreview();		
	}
	
	/**
	 * 举报用户
	 */
	public function reportguy(){
		if(!$GLOBALS['user_info'])
			exit();

		$user_id = intval($_REQUEST['user_id']);
		if($user_id==0)
			exit();
		$u_info = get_user("id,user_name",$user_id);
		
		$GLOBALS['tmpl']->assign("u_info",$u_info);
		
		
		$GLOBALS['tmpl']->display("inc/ajax/reportguy.html");
	}
	
	public function savereportguy(){
		$result  = array("status"=>0,"message"=>"");
		if(!$GLOBALS['user_info']){
			$result['message'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
			ajax_return($result);
			exit();
		}
		
		if(!check_ipop_limit(CLIENT_IP,"savereportguy",10,0))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['MESSAGE_SUBMIT_FAST'];
			ajax_return($data);
		}
		
		$user_id = intval($_REQUEST['user_id']);
		if($user_id==0){
			$result['message'] = "没有该用户";
			ajax_return($result);
			exit();
		}
		
		$data['user_id'] = $GLOBALS['user_info']['id'];
		$data['r_user_id'] = $user_id;
		$data['reason'] = htmlspecialchars($_REQUEST['reason']);
		$data['content'] = htmlspecialchars($_REQUEST['content']);
		
		$GLOBALS['db']->autoExecute(DB_PREFIX."reportguy",$data,"INSERT");
		
		$result['status'] = 1;
		ajax_return($result);
	}
	
	/**
	 * 站内信
	 */
	public function send_msg(){
		if(!$GLOBALS['user_info'])
			exit();

		$user_id = intval($_REQUEST['user_id']);
		if($user_id==0)
			exit();
		$u_info = get_user("id,user_name",$user_id);
		
		$GLOBALS['tmpl']->assign("u_info",$u_info);
		
		
		$GLOBALS['tmpl']->display("inc/ajax/send_msg.html");
	}
	
	public function send_mobile_verify_code()
	{
		if(app_conf("SMS_ON")==0)
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['SMS_OFF'];
			ajax_return($data);
		}
		$mobile = addslashes(htmlspecialchars(trim($_REQUEST['mobile'])));

		if($mobile == '')
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['MOBILE_EMPTY_TIP'];
			ajax_return($data);
		}

		if(!check_mobile($mobile))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'];
			ajax_return($data);
		}

		$field_name = addslashes(trim($_REQUEST['mobile']));
		$field_data = $mobile;
		require_once APP_ROOT_PATH."system/libs/user.php";
		$res = check_user($field_name,$field_data);
		$result = array("status"=>1,"info"=>'');
		if(!$res['status'])
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
			$result['status'] = 0;
			$result['info'] = $error_msg;
			ajax_return($result);
		}


		if(!check_ipop_limit(CLIENT_IP,"mobile_verify",60,0))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['MOBILE_SMS_SEND_FAST'];
			ajax_return($data);
		}

		if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."mobile_verify_code where mobile = '".$mobile."' and client_ip='".CLIENT_IP."' and create_time>=".(get_gmtime()-60)." ORDER BY id DESC") > 0)
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['MOBILE_SMS_SEND_FAST'];
			ajax_return($data);
		}

		/*
		//删除超过5分钟的验证码
		$GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."mobile_verify_code WHERE create_time <=".get_gmtime()-300);
		//开始生成手机验证
		$code = rand(1111,9999);
		$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",array("verify_code"=>$code,"mobile"=>$mobile,"create_time"=>get_gmtime(),"client_ip"=>CLIENT_IP),"INSERT");
		send_verify_sms($mobile,$code);
		$data['status'] = 1;
		$data['info'] = "验证码发送成功";
		*/
		
		//删除超过5分钟的验证码
		//$GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."mobile_verify_code WHERE create_time <=".get_gmtime()-300);
		
		$verify_code = $GLOBALS['db']->getOne("select verify_code from ".DB_PREFIX."mobile_verify_code where mobile = '".$mobile."' and create_time>=".(TIME_UTC-180)." ORDER BY id DESC");
		if(intval($verify_code) == 0)
		{
			//如果数据库中存在验证码，则取数据库中的（上次的 ）；确保连接发送时，前后2条的验证码是一至的.==为了防止延时
			//开始生成手机验证
			$verify_code = rand(111111,999999);
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",array("verify_code"=>$verify_code,"mobile"=>$mobile,"create_time"=>get_gmtime(),"client_ip"=>CLIENT_IP),"INSERT");
		}
		
		//使用立即发送方式	
		$result = send_verify_sms($mobile,$verify_code,null,true);//
		$data['status'] = $result['status'];		
		
		if ($data['status'] == 1){
			$data['info'] = $GLOBALS['lang']['MOBILE_VERIFY_SEND_OK'];
		}else{
			$data['info'] = $result['msg'];
			if ($data['info'] == null || $data['info'] == ''){
				$data['info'] = "验证码发送失败";
			}
		}
		
		
		ajax_return($data);
	}
	/**
	 * 检查用户资料
	 */
	function check_user_info(){
		$user_id = $GLOBALS['user_info']['id'];
		if($user_id == 0){
			showErr('请先登录',1);
		}
		$user_type = intval(trim($_REQUEST['user_type']));

		$data = array();
		$data = get_user("*",intval($user_id));
		
		$err_msg = "";
		if (empty($data)){
			showErr('用户不存在',1);
		}else{
			if (empty($data['ips_acct_no'])){			
				if (empty($data['idno'])){
					$err_msg .='身份证号码不能为空&nbsp;<a href="'.url("index","uc_account#security").'">去补充</a><br>';
				}else if (empty($data['real_name'])){
					$err_msg .='真实姓名不能为空&nbsp;<a href="'.url("index","uc_account#security").'">去补充</a><br>';
				}else if (empty($data['mobile'])){
					$err_msg .='手机号码不能为空&nbsp;<a href="'.url("index","uc_account#security").'">去补充</a><br>';
				}else if (empty($data['email'])){
					$err_msg .='邮箱不能为空&nbsp;<a href="'.url("index","uc_account#security").'">去补充</a><br>';
				}		
			}else{
				$err_msg .='该用户已经申请过资金托管帐户:'.$data['ips_acct_no'];
			}
		}
		if($err_msg!=""){
			showErr($err_msg,1);
		}
		else
		{
			$className = getCollName();
			if(strtolower($className) == "yeepay")
			{
				$user_type = $data['user_type'];
			}
			showSuccess("验证成功",1,APP_ROOT."/index.php?ctl=collocation&act=CreateNewAcct&user_type=$user_type&user_id=".$user_id);
		}
		
	}
	
	function get_user_load_item(){
		$deal_id = intval($_REQUEST['deal_id']);
		$l_key = intval($_REQUEST['l_key']);
		$obj = strim($_REQUEST['obj']);
		if($deal_id==0){
			showErr("数据错误",1);
		}
		require_once APP_ROOT_PATH."app/Lib/deal.php";
		$deal_info = get_deal($deal_id);
		
		if(!$deal_info){
			showErr("借款不存在",1);
		}
		
		
		//输出投标列表
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;

		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		
		$result = get_deal_user_load_list($deal_info,0,$l_key,-1,0,0,1,$limit);
		$rs_count = $result['count'];
		$page_all = ceil($rs_count/app_conf("PAGE_SIZE"));
		
		$GLOBALS['tmpl']->assign("load_user",$result['item']);
		$GLOBALS['tmpl']->assign("l_key",$l_key);
		$GLOBALS['tmpl']->assign("page_all",$page_all);
		$GLOBALS['tmpl']->assign("rs_count",$rs_count);
		$GLOBALS['tmpl']->assign("page",$page);
		$GLOBALS['tmpl']->assign("deal_id",$deal_id);
		$GLOBALS['tmpl']->assign("obj",$obj);
		$GLOBALS['tmpl']->assign("page_prev",$page - 1);
		$GLOBALS['tmpl']->assign("page_next",$page + 1);
		
		
		$html = $GLOBALS['tmpl']->fetch("inc/uc/ajax_load_user.html");
		
		showSuccess($html,1);
	}
	
	function bid_calculate(){
		require_once APP_ROOT_PATH."app/Lib/deal_func.php";	
		echo bid_calculate($_POST);
	}
	/*****计算预期收益*****/
	function expected_return()
	{
		require_once APP_ROOT_PATH."app/Lib/deal_func.php";	
		echo expected_return($_POST);
	}
	/*****计算理财计划预期收益*****/
	function plan_expected_return()
	{
		require_once APP_ROOT_PATH."app/Lib/deal_func.php";	
		echo plan_expected_return($_POST);
	}
	/*****计算体验标预期收益*****/
	function experience_deal_return()
	{
		require_once APP_ROOT_PATH."app/Lib/deal_func.php";	
		echo experience_deal_return($_POST);
	}
	function payment_fee(){
		$id = intval($_POST['id']);
		$vip_id = intval($GLOBALS['user_info']['vip_id']);
		$return = array("fee_type"=>0,"fee_amount"=>0);
		$payment_info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."payment WHERE id=".$id,false);
		if($payment_info){
			if($vip_id>0){
				$interface_class = $payment_info['class_name'];
				$recharge_info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."user_recharge_config WHERE interface_class='$interface_class' and vip_id = ".$vip_id);
				if($recharge_info){
					$return['fee_type'] = $recharge_info['fee_type'];
					$return['fee_amount'] = $recharge_info['fee'];
				}else{
					$return['fee_type'] = $payment_info['fee_type'];
					$return['fee_amount'] = $payment_info['fee_amount'];
				}
			}else{
				$return['fee_type'] = $payment_info['fee_type'];
				$return['fee_amount'] = $payment_info['fee_amount'];
			}
			
		}
		ajax_return($return);
	}
	
	public function weixin_login()
	{
		$session_id=es_session::id();
		$verify = rand(100000, 999999);
		
		$url=SITE_DOMAIN.wap_url("index","login",array("JCTP2P_SESSION_ID"=>$session_id,"sess_verify"=>$verify));
		es_session::set("sess_verify", $verify);
		
		gen_qrcode($url,4,true);
	}
	
	public function do_weixin_login()
	{
		$status=0;
		$user_info=es_session::get("user_info");
		if($user_info){
			$status=1;
		}
		echo $status;
	}
	
	public function getIdCardinfo(){
		$return['status'] = 0;
		$card = trim($_REQUEST['card']);
		
		$return = idCardInfo($card);
		
		ajax_return($return);
	}
	public function check_file_bank(){
		require_once APP_ROOT_PATH."system/utils/bankCard.php";
		$bankCard = new bankCard();
		$uid = $_SERVER["REMOTE_ADDR"];
		$img = explode(",",$_REQUEST['bankImg']);
		$image = $img[1];
		$result = $bankCard->zXBank($uid,$image);
		$array = json_decode($result,1);
		$bankname = mb_substr($array['bankCard']['bankname'],0,3,'utf-8');
		$bankInfo = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."bank WHERE name like '%".$bankname."%'");
		switch ($array['code']) {
			case '434':
				$json['status'] = 0;
				$json['error'] = "serviceCode illegal";
				$json['info'] = "您没有申请相应的服务，或服务的次数已到临界值，或服务已到期";
				ajax_return($json);
				break;
			case '437':
				$json['status'] = 0;
				$json['error'] = "input is null";
				$json['info'] = "您的请求参数为空";
				ajax_return($json);
				break;
			case '438':
				$json['status'] = 0;
				$json['error'] = "input json format invalid";
				$json['info'] = "您的请求参数json格式非法";
				ajax_return($json);
				break;
			case '439':
				$json['status'] = 0;
				$json['error'] = "image data is null";
				$json['info'] = "您的请求参数中图片数据为空";
				ajax_return($json);
				break;
			case '8101':
				$json['status'] = 0;
				$json['error'] = "recognize service exception";
				$json['info'] = "识别服务错误";
				ajax_return($json);
				break;
			case '8102':
				$json['status'] = 0;
				$json['error'] = "recognize core exception";
				$json['info'] = "不是有效的图片，请确保：1.图片格式为JPEG,2.银行卡应占图片的1/2或以上,3.分辨率至少为300dpi，拍摄清晰";
				ajax_return($json);
				break;
			default:
				$json['status'] = 1;
				$json['error'] = "ok";
				$json['info'] = "获取参数成功";
				$json['bankname'] = $bankInfo['name'];
				$json['cardno'] = $array['bankCard']['cardno'];
				$json['cardtype'] = $array['bankCard']['cardtype'];
				$json['cardname'] = $array['bankCard']['cardname'];
				$json['cardicon'] = $bankInfo['icon'];
				ajax_return($json);
				break;
		}
	}
	/*public function ajax_bank_card(){
		require APP_ROOT_PATH."system/utils/bankBin/llpay_apipost_submit.class.php";
		if(!$_POST)
		{
			 app_redirect("404.html");
			 exit();
		}
		foreach($_POST as $k=>$v)
		{
			$data[$k] = htmlspecialchars(addslashes($v));
		}
		$llpay_gateway_new = 'https://queryapi.lianlianpay.com/bankcardbin.htm';
		if(WAP==0){
			// $card=$data['bankcard'];
			$data['bankcard'] = trim($_REQUEST['bankcard']);
        	$card=str_replace(" ","",$data['bankcard']);
		}else{
			$card = $this->trimall($data['bankCard']);
		}
		$llpay_config = $this->llpay_return_config();
		$parameter = array(
			'api_version'=>$llpay_config['version'],
			'pay_type'=>'2',
			'flag_amt_limit'=>'0',
			"card_no" => $card,
    		"oid_partner" => trim($llpay_config['oid_partner']),
    		"sign_type" => trim($llpay_config['sign_type']),    		
    	);

    	$llpaySubmit = new LLpaySubmit($llpay_config);
		$html_text = $llpaySubmit->buildRequestJSON($parameter,$llpay_gateway_new);
		$arr = json_decode($html_text,1);
		if(WAP==0){
			return $arr;
		}
    	if($arr['ret_code'] == 0000){
    		switch ($arr['card_type']) {
    			case '2':
    				$cardtype = "储蓄卡";
    				break;    			
    			default:
    				$cardtype = "信用卡";
    				break;
    		}
        	$json = array(
        		'status' =>1,
        		'cardicon' =>$GLOBALS['db']->getOne("SELECT icon FROM ".DB_PREFIX."bank WHERE Banklist =".$arr['bank_code']),
        		'bankname' =>$arr['bank_name'],
        		'bank_code' =>$arr['bank_code'],
        		'cardtype' =>$cardtype,
        		'type' => $arr['card_type'],
        		'msg'=>'成功',
        	);
        	ajax_return($json);
		}else{
			$json = array(
        		'status' =>0,
        		'msg'=>'卡号格式错误，无法识别',
        	);
        	ajax_return($json);
		}
	}*/
	public function llpay_return_config(){
    	$wapllpay_config =array(
    		'oid_partner'=>'201411031000083504', //连连测试商户号
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
    		'key'=>'jiuxindai521', //连连测试key
    		'version'=>'1.2',
    		'app_request'=>'3',
    		'sign_type'=>strtoupper('MD5'),
    		'valid_order'=>'30',
    		'input_charset'=>strtolower('utf-8'),
    		'transport'=>'http',
    		);

    	return $wapllpay_config;
    }
	public function ajax_bank_card(){
		if(!$_POST)
		{
			 app_redirect("404.html");
			 exit();
		}
		foreach($_POST as $k=>$v)
		{
			$data[$k] = htmlspecialchars(addslashes($v));
		}
		$card = $this->trimall($data['bankCard']);
		$url = "http://apicloud.mob.com/appstore/bank/card/query?key=113f48b687bfe&card=".$card;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        $data = curl_exec($ch);//运行curl
        curl_close($ch);
        $arr = json_decode($data,1);
        if($arr['retCode'] == 200){
        	$json = array(
        		'status' =>1,
        		'cardicon' =>$GLOBALS['db']->getOne("SELECT icon FROM ".DB_PREFIX."bank WHERE name like '%".$arr['result']['bank']."%'"),
        		'bankname' =>$arr['result']['bank'],
        		'cardtype' =>$arr['result']['cardType'],
        		'msg'=>'成功',
        	);
        	ajax_return($json);
		}else{
			$json = array(
        		'status' =>0,
        		'msg'=>'卡号格式错误，无法识别',
        	);
        	ajax_return($json);
		}
	}
	/* 正则判断银行卡所属行
	public function ajax_bank_card(){
		require APP_ROOT_PATH."system/utils/bankList.php";
		if(!$_POST)
		{
			 app_redirect("404.html");
			 exit();
		}
		foreach($_POST as $k=>$v)
		{
			$data[$k] = htmlspecialchars(addslashes($v));
		}
		//发卡行判断
		$card = $this->trimall($data['bankCard']);
		$card_8 = substr($card, 0, 8); 
		if (isset($bankList[$card_8])) { 
		   	$bankInfo = $bankList[$card_8]; 
		} 
		$card_6 = substr($card, 0, 6); 
		if (isset($bankList[$card_6])) { 
		    $bankInfo = $bankList[$card_6]; 
		} 
		$card_5 = substr($card, 0, 5); 
		if (isset($bankList[$card_5])) { 
		    $bankInfo = $bankList[$card_5]; 
		} 
		$card_4 = substr($card, 0, 4); 
		if (isset($bankList[$card_4])) { 
		    $bankInfo = $bankList[$card_4];
		}
		$bankInfo = explode("-",$bankInfo);
		$bank = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."bank WHERE name like '%".$bankInfo[0]."%'");
		$json['status'] = 1;
		$json['error'] = "ok";
		$json['info'] = "获取参数成功";
		$json['bankname'] = $bankInfo[0];
		$json['cardtype'] = $bankInfo[2];
		$json['cardicon'] = $bank['icon'];
		ajax_return($json);
	}*/
	function trimall($str){
	    $qian=array(" ","　","\t","\n","\r");$hou=array("","","","","");
	    return str_replace($qian,$hou,$str);    
	}
	/*
	 * 绑卡发送短信验证码
	 */
	public function bankverify(){
		if(!$GLOBALS['user_info']){
			$data['status'] = 0;
			$data['info'] = "请先登录";
			ajax_return($data);
		}

		if(app_conf("SMS_ON")==0)
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['SMS_OFF'];
			ajax_return($data);
		}

		$user_mobile = strim($_REQUEST['user_mobile']);

		if($user_mobile == '')
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_MOBILE_EMPTY'];
			ajax_return($data);
		}

		if(!check_mobile($user_mobile))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'];
			ajax_return($data);
		}
		if(!check_ipop_limit(CLIENT_IP,"old_mobile_verify",60,0))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_CODE_SEND_FAST'];
			ajax_return($data);
		}
		//开始生成手机验证
		$verify_data['verify_code'] = rand(111111,999999);
		$verify_data['mobile'] = $user_mobile;
		$verify_data['create_time'] = TIME_UTC;
		$verify_data['client_ip'] = CLIENT_IP;
		$verify_data['send_count'] = 1;
		$t = time();
		$begin_time = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t)); 	//今天开始时间
		if($info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$user_mobile."'")){
			if($info['create_time'] < $begin_time){
				$verify_data['send_count'] = 1;
			}
			else{
				$verify_data['send_count'] = $info['send_count'] + 1;
			}
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"UPDATE","id=".$info['id']);
		}
		else
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"INSERT");

		send_verify_sms($user_mobile,$verify_data['verify_code'],'',true);
		$data['status'] = 1;
		$data['info'] = $GLOBALS['lang']['MOBILE_VERIFY_SEND_OK'];
		ajax_return($data);
	}
	/**
	 * 验证图文验证码	提供手机号码，验证手机号是否被注册；
	 */
	public function send_phone_verifycode_one(){
		$user_mobile = strim($_REQUEST['user_mobile']);
		$verify =  strim($_REQUEST['Verifycode']);
		// $ajax =  strim($_REQUEST['ajax']);
		$t = time();
		$begin_time = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t)); 	//今天开始时间 
		$result = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."mobile_verify_code where mobile =".$user_mobile." and create_time >".$begin_time);
		// if($ajax == 1){
		// 	if($result && $result['send_count'] >=3){
		// 		$return["status"] = 3;
		// 		$return["info"] = "获取次数过多，请输入图文验证码";
		// 		ajax_return($return);   
		// 	}
		// }
		if($verify==''){
				$data['status'] = 0;
				$data['info'] = "请输入图形验证码";
				ajax_return($data);
		}
		if($verify){
			if(!checkVeifyCode($verify)){
				$data['status'] = 0;
				$data['info'] = "图形验证码错误";
				ajax_return($data);
			}
		}
		if(app_conf("SMS_ON")==0)
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['SMS_OFF'];
			ajax_return($data);
		}	

		if($user_mobile == '')
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_MOBILE_EMPTY'];
			ajax_return($data);
		}

		if(!check_mobile($user_mobile))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'];
			ajax_return($data);
		}
		//避免手机重复注册
		$info = $GLOBALS['db']->getRow("SELECT id FROM ".DB_PREFIX."user WHERE mobile ='".$user_mobile."'");
		if($info  > 0){
			$data['status'] = 0;
			$data['info'] = "手机号码已被注册";
			ajax_return($data);
		}
		if(!check_ipop_limit(CLIENT_IP,"register_mobile_verify",60,0))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_CODE_SEND_FAST'];
			ajax_return($data);
		}
		if($GLOBALS['db']->getOne("select send_count from ".DB_PREFIX."mobile_verify_code where mobile =".$user_mobile." and create_time >".$begin_time) >= SEND_VERIFYSMS_LIMIT){
			$data['status'] = 0;
			$data['info'] = "你今天已经不能再发验证码了";
			ajax_return($data);
		}		
		/*if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE client_ip='".CLIENT_IP."'  AND  create_time >=".(TIME_UTC - 60)."  ") > 0){
			$data['status'] = 0;
			$data['info'] = "请稍后再试";
			ajax_return($data);
		}*/
		//开始生成手机验证
		$verify_data['verify_code'] = rand(111111,999999);
		$verify_data['mobile'] = $user_mobile;
		$verify_data['create_time'] = TIME_UTC;
		$verify_data['client_ip'] = CLIENT_IP;
		$verify_data['send_count'] = 1;
		
		if($info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$user_mobile."'")){
			if($info['create_time'] < $begin_time){
				$verify_data['send_count'] = 1;
			}
			else{
				$verify_data['send_count'] = $info['send_count'] + 1;
			}
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"UPDATE","id=".$info['id']);	
		}
		else
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"INSERT");
		
		send_verify_sms($user_mobile,$verify_data['verify_code'],'',true);
		$data['status'] = 1;
		$data['info'] = $GLOBALS['lang']['MOBILE_VERIFY_SEND_OK'];
		ajax_return($data);
	}
	/**
	 * 提供手机号码，无须验证是否用此手机号注册
	 */
	public function send_phone_verifycode_two(){
		if(app_conf("SMS_ON")==0)
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['SMS_OFF'];
			ajax_return($data);
		}
		$user_mobile = strim($_REQUEST['user_mobile']);

		if($user_mobile == '')
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_MOBILE_EMPTY'];
			ajax_return($data);
		}
		if(!check_mobile($user_mobile))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'];
			ajax_return($data);
		}
		$verify = strim($_REQUEST['Verifycode']);
		if($verify==''){
				$data['status'] = 0;
				$data['info'] = "请输入图形验证码";
				ajax_return($data);
		}
		if($verify){
			if(!checkVeifyCode($verify)){
				$data['status'] = 0;
				$data['info'] = "图形验证码错误";
				ajax_return($data);
			}
		}else{
			$sms_count = 1 + es_session::get('sms_count');
			es_session::set("sms_count",$sms_count);
			if(es_session::get('sms_count') >= 3){
				$data['status'] = 2;
				$data['info'] = "获取次数过多，请输入图文验证码";
				ajax_return($data);
			}
		}
		if(!check_ipop_limit(CLIENT_IP,"register_mobile_verify",60,0))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_CODE_SEND_FAST'];
			ajax_return($data);
		}


		//开始生成手机验证
		$verify_data['verify_code'] = rand(111111,999999);
		$verify_data['mobile'] = $user_mobile;
		$verify_data['create_time'] = TIME_UTC;
		$verify_data['client_ip'] = CLIENT_IP;

		if($vid = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$user_mobile."'"))
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"UPDATE","id=".$vid);
		else
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"INSERT");

		send_verify_sms($user_mobile,$verify_data['verify_code'],$users,true);
		$data['status'] = 1;
		$data['info'] = $GLOBALS['lang']['MOBILE_VERIFY_SEND_OK'];
		ajax_return($data);
	}
	/**
	 * 提供手机号码，须验证是否为当前绑定的手机号；
	 */
	public function send_phone_verifycode_three(){
		if(!$_POST)
		{
			 app_redirect("404.html");
			 exit();
		}
		foreach($_POST as $k=>$v)
		{
			$data[$k] = htmlspecialchars(addslashes($v));
		}
		if(app_conf("SMS_ON")==0)
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['SMS_OFF'];
			ajax_return($data);
		}
		$user_mobile = strim($data['user_mobile']);
		$verify = strim($data['Verifycode']);
		if($verify==''){
			$data['status'] = 0;
			$data['info'] = "请输入图形验证码";
			ajax_return($data);
		}
		if(!checkVeifyCode($verify)){
				$data['status'] = 0;
				$data['info'] = "图形验证码错误";
				ajax_return($data);
		}
		// if($verify){
		// 	if(!checkVeifyCode($verify)){
		// 		$data['status'] = 0;
		// 		$data['info'] = "图形验证码错误";
		// 		ajax_return($data);
		// 	}
		// }else{
		// 	$sms_count = 1 + es_session::get('sms_count');
		// 	es_session::set("sms_count",$sms_count);
		// 	if(es_session::get('sms_count') >= 3){
		// 		$data['status'] = 0;
		// 		$data['info'] = "获取次数过多，请输入图文验证码";
		// 		ajax_return($data);
		// 	}
		// }
		if($user_mobile == '')
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_MOBILE_EMPTY'];
			ajax_return($data);
		}
		if(!check_mobile($user_mobile))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'];
			ajax_return($data);
		}
		$get_user_info = get_user_info("*","mobile_encrypt =AES_ENCRYPT('".$user_mobile."','".AES_DECRYPT_KEY."') ");
		if(empty($get_user_info)){
			$data['status'] = 0;
			$data['info'] = "该用户不存在";
			ajax_return($data);
		}
		if($users = get_user_info("*","mobile_encrypt=AES_ENCRYPT('".$user_mobile."','".AES_DECRYPT_KEY."')")){
			if($users['is_delete'] == 1 || $users['is_effect']==0){
				$data['status'] = 0;
				$data['info'] = "会员被锁定或者删除";
				ajax_return($data);
			}
		}
		else{
			$data['status'] = 0;
			$data['info'] = "会员不存在";
			ajax_return($data);
		}

		if(!check_ipop_limit(CLIENT_IP,"register_mobile_verify",60,0))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_CODE_SEND_FAST'];
			ajax_return($data);
		}


		//开始生成手机验证
		$verify_data['verify_code'] = rand(111111,999999);
		$verify_data['mobile'] = $user_mobile;
		$verify_data['create_time'] = TIME_UTC;
		$verify_data['client_ip'] = CLIENT_IP;

		if($vid = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$user_mobile."'"))
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"UPDATE","id=".$vid);
		else
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"INSERT");

		send_verify_sms($user_mobile,$verify_data['verify_code'],$users,true);
		$data['status'] = 1;
		$data['info'] = $GLOBALS['lang']['MOBILE_VERIFY_SEND_OK'];
		ajax_return($data);
	}
	/**
	 * 不提供手机号码，须验证是否为当前绑定的手机号码。
	 */
	public function send_phone_verifycode_four(){
		$verify = strim($_REQUEST['Verifycode']);
		if($verify==''){
			$data['status'] = 0;
			$data['info'] = "请输入图形验证码";
			ajax_return($data);
		}
		if(!checkVeifyCode($verify)){
				$data['status'] = 0;
				$data['info'] = "图形验证码错误";
				ajax_return($data);
		}
		if(app_conf("SMS_ON")==0)
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['SMS_OFF'];
			ajax_return($data);		
		}
		
		$user_id = intval($GLOBALS['user_info']['id']);
		if($user_id == 0)
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
			ajax_return($data);
		}
		if(!check_ipop_limit(CLIENT_IP,"register_mobile_verify",60,0))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_CODE_SEND_FAST'];
			ajax_return($data);
		}
		$user_mobile = $GLOBALS['user_info']['mobile'];
		
		//开始生成手机验证
		$verify_data['verify_code'] = rand(111111,999999);
		$verify_data['mobile'] = $user_mobile;
		$verify_data['create_time'] = TIME_UTC;
		$verify_data['client_ip'] = CLIENT_IP;

		if($vid = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$user_mobile."'"))
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"UPDATE","id=".$vid);
		else
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"INSERT");
		
		send_verify_sms($user_mobile,$verify_data['verify_code'],$GLOBALS['user_info'],true);
		$data['status'] = 1;
		$data['info'] = $GLOBALS['lang']['MOBILE_VERIFY_SEND_OK'];

		ajax_return($data);
	}
	/**
	 * 提供手机号码，须验证是否注册过。  status=1  存在该用户
	 */
	public function send_phone_verifycode_five(){
		if(!$_POST)
		{
			 app_redirect("404.html");
			 exit();
		}
		foreach($_POST as $k=>$v)
		{
			$data[$k] = htmlspecialchars(addslashes($v));
		}
		if(app_conf("SMS_ON")==0)
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['SMS_OFF'];
			ajax_return($data);
		}
		$user_mobile = strim($data['user_mobile']);
		$verify = strim($data['Verifycode']);
		if($user_mobile == '')
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_MOBILE_EMPTY'];
			ajax_return($data);
		}
		if(!check_mobile($user_mobile))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'];
			ajax_return($data);
		}
		$get_user_info = get_user_info("*","mobile_encrypt =AES_ENCRYPT('".$user_mobile."','".AES_DECRYPT_KEY."') ");
		if(empty($get_user_info)){
			$data['status'] = 0;
			$data['info'] = "该用户不存在";
			ajax_return($data);
		}
		if($users = get_user_info("*","mobile_encrypt=AES_ENCRYPT('".$user_mobile."','".AES_DECRYPT_KEY."')")){
			if($users['is_delete'] == 1 || $users['is_effect']==0){
				$data['status'] = 0;
				$data['info'] = "会员被锁定或者删除";
				ajax_return($data);
			}
		}
		else{
			$data['status'] = 0;
			$data['info'] = "会员不存在";
			ajax_return($data);
		}

		if(!check_ipop_limit(CLIENT_IP,"register_mobile_verify",60,0))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_CODE_SEND_FAST'];
			ajax_return($data);
		}
		if($verify==''){
				$data['status'] = 0;
				$data['info'] = "请输入图形验证码";
				ajax_return($data);
		}
		if($verify){
			if(!checkVeifyCode($verify)){
				$data['status'] = 0;
				$data['info'] = "图形验证码错误";
				ajax_return($data);
			}
		}else{
			$sms_count = 1 + es_session::get('sms_count');
			es_session::set("sms_count",$sms_count);
			if(es_session::get('sms_count') >= 3){
				$data['status'] = 0;
				$data['info'] = "获取次数过多，请输入图文验证码";
				ajax_return($data);
			}
		}

		//开始生成手机验证
		$verify_data['verify_code'] = rand(111111,999999);
		$verify_data['mobile'] = $user_mobile;
		$verify_data['create_time'] = TIME_UTC;
		$verify_data['client_ip'] = CLIENT_IP;

		if($vid = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$user_mobile."'"))
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"UPDATE","id=".$vid);
		else
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"INSERT");

		send_verify_sms($user_mobile,$verify_data['verify_code'],$users,true);
		$data['status'] = 1;
		$data['info'] = $GLOBALS['lang']['MOBILE_VERIFY_SEND_OK'];
		ajax_return($data);
	}
	/**
	 * 	需要提供手机号,需验证是否注册,不需图文验证码
	 */
	public function send_phone_verifycode_six(){
		$user_mobile = strim($_REQUEST['user_mobile']);
		$verify =  strim($_REQUEST['Verifycode']);
		$ajax =  strim($_REQUEST['ajax']);
		$t = time();
		$begin_time = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t)); 	//今天开始时间 
		$result = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."mobile_verify_code where mobile =".$user_mobile." and create_time >".$begin_time);
		// if($ajax == 1){
		// 	if($result && $result['send_count'] >=3){
		// 		$return["status"] = 3;
		// 		$return["info"] = "获取次数过多，请输入图文验证码";
		// 		ajax_return($return);   
		// 	}
		// }
		// if($verify){
		// 	if(!checkVeifyCode($verify)){
		// 		$data['status'] = 0;
		// 		$data['info'] = "图形验证码错误";
		// 		ajax_return($data);
		// 	}
		// }
		if(app_conf("SMS_ON")==0)
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['SMS_OFF'];
			ajax_return($data);
		}	

		if($user_mobile == '')
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_MOBILE_EMPTY'];
			ajax_return($data);
		}

		if(!check_mobile($user_mobile))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'];
			ajax_return($data);
		}
		//避免手机重复注册
		$info = $GLOBALS['db']->getRow("SELECT id FROM ".DB_PREFIX."user WHERE mobile ='".$user_mobile."'");
		if($info  > 0){
			$data['status'] = 0;
			$data['info'] = "手机号码已被注册";
			ajax_return($data);
		}
		if(!check_ipop_limit(CLIENT_IP,"register_mobile_verify",60,0))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_CODE_SEND_FAST'];
			ajax_return($data);
		}
		if($GLOBALS['db']->getOne("select send_count from ".DB_PREFIX."mobile_verify_code where mobile =".$user_mobile." and create_time >".$begin_time) >= SEND_VERIFYSMS_LIMIT){
			$data['status'] = 0;
			$data['info'] = "你今天已经不能再发验证码了";
			ajax_return($data);
		}		
		/*if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE client_ip='".CLIENT_IP."'  AND  create_time >=".(TIME_UTC - 60)."  ") > 0){
			$data['status'] = 0;
			$data['info'] = "请稍后再试";
			ajax_return($data);
		}*/
		//开始生成手机验证
		$verify_data['verify_code'] = rand(111111,999999);
		$verify_data['mobile'] = $user_mobile;
		$verify_data['create_time'] = TIME_UTC;
		$verify_data['client_ip'] = CLIENT_IP;
		$verify_data['send_count'] = 1;
		
		if($info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$user_mobile."'")){
			if($info['create_time'] < $begin_time){
				$verify_data['send_count'] = 1;
			}
			else{
				$verify_data['send_count'] = $info['send_count'] + 1;
			}
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"UPDATE","id=".$info['id']);	
		}
		else
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"INSERT");
		
		send_verify_sms($user_mobile,$verify_data['verify_code'],'',true);
		$data['status'] = 1;
		$data['info'] = $GLOBALS['lang']['MOBILE_VERIFY_SEND_OK'];
		ajax_return($data);
	}
	/**
	 * 	需要提供手机号,需验证是否注册,不需图文验证码
	 */
	public function send_phone_verifycode_seven(){
		$user_mobile = strim($_REQUEST['mobile']);
		$verify =  strim($_REQUEST['imgCode']);
		$verify_code =  strim($_REQUEST['sms_code']);
		$user_pwd = strim($_REQUEST['user_pwd']);
		$key =  strim($_REQUEST['key']);
		
		//var_dump($user_mobile);die;
		//$verify =  strim($_REQUEST['Verifycode']);
		//$ajax =  strim($_REQUEST['ajax']);
		//判断手机号是否注册
		if($user_mobile==''){
            $data['status'] = 0;
            $data['info'] = "手机号不能为空";
            ajax_return($data);
		}
		if(!preg_match("/^1[34578]\d{9}$/",$user_mobile)){    
            $data['status'] = 0;
            $data['info'] = "请正确输入手机号";
            ajax_return($data); 
		}
		$info = $GLOBALS['db']->getRow("SELECT id FROM ".DB_PREFIX."user WHERE mobile ='".$user_mobile."'");
		if($info  > 0){
			$data['status'] = 0;
			$data['info'] = "手机号码已被注册";
			ajax_return($data);
		}
		//判断图形验证码是否为空
		if ($key==1) {
			$shortmessage = 1 + es_session::get('shortmessage');
			es_session::set("shortmessage",$shortmessage);
			if(es_session::get('shortmessage') <= 2){
							//开始生成手机验证
			$t = time();
			$begin_time = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t)); 	//今天开始时间 
			$result = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."mobile_verify_code where mobile =".$user_mobile." and create_time >".$begin_time);
			if(!check_ipop_limit(CLIENT_IP,"register_mobile_verify",60,0))
			{
				$data['status'] = 0;
				$data['info'] = $GLOBALS['lang']['VERIFY_CODE_SEND_FAST'];
				ajax_return($data);
			}
			if($GLOBALS['db']->getOne("select send_count from ".DB_PREFIX."mobile_verify_code where mobile =".$user_mobile." and create_time >".$begin_time) >= SEND_VERIFYSMS_LIMIT){
				$data['status'] = 0;
				$data['info'] = "你今天已经不能再发验证码了";
				ajax_return($data);
			}
					$verify_data['verify_code'] = rand(111111,999999);
					$verify_data['mobile'] = $user_mobile;
					$verify_data['create_time'] = TIME_UTC;
					$verify_data['client_ip'] = CLIENT_IP;
					if($vid = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$user_mobile."'"))
						$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"UPDATE","id=".$vid);
					else
						$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"INSERT");
					send_verify_sms($user_mobile,$verify_data['verify_code'],$users,true);
					$data['status'] = 1;
					$data['info'] = $GLOBALS['lang']['MOBILE_VERIFY_SEND_OK'];
					ajax_return($data);
			}
			if (es_session::get('shortmessage') > 2) {
					$data['status'] = 2;
					ajax_return($data);
			}
		}

		//第一次 session shortmessage += 1
//				if(es_session::get("shortmessage")){
//					$data['status'] = 1;
//				}else{
//					es_session::set("shortmessage",1);
//					$data['status'] = 2;
//				}
		

//		$data['info'] = "验证码发送成功";
//		ajax_return($data);
		if ($key==2) {
			if($verify==''){
					$data['status'] = 0;
					$data['info'] = "请输入图形验证码";
					ajax_return($data);
			}
			if($verify){
				if(!checkVeifyCode($verify)){
					$data['status'] = 0;
					$data['info'] = "图形验证码错误";
					ajax_return($data);
				}
			}
			$t = time();
			$begin_time = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t)); 	//今天开始时间 
			$result = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."mobile_verify_code where mobile =".$user_mobile." and create_time >".$begin_time);
			if(!check_ipop_limit(CLIENT_IP,"register_mobile_verify",60,0))
			{
				$data['status'] = 0;
				$data['info'] = $GLOBALS['lang']['VERIFY_CODE_SEND_FAST'];
				ajax_return($data);
			}
			if($GLOBALS['db']->getOne("select send_count from ".DB_PREFIX."mobile_verify_code where mobile =".$user_mobile." and create_time >".$begin_time) >= SEND_VERIFYSMS_LIMIT){
				$data['status'] = 0;
				$data['info'] = "你今天已经不能再发验证码了";
				ajax_return($data);
			}
			$verify_data['verify_code'] = rand(111111,999999);
			$verify_data['mobile'] = $user_mobile;
			$verify_data['create_time'] = TIME_UTC;
			$verify_data['client_ip'] = CLIENT_IP;
			if($vid = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$user_mobile."'"))
				$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"UPDATE","id=".$vid);
			else
				$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"INSERT");
			send_verify_sms($user_mobile,$verify_data['verify_code'],$users,true);
			$data['status'] = 1;
			//$data['info'] = $GLOBALS['lang']['MOBILE_VERIFY_SEND_OK'];
			$data['info'] = "短信已发送";
			ajax_return($data);
		}
        //判断验证码是否正确
		if ($key==3) {
	        //判断验证码是否正确
	        if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$user_mobile."' AND verify_code='".$verify_code."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ")==0){
	            $return['status'] = 3;
	            $return['info'] = "手机验证码出错,或已过期";
	            ajax_return($return);
	            //showErr("手机验证码出错,或已过期");
	        }
				$data['status'] = 1;
				$data['info'] = "验证成功";
				ajax_return($data);
		}
		if ($key==4) {
			if($user_pwd=='')
	        {   
	            $return['status'] = 0;
	            $return['info'] = "密码不能为空";
	            ajax_return($return);
	        }
	        if(!preg_match("/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])[A-Za-z0-9]{6,16}/",$user_pwd)){    
            $data['status'] = 0;
            $data['info'] = "请输入6-16位数字和字母组合";
            ajax_return($data); 
			}
			$data['status'] = 1;
			$data['jump'] = url("index","find#W644_success");
            $data['info'] = "注册成功！";
            ajax_return($data); 

		}


	}
	
	public function get_login_out(){
		//一个账号只可以在一台设备登录
		$sessid = es_session::id();
		if($GLOBALS['user_info']){
			$sess = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."session where user_id ='".$GLOBALS['user_info']['id']."'");
    		if($sess['session_id'] != $sessid){
		    	es_session::delete("user_info");
				$root['status'] = 1;
		        $root['info'] = "当前账号于".$sess['session_data']."在其他设备上登录，请注意账户安全。您已被迫下线，是否重新登录？";
		       	ajax_return($root);
		    }
		}
	}
	/*
	* 微信签到代金券红包领取
	*
	*/
	public  function  wx_get_award(){
		$unionid = strim($_REQUEST['unionid']);
		$phone = strim($_REQUEST['phone']);
		$award_style = strim($_REQUEST['awardStyle']);
		$award_value = strim($_REQUEST['awardValue']);
		if(empty($unionid)||empty($phone)||empty($award_style)|| empty($award_value)){
			$root['status'] = 2;
            $root['msg']='请稍后再试！';
			ajax_return($root);
		}
		//查询是否已有用户
		$user = $GLOBALS['db']->getRow("SELECT id,red_money,wx_openid FROM ".DB_PREFIX."user WHERE mobile='".$phone."'");
		if($user){
			if($award_style=='daijinquan'){//微信代金券发放
				$order_data['begin_time'] = TIME_UTC;
				$order_data['end_time'] = to_timespan(to_date($order_data['begin_time'],'Y-m-d')." ".app_conf("INTERESTRATE_TIME")." month")-1;
				$order_data['money'] = $award_value;
				$sn = unpack('H12',str_shuffle(md5(uniqid())));
				$order_data['sn'] = $sn[1];
				$order_data['content'] = '微信签到有礼'.$award_value.'元代金券';
				$order_data['activity_id'] = 1001;
				$order_data['add_time'] = TIME_UTC;
				$order_data['user_id'] = $user['id'];
				$result = $GLOBALS['db']->autoExecute(DB_PREFIX."ecv",$order_data,"INSERT");
				if(!$result){
					$root['status']=2;
					$root['msg']='请稍后再试！';
					ajax_return($root);
				}
			}elseif($award_style=='licaihongbao'){//微信理财红包发放
				$order_data['red_money'] = $user['red_money']+$award_value;
				$order_data['update_time'] = TIME_UTC;
				if(empty($user['wx_openid'])){
					$order_data['wx_openid'] = $unionid;
				}
				$order_datas['money'] = $award_value;
				$order_datas['memo'] = '微信签到有礼'.$award_value.'元红包';
				$order_datas['from_activity_id'] = 1001;
				$order_datas['type'] = 56;
				$order_datas['create_time'] = TIME_UTC;
				$order_datas['create_time_ymd'] = to_time(TIME_UTC,'Y-m-d');
				$order_datas['create_time_ym'] = to_time(TIME_UTC,'Y');
				$order_datas['create_time_y'] = to_time(TIME_UTC,'Y');
				$order_datas['user_id'] = $user['id'];
				$insert_red_money_log = $GLOBALS['db']->autoExecute(DB_PREFIX."user_red_money_log",$order_datas,"INSERT");//插入红包记录
				$insert_red_money_log_id = $GLOBALS['db'] ->insert_id();
				if($insert_red_money_log_id){
					$update_user_red_money = $GLOBALS['db']->autoExecute(DB_PREFIX."user",$order_data,"UPDATE","id=".$user['id']);//更新用户红包金额
					if(!$update_user_red_money){
						$GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."user_red_money_log where id='".$insert_red_money_log_id."'");
						$root['status']=2;
						$root['msg']='请稍后再试！';
						ajax_return($root);
					}
				}else{
					$root['status']=2;
					$root['msg']='请稍后再试！';
					ajax_return($root);
				}
				/* if($GLOBALS['db']->autoExecute(DB_PREFIX."user",$order_data,"UPDATE","id=".$user['id'])){
					$order_data['money'] = $award_value;
					$order_data['memo'] = '微信签到有礼'.$award_value.'元红包';
					$order_data['activity_id'] = 1001;
					$order_data['create_time'] = TIME_UTC;
					$order_data['create_time_ymd'] = to_time(TIME_UTC,'Y-m-d');
					$order_data['create_time_ym'] = to_time(TIME_UTC,'Y');
					$order_data['create_time_y'] = to_time(TIME_UTC,'Y');
					$order_data['user_id'] = $user['id'];
					$result = $GLOBALS['db']->autoExecute(DB_PREFIX."user_red_money_log",$order_data,"INSERT");
				} */
			}
			$root['status']=1;
            $root['msg']='用户已注册';
			ajax_return($root);
		}else{
			$root['status']=0;
            $root['msg']='用户未注册';
			ajax_return($root);
		}
		
	}
	
	//微信是否绑定
	public function wx_isbind(){
		$openid = strim($_REQUEST['wx_openid']);
		if(!$openid){
			return false;
		}
		$root = array();
		$wx_info =$GLOBALS['db'] ->getRow("select mobile,wx_openid from ".DB_PREFIX."user where wx_openid ='$openid'");
		if($wx_info['wx_openid']){
			$root['status']=1;
			$root['phone']=$wx_info['mobile'];
		}else{
			$root['status'] =0;
		}
		echo json_encode($root);
	}
	
	//微信绑定
	/*params 微信wx_openid：wx_openid 手机号：phone 登陆密码：password
	*
	*/
	public function wx_bind(){
		$openid = strim($_REQUEST['wx_openid']);
		$phone = strim($_REQUEST['phone']);
		$password = strim($_REQUEST['password']);
		if(!$openid||!$phone||!$password){//过滤数据 不能为空
			return false;
		}
		$user_name = 'w'.$phone;
		$root = array();
		$userinfo =$GLOBALS['db'] ->getRow("select id,wx_openid from ".DB_PREFIX."user where user_name ='$user_name' and user_pwd = '$password'");
		if($userinfo['id']){//用户是否存在
			if($userinfo['wx_openid']){//若已存在
				$root['status']=1;
				$root['openid']=$userinfo['wx_openid'];
			}else{
				$grouth = $GLOBALS['db']->getOne('select user_id from '.DB_PREFIX.'user_group_point where user_id='.$userinfo['id'].' and task_type=7 and status=1');
				if(!$grouth){
					$level = new Level();
					$GLOBALS['user_info']['id']=$userinfo['id'];
					$res = $level->get_grow_point(7);
					unset($GLOBALS['user_info']);
				}
				
				$root['status']=1;
				$data['wx_openid'] = $openid;
				$GLOBALS['db']->autoExecute(DB_PREFIX."user",$data,"UPDATE","id=".$userinfo['id']);
			}
			
		}else{
			$root['status'] =0;
		}
		echo json_encode($root);
	}
	
	//微信解绑
	/*
	params 手机号：phone
	*/
	public function wx_unbind(){
		$phone = strim($_REQUEST['phone']);
		if(!$phone){
			return false;
		}
		$root = array();
		$user_name = 'w'.$phone;
		$userinfo =$GLOBALS['db'] ->getRow("select id from ".DB_PREFIX."user where user_name ='$user_name'");
		if($userinfo){
			$data['wx_openid'] = '';
			$GLOBALS['db']->autoExecute(DB_PREFIX."user",$data,"UPDATE","id=".$userinfo['id']);
			$root['status'] =1;
			
		}else{
			$root['status'] =0;
		}
		echo json_encode($root);
	}
	//ajax session储存变量参数
	public function storage(){
		$user_id = intval($GLOBALS['user_info']['id']);
		if(!$user_id)
		{
			app_redirect(url("index","user#login"));		
		}
		foreach($_POST as $k=>$v){
			$data[$k] = htmlspecialchars(addslashes($v));
		}
		if($data['money'] && $data['deal_id']){
			es_session::set('deal_id',$data['deal_id']);
			es_session::set('lend_money',$data['money']);
			es_session::set('repay_time',$data['repay_time']);
		}
		if(es_session::get('deal_id') && es_session::get('lend_money')){
			$root['status'] = 1;
		}else{
			$root['status'] = 0;
		}
		echo json_encode($root);
	}
	public function storage_red_id(){
		$user_id = intval($GLOBALS['user_info']['id']);
		if(!$user_id)
		{
			app_redirect(url("index","user#login"));		
		}
		foreach($_POST as $k=>$v){
			$data[$k] = htmlspecialchars(addslashes($v));
		}
		//如果clean = 1，清除红包
		if($data['clean'] == 1){
			es_session::delete('red_id');
			es_session::delete('coupon_id');
		}
		if($data['red_id']){
			es_session::set('red_id',$data['red_id']);
		}else{
			es_session::delete('red_id');
		}
		if(es_session::get('red_id')){
			$root['status'] = 1;
		}else{
			$root['status'] = 0;
		}
		echo json_encode($root);
	}

	public function storage_coupon_id(){
		$user_id = intval($GLOBALS['user_info']['id']);
		if(!$user_id)
		{
			app_redirect(url("index","user#login"));		
		}
		foreach($_POST as $k=>$v){
			$data[$k] = htmlspecialchars(addslashes($v));
		}
		if($data['coupon_id']){
			es_session::set('coupon_id',$data['coupon_id']);
		}else{
			es_session::delete('coupon_id');
		}
		if(es_session::get('coupon_id')){
			$root['status'] = 1;
		}else{
			$root['status'] = 0;
		}
		echo json_encode($root);
	}	
	public function debitregister(){
		$user_name = strim($_REQUEST['user_name']);
		$mobile = strim($_REQUEST['mobile']);
		$user_pwd = strim($_REQUEST['user_pwd']);
		$sms_code = strim($_REQUEST['sms_code']);
		$verify = strim($_REQUEST['verify']);
		//echo es_session::get("file_url"); die;
		
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
		// 判断邀请码是否有效
		if(isset($user_data['referer'])&&$user_data['referer']!=''){
			$p_user_id = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."user WHERE mobile ='".$user_data['referer']."'");
			if(empty($p_user_id)){
				$return['info'] = "邀请码不存在";
				ajax_return($return);
			}
		}
		/*if(!empty($_FILES)){
			$oss_domain = "https://oss.9caitong.com";
			$oss_img_path = "Img";
			$y = date("Y");
			$m = date("m");
			$d = date("d");
			$endpoint = HOSTNAME;  // http://oss-cn-hangzhou.aliyuncs.com
            $accessKeyId = ACCESS_ID;
            $accessKeySecret = ACCESS_KEY;
            $bucket = BUCKET;
			$imgcount=0;
			foreach($_FILES['myfile']['name'] as $key=>$value){
				//得到上传的临时文件流
			$tempFile = $_FILES['myfile']['tmp_name'][$key];
			$fileExt = explode('.',$value);
			$fileext = $fileExt[1];
			//允许的文件后缀
			$fileTypes = array('jpg','jpeg','gif','png'); 
			if(!in_array($fileext,$fileTypes)){
				continue;
			}
			$imgcount++;
			 //新文件名
                $new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $fileext;
                //移动文件
                $object = $oss_img_path."/".$y."/".$m."/".$d."/".$new_file_name;
                //上传oss完成返回
                try {
                    $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
                    $s = $ossClient->uploadFile($bucket, $object, $tempFile);
					$file_url .= $oss_domain."/".$oss_img_path."/".$y."/".$m."/".$d."/".$new_file_name.";";
					//echo "上传成功！";
                } catch (OssException $e) {
                    $rs=$e->getMessage() . "\n";
					$res['info'] =$rs;
					ajax_return($res);
                   
                }
			}
			if($imgcount==0){
				$res['info'] ='必须上传至少一张图片';
				ajax_return($res);
			}
		}else{
			$res['info'] ='必须上传至少一张图片';
			ajax_return($res);
		}*/
		$file_url = es_session::get("file_url");
		if(!$file_url){
			$res['info'] ='必须上传至少一张图片';
			ajax_return($res);
		}
		es_session::delete("file_url");
		$user_data['is_debit'] = 1;
		$user_data['cunguan_register'] = 1;
		$user_data['debit_images'] = $file_url;
		$user_data['user_pwd'] = $user_pwd;
		$user_data['is_effect'] = 1;
		$user_data['create_time'] = TIME_UTC;
		$user_data['mobilepassed'] = 1;
		$user_data["mobile_encrypt"] = " AES_ENCRYPT('".$user_data['mobile']."','".AES_DECRYPT_KEY."') ";
		$res = save_user($user_data);
		if($res['status']==1){
			$result = do_login_user($user_data['user_name'],$user_data['user_pwd']);
			$root['status'] =1;
			$root['info'] ="注册成功！";
			$root['msg'] ="恭喜您";
			ajax_return($root);
		}else{
			$root['info'] ="注册失败";
			ajax_return($root);
		}		
	}
	public function uploadFile(){
		if(!empty($_FILES)){
			$oss_domain = "https://oss.9caitong.com";
			$oss_img_path = "Img";
			$y = date("Y");
			$m = date("m");
			$d = date("d");
			$endpoint = HOSTNAME;  // http://oss-cn-hangzhou.aliyuncs.com
            $accessKeyId = ACCESS_ID;
            $accessKeySecret = ACCESS_KEY;
            $bucket = BUCKET;
				//得到上传的临时文件流
			$file = $_FILES['myfile']['name'];
			$tempFile = $_FILES['myfile']['tmp_name'];
			$fileExt = explode('.',$file);
			$fileext = $fileExt[count($fileExt)-1];
			//允许的文件后缀
			$fileTypes = array('jpg','jpeg','gif','png','PNG','GIF','JPG','JPEG'); 
			if(!in_array($fileext,$fileTypes)){
				$root['status'] =1;
				$root['info'] ="文件格式不正确";
				ajax_return($root);
			}
			 //新文件名
                $new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $fileext;
                //移动文件
                $object = $oss_img_path."/".$y."/".$m."/".$d."/".$new_file_name;
                //上传oss完成返回
                try {
                    $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
                    $s = $ossClient->uploadFile($bucket, $object, $tempFile);
					
					$file_url = es_session::get("file_url");
					if($file_url){
						$file_url .= $oss_domain."/".$oss_img_path."/".$y."/".$m."/".$d."/".$new_file_name.";";
						es_session::set("file_url",$file_url);
					}else{
						$file_url = $oss_domain."/".$oss_img_path."/".$y."/".$m."/".$d."/".$new_file_name.";";
						es_session::set("file_url",$file_url);
					}
					
					//echo "上传成功！";
                } catch (OssException $e) {
                    $rs=$e->getMessage() . "\n";
                   
                }
			
		}else{
			$res['info'] ='上传失败';
			ajax_return($res);
		}
	}

	
	
	//微信抽奖活动发放理财红包
	
	public function wx_red_packet(){
        $wx_openid = $_REQUEST['unionid'];
        $red_packet_type_id = $_REQUEST['red_packet_type_id'];
        $msg = $_REQUEST['msg'];
        $awardValue = $_REQUEST['awardValue'];
        $mobile = $_REQUEST['mobile'];
        if(empty($red_packet_type_id)||empty($msg)|| empty($awardValue)){
			$data['status'] = 0;
            $data['info']='系统繁忙，请稍后再试！！';
			ajax_return($data);

		}
        //根据手机号兑换红包
        if(!empty($mobile)){
        	
            $user = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."user WHERE mobile='".$mobile."'");

            if(!empty($wx_openid)){//再次进行微信绑定
              $data['wx_openid'] = $wx_openid;
			  $GLOBALS['db']->autoExecute(DB_PREFIX."user",$data,"UPDATE","id=".$user['id']);
        	}
             if($user&&!empty($red_packet_type_id)&&!empty($msg)&&! empty($awardValue)){
                $user_id = $user['id'];
	        	$result=send_honbao($red_packet_type_id,$user_id,$msg,$awardValue);

				if($result)
				{
		          $data['status']=1;
		          $data['info']="恭喜你获得".$awardValue."元理财红包";
		        }else{
		          $data['status']=0;
		          $data['info']="发放失败，请稍后再试";
		        }
		        ajax_return($data);
             }else{
                $data['status']=0;
		        $data['info']="用户未注册或者信息为空";
		        
		        ajax_return($data);
             }
        }
        
        
        $user = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."user WHERE wx_openid='".$wx_openid."'");

        if($user){

            $user_id = $user['id'];
        	$result=send_honbao($red_packet_type_id,$user_id,$msg,$awardValue);

			if($result)
			{
	          $data['status']=1;
	          $data['info']="恭喜你获得".$awardValue."元理财红包";
	        }else{
	          $data['status']=-1;
	          $data['info']="发放失败，请稍后再试";
	        }
        }else{
          $data['status']=-2;
          $data['info']="系统繁忙，请稍后再试";
       }
          ajax_return($data);
	}
	
	//根据手机号判断是否注册玖财通账户
	public function is_jctuser(){
         
         $phone = $_REQUEST['phone'];
         if($phone){
         	$info = $GLOBALS['db']->getOne("SELECT * FROM ".DB_PREFIX."user WHERE mobile ='".$phone."'");
         	if($info){
         		$data['status']=1;
		        $data['info']="用户已经注册";
		        ajax_return($data);
         	}
         }


         $data['status']=0;
         $data['info']="未注册玖财通账户";
         ajax_return($data);
	}
	
	
	//微信玖币抽奖活动注册 并发放奖励红包
	public function jbregister()
    {
        $switch_conf = $GLOBALS['db']->getAll("SELECT status FROM ".DB_PREFIX."switch_conf where switch_id=1 or switch_id=2");
        foreach ($switch_conf as $k => $v) {
            if($v['status'] != 1){
                $return['status'] = 0;
                $return['info'] = "系统正在升级，请稍后再试";
                ajax_return($return);
            }
        }
        
         //非微信后台ip过来的不让注册  防止灌数据
        if(!isset($_SERVER['HTTP_REMOTE_HOST']) || $_SERVER['HTTP_REMOTE_HOST']!='123.57.1.42'){
		    $return['status'] = 0;
            $return['info'] = $_SERVER;
            ajax_return($return);
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
        
        
        
        if(trim($user_data['user_pwd'])=='')
        {   
            $return['status'] = 0;
            $return['info'] = "密码不能为空";
            ajax_return($return);
            
        }
        
        
        
        //避免手机重复注册
        $info = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."user WHERE mobile ='".$user_data['mobile']."'");
        if($info  > 0){
            $return['status'] = 0;
            $return['info'] = "手机号码已被注册";
            ajax_return($return);
        }else if($user_data['mobile']==''){
            $return['status'] = 0;
            $return['info'] = "手机号不能为空";
            ajax_return($return);
        }
       
        $user_data['cunguan_register'] = 1;
		$user_data['is_effect'] = 1;
		$user_data['create_time'] = TIME_UTC;
		$user_data['mobilepassed'] = 1;
		$user_data["mobile_encrypt"] = " AES_ENCRYPT('".$user_data['mobile']."','".AES_DECRYPT_KEY."') ";
        $res = save_user($user_data);

        if($res['status'] == 1)
        {         
                //在此自动登录
                $result = do_login_user($user_data['user_name'],$user_data['user_pwd']);
                $return['status'] = 1;
                $return['info'] = "注册成功<br/>恭喜您已获得8888元体验金+518元红包，请到我的账户查看!";
                
                $return['jump'] = url("index","user#steptwo");
                ajax_return($return);               
        }
        else
        {
            $return['status'] = 0;
            $return['info'] = "注册失败";
            ajax_return($return);
        }
    }
	
	

}
?>