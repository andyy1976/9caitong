<?php

/**
 * 抢陌生人红包接口
 * Class red_packet_stranger
 */

class red_packet_stranger{

    public function index(){
        $user = $GLOBALS['user_info'];
        $root = get_baseroot();
        $root['session_id'] = es_session::id();
        if($user['id']>0){
            $redis = new redis();
            //$redis->connect('127.0.0.1', 6379);
            $redis->connect(REDIS_HOST, REDIS_PORT);
            $redis->auth(REDIS_PWD);
            $redis->select(8);
            $red_conf = $redis->hGetAll(REDIS_PREFIX.'red_packet_config');
            $send_num = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."red_packet_send where `type`=1 and send_time>=".strtotime(date('Y-m-d')));
            $rob_num = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."red_packet_rob where `type`=1 and rob_time>=".strtotime(date('Y-m-d')));
            $surplus_num = $send_num*$red_conf['red_num']-$rob_num;
            $root['stranger_num']="还有".($surplus_num+1000)."个红包可以偷，快来偷吧！";
            $root['response_code'] = 1;
            $refresh_time = $redis->hGet(REDIS_PREFIX.'refresh_time',$user['id']);
            if(!$refresh_time){
                $friend_list_all = $this->lists($redis,$user['id']);

                foreach ($friend_list_all as $k=>$v){
                    $info[] = $this->stranger_infos($redis,$user['id'],$v);
                    $sort[] = $info[$k]['red_money_total'];
                }
                $root['friends_list'] = $info;

                $root['times'] = time();
                $root['now_time'] = time();
                $root['end_time'] = time()+$red_conf['strange_refresh_time']*60;
                $redis->hSet(REDIS_PREFIX.'refresh_time',$user['id'],time());
                $redis->hSet(REDIS_PREFIX.'stranger_list',$user['id'],json_encode($friend_list_all));
            }else{
                if(time() - $refresh_time > $red_conf['strange_refresh_time']*60){
                    $friend_list_all = $this->lists($redis,$user['id']);

                    foreach ($friend_list_all as $k=>$v){
                        $info[] = $this->stranger_infos($redis,$user['id'],$v);
                        $sort[] = $info[$k]['red_money_total'];
                    }
                    $root['friends_list'] = $info;
                    $root['times'] = time();
                    $root['now_time'] = time();
                    $root['end_time'] = time()+$red_conf['strange_refresh_time']*60;
                    $redis->hSet(REDIS_PREFIX.'refresh_time',$user['id'],time());
                    $redis->hSet(REDIS_PREFIX.'stranger_list',$user['id'],json_encode($friend_list_all));
                }else{
                    $friends_list = json_decode($redis->hGet(REDIS_PREFIX.'stranger_list',$user['id']),true);
                    foreach ($friends_list as $k=>$v){
                        $info[] = $this->stranger_infos($redis,$user['id'],$friends_list[$k]);
                        $sort[] = $info[$k]['red_money_total'];
                        $self_rob = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."red_packet_rob where red_packet_id=".$v['packet_status']['id']." and user_id=".$user['id']);
                        if($self_rob){
                            $info[$k]['packet_status']['red_packet_status']=2;
                        }
                        $rob_info = $redis->lLen(REDIS_PREFIX.'rob_list'.$v['packet_status']['id']);
                        if($rob_info>=$red_conf['strange_people_num']){
                            $info[$k]['packet_status']['red_packet_status']=3;
                        }

                    }

                    $root['friends_list'] = $info;
                    $root['times'] = $refresh_time;
                    $root['now_time'] = time();
                    $root['end_time'] = $refresh_time+$red_conf['strange_refresh_time']*60;
                }
            }

            array_multisort($sort,SORT_DESC,$info);
            output($root);

        }else{
            $root['response_code'] = 0;
            $root['user_login_status'] = 0;
            $root['show_err'] = '未登录';
            output($root);
        }
    }

    /**
     * @param $user
     * @param $monday_time 本周一零点
     * @param $next_monday_time 下周一零点
     * @param $limit 取多少条
     * @return mixed
     * @author:zhuxiang
     */
    //private function lists($user,$monday_time,$next_monday_time,$two_times_ago,$today_times){
    private function lists($redis,$user_id){
        $red_conf = $redis->hGetAll(REDIS_PREFIX.'red_packet_config');
        $two_times_ago = time()-$red_conf['strange_see_time']*60;//time()-2*3600;  //两小时以前
        $today_times = strtotime(date('Y-m-d'));
        //当天两小时前所有人发的红包
        $red_packet_friends = $GLOBALS['db']->getAll("select id,user_id,send_red_money,red_num from ".DB_PREFIX."red_packet_send where `type`=1 and send_time<=".$two_times_ago." and send_time >=".$today_times." order by rand() limit 10");
        if(empty($red_packet_friends)){
            return false;
        }
        //用户的好友
        $my_friends = json_decode($redis->hGet(REDIS_PREFIX."red_friends_list",$user_id),true);
        $ids = array();
        foreach ($my_friends as $v){
            $ids[] = $v['friend_id'];
        }

        foreach ($red_packet_friends as $k=>$v) {
            if (in_array($v['user_id'], $ids)) {
                unset($red_packet_friends[$k]);
                continue;
            }
        }
        return $red_packet_friends;

    }

    private function stranger_infos($redis,$user_id,$arr){
        //陌生人信息

        $stranger_info = json_decode($redis->hGet(REDIS_PREFIX."user_info",$arr['user_id']),true);

        if(empty($strnger_info)){
            $stranger_info = $GLOBALS['db']->getRow("select id,header_url,real_name as realname,mobile from ".DB_PREFIX."user where id=".$arr['user_id']);
            $stranger_info['mobile'] = cut_str($stranger_info['mobile'], 3, 0).'****'.cut_str($stranger_info['mobile'], 2, -2);
            $redis->hSet(REDIS_PREFIX.'user_info',$arr['user_id'],json_encode($stranger_info));
        }
        $stranger_info['realname'] = $stranger_info['realname']?'*'.cut_str($stranger_info['realname'], 1, -1):'';
        if($user_id == $arr['user_id']){
            $strnger_info['realname'] = '我';
            $strnger_info['mobile'] = '我';
        }

        $red_money_total  = $redis->zScore(REDIS_PREFIX."red_money_total",$arr['user_id']);
        $rob = $GLOBALS['db']->getOne("select rob_red_money from ".DB_PREFIX."red_packet_rob where red_packet_id=".$arr['id']." and user_id = ".$user_id);
        if($rob){
            $packet_s['red_packet_status']=2;//已抢过
            $packet_s['id']=$arr['id'];
            $packet_s['source'] =1;
        }else{
            $rob_num = $GLOBALS['db']->getRow("select count(*) as num , SUM(rob_red_money) as total from ".DB_PREFIX."red_packet_rob where red_packet_id=".$arr['id'] );
            if($rob_num['total'] >= $arr['send_red_money'] || $rob_num['num'] >= $arr['red_num']){
                $packet_s['red_packet_status']=3;//已抢光
                $packet_s['id']=$arr['id'];
                $packet_s['source'] =1;

            }else{
                $packet_s['red_packet_status']=1;
                $packet_s['id']=$arr['id'];
                $packet_s['source'] =1;
            }
        }
        $stranger['friends_info'] = $stranger_info;
        $stranger['red_money_total'] =strval(sprintf("%.2f", $red_money_total));
        $stranger['packet_status'] = $packet_s;
        return $stranger;
    }


}