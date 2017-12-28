<?php

//发红包接口

class red_packet_friend_send{

    public function index(){

        $root['response_code'] = 0;
        $root['show_err'] = '第三方服务器升级，好友红包暂不可用。';
        output($root);
        $user = $GLOBALS['user_info'];
        $root = get_baseroot();
        $root['session_id'] = es_session::id();
        $today_time = strtotime(date('Y-m-d'));
        if($user['id']>0){
            $root['new_red_money'] = strval($user['new_red_money']);
            $root['info'] = "• 好友红包只有自己的好友可以抢；\r\n\r\n• 好友红包使用已抢到的红包进行派发。";
            $root['response_code'] = 1;
            output($root);
        }else{
            $root['response_code'] = 0;
            $root['user_login_status'] = 0;
            $root['show_err'] = '未登录';
            output($root);
        }
    }

}