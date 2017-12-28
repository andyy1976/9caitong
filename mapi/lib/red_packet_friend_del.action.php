<?php

class red_packet_friend_del{

    public function index(){

        $root= get_baseroot();
        $user = $GLOBALS['user_info'];
        $root['session_id'] = es_session::id();
        $friend_id = strim(base64_decode($GLOBALS['request']['friend_id']));
        $redis = new Redis();
        $redis->connect(REDIS_HOST, REDIS_PORT);
        $redis->auth(REDIS_PWD);
        $redis->select(8);

        if($user['id']>0){
            if($user['id']==$friend_id){
                $root['response_code'] = 0;
                $root['show_err'] = '不能删除自己';
                output($root);
            }
            $friend = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."red_packet_friends where user_id = ".$user['id']." and friend_id=".$friend_id." and status=0");
            if($friend){
                $res = $GLOBALS['db']->query("update ".DB_PREFIX."red_packet_friends set status = 1,deltime = ".time()." where user_id = ".$user['id']." and friend_id=".$friend_id);
                $red_data_log['user_id'] = $user['id'];
                $red_data_log['red_money'] = 0;
                $red_data_log['addtime'] = date('Y-m-d H:i:s');
                $red_data_log['remark'] = '删除好友成功';
                $resl = $GLOBALS['db']->autoExecute(DB_PREFIX."red_packet_log",$red_data_log,"INSERT");
                $my = json_decode($redis->hGet(REDIS_PREFIX.'red_friends_list',$user['id']),true);
                foreach ($my as $k=>$v){
                    if($v['friend_id']==$friend_id){
                        unset($my[$k]);
                    }
                }
                $redis->hset(REDIS_PREFIX."red_friends_list",$user['id'],json_encode($my));
                if($res && $resl){

                    $root['response_code'] = 1;
                    $root['show_err'] = '删除成功';
                    output($root);
                }else{
                    $root['response_code'] = 0;
                    $root['show_err'] = '删除失败';
                    output($root);
                }

            }else{
                $root['response_code'] = 0;
                $root['show_err'] = '好友不存在或已被删除';
                output($root);
            }
        }else{
            $root['response_code'] = 0;
            $root['user_login_status'] = 0;
            $root['show_err'] = '未登录';
            output($root);
        }
    }
}