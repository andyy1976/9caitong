<?php

//抢红包邀请好友接口

class red_packet_addfriend{

    public function index(){
        $user = $GLOBALS['user_info'];
        $root = get_baseroot();
        $root['session_id'] = es_session::id();
        $friend_id = base64_decode($GLOBALS['request']['friend_id']);
        $redis = new Redis();
        //$redis->connect('127.0.0.1', 6379);
        $redis->connect(REDIS_HOST, REDIS_PORT);
        $redis->auth(REDIS_PWD);
        $redis->select(8);
        if(empty($friend_id)){
            $root['response_code'] = 0;
            $root['msg_err'] = '参数错误';
            output($root);
        }
        if(empty($user['id'])){
            $root['response_code'] = 0;
            $root['user_login_status'] = 0;
            $root['show_err'] = '未登录';
            output($root);
        }
        $user_type = $GLOBALS['db']->getOne("select user_type from ".DB_PREFIX."user where id=".$friend_id);
        if($user_type){
            $root['response_code'] = 0;
            $root['msg_err'] = '无法添加企业用户';
            output($root);
        }
        
        $is_my_friend = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."red_packet_friends where user_id=".$user['id']." and friend_id=".$friend_id);
        $is_he_friend = $GLOBALS['db']->getRow("select id from ".DB_PREFIX."red_packet_friends where user_id=".$friend_id." and friend_id=".$user['id']);
        
        if(!$is_my_friend && !$is_he_friend){
            $data['user_id'] = $user['id'];
            $data['friend_id'] = $friend_id;
            $data['addtime'] = time();
            $rs = $GLOBALS['db']->autoExecute(DB_PREFIX."red_packet_friends",$data,"INSERT");
            $data['user_id'] = $friend_id;
            $data['friend_id'] = $user['id'];
            $data['addtime'] = time();
            $rs = $GLOBALS['db']->autoExecute(DB_PREFIX."red_packet_friends",$data,"INSERT");

            $my = json_decode($redis->hGet(REDIS_PREFIX.'red_friends_list',$user['id']),true);
            $my[] = array("friend_id"=>$friend_id);
            $redis->hset(REDIS_PREFIX."red_friends_list",$user['id'],json_encode($my));

            $he = json_decode($redis->hGet(REDIS_PREFIX.'red_friends_list',$friend_id),true);
            $he[] = array("friend_id"=>$user['id']);
            $redis->hset(REDIS_PREFIX."red_friends_list",$friend_id,json_encode($he));

        }elseif($is_my_friend && $is_my_friend['status'] == 1){
            $rs = $GLOBALS['db']->query("update ".DB_PREFIX."red_packet_friends set status=0,deltime=null where id=".$is_my_friend['id']);
            $my = json_decode($redis->hGet(REDIS_PREFIX.'red_friends_list',$user['id']),true);
            $my[] = array("friend_id"=>$friend_id);
            $redis->hset(REDIS_PREFIX."red_friends_list",$user['id'],json_encode($my));
        }elseif(!$is_my_friend){
            $data['user_id'] = $user['id'];
            $data['friend_id'] = $friend_id;
            $data['addtime'] = time();
            $rs = $GLOBALS['db']->autoExecute(DB_PREFIX."red_packet_friends",$data,"INSERT");

            $my = json_decode($redis->hGet(REDIS_PREFIX.'red_friends_list',$user['id']),true);
            $my[] = array("friend_id"=>$friend_id);
            $redis->hset(REDIS_PREFIX."red_friends_list",$user['id'],json_encode($my));
        }
        if($rs){
            $root['response_code'] = 1;
            $root['show_err'] = '添加成功';
        }else{
            $root['response_code'] = 0;
            $root['show_err'] = '添加失败，请稍后再试';
        }
        output($root);
    }

}