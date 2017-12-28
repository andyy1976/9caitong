<?php

//使用红包接口

class red_packet_exchange{

    public function index(){
        $user = $GLOBALS['user_info'];
        $root = get_baseroot();
        $root['session_id'] = es_session::id();
        if(empty($user['id'])){
            $root['response_code'] = 0;
            $root['user_login_status'] = 0;
            $root['show_err'] = '未登录';
            output($root);
        }
        if($user ['user_type'] == '1') {
            $root ['show_err'] = '企业用户暂不可用';
            $root ['response_code'] = 0;
            output($root);
        }
//         if($user['red_packet_status']==0){
//             $root['response_code'] = 0;
//             $root['show_err'] = '您涉嫌违规操作，暂停抢红包功能';
//             output($root);
//         }
//         if($user['is_effect'] != 1){
//             $root['response_code'] = 0;
//             $root['show_err'] = '您的帐号异常，请联系客服';
//             output($root);
//         }
        //判断是否开通存管
        if(!$user['cunguan_tag']){
            $root['response_code'] = 0;
            $root['show_err'] = "请您先开通银行存管账户";
            $root['jump_url'] = WAP_SITE_DOMAIN.'/member.php?ctl=uc_depository_account&act=index';
            output($root);
        }
        //判断是否设置存管交易密码
        if(!$user['cunguan_pwd']){
            $root['response_code'] = 0;
            $root['show_err'] = "请您先设置存管系统交易密码";
            $root['jump_url'] = WAP_SITE_DOMAIN.'/member.php?ctl=uc_depository_paypassword&act=setpaypassword';
            output($root);
        }
        //判断是否绑卡
        $user_bank = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."user_bank where user_id=".intval($user['id'])." AND cunguan_tag=1");
        if(empty($user_bank)){
            $root['response_code'] = 0;
            $root['show_err'] = "请您先绑定存管系统的银行卡";
            $root['jump_url'] = WAP_SITE_DOMAIN.'/member.php?ctl=uc_depository_addbank&act=wap_check_pwd';
            output($root);
        }
        if((date('w') == 6) || (date('w') == 0)){
            $root['response_code'] = 0;
            $root['show_err'] = '兑换时间暂时调整为周一至周五10:00-21:00，请谅解。';
            output($root);
        }
        
        $a = strtotime(date("Y-m-d")." 10:00:00");
        $time = time();
        if($time < $a){
            $root['response_code'] = 0;
            $root['show_err'] = '兑换时间暂时调整为周一至周五10:00-21:00，请谅解。';
            output($root);
        }
        $b = strtotime(date("Y-m-d")." 21:00:00");
        $c = strtotime(date("Y-m-d")." 23:59:59");
        if($time > $b && $time < $c){
            $root['response_code'] = 0;
            $root['show_err'] = '兑换时间暂时调整为周一至周五10:00-21:00，请谅解。';
            output($root);
        }
        
        //账户红包总金额
        $GLOBALS['db']->startTrans();
        $user_info = $GLOBALS['db']->getRow("select new_red_money,is_effect,red_packet_status from ".DB_PREFIX."user where id=".$user['id']." FOR UPDATE");
        if($user_info['red_packet_status'] == 0){
            $root['response_code'] = 0;
            $root['show_err'] = '您涉嫌违规操作，暂停抢红包功能';
            output($root);
        }
        if($user_info['is_effect'] != 1){
            $root['response_code'] = 0;
            $root['show_err'] = '您的帐号异常，请联系客服';
            output($root);
        }
        if(empty($user_info['new_red_money']) || $user_info['new_red_money'] == 0){
            $root['response_code'] = 0;
            $root['show_err'] = '您的红包余额为0，快去抢红包吧';
            output($root);
        }
        //最小兑换金额 需要读取后台配置
        $red_money = $user_info['new_red_money'];
        $min_money = $GLOBALS['db']->getOne("select min_money from ".DB_PREFIX."red_packet_config order by id desc limit 1");
        if($red_money < $min_money){
            $root['response_code'] = 0;
            $root['show_err'] = '红包满'.$min_money.'元才可兑换';
            output($root);
        }
        
        $config = $GLOBALS['db']->getRow("select * from ". DB_PREFIX ."red_packet_newconfig where id=12");
        $sn = unpack('H12',str_shuffle(md5(uniqid())));
        $data['sn'] = $sn[1];
        $data['use_limit'] = $config['use_limit'];
        $data['user_id'] = $user['id'];
        $data['begin_time'] = time();
        $data['end_time'] = strtotime(date('Y-m-d 23:59:59',strtotime('+'.($config['use_limit']-1).' day')));
        $data['money'] = $red_money;
        $data['red_type_id'] = $config['id'];
        $data['activity_id'] = 8; //对应jctp2p_app_activity_cg表ID
        $data['status'] = 0;
        $data['content'] = $config['red_name'];
        $data['packet_type'] =$config['red_type'];
        $data['create_time'] = time();
        $data['publish_wait'] = 0;
        $rs = $GLOBALS['db']->autoExecute(DB_PREFIX."red_packet",$data);
        $rs = $GLOBALS['db']->insert_id();
        //更改红包金额为0
        $rs_red_money = $GLOBALS['db']->query("update ".DB_PREFIX."user set new_red_money=0.00 where id=".$user['id']);
        
        $red_data_log['user_id'] = $user['id'];
        $red_data_log['red_money'] = -$red_money;
        $red_data_log['new_red_money'] = 0;
        $red_data_log['addtime'] = date('Y-m-d H:i:s');
        $red_data_log['remark'] = '兑换红包';
        $red_data_log['type'] = 0;
        $red_data_log['action'] = 4;
        $resl = $GLOBALS['db']->autoExecute(DB_PREFIX."red_packet_log",$red_data_log,"INSERT");
        
        if($rs && $rs_red_money){
            $GLOBALS['db']->commit();
            $root['response_code'] = 1;
            $root['show_err'] = "现金红包兑换成功，可在账户余额查看！";
        }else{
            $GLOBALS['db']->rollback();
            $root['response_code'] = 0;
            $root['show_err'] = "兑换失败";
        }
        output($root);
        
    }

}