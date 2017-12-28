<?php
require APP_ROOT_PATH.'system/libs/user.php';
require APP_ROOT_PATH.'system/libs/voucher.php';
class ajax_activityModule extends SiteBaseModule
{
	public function __construct(){
		parent::__construct();
		$no_action_array = array("check_field","load_api_url","weixin_login","bid_calculate");
		if(!in_array(ACTION_NAME,$no_action_array) && !check_hash_key()){
			showErr("非法请求!",1);
		}
	}
	public function activity_receive(){
		if(!$_POST)
		{
			 app_redirect("404.html");
			 exit();
		}
		foreach($_POST as $k=>$v)
		{
			$user_data[$k] = htmlspecialchars(addslashes($v));
		}
		//活动的具体信息
		$act = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."activity where activity_id=".$user_data['activity_id']);
		$create_time = strtotime($act['start_time']);
		$end_time = strtotime($act['end_time']);
		if (time() < $create_time || time() > $end_time){
			$return['status'] = 0;
			$return['msg'] = "不在活动时间内";
            ajax_return($return);
        }
        $award_type = $user_data['lie_num'];
        $lie_award_value = $user_data['lie_award_value'];
        $invite_num = array('1' => 10, '5' => 30, '6' => 40, '7' => 40, '8' => 70, '9' => 80, '10' => 120, '15' => 150, '20' => 300, '40' => 400, '50' => 800, '100' => 1500);
        if ($invite_num[$award_type] == $lie_award_value) {
            $data['award_value'] = $invite_num[$award_type];
        }else {
        	$return['status'] = 0;
			$return['msg'] = "请勿擅自更改数据";
            ajax_return($return);
        }
        $user_list = get_referer_list($user_data['activity_id'],1);
        if ($user_list['count'] < $award_type) {
            $return['status'] = 0;
			$return['msg'] = "不符合条件";
            ajax_return($return);
        }
        $user = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."activity_log where user_id=".$GLOBALS['user_info']['id']." and activity_id=".$user_data['activity_id']." and unique_id =".$user_data['lie_num']." and award_value=".$user_data['lie_award_value']);
        if($user){
            $return['status'] = 0;
            $return['msg'] = "不可重复领取";
            ajax_return($return);
        }
        $data['user_id'] = intval($GLOBALS['user_info']['id']);
        $data['content'] = $act['title'];
        $data['activity_id'] = intval($user_data['activity_id']);
        $data['addtime'] = time();
        $data['endtime'] = time() + (30 * 86400);
        $data['unique_id'] = $award_type;
        $userInfo['red_money'] = $lie_award_value;
        $userInfo['activity_id'] = $user_data['activity_id'];
        //发红包的活动
        if ($user_list['user_money'] < 2000) {//小于2000发代金券
            $data['status'] = 1;
            send_voucher(4,$GLOBALS['user_info']['id'],false,$userInfo['red_money']);
        }else {//大于等于2000发红包
            $data['status'] = 2;
            red_modify_account($userInfo,$GLOBALS['user_info']['id'],$act['title'].',获取红包');
        }
        $GLOBALS['db']->autoExecute(DB_PREFIX."activity_log",$data);
        $insert_id = $GLOBALS['db']->insert_id();
        if($insert_id){
        	$return['status'] = 1;
			$return['msg'] = "领取成功";
            ajax_return($return);
        }
        
	}
	public function activity_receive_y(){
		if(!$_POST)
		{
			 app_redirect("404.html");
			 exit();
		}
		foreach($_POST as $k=>$v)
		{
			$user_data[$k] = htmlspecialchars(addslashes($v));
		}
		//活动的具体信息
		$act = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."activity where activity_id=".$user_data['activity_id']);
		$create_time = strtotime($act['start_time']);
		$end_time = strtotime($act['end_time']);
		if (time() < $create_time || time() > $end_time){
			$return['status'] = 0;
			$return['msg'] = "不在活动时间内";
            ajax_return($return);
        }
        $award_type = $user_data['lie_num'];
        $lie_award_value = $user_data['lie_award_value'];
        $invite_num = array('1000' => 5, '5000' => 30, '10000' => 30, '50000' => 150, '100000' => 300, '200000' => 800, '500000' => 2000, '1000000' => 3000, '2000000' => 4000, '3000000' => 4500, '4000000' => 6000, '5000000' => 8000);
        if ($invite_num[$award_type] == $lie_award_value) {
            $data['award_value'] = $invite_num[$award_type];
        }else {
        	$return['status'] = 0;
			$return['msg'] = "请勿擅自更改数据";
            ajax_return($return);
        }
        $user_list = get_referer_list($user_data['activity_id'],1);
        if ($user_list['money'] < $award_type) {
            $return['status'] = 0;
			$return['msg'] = "不符合条件";
            ajax_return($return);
        }
        $user = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."activity_log where user_id=".$GLOBALS['user_info']['id']." and activity_id=".$user_data['activity_id']." and unique_id =".$user_data['lie_num']." and award_value=".$user_data['lie_award_value']);
        if($user){
            $return['status'] = 0;
            $return['msg'] = "不可重复领取";
            ajax_return($return);
        }
        $data['user_id'] = intval($GLOBALS['user_info']['id']);
        $data['content'] = $act['title'];
        $data['activity_id'] = intval($user_data['activity_id']);
        $data['addtime'] = time();
        $data['endtime'] = time() + (30 * 86400);
        $data['unique_id'] = $award_type;
        $userInfo['red_money'] = $lie_award_value;
        $userInfo['activity_id'] = $user_data['activity_id'];
        //发红包的活动
        if ($user_list['user_money'] < 2000) {//小于2000发代金券           
            $data['status'] = 1;
            send_voucher(4,$GLOBALS['user_info']['id'],false,$userInfo['red_money']);
        }else {//大于等于2000发红包
            $data['status'] = 2;
            red_modify_account($userInfo,$GLOBALS['user_info']['id'],$act['title'].',获取红包');
        }
        $GLOBALS['db']->autoExecute(DB_PREFIX."activity_log",$data);
        $insert_id = $GLOBALS['db']->insert_id();
        if($insert_id){
        	$return['status'] = 1;
			$return['msg'] = "领取成功";
            ajax_return($return);
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
                $return['status'] = 0;
                $return['info'] = "图形验证码有误";
                ajax_return($return);
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
        // if(trim($user_data['user_pwd_confirm']) != ""){
        //     if(trim($user_data['user_pwd'])!=trim($user_data['user_pwd_confirm']))
        //     {
        //         // $return['status'] = 1;
        //         $return['info'] = $GLOBALS['lang']['USER_PWD_CONFIRM_ERROR'];
        //         ajax_return($return);
        //         //showErr($GLOBALS['lang']['USER_PWD_CONFIRM_ERROR']);
        //     }
        // }
        //判断验证码是否正确
        if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".strim($user_data['mobile'])."' AND verify_code='".strim($user_data['sms_code'])."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ")==0){
            $return['status'] = 0;
            $return['info'] = "手机验证码出错,或已过期";
            ajax_return($return);
            //showErr("手机验证码出错,或已过期");
        }
        
        if(trim($user_data['user_pwd'])=='')
        {   
            $return['status'] = 0;
            $return['info'] = "密码不能为空";
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
            $return['status'] = 0;
            $return['info'] = "手机号码已被注册";
            ajax_return($return);
        }else if($user_data['mobile']==''){
            $return['status'] = 0;
            $return['info'] = "手机号不能为空";
            ajax_return($return);
        }

        //判断是否为手机注册
        if((app_conf("REGISTER_TYPE") == 0 || app_conf("REGISTER_TYPE") == 1) && (app_conf("USER_VERIFY") == 0 || app_conf("USER_VERIFY") == 2)){
            if(strim($user_data['sms_code']) == ""){
                $return['status'] = 0;
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
            $return['status'] = 0;
            $return['info'] = "手机号码已被注册";
            ajax_return($return);
        }else if($user_data['mobile']==''){
            $return['status'] = 0;
            $return['info'] = "手机号不能为空";
            ajax_return($return);
        }
        // 判断邀请码是否有效
        if(isset($user_data['referer'])&&$user_data['referer']!=''){
            $p_user_id = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."user WHERE mobile ='".$user_data['referer']."'");
            if(!$p_user_id){
                $return['status'] = 0;
                $return['info'] = "邀请码不存在";
                ajax_return($return);
            }
        }

        $res = save_user($user_data);

        if($res['status'] == 1)
        {
            // 邀请人数量加1
            $res_ref=$GLOBALS['db']->query("update ".DB_PREFIX."user set referral_count=referral_count+1 where id=".$p_user_id);
            if($res_ref&&$p_user_id){
                $order_data['begin_time'] = TIME_UTC;
                $order_data['end_time'] = to_timespan(to_date($order_data['begin_time'])." ".app_conf("INTERESTRATE_TIME")." month -1 day");
                $order_data['money'] = 20;
                $order_data['ecv_type_id'] = 5;
                $sn = unpack('H12',str_shuffle(md5(uniqid())));
                $order_data['sn'] = $sn[1];
                $order_data['password'] = rand(10000000,99999999);
                $order_data['user_id']=$p_user_id;
                $order_data['child_id']=$GLOBALS['user_info']['id'];
                $order_data['content']="邀请好友奖励代金券！";
                $order_data['cunguan_tag']=1;
                $check=$GLOBALS['db']->getOne("select id from ".DB_PREFIX."ecv where user_id=".$GLOBALS['user_info']['pid']." and child_id=".$GLOBALS['user_info']['id']);
                           
                // if(empty($check)){
                //     $result=$GLOBALS['db']->autoExecute(DB_PREFIX."ecv",$order_data,"INSERT");
                //     if($result==false){
                //         showErr("代金券发放失败",1);
                //     }
                // }
            }
            // 注册成功站内信
            $user_id = intval($res['data']);
            $notices['site_name'] = app_conf("SHOP_TITLE");
            $notices['user_name'] = $user_data['mobile'];
            $notices['time'] = to_date(TIME_UTC,"Y年m月d日 H:i");
            $time=TIME_UTC;
            $tmpl_contents = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_INS_REGISTER_SUCCESS_MSG'",false);
            $GLOBALS['tmpl']->assign("notice",$notices);
            $content = $GLOBALS['tmpl']->fetch("str:".$tmpl_contents['content']);
            
            send_user_msg("恭喜您获得84442体验金+50代金券",$content,0,$user_id,$time,0,true,21);
            //更新来路
            //$GLOBALS['db']->query("update ".DB_PREFIX."user set referer = '".$GLOBALS['referer']."' where id = ".$user_id);
            /*$user_info = get_user_info("is_effect","id = ".$user_id);
            if($user_info['is_effect']==1)
            {*/
                //在此自动登录
                $result = do_login_user($user_data['user_name'],$user_data['user_pwd']);
                $return['status'] = 1;
                $return['info'] = "注册成功<br/>恭喜您已获得8888元体验金+518元红包，请到我的账户查看!";
                //$return['info'] = $GLOBALS["tmpl"]->fetch("reg_successTip.html");
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
            $return['status'] = 0;
            $return['info'] = "注册失败";
            ajax_return($return);
        }
    }
     /*
    * 邀友注册页-wap W644 邀请好友送体验金
    */
    public function doregister_W644()
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
//        if(intval(app_conf("VERIFY_IMAGE")) == 1 && intval(app_conf("USER_VERIFY")) >= 3){
//            $verify = strim($_REQUEST['verify']);
//            if(!checkVeifyCode($verify))
//            {
//                $return['status'] = 0;
//                $return['info'] = "图形验证码有误";
//                ajax_return($return);
//            }
//        }
        require_once APP_ROOT_PATH."system/libs/user.php";
        $user_data = $_POST;
		$user_datas = $user_data['id'];
		unset($user_data['id']);
        if(!$user_data){
             app_redirect("404.html");
             exit();
        }
        foreach($user_data as $k=>$v)
        {
            $user_data[$k] = htmlspecialchars(addslashes($v));
        }
        //防止wap冲突
        // if(trim($user_data['user_pwd_confirm']) != ""){
        //     if(trim($user_data['user_pwd'])!=trim($user_data['user_pwd_confirm']))
        //     {
        //         // $return['status'] = 1;
        //         $return['info'] = $GLOBALS['lang']['USER_PWD_CONFIRM_ERROR'];
        //         ajax_return($return);
        //         //showErr($GLOBALS['lang']['USER_PWD_CONFIRM_ERROR']);
        //     }
        // }
        //判断验证码是否正确
        if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".strim($user_data['mobile'])."' AND verify_code='".strim($user_data['sms_code'])."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ")==0){
            $return['status'] = 0;
            $return['info'] = "手机验证码出错,或已过期";
            ajax_return($return);
            //showErr("手机验证码出错,或已过期");
        }
        
        if(trim($user_data['user_pwd'])=='')
        {   
            $return['status'] = 0;
            $return['info'] = "密码不能为空";
            ajax_return($return);
            //showErr($GLOBALS['lang']['USER_PWD_ERROR']);
        }
        
        
        if(isset($user_data['referer']) && $user_data['referer']!=""){
            //$p_user_data = get_user_info("id,user_type","mobile_encrypt =AES_ENCRYPT('".$user_data['referer']."','".AES_DECRYPT_KEY."') OR user_name='w".$user_data['referer']."'");
			//抢红包增加次数
			if($user_datas){
				insert_red_log($user_datas,5);
			}
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
            $return['status'] = 0;
            $return['info'] = "手机号码已被注册";
            ajax_return($return);
        }else if($user_data['mobile']==''){
            $return['status'] = 0;
            $return['info'] = "手机号不能为空";
            ajax_return($return);
        }

        //判断是否为手机注册
        if((app_conf("REGISTER_TYPE") == 0 || app_conf("REGISTER_TYPE") == 1) && (app_conf("USER_VERIFY") == 0 || app_conf("USER_VERIFY") == 2)){
            if(strim($user_data['sms_code']) == ""){
                $return['status'] = 0;
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
            $return['status'] = 0;
            $return['info'] = "手机号码已被注册";
            ajax_return($return);
        }else if($user_data['mobile']==''){
            $return['status'] = 0;
            $return['info'] = "手机号不能为空";
            ajax_return($return);
        }
        // 判断邀请码是否有效
        if(isset($user_data['referer'])&&$user_data['referer']!=''){
            $p_user_id = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."user WHERE mobile ='".$user_data['referer']."'");
            if(!$p_user_id){
                $return['status'] = 0;
                $return['info'] = "邀请码不存在";
                ajax_return($return);
            }
        }
        $res = save_user($user_data);

        if($res['status'] == 1)
        {
            // 邀请人数量加1
            $res_ref=$GLOBALS['db']->query("update ".DB_PREFIX."user set referral_count=referral_count+1 where id=".$p_user_id);
            if($res_ref&&$p_user_id){
                $order_data['begin_time'] = TIME_UTC;
                $order_data['end_time'] = to_timespan(to_date($order_data['begin_time'])." ".app_conf("INTERESTRATE_TIME")." month -1 day");
                $order_data['money'] = 20;
                $order_data['ecv_type_id'] = 5;
                $sn = unpack('H12',str_shuffle(md5(uniqid())));
                $order_data['sn'] = $sn[1];
                $order_data['password'] = rand(10000000,99999999);
                $order_data['user_id']=$p_user_id;
                $order_data['child_id']=$GLOBALS['user_info']['id'];
                $order_data['content']="邀请好友奖励代金券！";
                $order_data['cunguan_tag']=1;
                $check=$GLOBALS['db']->getOne("select id from ".DB_PREFIX."ecv where user_id=".$GLOBALS['user_info']['pid']." and child_id=".$GLOBALS['user_info']['id']);
                           
                // if(empty($check)){
                //     $result=$GLOBALS['db']->autoExecute(DB_PREFIX."ecv",$order_data,"INSERT");
                //     if($result==false){
                //         showErr("代金券发放失败",1);
                //     }
                // }
            }
            // 注册成功站内信
//            $user_id = intval($res['data']);
//            $notices['site_name'] = app_conf("SHOP_TITLE");
//            $notices['user_name'] = $user_data['mobile'];
//            $notices['time'] = to_date(TIME_UTC,"Y年m月d日 H:i");
//            $time=TIME_UTC;
//            $tmpl_contents = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_INS_REGISTER_SUCCESS_MSG'",false);
//            $GLOBALS['tmpl']->assign("notice",$notices);
//            $content = $GLOBALS['tmpl']->fetch("str:".$tmpl_contents['content']);
//            
//            send_user_msg("恭喜您获得84442体验金+50代金券",$content,0,$user_id,$time,0,true,21);
            //更新来路
            //$GLOBALS['db']->query("update ".DB_PREFIX."user set referer = '".$GLOBALS['referer']."' where id = ".$user_id);
            /*$user_info = get_user_info("is_effect","id = ".$user_id);
            if($user_info['is_effect']==1)
            {*/
                //在此自动登录
                $result = do_login_user($user_data['user_name'],$user_data['user_pwd']);
                $return['status'] = 1;
                $return['info'] = "注册成功<br/>恭喜您已获得8888元体验金+518元红包，请到我的账户查看!";
                //$return['info'] = $GLOBALS["tmpl"]->fetch("reg_successTip.html");
                //$return['msg'] = "8888元注册体验金+16666元分享体验金+58888出借体验金+50元代金券";
                $return['jump'] = url("index","find#W644_success");
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
            $return['status'] = 0;
            $return['info'] = "注册失败";
            ajax_return($return);
        }
    }
    
//*七夕奖励发放
    /* interestrate_id 加息卡类型id
     * $user_id 用户id
     * msg 描述
     * 
     */
 public function ajax_sendAward(){
       if($GLOBALS["user_info"]){
            $task = intval($_POST['task']);
            es_session::set("task",$task); //1失败 0成功
            //判断活动是否开始
            $time = timeDay(strtotime("2017-08-28 00:00:00"), strtotime("2017-08-30 23:59:59"));
            if($time['status']==false){ //活动时间
                $root['status']=false;
                $root['info'] =$time['info'];
                $root['num'] =10;
            }else{
               //判断领取状态
                $data['user_id'] = intval($GLOBALS["user_info"]['id']);
                $sign=intval($_POST['sign']);// 题号 0 -7
                $answerPost=intval($_POST['answer']); //答案
                switch($sign){
                    case 0:
                        $question='任务一';
                        $answer='3';
                        $award="5元红包";
                        $type=1;
                        break;
                    case 1:
                        $question='任务二';
                        $answer='2';
                        $award="20元红包";
                        $type=1;
                        break;
                    case 2:
                        $question='任务三';
                        $answer='3';
                        $award="0.5%加息卡";
                        $type=2;
                        break;
                    case 3:
                        $question='任务四';
                        $answer='4';
                        $award="1%加息卡";
                        $type=2;
                        break;
                    case 4:
                        $question='任务五';
                        $award="金箔玫瑰花";
                        $type=0;
                        break;
                     case 5:
                        $question='任务六';
                        $award="玖财通空气净化器";
                        $type=0;
                        break;
                     case 6:
                        $question='任务七';
                        $award="7.5L车载冰箱";
                        $type=0;
                        break;
                }
                $isQixi = $GLOBALS['db']->getRow("select question from ".DB_PREFIX."send_qixi where user_id=".$data['user_id']." and question=".'"'.$question.'"');
                if(!empty($isQixi['question'])){
                     $root['status']=false;
                     $root['err'] =5;
                     $root['info'] ="您已经答过此题了";
                }
                if($answerPost!=$answer){
                        $root['status']=false;
                        $root['err'] =5;
                        $root['info'] ="分享可再来一次机会";
                        }
                    //判断是否满足条件
                $begin_time=strtotime("2017-08-28 0:0:0");
                $end_time=strtotime("2017-08-30 23:59:59");
                
                //计算任务五
                $id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user where id=".$data['user_id']." and cunguan_tag=1");
                //查询同一推荐人满足条件人数
                $invited_count = count($GLOBALS['db']->getAll("select u.id,sum(dl.money) as money from ".DB_PREFIX."user u inner join ".DB_PREFIX."deal_load dl on u.id=dl.user_id  where u.cunguan_tag=1 and u.pid =".$id." and u.create_time >".$begin_time." and u.create_time < ".$end_time." group by u.id"));
                if($sign == 4 && $invited_count < 2){
                    $root['status']=false;
                    $root['info']='不满足领取条件';
                    $root['err'] =4;
                    $root['num']=13;
                    ajax_return($root);
                }
                 //计算投资金额
                 //活动期间玖财通存管版出借普通标的10000元及以上的用户（折标后）
                 //活动期间玖财通存管版出借普通标的50000元及以上的用户（折标后）
                 $conditon = " d.is_effect=1 and d.is_delete=0 and d.publish_wait = 0 and dl.user_id = ".$id." and dl.create_time between  $begin_time and $end_time and d.cunguan_tag=1";
                 
                 $investInfo = $GLOBALS['db']->getAll("SELECT dl.money,d.repay_time FROM ".DB_PREFIX."deal_load dl LEFT JOIN ".DB_PREFIX."deal d ON d.id =dl.deal_id LEFT JOIN ".DB_PREFIX."user u ON u.id=dl.user_id where" .$conditon);
                 $zhebiao_money = 0;
                  foreach($investInfo as $k => $v){
                    $zhebiao_money += round($v['money'] * $v['repay_time']/12,2);
                 }
                if($sign == 5 && $zhebiao_money < 10000){
                    $root['status']=false;
                    $root['info']='不满足领取条件';
                    $root['err'] =4;
                    $root['num']=13;
                    ajax_return($root);
                }
                if($sign == 6 && $zhebiao_money < 50000){
                    $root['status']=false;
                    $root['err'] =4;
                    $root['info']='不满足领取条件';
                    $root['num']=13;
                    ajax_return($root);
                }
                //判断是否满足条件
               //发放奖励
               $res=$this->sendAward($data['user_id'],$sign,$answerPost);
                if($res){
                    $all = $GLOBALS['db']->getAll("select sign from ".DB_PREFIX."send_qixi where user_id=".$data['user_id']);
                    foreach($all as $k=>$v){
                        $number[]=$v['sign'];
                    }
                  $user_name =  substr($GLOBALS["user_info"]["user_name"],1,11);
                    $str="<li>
                            <p class='con2-p1'>".substr_replace($user_name,'***',3,4)."</p><p class='con2-p2'>".date('m月d日')."</p><p class='con2-p3'>".$question."</p><p class='con2-p4'>".$award."</p></li>";

                    $number=count($number);
                    $root['status']=true;
                    $root['number'] =$number;
                    $root['title'] = "恭喜您完成".$question;
                    $root['info'] ="获得".$award."！";
                    $root['str'] = $str;
                }
                $root['zhebiao_money']=$zhebiao_money;
                $root['invited_count'] =$invited_count;
                $root['user_id']=$data['user_id'];
            }
            
          }else{
            $root['status']=false;
            $root['info']='请登录！';
          }
            ajax_return($root);
    }
    public function sendAward($user_id,$question,$answer){
        $award=array(
            array('question'=>'任务一','answer'=>3,'type'=>1,'award'=>'5元红包','awardValue'=>5,'sign'=>0),
            array('question'=>'任务二','answer'=>2,'type'=>1,'award'=>'20元红包','awardValue'=>20,'sign'=>1),
            array('question'=>'任务三','answer'=>3,'type'=>2,'award'=>'0.5%加息卡','awardValue'=>0.5,'sign'=>2),
            array('question'=>'任务四','answer'=>4,'type'=>2,'award'=>'1%加息卡','awardValue'=>1,'sign'=>3),
            array('question'=>'任务五','answer'=>0,'type'=>0,'award'=>'金箔玫瑰花','awardValue'=>0,'num'=>77,'sign'=>4),
            array('question'=>'任务六','answer'=>0,'type'=>0,'award'=>'玖财通空气净化器','awardValue'=>0,'num'=>77,'sign'=>5),
            array('question'=>'任务七','answer'=>0,'type'=>0,'award'=>'7.5L车载冰箱','awardValue'=>0,'num'=>15,'sign'=>6)
        );
        if($award[$question]['sign'] >=4){
            $signNum=$award[$question]['sign']; //sign = 4;
            $awardNum = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."send_qixi where sign = ".$signNum);
            if($awardNum >= $award[$question]['num']){
                $data['status']=false;
                $data['err']=7;
                $data['info']='很遗憾！奖品已经领完';
                $awardInfo = $GLOBALS['db']->getOne("select type from ".DB_PREFIX."send_qixi where user_id =".$user_id." and type = 7 and sign =" .$award[$question]['sign']);
                if(empty($awardInfo)){
                //奖品领完入库
                $date['user_id']=$user_id;
                $date['user_name']=$GLOBALS["user_info"]['user_name'];
                $date['award']=$award[$question]['award']."奖品已领完";
                $date['question']=$award[$question]['question']."奖品已领完";
                $date['type']=7;
                $date['addtime']=time();
                $date['date']=date("Y-m-d",time());
                $date['sign']=$question;
                $res1 = $GLOBALS['db']->autoExecute(DB_PREFIX."send_qixi",$date,"INSERT");
                $load_id1 = $GLOBALS['db']->insert_id();
                }else{
                    $data['status']=false;
                }
                
                ajax_return($data);
            }
            
           
           }
            if($award[$question]['answer']==$answer){
            $row = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."send_qixi where user_id = ".$user_id);
            //
            //奖励判断
               $data['user_id']=$user_id;
               $data['user_name']=$GLOBALS["user_info"]['user_name'];
               $data['award']=$award[$question]['award'];
               $data['question']=$award[$question]['question'];
               $data['type']=$award[$question]['type'];
               $data['addtime']=time();
               $data['date']=date("Y-m-d",time());
               $data['sign']=$question;
               $res = $GLOBALS['db']->autoExecute(DB_PREFIX."send_qixi",$data,"INSERT");
               $load_id = $GLOBALS['db']->insert_id();
               
               $interest_count = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."send_qixi where user_id = ".$user_id." and sign=".$question);
               $interest_info = $GLOBALS['db']->getRow("select id,sign from ".DB_PREFIX."send_qixi where user_id =".$user_id." and sign=".$question ." order by id");
               //重复数据判断 
               
               if($interest_count >1){
                   $GLOBALS['db']->getRow("delete  from ".DB_PREFIX."send_qixi where user_id = ".$user_id." and id=".$interest_info['id']);
                   return false;
               }else{
                    if($award[$question]['type']==1){
                        if($award[$question]['question']=='任务一'){
                            $result=send_honbao(13,$user_id,"七夕活动红包",$award[$question]['awardValue']);
                        }else if($award[$question]['question']=='任务二'){
                            $result=send_honbao(13,$user_id,"七夕活动红包",$award[$question]['awardValue']);
                        }

                    }else{
                        if($award[$question]['question']=='任务三'){
                            $result=send_jiaxika(1,$award[$question]['awardValue'],$user_id,"七夕活动加息卡0.5%");
                        }else if($award[$question]['question']=='任务四'){
                            $result=send_jiaxika(1,$award[$question]['awardValue'],$user_id,"七夕活动加息卡1%");
                        }

                    }
                   
                 if($result && $load_id){
                      $data['status']=true;
                      $data['info']="恭喜你获得".$award[$question]['award'];
                   }else{
                      $data['status']=false;
                      $data['info']="系统繁忙，请稍后再试";
                   }
                      return $data;  
                  }
                }
    }
    
    public function checkinfo(){
       $key=intval($_POST['key']);// 题号 0 -6
        $data['user_id'] = intval($GLOBALS["user_info"]['id']);
        $time = timeDay(strtotime("2017-08-28 00:00:00"), strtotime("2017-08-30 23:59:59"));
        if($time['status']==false){ //活动时间
            $info['status']=false;
            $info['info']=0;
            $info['msg']='不在活动期间内';
            $info['num'] =10;
            ajax_return($info);
        }

        if($data['user_id'] ){
            $share_rs = is_share($data['user_id']);
            if(es_session::get("task") == 1 && !intval($share_rs['share_count'])){
                $info['status']=false;
                $info['info']= 2;
                $info['msg']='分享可再来一次机会';
                $info['num']=12;
                ajax_return($info);
            }
            if($key==0){
                $info['status']=true;
                $info['info']=1;
                $info['msg']='成功';
                ajax_return($info);  
            }else{
                $key = $key -1;
                $row = $GLOBALS['db']->getRow("select sign from ".DB_PREFIX."send_qixi where user_id=".$data['user_id']." and sign=".$key);
                if($row){
                    $info['status']=true;
                    $info['info']=1;
                    $info['msg']='成功';
                    ajax_return($info);
                }else{
                    $info['status']=false;
                    $info['info']=0;
                    $info['msg']='请按顺序答题';
                    ajax_return($info);
                }
            }
            
        }else{
            $info['status']=false;
            $info['info']=0;
            $info['msg']='请登录';
            $info['num']=9;
            ajax_return($info);
        }
        
    }
    /*
    public function checkinfo(){
        $key=intval($_POST['key']);// 题号 0 -6
        $data['user_id'] = intval($GLOBALS["user_info"]['id']);
        if($data['user_id'] ){
            if($key==0){
                $info['info']=true;
                $info['msg']='成功';
                ajax_return($info);
            }else{
                $row = $GLOBALS['db']->getRow("select sign from ".DB_PREFIX."send_qixi where user_id=".$data['user_id']." and sign=".$key-1);
                if($row){
                    $info['info']=true;
                    $info['msg']='成功';
                    ajax_return($info);
                }else{
                    $info['info']=false;
                    $info['msg']='请按顺序答题';
                    ajax_return($info);
                }
            }
    
        }
    
    }
    */
    
    //国庆活动补签
    public function buqian(){

        $user_id = $GLOBALS['user_info']['id'];
        if(!$user_id){
          
          $root['status']=0;
          $root['info']  ="请先登录";
        }

    $award=array(
    array('id'=>'29','prizename'=>'月模',  'time'=>'2017/09/29', 'date'=>'09月29日'),
    array('id'=>'30','prizename'=>'烤箱',  'time'=>'2017/09/30', 'date'=>'09月30日'),
    array('id'=>'1','prizename'=>'面粉',  'time'=>'2017/10/01', 'date'=>'10月01日'),
    array('id'=>'2','prizename'=>'鸡蛋',  'time'=>'2017/10/02', 'date'=>'10月02日'),
    array('id'=>'3','prizename'=>'植物油','time'=>'2017/10/03', 'date'=>'10月03日'),
    array('id'=>'4','prizename'=>'酒',    'time'=>'2017/10/04', 'date'=>'10月04日'),
    array('id'=>'5','prizename'=>'莲蓉',  'time'=>'2017/10/05', 'date'=>'10月05日'),
    array('id'=>'6','prizename'=>'鲜肉',  'time'=>'2017/10/06', 'date'=>'10月06日'),
    array('id'=>'7','prizename'=>'栗蓉',  'time'=>'2017/10/07', 'date'=>'10月07日'),
    array('id'=>'8','prizename'=>'五仁',  'time'=>'2017/10/08', 'date'=>'10月08日'),
    array('id'=>'9','prizename'=>'豆沙',  'time'=>'2017/10/09', 'date'=>'10月09日'),
  );

    
        $nowdate = date('Y/m/d',time());
        
        //补签插入的数组
        
        $signinfo = $GLOBALS['db']->getRow('select * from '.DB_PREFIX.'guoqing_sign where user_id='.$user_id);
        //求漏签的次数
        $alldays=array(0,1,2,3,4,5,6,7,8,9,10);
        $arr=unserialize($signinfo['day_sign']);//查询的结果
        $arr_louqian=array_slice($alldays,0,$arr[count($arr)-1]+1);//目标比较数组

        $res=array_diff($arr_louqian,$arr);//漏签的天数
        $forgetsign = count($res);//求漏签的天数
        if($signinfo&&$forgetsign>0&&$nowdate!=$signinfo['buqian_time']){

            //查询今日是否分享
            $shareinfo = $GLOBALS['db']->getRow('select * from '.DB_PREFIX.'user_share_log where user_id='.$user_id.' order by id desc limit 1');
            
            $sharetime = date('Y/m/d',$shareinfo['share_time']);
            if($nowdate == $sharetime){//说明今日已经分享 可补签

                $arr=unserialize($signinfo['day_sign']); //查询的结果 签到的数据

                //把第一次漏签的补上 备用
                $arr1=array(0,1,2,3,4,5,6,7,8,9,10);
                $arr3=array_slice($arr1,0,$arr[count($arr)-1]+1);//目标比较数组
                $louqian=array_diff($arr3,$arr);
                $louqian_day=current($louqian);//获取第一次漏签是哪一天
                array_push($arr,$louqian_day);
                sort($arr);
                $day_sign=serialize($arr);
                //补签完检查是否签满11天
                if(array_sum($arr)==55){
                 $updata['iscomplete']  =1;
                 $updata['completetime'] =time();
                }
                //跟新签到表
                //$forgetsign = $signinfo['forgetsign_days']-1;
                $updata['day_sign']  = $day_sign;
                $updata['buqian_time']  =date('Y/m/d',time());//跟新补签时间
                //$updata['forgetsign_days']  =$forgetsign;
                $updata['today_bqaward']  = $award[$louqian_day]['prizename'];//今天补签得到的奖品
                
                $GLOBALS['db']->autoExecute(DB_PREFIX.'guoqing_sign',$updata,'UPDATE','user_id='.$user_id);
                $root['status']=1;
                $root['info']  ="恭喜补签成功";

            }else{
                $root['status']=0;
                $root['info']  ="您先分享再补签";
            }
            
        }else{
            $root['status']=-1;
            $root['info']  ="您已经补签过了，稍后再试";
        }
        ajax_return($root);
        
    }

    //健康活动 步数兑换红包
    public function W650_receive(){
        $step =intval($_POST['step']);//兑换是哪一个步数类型的 1-step1 2-step2
        $user_id = $GLOBALS['user_info']['id'];
        if(!$user_id){
          
          $root['status']=0;
          $root['info']  ="请先登录";
          ajax_return($root);
        }
       $today = strtotime(date("Y-m-d",TIME_UTC));
              //查询今天领取情况 一天只有2次
       // $award = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."health_activity where user_id=".$user_id." and time>=".$today);
       $award = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."health_activity_new where (time>=".$today."  or  time2>=".$today." ) and user_id=".$user_id);
       if($step==1&&$award['step1']==1){//防止重复数据
            $root['status']=0;
            $root['ratio']  ='';
            $root['info']="您今天已经兑换过了";
            ajax_return($root);
       }elseif($step==2&&$award['step2']==1){//防止重复数据
          
            $root['status']=0;
            $root['ratio']  ='';
            $root['info']="您今天已经兑换过了";
            ajax_return($root);
        }
          if($step){
            $red_packet_type_id =rand(15,19);//正式环境需修改id 正式环境改为
            //$red_packet_type_id =rand(24,28);//正式环境需修改id 正式环境改为
            $red_packet_type = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."red_packet_newconfig where id = ".$red_packet_type_id);
            $awardValue = $red_packet_type['amount'];//红包值
            $ratio = $red_packet_type['ratio'];//红包满足出借金额
            $msg = '健康活动兑换红包';
            $result=send_red_packet($red_packet_type_id,$user_id,$msg);
            
            if($result)
            {
              
              //红包发放成功 跟新健康活动表
              
              
              if($award)
              {//说明今天已经兑换过
                if($step==1){
                    $updata['time']=TIME_UTC;
                    $updata['step1']  =1;
                    $GLOBALS['db']->autoExecute(DB_PREFIX.'health_activity_new',$updata,'UPDATE','id='.$award['id']);
                }else if($step==2){
                    $updata['time2']=TIME_UTC;
                    $updata['step2']  =1;
                    $GLOBALS['db']->autoExecute(DB_PREFIX.'health_activity_new',$updata,'UPDATE','id='.$award['id']);
                }
              }else{

                if($step==1){
                    $data['user_id'] = $user_id;
                    $data['step1']  =1;
                    $data['time'] = TIME_UTC;
                     
                    $GLOBALS['db']->autoExecute(DB_PREFIX."health_activity_new",$data,"INSERT");
                }else if($step==2){
                    $data['user_id'] = $user_id;
                    $data['step2']  =1;
                    $data['time2'] = TIME_UTC;
                     
                    $GLOBALS['db']->autoExecute(DB_PREFIX."health_activity_new",$data,"INSERT");
                }

              }
              $root['status']=1;
              $root['ratio']  ='（出借满 '.$ratio.'元可用）';
              $root['info']="恭喜您获得".$awardValue."元红包";
              ajax_return($root);
            }else{

              $root['status']=0;
              $root['ratio']  ='';
              $root['info']="兑换失败，请稍后再试";
              ajax_return($root);
            }            
        }
    
        

        $root['status']=0;
        $root['ratio']  ='';
        $root['info']  ="兑换失败，请稍后再试";
        ajax_return($root);

    }

    //积分抽积分转盘活动
    
    public function W651_receive(){
        
        //$user_id = mt_rand(1123430,1123450);
        $user_id = $GLOBALS['user_info']['id'];
        if(!$user_id){
          
          $root['status']=0;
          $root['info']  ="请先登录";
          ajax_return($root);
        }
        
        $nowdate =date('Ymd',TIME_UTC);
        //今日抽奖次数
       

        $num = $GLOBALS['db']->getOne("SELECT count(id) FROM ".DB_PREFIX."turntable_activity WHERE user_id ='".$user_id."' and type != 11 and create_time_ymd=".$nowdate);
		$num_red = $GLOBALS['db']->getOne("SELECT count(id) FROM ".DB_PREFIX."turntable_activity WHERE user_id =".$user_id."  and create_time_ymd='".$nowdate."'");
         if($num_red==4){
             insert_red_log($user_id,3);
          }
		if($num>4){
           $root['status']=0;
           $root['msg'] ="小主，您今天的抽奖次数已用完";
           ajax_return($root);
        }

        $prizeinfo=array(
           '1'=>array('id'=>1,'credits'=>10,'chance'=>'3','prizename'=>'5元出借红包'),
           '2'=>array('id'=>2,'credits'=>-1,'chance'=>'2','prizename'=>'6％加息券'),
           '3'=>array('id'=>2,'credits'=>0,'chance'=>'0.03','prizename'=>'10元话费'),
           '4'=>array('id'=>3,'credits'=>0,'chance'=>'0.02','prizename'=>'玖财通定制抱枕'),
           //'5'=>array('id'=>4,'credits'=>-1,'chance'=>'-1','prizename'=>'小米净化器pro'),
           '6'=>array('id'=>4,'credits'=>0,'chance'=>'60','prizename'=>'3积分'),
           '7'=>array('id'=>5,'credits'=>0,'chance'=>'29.95','prizename'=>'再来一次'),
           '8'=>array('id'=>6,'credits'=>0,'chance'=>'5','prizename'=>'45积分'),                  
        );

        

        foreach($prizeinfo as $k=>$v){
                $chancearr[$k] = $v['chance'];
        }
        

        $randPrize = $this->get_rand($chancearr);
        while (empty($randPrize)) {
            $randPrize = $this->get_rand($chancearr);
        }

        $GLOBALS['db']->startTrans();
        $score = $GLOBALS['db']->getOne("SELECT score FROM ".DB_PREFIX."user WHERE id =".$user_id."  FOR UPDATE");
        $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."user_score_log WHERE user_id =".$user_id."  FOR UPDATE");
        $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."turntable_activity WHERE user_id =".$user_id."  FOR UPDATE");
        
        if($score&&$score>=9){
            //添加user_score_log表记录 减去积分记录 -9记录
            
            if($randPrize !=7){ //再来一次不减积分
            $data['user_id'] = $user_id;
            $data['score'] = -9;
            $data['account_score'] = $score-9;
            $data['memo'] = '积分商城转盘活动';
            $data['type'] = 22;
            $data['create_time'] = TIME_UTC;
            $data['create_time_ymd'] = date('Ymd',time());
            $data['create_time_ym'] = date('Ymd',TIME_UTC);
            $data['create_time_y'] = date('Y',TIME_UTC);   
            $GLOBALS['db']->autoExecute(DB_PREFIX."user_score_log",$data,"INSERT");
            
            $insert_id = $GLOBALS['db']->insert_id();
            }
            if($insert_id || ($randPrize ==7)){

              if($randPrize==6||$randPrize==8){
                //添加user_score_log表记录 增加积分记录
                if($randPrize==6){//增加的积分
                    $addscore =3;
                }else{
                    $addscore = 45;
                }
                
                if($randPrize==6){//现在总积分
                    $score=$score-9+3;
                }else{
                    $score=$score-9+45;
                }

                $data['user_id'] = $user_id;
                $data['score'] = $addscore;
                $data['account_score'] = $score;
                $data['memo'] = '积分商城转盘活动';
                $data['type'] = -1;
                $data['create_time'] = TIME_UTC;
                $data['create_time_ymd'] = date('Ymd',time());
                $data['create_time_ym'] = date('Ymd',TIME_UTC);
                $data['create_time_y'] = date('Y',TIME_UTC);   
                $GLOBALS['db']->autoExecute(DB_PREFIX."user_score_log",$data,"INSERT");

                $insert_id2 = $GLOBALS['db']->insert_id();
                if($insert_id2){
                   //更新user的表
                 
                    $updata['score'] = $score;
                    $res= $GLOBALS['db']->autoExecute(DB_PREFIX.'user',$updata,'UPDATE','id='.$user_id); 
                      
                    if($res){

                        $insrtdata['user_id'] = $user_id;
                        $insrtdata['mobile'] = $GLOBALS['user_info']['mobile'];
                        $insrtdata['create_time'] = time();
                        $insrtdata['create_time_ymd']= date('Ymd',time());
                        $insrtdata['prizename'] = $prizeinfo[$randPrize]['prizename'];  
                        $GLOBALS['db']->autoExecute(DB_PREFIX."turntable_activity",$insrtdata,"INSERT"); 
                        $insert_id4 = $GLOBALS['db']->insert_id();
                        
                        if($insert_id4){

                          $ret = $GLOBALS['db']->getRow('select * from '.DB_PREFIX.'turntable_activity where user_id='.$user_id.' order by id desc limit 1');
                       
                          $count = $GLOBALS['db']->getOne('select count(id) from '.DB_PREFIX.'turntable_activity where user_id='.$user_id.' and create_time='.$ret['create_time']);
                       
                           if($count>1){
                             $GLOBALS['db']->rollback();
                             $root['status']=0;
                             $root['msg'] ="请稍候再试";
                             ajax_return($root);
                            }

                            $GLOBALS['db']->commit();
                            $root['status']=1;
                            $root['prizenum']=$randPrize-1;
                            $root['msg'] =$prizeinfo[$randPrize]['prizename'];
                            ajax_return($root);
                        }
                        
                    }
                }
              }else if($randPrize==7){//再来一次

                    $insrtdata['user_id'] = $user_id;
                    $insrtdata['mobile'] = $GLOBALS['user_info']['mobile'];
                    $insrtdata['create_time'] = time();
                    $insrtdata['type'] = 11; //标识再来一次字段
                    $insrtdata['create_time_ymd']= date('Ymd',time());
                    $insrtdata['prizename'] = $prizeinfo[$randPrize]['prizename'];  
                    $GLOBALS['db']->autoExecute(DB_PREFIX."turntable_activity",$insrtdata,"INSERT"); 
                    $insert_id4 = $GLOBALS['db']->insert_id();

                    if($insert_id4){

                          $ret = $GLOBALS['db']->getRow('select * from '.DB_PREFIX.'turntable_activity where user_id='.$user_id.' order by id desc limit 1');
                       
                          $count = $GLOBALS['db']->getOne('select count(id) from '.DB_PREFIX.'turntable_activity where user_id='.$user_id.' and create_time='.$ret['create_time']);
                       
                           if($count>1){
                             $GLOBALS['db']->rollback();
                             $root['status']=0;
                             $root['msg'] ="请稍候再试";
                             ajax_return($root);
                            }

                            $GLOBALS['db']->commit();
                            $root['status']=1;
                            $root['prizenum']=$randPrize-1;
                            $root['msg'] =$prizeinfo[$randPrize]['prizename'];
                            ajax_return($root);
                    }

              }else if($randPrize==1){ //5元红包
                    $red_packet_type_id = 35; //线上需改为25
                    $msg = '积分转盘活动';
                    $result=send_red_packet($red_packet_type_id,$user_id,$msg);
                    if($result){
                         $insrtdata['user_id'] = $user_id;
                         $insrtdata['mobile'] = $GLOBALS['user_info']['mobile'];
                         $insrtdata['create_time'] = TIME_UTC;
                         $insrtdata['create_time_ymd']= date('Ymd',TIME_UTC);
                         $insrtdata['prizename'] = $prizeinfo[$randPrize]['prizename']; 
                         $insrtdata['type'] = 1;
                         $GLOBALS['db']->autoExecute(DB_PREFIX."turntable_activity",$insrtdata,"INSERT"); 
                         $insert_id3 = $GLOBALS['db']->insert_id();

                         //跟新user 表score值
                         $updata['score'] = $score-9;
                         $res= $GLOBALS['db']->autoExecute(DB_PREFIX.'user',$updata,'UPDATE','id='.$user_id);
                         if($insert_id3){

                             $GLOBALS['db']->commit();
                             $root['status']=1;
                             $root['prizenum']=$randPrize-1;
                             $root['msg'] =$prizeinfo[$randPrize]['prizename'];
                             ajax_return($root); 
                         }
                    }

              }else if($randPrize==2){ //6%加息券
                $GLOBALS['db']->getAll("SELECT id FROM ".DB_PREFIX."interest_card WHERE user_id =".$user_id."  FOR UPDATE");
                 $result=send_jiaxika(19,6,$user_id,"积分转盘活动");//线上19改为2  加息额度6待确认

                 if($result){
                         $insrtdata['user_id'] = $user_id;
                         $insrtdata['mobile'] = $GLOBALS['user_info']['mobile'];
                         $insrtdata['create_time'] = TIME_UTC;
                         $insrtdata['create_time_ymd']= date('Ymd',TIME_UTC);
                         $insrtdata['prizename'] = $prizeinfo[$randPrize]['prizename']; 
                         $insrtdata['type'] = 1;
                         $GLOBALS['db']->autoExecute(DB_PREFIX."turntable_activity",$insrtdata,"INSERT"); 
                         $insert_id3 = $GLOBALS['db']->insert_id();

                         //跟新user 表score值
                         $updata['score'] = $score-9;
                         $res= $GLOBALS['db']->autoExecute(DB_PREFIX.'user',$updata,'UPDATE','id='.$user_id);
                         if($insert_id3){

                             $GLOBALS['db']->commit();
                             $root['status']=1;
                             $root['prizenum']=$randPrize-1;
                             $root['msg'] =$prizeinfo[$randPrize]['prizename'];
                             ajax_return($root); 
                         }
                 }
              }else if($randPrize==4 ||$randPrize==3){//中了实物
                 //如果中实物 则记录
                       
                 $insrtdata['user_id'] = $user_id;
                 $insrtdata['mobile'] = $GLOBALS['user_info']['mobile'];
                 $insrtdata['create_time'] = TIME_UTC;
                 $insrtdata['create_time_ymd']= date('Ymd',TIME_UTC);
                 $insrtdata['prizename'] = $prizeinfo[$randPrize]['prizename']; 
                 $insrtdata['type'] = 1;
                 $GLOBALS['db']->autoExecute(DB_PREFIX."turntable_activity",$insrtdata,"INSERT"); 
                 $insert_id3 = $GLOBALS['db']->insert_id();

                 //跟新user 表score值
                 $updata['score'] = $score-9;
                 $res= $GLOBALS['db']->autoExecute(DB_PREFIX.'user',$updata,'UPDATE','id='.$user_id);
                 if($insert_id3){

                     $GLOBALS['db']->commit();
                     $root['status']=1;
                     $root['prizenum']=$randPrize-1;
                     $root['msg'] =$prizeinfo[$randPrize]['prizename'];
                     ajax_return($root); 
                 }
                       

              }else{
                 
                 $GLOBALS['db']->rollback();
                 $root['status']=0;
                 $root['msg'] ="请稍候再试";
                 ajax_return($root);

              }
            }
            

            
        }
            $GLOBALS['db']->rollback();
            $root['status']=0;
            $root['msg'] ="请稍候再试";
            ajax_return($root);
        
    }

    public function get_rand($proArr) {
        $result = '';
         //概率数组的总概率精度
        $proSum = array_sum($proArr);   //100   
        //概率数组循环     
        foreach ($proArr as $key => $proCur) {       
           $randNum = $this->randomFloat(0, $proSum);      //1-100
           if ($randNum < $proCur) {          
                $result = $key;
                break;
           } else {
                $proSum -= $proCur;
           }
        }
        unset ($proArr);
        return $result;
   }

    public function randomFloat($min = 0, $max = 100)  
    {  
       $num = $min + mt_rand() / mt_getrandmax() * ($max - $min);  
       // if($num<1){
       //    $num=1;
       // }
       return sprintf("%.2f", $num);  

    }

   
    
}
?>