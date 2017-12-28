<?php

//更换银行卡接口--提交页面

class change_bankcard{

    public function index(){
        $root = get_baseroot();
        $user = $GLOBALS['user_info'];
        $root['session_id'] = es_session::id();
        $bank = strim(base64_decode($GLOBALS['request']['bank'])); //银行名称
        $bank_card = strim(base64_decode($GLOBALS['request']['bank_card']));//银行卡号
        $mobile = strim(base64_decode($GLOBALS['request']['mobile'])); //银行预留手机号
        $sms_code = strim(base64_decode($GLOBALS['request']['sms_code'])); //短信验证码
        
        if($user['id']>0){
            if(empty($sms_code) || empty($bank_card) || empty($mobile)){
                $root['response_code'] = 0;
                $root['show_err'] = '参数不能为空';
                output($root);
            }
            if(empty($bank)){
                $root['response_code'] = 0;
                $root['show_err'] = '请再次输入银行卡号，并重试';
                output($root);
            }
            $bank_infos = $GLOBALS['db']->getOne("SELECT id FROM  ".DB_PREFIX."bank WHERE Banklist = '$bank'");
            if(!$bank_infos){
                $root['response_code'] = 0;
                $root['show_err'] = "暂不支持此银行";
                output($root);
            }
            $userbank = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_bank where bankcard='".$bank_card."'");
            if($userbank){
                $root['response_code'] = 0;
                $root['show_err'] = '银行卡已被绑定';
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
            $result=$verifyFun->zXBank($sid, $user['real_name'], $user['idno'], $vtype, $mobile, $bank_card, $cpserialnum, $despwd,$md5key);
            $array = json_decode($result,1);


            switch ($array['result']) {
                case 'BANKCONSISTENT':
                    $res = $this->saveCard($bank,$user['real_name'],$bank_card,$mobile,$result['cpserialnum'],$result['sysserialnum'],$result['md5num']);
                    if($res){
                        if($GLOBALS['user_info']['paypassword']){
                            $root['three_go_code'] = 3;
                        }else{
                            $root['three_go_code'] = 2;
                        }
                        $root['response_code'] = 1;
                        $root['show_err'] = "银行卡更改成功";
                        es_session::delete('send_changepwd_code_num');
                        output($root);
                    }else{
                        $root['response_code'] = 0;
                        $root['show_err'] = "暂不支持此银行";
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
        return $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
    }

    public function saveCard($bank,$name,$bankCard,$phone,$cpserialnum,$sysserialnum,$md5num){
        $bank_info = $GLOBALS['db']->getOne("SELECT id FROM  ".DB_PREFIX."bank WHERE Banklist = '$bank'");
        if($bank_info){
            $user_info['real_name'] = $name;
            $user_info['bankcard'] = $bankCard;
            $user_info['bank_id'] = $bank_info;
            $user_info['bank_mobile'] = $phone;
            $user_info['create_time'] = TIME_UTC;
            $user_info['region_lv1'] =0;
            $user_info['region_lv2'] =0;
            $user_info['region_lv3'] =0;
            $user_info['region_lv4'] =0;
            $user_info['bankzone'] ='';
            $user_info['status'] =1;
            return $GLOBALS['db']->autoExecute(DB_PREFIX."user_bank",$user_info,"UPDATE","user_id=".$GLOBALS['user_info']['id']);
        }else{
            return false;
        }
        

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
    
    
}