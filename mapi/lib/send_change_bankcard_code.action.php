<?php

//更换发送短信验证码接口

class send_change_bankcard_code{

    public function index(){
        $root = get_baseroot();
        $user = $GLOBALS['user_info'];
        $root['session_id'] = es_session::id();
        $bank = strim(base64_decode($GLOBALS['request']['bank'])); //银行名称
        $bank_card = strim(base64_decode($GLOBALS['request']['bank_card']));//银行卡号
        $mobile = strim(base64_decode($GLOBALS['request']['mobile'])); //银行预留手机号
        $verify = strim(base64_decode($GLOBALS['request']['verify'])); //图形验证码
        if($user['id']>0){
            if(empty($bank) || empty($bank_card) || empty($mobile)){
                $root['response_code'] = 0;
                $root['show_err'] = '参数不能为空';
                output($root);
            }
            $search ='/^(1(([38][0-9])|[7][356780]|[4][57]|[5][012356789]))\d{8}$/';
            if(!preg_match($search,$mobile)) {
                $root['response_code'] = 0;
                $root['show_err'] = "您输入的手机号有误";//您输入的手机号有误
                output($root);
            }
            $userbank = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_bank where bankcard='".$bank_card."'");
            if($userbank){
                $root['response_code'] = 0;
                $root['show_err'] = '银行卡已被绑定';
                output($root);
            }

            //获取短信验证码发送的次数  如果大于三次显示图形验证码
            $send_code_num = es_session::get('send_changepwd_code_num');
            if(!$verify) {
                if ($send_code_num >= 2) {
                    $root['img_verify'] = 1;
                    $root['response_code'] = 0;
                    $root['show_err'] = "获取次数过多，请输入图文验证码";//请输入你的手机号
                    output($root);
                }
            }
            if(!$verify){
                //限制用户一分钟内多次获取短信验证码
                if(!check_ipop_limit(CLIENT_IP,"mobile_verify",60,0))
                {
                    $root['response_code'] = 0;
                    $root['show_err'] = $GLOBALS['lang']['MOBILE_SMS_SEND_FAST']; //短信发送太快
                    output($root);
                }
            }

            //短信验证码错误三次以后,验证图形验证码
            if($verify){
                if(!checkVeifyCode($verify))
                {
                    $root['img_verify'] = 0;
                    $root['response_code'] = 0;
                    $root['show_err'] = "图形验证码有误";
                    output($root);
                }
            }

            $begin_time = to_timespan(to_date(TIME_UTC,"Y-m-d"));
            //查询当天用户是不是获取手机验证码超过100次
            if($GLOBALS['db']->getOne("SELECT send_count FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$mobile."'  AND  create_time between ".$begin_time." and  ".($begin_time+24*3600)."") >= 100){
                $root['response_code'] = 0;
                $root['show_err'] = "你今天已经不能再发验证码了";
                output($root);
            }

            //开始生成手机验证
            $verify_data['verify_code'] = rand(111111,999999);
            $verify_data['mobile'] = $mobile;
            $verify_data['create_time'] = TIME_UTC;
            $verify_data['client_ip'] = CLIENT_IP;
            $verify_data['send_count'] = 1;

            if($info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$mobile."'")){
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

            //使用立即发送方式
            $result = send_verify_sms($mobile,$verify_data['verify_code'],null,true);//
            $root['response_code'] = $result['status'];
            if ($root['response_code'] == 1){
                $root['show_err'] = "验证码发送成功";
                //验证码发送成功后记录发送次数
                $send_reset_pwd_code_num =1 + es_session::get('send_changepwd_code_num');
                es_session::set('send_changepwd_code_num',$send_reset_pwd_code_num);

            }else{
                $root['response_code'] = 0;
                $root['show_err'] = $result['msg'];
                if ($root['show_err'] == null || $root['show_err'] == ''){
                    $root['show_err'] = "验证码发送失败";
                }
            }
            output($root);
        }else{
            $root['response_code'] = 0;
            $root['show_err'] = '请先登录';
            output($root);
        }

    }



}
?>