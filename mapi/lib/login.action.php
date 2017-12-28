<?php
class login{
	public function index()
	{
        require_once APP_ROOT_PATH."system/user_level/Level.php";
        $level=new Level();
        $switch = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."switch_conf where switch_id = 1 or switch_id = 3");
        foreach ($switch as $k=>$v){
            if($v['status']!=1){
                $root['response_code'] = 0;
                $root['show_err'] = '系统正在升级，请稍后再试';
                output($root);
            }
        }
		$root= get_baseroot();
		$email = strim(base64_decode($GLOBALS['request']['email']));//用户名或邮箱
		$pwd = strim(base64_decode($GLOBALS['request']['pwd']));//密码
		$verify = strim(base64_decode($GLOBALS['request']['verify']));//密码
		$wx_openid = strim(base64_decode($GLOBALS['request']['wx_openid']));	//open_id 微信	
		$result = user_login($email,$pwd);
		$count = es_session::get("check_login_count");
		$info = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."user WHERE mobile ='".$email."'");
		if(!$info){
			$root['response_code'] = 0;
			$root['show_err'] = "用户名或密码错误";
			output($root);
		}
		if(!$verify){
			if($count > 2){
				$root['response_code'] = 0;
				$root['img_code'] = 1;
				$root['show_err'] = "错误次数过多,请输入图形验证码";
				$root['user_name'] = $email;
				$root['user_email'] = $email;
				output($root);
			}
		}		
		if($result['status'])
		{
			if($verify){
				if(!checkVeifyCode($verify))
				{
				    $root['verify'] = $verify;
					$root['response_code'] = 0;
					$root['show_err'] = "图形验证码有误";
					output($root);		
				}
			}
			$usinfos = $GLOBALS['db']->getRow("select AES_DECRYPT(u.real_name_encrypt,'".AES_DECRYPT_KEY."') as realname,AES_DECRYPT(u.idno_encrypt,'".AES_DECRYPT_KEY."') as idno,u.header_url,u.paypassword,b.bankcard from ".DB_PREFIX."user u left join ".DB_PREFIX."user_bank b on u.id = b.user_id where u.id= ".$GLOBALS['user_info']['id']);
			if(empty($usinfos['realname']) || empty($usinfos['idno'])||empty($usinfos['bankcard'])){
				$root['three_go_code'] = 1;
			}else{
				if(empty($usinfos['paypassword'])){
					$root['three_go_code'] = 2;
				}else{
					$root['three_go_code'] = 3;
				}
			}

			$user_data = $GLOBALS['user_info'];//$result['user'];
			//open_id处理
			if($wx_openid){
				$header_url = $GLOBALS['db']->getOne("select header_url FROM ".DB_PREFIX."user where wx_openid='".$wx_openid."'");
				if($header_url == ""){
					$root['header_code'] = 0; //用户头像不存在
				}else{
					$root['header_code'] = 1;
				}
				$GLOBALS['db']->query("update ".DB_PREFIX."user set wx_openid='".$wx_openid."' where id=".$user_data['id']);
                /************绑定后微信模板消息开始*********************/
                if(app_conf('WEIXIN_TMPL')){
                    $tmpl_url = app_conf('WEIXIN_TMPL_URL');
                    $tmpl_datas = array();
                    $tmpl_datas['first'] = '恭喜您的玖财通账户与微信绑定成功！';
                    $tmpl_datas['keyword1'] = $email;
                    $tmpl_datas['keyword2'] = date('Y-m-d H:i:s');
                    //$tmpl_datas['time'] = date('Y-m-d H:i:s');
                    $tmpl_datas['remark'] = '您将可以享受如下服务：微信官网免密登录、账户信息及时提醒、用微信快捷登录APP等众多服务。';
                    $tmpl_data = create_request_data('4',$wx_openid,app_conf('WEIXIN_JUMP_URL'),$tmpl_datas);
                    $resl = request_curl($tmpl_url,$tmpl_data);
                    $root['wx_msg'] = $resl;
                    $tmpl_msg['dest'] = $wx_openid;
                    $tmpl_msg['send_type'] = 4;
                    $tmpl_msg['content'] = serialize($tmpl_datas);
                    $tmpl_msg['send_time'] = time();
                    $tmpl_msg['create_time'] = time();
                    $tmpl_msg['user_id'] = $user_data['id'];
                    $tmpl_msg['title'] = '绑定成功';
                    if($resl===true){
                        $GLOBALS['db']->query("update ".DB_PREFIX."user set wx_openid_status=1 where id=".$user_data['id']);
                        $tmpl_msg['is_send'] = 1;
                        $tmpl_msg['result'] = '发送成功';
                        $tmpl_msg['is_success'] = 1;
                    }else{
                        $tmpl_msg['is_send'] = 0;
                        $tmpl_msg['result'] = $resl['message'];
                        $tmpl_msg['is_success'] = 0;
                    }
                    $GLOBALS['db']->autoExecute(DB_PREFIX."weixin_msg_list",$tmpl_msg,'INSERT','','SILENT');

                }
                /************绑定成功后微信模板消息结束*********************/

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
			$root['response_code'] = 1;
			$root['user_login_status'] = 1;//用户登陆状态：1:成功登陆;0：未成功登陆
			$root['show_err'] = "用户登录成功";		
			$root['id'] = $user_data['id'];
			$root['is_company']=$user_data['user_type'];
			$root['user_name'] = $user_data['mobile'];
			$root['mobile'] = $user_data['mobile'];
			$root['gesture_cipher'] = ($user_data['gesture_cipher']==null) ? "" : $user_data['gesture_cipher'];
			$root['user_pwd'] = $user_data['user_pwd'];
			$edition = $GLOBALS['db']->getOne("select edition from ".DB_PREFIX."user where id = ".$user_data['id']);
			if($edition == 0){
				$root['edition'] = "0";
			}else{
				$root['edition'] = "1";
			}

			//session_id存入数据库
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
			$root['user_money'] = number_format($user_data['money'],2);
			$root['user_money_format'] = number_format($user_data['money'],2);//用户金额	
			$root['total_money'] = number_format($user_data['money'],2);  //总金额  		
			$root['yesterday_invert'] = number_format($user_data['money'],2);  //昨日金额
			$root['cum_money'] = number_format($user_data['money'],2); //累计收益
			
			$user = $GLOBALS['db']->getRow("SELECT user_type,AES_DECRYPT(mobile_encrypt,'".AES_DECRYPT_KEY."') as mobile,AES_DECRYPT(idno_encrypt,'".AES_DECRYPT_KEY."') as idno,AES_DECRYPT(real_name_encrypt,'".AES_DECRYPT_KEY."') as real_name FROM ".DB_PREFIX."user WHERE id=".$user_data['id']);
			if($user['idno'] == ""){
				$root['real_name'] = "";
				$root['idno'] = "";
			}else{
				//$root['real_name'] = utf_substr($user['real_name']); //真实姓名带***
				$root['real_name'] = $user['real_name'];
				//$root['idno'] = hideIdCard($user['idno']);身份证号码带****
				$root['idno'] = $user['idno'];
			}
			
		}
		else
		{			
			$root['response_code'] = 0;
			$root['user_login_status'] = 0;//用户登陆状态：1:成功登陆;0：未成功登陆
			$root['show_err'] = $result['msg'];
			$root['id'] = 0;
			$root['user_name'] = $email;
			$root['user_email'] = $email;					
		}
		if($user['user_type'] == 1){
			$root['real_name'] = '';
			$root['idno'] = '*****************************';
		}
		$root['program_title'] = "登录";
		
		output($root);		
	}
}
?>