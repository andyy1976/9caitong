<?php

//三步走接口第一步---实名,银行卡

class three_go_one{
    
    public function index(){
        $root = get_baseroot();
        $user = $GLOBALS['user_info'];
        $root['session_id'] = es_session::id();
        $real_name = strim(base64_decode($GLOBALS['request']['real_name'])); //真实姓名
        $idno = strim(base64_decode($GLOBALS['request']['idno'])); //身份证号码
        $bank = strim(base64_decode($GLOBALS['request']['bank'])); //银行名称
        $bank_card = strim(base64_decode($GLOBALS['request']['bank_card']));//银行卡号
        $mobile = strim(base64_decode($GLOBALS['request']['mobile'])); //银行预留手机号
        $sms_code = strim(base64_decode($GLOBALS['request']['sms_code'])); //短信验证码

        if($user['id']>0){
            if(empty($real_name) || empty($idno) || empty($sms_code) || empty($bank_card) || empty($mobile)){
                $root['response_code'] = 0;
                $root['show_err'] = '参数不能为空';
                output($root);
            }
            if(empty($bank)){
                $root['response_code'] = 0;
                $root['show_err'] = '请再次输入银行卡号，并重试';
                output($root);
            }
            $bank_infos = $GLOBALS['db']->getOne("SELECT id FROM  ".DB_PREFIX."bank WHERE Banklist = '".$bank."'");
            if(!$bank_infos){
                $root['response_code'] = 0;
                $root['show_err'] = "暂不支持此银行";
                output($root);
            }
            if(!$user['real_name'] && !$user['idno']){
                $user_data = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where idno_encrypt = AES_ENCRYPT('".$idno."','".AES_DECRYPT_KEY."') and is_delete = 0");
                if($user_data>0){
                    $root['response_code'] = 0;
                    $root['show_err'] = '该身份信息已被占用';
                    output($root);
                }
            }else{
                if($real_name != $user['real_name'] && $idno != $user['idno']){
                    $root['response_code'] = 0;
                    $root['show_err'] = '1.0用户';
                    output($root);
                }
            }

            $userbank = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_bank where bankcard='".$bank_card."'");
            $usbank = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_bank where user_id=".$user['id']." and status=1");
            if($userbank){
                $root['response_code'] = 0;
                $root['show_err'] = '银行卡已被绑定';
                output($root);
            }
            if($usbank){
                $root['response_code'] = 0;
                $root['show_err'] = '此账户已绑过银行卡';
                output($root);
            }
            //判断验证码是过期
            if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$mobile."' AND verify_code='".$sms_code."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ")==0){
                $root['response_code'] = 0;
                $root['show_err'] = "手机验证码出错,或已过期.";
                output($root);
            }
            require APP_ROOT_PATH."system/utils/Verify.php";
            require APP_ROOT_PATH."system/utils/BinkCard/Imagebase64.php";
            require APP_ROOT_PATH."system/utils/bankList.php";
            $url = "http://verifyapi.huiyuenet.com/zxbank/verifyApi.do";
            $sid = "jxdbc";
            $cpserialnum = $this->orderId();
            $md5key = "l46g6i";
            $despwd = "9cwcweunozhw15ul6elezl5y";
            $vtype = "03";
            $verifyFun = new VerifyFun($url);
            /*--------------------实名验证DEMO----------------------------------*/
            $result=$verifyFun->zXBank($sid, $real_name, $idno, $vtype, $mobile, $bank_card, $cpserialnum, $despwd,$md5key);
            $array = json_decode($result,1);

            switch ($array['result']) {
                case 'BANKCONSISTENT':
                    $resl = $this->addCard($idno,$bank,$real_name,$bank_card,$mobile,$result['cpserialnum'],$result['sysserialnum'],$result['md5num']);
                    if($resl['status']==1){
                        if($GLOBALS['user_info']['paypassword']){
                            $root['three_go_code'] = 3;
                        }else{
                            $root['three_go_code'] = 2;
                        }
                        $root['response_code'] = 1;
                        //发送邀请代金券
                        $ecv_list = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."ecv_type WHERE (begin_time= 0 or begin_time < ".TIME_UTC.") AND (end_time= 0 or end_time +24*3600-1 > ".TIME_UTC.") AND  send_type=4 ");
                        if($this->is_get_rewards($GLOBALS['user_info']['id']) && $ecv_list){
                            /*send_voucher($ecv_list['id'],$GLOBALS['user_info']['pid'],false,$ecv_list['money'],$GLOBALS['user_info']['id']);*/
                            $order_data['begin_time'] = TIME_UTC;
                            $order_data['end_time'] = to_timespan(to_date($order_data['begin_time'])." ".app_conf("INTERESTRATE_TIME")." month -1 day");
                            $order_data['money'] = 20;
                            $order_data['ecv_type_id'] = 5;
                            $sn = unpack('H12',str_shuffle(md5(uniqid())));
                            $order_data['sn'] = $sn[1];
                            //$order_data['sn'] = rand(100000000000,999999999999);
                            $order_data['password'] = rand(10000000,99999999);
                            $order_data['user_id']=$GLOBALS['user_info']['pid'];
                            $order_data['child_id']=$GLOBALS['user_info']['id'];
                            $result=$GLOBALS['db']->autoExecute(DB_PREFIX."ecv",$order_data,"INSERT");
                            if($result==false){
                                $root['response_code'] = 0;
                                $root['show_err'] = "代金券发放失败";
                                output($root);
                            }
                        }
                        $root['show_err'] = "银行卡绑定成功";
                        es_session::delete('send_changepwd_code_num');
                        output($root);
                    }else{
                        $root['response_code'] = 0;
                        $root['show_err'] = $resl['msg'];
                        output($root);
                    }
                    break;
                case 'BANKNOLIB':
                    $root['response_code'] = 0;
                    $root['show_err'] = "没有此银行卡信息";
                    output($root);
                    break;
                case 'BANKINCONSISTENT':
                    $root['response_code'] = 0;
                    $root['show_err'] = "银行卡信息不一致";
                    output($root);
                    break;
                case 'BANKUNKNOWN':
                    $root['response_code'] = 0;
                    $root['show_err'] = "银行卡信息未知";
                    output($root);
                    break;
                case 'FAIL':
                    $info = $this->fail($array['errmsg']);
                    $root['response_code'] = 0;
                    $root['show_err'] = $info;
                    output($root);
                    break;
                default:
                    break;
            }
        }else{
            $root['response_code'] = 0;
            $root['show_err'] = '请先登录';
            output($root);
        }
        
    }


    public function orderId(){
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $orderSn = $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
        return $orderSn;

    }

    //添加银行卡
    public function addCard($idnum,$bank,$name,$bankCard,$phone,$cpserialnum,$sysserialnum,$md5num){
        $bank_info = $GLOBALS['db']->getOne("SELECT id FROM  ".DB_PREFIX."bank WHERE Banklist = '".$bank."'");
        if($bank_info>0){
            $user_info['user_id'] = $GLOBALS['user_info']['id'];
            $user_info['real_name'] = $name;
            $user_info['bankcard'] = $bankCard;
            $user_info['bank_id'] = $bank_info;
            $user_info['bank_mobile'] = $phone;
            $user_info['create_time'] = TIME_UTC;
            $user_info['status'] = 1;
            /*$user_info['cpserialnum'] = $cpserialnum;
            $user_info['sysserialnum'] = $sysserialnum;
            $user_info['md5num'] = $md5num;*/
            $res = $GLOBALS['db']->getOne("SELECT * FROM  ".DB_PREFIX."user_bank WHERE user_id=".$GLOBALS['user_info']['id']." and status=1");
            if(!$res){
                $result = $GLOBALS['db']->autoExecute(DB_PREFIX."user_bank",$user_info);
                if($result){
                    if(!$GLOBALS['user_info']['real_name'] && !$GLOBALS['user_info']['idno']){

                        $GLOBALS['user_info']['real_name'] = $name;
                        $GLOBALS['user_info']['idno'] = $idnum;
                        $data['real_name'] = $name;
                        $data['idno'] = $idnum;
                        $data['idcardpassed']=1;
                        $data['idcardpassed_time'] = TIME_UTC;
                        $data["real_name_encrypt"] = " AES_ENCRYPT('".$name."','".AES_DECRYPT_KEY."') ";
                        $data["idno_encrypt"] = " AES_ENCRYPT('".$idnum."','".AES_DECRYPT_KEY."') ";
                        $GLOBALS['db']->autoExecute(DB_PREFIX."user",$data,"UPDATE","id=".$GLOBALS['user_info']['id']);
                    }
					//addsource(0,0,$GLOBALS['user_info']['id'],2,1); 
                    $reslu['status']=1;
                    $reslu['msg'] = '绑定成功';
                }else{
                    $reslu['status']=0;
                    $reslu['msg'] = '绑定失败';
                }
            }else{
                $reslu['status']=0;
                $reslu['msg'] = '已绑过银行卡';
            }
        }else{
            $reslu['status']=0;
            $reslu['msg'] = '不支持该银行';
        }
        return $reslu;
    }

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
    public function is_get_rewards($user_id){
        $user_bank_sql="SELECT u.idcardpassed,u.pid,u.create_time as r_create_time,ub.create_time,u.referer FROM ".DB_PREFIX."user as u LEFT JOIN ".DB_PREFIX."user_bank as ub on ub.user_id=u.id where ub.status =1 and u.id=".$user_id;
        $user_bank=$GLOBALS['db']->getRow($user_bank_sql);
        return $user_bank;
        // var_dump($user_bank);
        // 是否存在邀请人
        if($user_bank['pid']==0){
            return false;
        }
        //是否绑定银行卡/实名认证
        if($user_bank['idcardpassed']==0){
            return false;
        }
        // 绑卡时间与注册时间是否超过30天
        $time_out=ceil(($user_bank['create_time']-$user_bank['r_create_time'])/3600/24);
        if($time_out>30||$time_out<0){
            return false;
        }
        //奖励次数不超过50
        $get_rewards_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."ecv where user_id = ".$GLOBALS['user_info']['id']." and ecv_type_id = 5");
        if($get_rewards_count > 50){
            return false;
        }
        return true;
    }
    
    
}