<?php

//更换银行卡接口--右滑更换按钮

class change_bank{
    public function index(){
        $root = get_baseroot();
        $bank_card = strim(base64_decode($GLOBALS['request']['bank_card'])); //银行卡
        $user= $GLOBALS['user_info'];
        if($user['user_type']=='1'){
        	$root['response_code'] = 0;
        	$root['change_card_code'] = 0;//根据该字段判断显示哪种弹框
        	$root['change_card_url'] = '';
        	$root['tip_str'] = '企业用户暂不可使用';
        	output($root);
        }
        $root['session_id'] = es_session::id();
        if($user['id']>0){
            $root['user_login_status'] = 1;
            if(!$bank_card){
                $root['response_code'] = 0;
                $root['show_err'] = '银行卡不能为空';
                output($root);
            }
            $bankcard = $GLOBALS['db']->getOne("SELECT bankcard FROM ".DB_PREFIX."user_bank WHERE user_id=".$user['id']." AND bankcard=".$bank_card);
            if(!$bankcard){
                $root['response_code'] = 0;
                $root['show_err'] = '请先绑卡';
                output($root);
            }
            //是否有未处理的提现记录
            $cash_log = $GLOBALS['db']->getAll("SELECT money FROM ".DB_PREFIX."user_carry WHERE user_id=".$user['id']."AND cunguan_tag=1 and status = 0 OR status = 3");
            //查询账户余额是否为零
            $use_money = $user['cunguan_money'];
            //查询用户通过此卡充值的金额
            $recharge = $GLOBALS['db']->getOne("select SUM(money) from ".DB_PREFIX."payment_notice where user_id=".$user['id']." and cunguan_tag=1 and is_paid=1 and bank_id=".$bank_card);
            //查询用户通过此卡提现的金额
            $cash = $GLOBALS['db']->getOne("select SUM(money) from ".DB_PREFIX."user_carry where user_id=".$user['id']." and cunguan_tag=1 and status=1 and bankcard=".$bank_card);
            if($use_money>0 || !empty($cash_log) || $recharge > $cash){
                $root['response_code'] = 0;
                $root['change_card_code'] = 0;//根据该字段判断显示哪种弹框
                $root['change_card_url'] = '';
                $root['tip_str'] = "您暂时无法更换银行卡，可能有以下原因：\n\n1、您尚有提现申请未到账。\n\n2、您账户可用余额不为0。\n\n3、使用该卡充值的资金尚未完全提现。";
                //$root['show_err'] = '暂时无法更换银行卡';
                output($root);
            }

            $root['response_code'] = 1;
            $root['change_card_code'] = 1;//根据该字段判断显示哪种弹框
            $root['change_card_url'] = 'http://www.baidu.com';
            //$root['change_bank_url'] = WAP_SITE_DOMAIN.'/member.php?ctl=uc_depository_addbank&act=change_check_pwd';
            $root['change_bank_url'] = WAP_SITE_DOMAIN.'/member.php?ctl=uc_depository_addbank&act=bank_paypassword';
            //$root['tip_str'] ='确定要更换该银行卡吗？';
            $root['tip_str'] ='确定要解绑该银行卡吗？';
            $root['cash'] = $cash_log;
            //$root['show_err'] = '可以更换银行卡';
        }else{
            $root['response_code'] = 0;
            $root['user_login_status'] = 0;
            $root['show_err'] = '请先登录';
        }
        output($root);
    }
}