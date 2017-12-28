<?php


class red_packet_tips{

    public function index(){

        $red_packet_id = strim(base64_decode($GLOBALS['request']['red_packet_id']));
        $friend_id = strim(base64_decode($GLOBALS['request']['friend_id']));
        $root = get_baseroot();
        $user = $GLOBALS['user_info'];
        if($user['user_type']=='1'){
        	$root['response_code'] = 0;
        	$root['show_err'] = '企业用户暂不可使用';
        	output($root);
        }
        $today_time = strtotime(date('Y-m-d'));
        $root['session_id'] = es_session::id();
        if ($user['id'] > 0) {
            if($user['red_packet_status']==0){
                $root['response_code'] = 0;
                $root['show_err'] = '您的帐号异常，请联系客服';
                output($root);
            }
            //好友当天是不是发过红包
            $red_packet = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "red_packet_send where id=" . $red_packet_id . " and user_id=" . $friend_id);

            if (!$red_packet) {
                $root['response_code'] = 0;
                $root['show_err'] = '红包不存在';
                output($root);
            }
            if($red_packet['type'] == 1){
                //用户今天抢了多少个红包
                $rob_num = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."red_packet_rob where user_id=".$user['id']." and rob_time >=".$today_time." and rob_red_money>0 and type=1");
                if ($rob_num >= 10) {
                    $root['response_code'] = -1;
                    $root['show_err'] = '每天最多抢10个红包哦，明天再来抢吧！';
                    output($root);
                }
            }
            
            $root['response_code'] = 1;
            output($root);
            
        }else{
            $root['response_code'] = 0;
            $root['user_login_status'] = 0;
            $root['show_err'] = '请先登录';
            output($root);
        }
    }

}